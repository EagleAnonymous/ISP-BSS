<?php

use App\Jobs\ProcessOverdueInvoice;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use App\Models\InvoiceReminder;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\OverdueReminder;
use App\Notifications\ServiceSuspended;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

function makeOverdueSubscriber(int $daysOverdue, string $status = 'active'): array
{
    $plan = Plan::create(['name' => 'Overdue-'.uniqid(), 'speed' => '50 Mbps', 'price' => 1499.00, 'billing_cycle' => 'monthly']);

    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $subscriber = Subscriber::create([
        'user_id' => $user->id,
        'subscriber_id' => 'SUB-'.uniqid(),
        'plan_id' => $plan->id,
        'status' => $status,
        'joined_date' => now()->subMonths(2)->toDateString(),
    ]);

    $subscription = Subscription::create([
        'subscriber_id' => $subscriber->id,
        'plan_id' => $plan->id,
        'locked_price' => 1499.00,
        'status' => 'active',
        'starts_at' => now()->subMonths(2)->toDateString(),
    ]);

    $invoice = Invoice::create([
        'subscriber_id' => $subscriber->id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'amount' => 1499.00,
        'billing_period_start' => now()->subMonth()->startOfMonth()->toDateString(),
        'billing_period_end' => now()->subMonth()->endOfMonth()->toDateString(),
        'due_date' => now()->subDays($daysOverdue)->toDateString(),
        'status' => 'unpaid',
    ]);

    return [$subscriber, $subscription, $invoice];
}

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('the scheduled command dispatches processing only for currently overdue invoices', function () {
    Bus::fake();

    [, , $overdueInvoice] = makeOverdueSubscriber(5);
    [, , $currentInvoice] = makeOverdueSubscriber(-5);

    Artisan::call('billing:process-overdue');

    Bus::assertDispatchedTimes(ProcessOverdueInvoice::class, 1);
    Bus::assertDispatched(ProcessOverdueInvoice::class, fn ($job) => $job->invoice->is($overdueInvoice));
});

test('a reminder is sent and recorded once an invoice crosses a configured tier', function () {
    Notification::fake();

    [$subscriber, , $invoice] = makeOverdueSubscriber(3);

    (new ProcessOverdueInvoice($invoice))->handle();

    Notification::assertSentTo($subscriber->user, OverdueReminder::class);
    expect(InvoiceReminder::where('invoice_id', $invoice->id)->pluck('days_overdue')->all())->toBe([3]);
});

test('running the job again does not resend a reminder already recorded for that tier', function () {
    Notification::fake();

    [$subscriber, , $invoice] = makeOverdueSubscriber(3);

    (new ProcessOverdueInvoice($invoice))->handle();
    (new ProcessOverdueInvoice($invoice))->handle();

    Notification::assertSentToTimes($subscriber->user, OverdueReminder::class, 1);
    expect(InvoiceReminder::where('invoice_id', $invoice->id)->count())->toBe(1);
});

test('every reminder tier already crossed fires in a single run', function () {
    Notification::fake();

    [$subscriber, , $invoice] = makeOverdueSubscriber(10);

    (new ProcessOverdueInvoice($invoice))->handle();

    Notification::assertSentToTimes($subscriber->user, OverdueReminder::class, 2);
    expect(InvoiceReminder::where('invoice_id', $invoice->id)->pluck('days_overdue')->sort()->values()->all())->toBe([3, 7]);
});

test('a late fee is applied once an invoice reaches the configured threshold, and never duplicated', function () {
    [, , $invoice] = makeOverdueSubscriber(16);

    (new ProcessOverdueInvoice($invoice))->handle();
    (new ProcessOverdueInvoice($invoice))->handle();

    $lateFees = InvoiceAdjustment::where('invoice_id', $invoice->id)
        ->where('reason', 'Automatic late fee (system)')
        ->get();

    expect($lateFees)->toHaveCount(1);
    expect((float) $lateFees->first()->amount)->toBe(200.00);
    expect($lateFees->first()->created_by)->toBe(User::systemUser()->id);
    expect((float) $invoice->fresh()->load('adjustments')->amount_due)->toBe(1699.00);
});

test('a subscriber is automatically suspended after the grace period and notified once', function () {
    Notification::fake();

    [$subscriber, , $invoice] = makeOverdueSubscriber(31);

    (new ProcessOverdueInvoice($invoice))->handle();
    (new ProcessOverdueInvoice($invoice))->handle();

    expect($subscriber->fresh()->status)->toBe('suspended');
    Notification::assertSentToTimes($subscriber->user, ServiceSuspended::class, 1);

    $logs = ActivityLog::where('action', 'subscriber.auto_suspended')
        ->where('subject_type', Subscriber::class)
        ->where('subject_id', $subscriber->id)
        ->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->user_id)->toBeNull();
});

test('a paid invoice is left untouched even if its due date already passed', function () {
    [$subscriber, , $invoice] = makeOverdueSubscriber(40);
    $invoice->update(['status' => 'paid', 'paid_at' => now()]);

    (new ProcessOverdueInvoice($invoice))->handle();

    expect(InvoiceReminder::where('invoice_id', $invoice->id)->count())->toBe(0);
    expect(InvoiceAdjustment::where('invoice_id', $invoice->id)->count())->toBe(0);
    expect($subscriber->fresh()->status)->toBe('active');
});

test('an invoice that is not yet due triggers no automated action', function () {
    [, , $invoice] = makeOverdueSubscriber(-2);

    (new ProcessOverdueInvoice($invoice))->handle();

    expect(InvoiceReminder::where('invoice_id', $invoice->id)->count())->toBe(0);
});