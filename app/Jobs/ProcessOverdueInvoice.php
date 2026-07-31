<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Subscriber;
use App\Models\User;
use App\Notifications\OverdueReminder;
use App\Notifications\ServiceSuspended;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessOverdueInvoice implements ShouldQueue
{
    use Queueable;

    /**
     * If a step below throws (a database hiccup, a race with another run),
     * Laravel will retry the whole job up to 3 times, waiting a bit longer
     * between each attempt.
     */
    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public Invoice $invoice)
    {
    }

    /**
     * Apply escalating overdue rules to a single invoice: tiered reminder
     * emails, then a one-time late fee, then subscriber suspension. Every
     * step is idempotent, so re-running this for the same invoice on a
     * later day only applies whatever tier has newly been reached.
     *
     * Each step is wrapped in `rescue()` so that if one step fails, the
     * others still get a chance to run instead of the whole invoice being
     * skipped for the day (the failure is still logged, just not fatal).
     */
    public function handle(): void
    {
        $this->invoice->load(['adjustments', 'reminders', 'subscriber.user']);

        if ($this->invoice->effective_status !== 'overdue') {
            return;
        }

        // Carbon 3 returns a signed diff (due date is in the past relative to now), hence abs().
        $daysOverdue = (int) abs(now()->startOfDay()->diffInDays($this->invoice->due_date->startOfDay()));
        $rules = config('billing.overdue');

        rescue(fn () => $this->sendDueReminders($daysOverdue, $rules['reminder_days']));
        rescue(fn () => $this->applyLateFee($daysOverdue, $rules['late_fee']));
        rescue(fn () => $this->suspendIfNeeded($daysOverdue, $rules['suspend_after_days']));
    }

    /**
     * @param  array<int, int>  $tiers
     */
    private function sendDueReminders(int $daysOverdue, array $tiers): void
    {
        $alreadySent = $this->invoice->reminders->pluck('days_overdue')->all();

        foreach ($tiers as $tier) {
            if ($daysOverdue < $tier || in_array($tier, $alreadySent, true)) {
                continue;
            }

            // The invoice_reminders table also has a unique (invoice_id,
            // days_overdue) constraint as a backstop, in case two runs of
            // this job somehow overlap for the same invoice.
            $this->invoice->reminders()->create([
                'days_overdue' => $tier,
                'sent_at' => now(),
            ]);

            $this->invoice->subscriber->user->notify(new OverdueReminder($this->invoice, $daysOverdue));
        }
    }

    /**
     * @param  array{after_days: int, amount: float}  $rule
     */
    private function applyLateFee(int $daysOverdue, array $rule): void
    {
        if ($daysOverdue < $rule['after_days']) {
            return;
        }

        DB::transaction(function () use ($rule) {
            // Lock this invoice row for the rest of the transaction so that
            // if this job somehow runs twice for the same invoice at once,
            // the second run waits, sees the fee already applied, and does
            // nothing — instead of both runs charging the fee.
            $locked = Invoice::where('id', $this->invoice->id)->lockForUpdate()->firstOrFail();

            $alreadyCharged = $locked->adjustments()
                ->where('type', 'charge')
                ->where('reason', self::LATE_FEE_REASON)
                ->exists();

            if ($alreadyCharged) {
                return;
            }

            $locked->adjustments()->create([
                'type' => 'charge',
                'amount' => $rule['amount'],
                'reason' => self::LATE_FEE_REASON,
                'created_by' => User::systemUser()->id,
            ]);

            ActivityLog::record(
                'invoice.late_fee_applied',
                $this->invoice,
                'System applied a ₱'.number_format($rule['amount'], 2).' late fee.'
            );
        });
    }

    private function suspendIfNeeded(int $daysOverdue, int $suspendAfterDays): void
    {
        if ($daysOverdue < $suspendAfterDays) {
            return;
        }

        $subscriber = DB::transaction(function () {
            // Same locking idea as the late fee above: only one concurrent
            // run should be able to suspend (and notify) this subscriber.
            $locked = Subscriber::where('id', $this->invoice->subscriber_id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'suspended') {
                return null;
            }

            $locked->update(['status' => 'suspended']);

            ActivityLog::record(
                'subscriber.auto_suspended',
                $locked,
                'System suspended the subscriber for non-payment of invoice '.$this->invoice->invoice_number.'.'
            );

            return $locked;
        });

        if ($subscriber !== null) {
            $subscriber->user->notify(new ServiceSuspended($this->invoice, $daysOverdue));
        }
    }

    private const LATE_FEE_REASON = 'Automatic late fee (system)';
}