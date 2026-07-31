<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'subscriber_id', 'subscription_id', 'invoice_number', 'amount',
    'billing_period_start', 'billing_period_end', 'due_date', 'status', 'paid_at',
])]
class Invoice extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_period_start' => 'date',
            'billing_period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InvoiceAdjustment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(InvoiceReminder::class);
    }

    /**
     * Amount due is always computed from the original billed amount plus
     * every linked adjustment — invoices.amount itself is never mutated.
     */
    protected function amountDue(): Attribute
    {
        return Attribute::get(function () {
            $charges = $this->adjustments->where('type', 'charge')->sum('amount');
            $credits = $this->adjustments->where('type', 'credit')->sum('amount');

            return round((float) $this->amount + (float) $charges - (float) $credits, 2);
        });
    }

    /**
     * "Overdue" is derived (unpaid + due date passed), not a stored status,
     * so it's always correct without a scheduled job to flip it.
     *
     * An invoice only counts as overdue starting the day AFTER its due
     * date — the due date itself is still "current." This must stay in
     * sync with scopeOverdue()/scopeStatus() below, which the Overdue tab
     * and the automated reminder/late-fee/suspension job both rely on; if
     * this ever drifted out of sync with those, the badge shown here could
     * disagree with what the automation actually does.
     */
    protected function effectiveStatus(): Attribute
    {
        return Attribute::get(function () {
            if ($this->status === 'unpaid' && $this->due_date->isBefore(Carbon::today())) {
                return 'overdue';
            }

            return $this->status;
        });
    }

    /**
     * Filter by the user-facing status, including the derived "overdue" state.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'paid' => $query->where('status', 'paid'),
            'overdue' => $query->where('status', 'unpaid')->whereDate('due_date', '<', Carbon::today()),
            'unpaid' => $query->where('status', 'unpaid')->whereDate('due_date', '>=', Carbon::today()),
            default => $query,
        };
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'unpaid')->whereDate('due_date', '<', Carbon::today());
    }
}