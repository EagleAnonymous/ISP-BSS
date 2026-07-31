<?php

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\OverdueReminder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;

function makeSubscriberWithInvoice(array $invoiceOverrides = [], array $subscriptionOverrides = []): array
{
    $plan = Plan::create(['name' => 'Standard-'.uniqid(), 'speed' => '50 Mbps', 'price' => 1499.00, 'billing_cycle' => 'monthly']);

    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $subscriber = Subscriber::create([
        'user_id' => $user->id,
        'subscriber_id' => 'SUB-'.uniqid(),
        'plan_id' => $plan->id,
        'status' => 'active',
        'joined_date' => now()->subMonths(2)->toDateString(),
    ]);

    $subscription = Subscription::create(array_merge([
        'subscriber_id' => $subscriber->id,
        'plan_id' => $plan->id,
        'locked_price' => 1499.00,
        'status' => 'active',
        'starts_at' => now()->subMonths(2)->toDateString(),
    ], $subscriptionOverrides));

    $invoice = Invoice::create(array_merge([
        'subscriber_id' => $subscriber->id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'INV-TEST-'.uniqid(),
        'amount' => 1499.00,
        'billing_period_start' => now()->startOfMonth()->toDateString(),
        'billing_period_end' => now()->endOfMonth()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => 'unpaid',
    ], $invoiceOverrides));

    return [$subscriber, $subscription, $invoice];
}

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

// --- 1. System-wide invoice oversight ---------------------------------

test('admin can view all invoices system-wide', function () {
    [, , $invoiceA] = makeSubscriberWithInvoice();
    [, , $invoiceB] = makeSubscriberWithInvoice();

    $response = $this->actingAs($this->admin)->get(route('admin.billing.index'));

    $response->assertOk();
    $response->assertSee($invoiceA->invoice_number);
    $response->assertSee($invoiceB->invoice_number);
});

test('billing index filters by status', function () {
    [, , $paid] = makeSubscriberWithInvoice(['status' => 'paid', 'paid_at' => now()]);
    [, , $unpaid] = makeSubscriberWithInvoice(['status' => 'unpaid', 'due_date' => now()->addDays(10)]);

    $response = $this->actingAs($this->admin)->get(route('admin.billing.index', ['status' => 'paid']));

    $response->assertSee($paid->invoice_number);
    $response->assertDontSee($unpaid->invoice_number);
});

test('billing index filters by subscriber name search', function () {
    $userA = User::factory()->create(['name' => 'Alexandra Cruz']);
    $userA->assignRole('subscriber');
    [, , $invoiceOther] = makeSubscriberWithInvoice();

    $response = $this->actingAs($this->admin)->get(route('admin.billing.index', ['search' => 'Alexandra']));

    $response->assertOk();
    $response->assertDontSee($invoiceOther->invoice_number);
});

test('a non-admin cannot access the billing pages', function () {
    $subscriberUser = User::factory()->create();
    $subscriberUser->assignRole('subscriber');

    $this->actingAs($subscriberUser)->get(route('admin.billing.index'))->assertForbidden();
});

// --- 2. Append-only adjustments ----------------------------------------

test('adding an adjustment creates a row and never touches invoices.amount', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['amount' => 1000.00]);

    $this->actingAs($this->admin)->post(route('admin.billing.adjustments.store', $invoice), [
        'type' => 'charge',
        'amount' => 150.00,
        'reason' => 'Late fee',
    ]);

    expect(InvoiceAdjustment::where('invoice_id', $invoice->id)->count())->toBe(1);
    expect((float) $invoice->fresh()->amount)->toBe(1000.00);
});

test('amount due is computed live from original amount plus adjustments', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['amount' => 1000.00]);

    $this->actingAs($this->admin)->post(route('admin.billing.adjustments.store', $invoice), [
        'type' => 'charge', 'amount' => 200.00, 'reason' => 'Late fee',
    ]);

    $this->actingAs($this->admin)->post(route('admin.billing.adjustments.store', $invoice), [
        'type' => 'credit', 'amount' => 50.00, 'reason' => 'Goodwill credit',
    ]);

    // 1000 + 200 - 50 = 1150
    expect((float) $invoice->fresh()->load('adjustments')->amount_due)->toBe(1150.00);
});

test('adjustment history page shows original amount, each entry, and the running total', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['amount' => 1000.00]);
    $this->actingAs($this->admin)->post(route('admin.billing.adjustments.store', $invoice), [
        'type' => 'charge', 'amount' => 200.00, 'reason' => 'Late fee',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.billing.adjustments.history', $invoice));

    $response->assertOk();
    $response->assertSee('Late fee');
    $response->assertSee('1,200.00'); // amount due after the charge
});

// --- 3. Manual payment recording ----------------------------------------

test('marking an invoice paid creates a real payments row and updates invoice status', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['amount' => 1499.00]);

    $response = $this->actingAs($this->admin)->post(route('admin.billing.mark-paid', $invoice), [
        'method' => 'cash',
        'reference_number' => 'RCPT-001',
    ]);

    $response->assertRedirect(route('admin.billing.index'));

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->method)->toBe('cash');
    expect((float) $payment->amount)->toBe(1499.00);
    expect($invoice->fresh()->status)->toBe('paid');
});

test('mark-paid only accepts cash or bank_transfer', function () {
    [, , $invoice] = makeSubscriberWithInvoice();

    $response = $this->actingAs($this->admin)->post(route('admin.billing.mark-paid', $invoice), [
        'method' => 'credit_card',
    ]);

    $response->assertSessionHasErrors('method');
});

test('every manual payment recording is written to activity_logs with the acting admin', function () {
    [, , $invoice] = makeSubscriberWithInvoice();

    $this->actingAs($this->admin)->post(route('admin.billing.mark-paid', $invoice), [
        'method' => 'bank_transfer',
    ]);

    $log = ActivityLog::where('subject_type', Invoice::class)->where('subject_id', $invoice->id)->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($this->admin->id);
    expect($log->action)->toBe('invoice.marked_paid');
});

test('an already-paid invoice cannot be marked paid again', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['status' => 'paid', 'paid_at' => now()]);

    $this->actingAs($this->admin)->post(route('admin.billing.mark-paid', $invoice), [
        'method' => 'cash',
    ])->assertStatus(422);

    expect(Payment::where('invoice_id', $invoice->id)->count())->toBe(0);
});

// --- 4. Overdue management & reminders ----------------------------------

test('overdue invoices appear in a dedicated view separate from the general list', function () {
    [, , $overdue] = makeSubscriberWithInvoice(['due_date' => now()->subDays(3), 'status' => 'unpaid']);
    [, , $current] = makeSubscriberWithInvoice(['due_date' => now()->addDays(10), 'status' => 'unpaid']);

    $response = $this->actingAs($this->admin)->get(route('admin.billing.overdue'));

    $response->assertOk();
    $response->assertSee($overdue->invoice_number);
    $response->assertDontSee($current->invoice_number);
});

test('sending a reminder notifies the subscriber without changing invoice or payment data', function () {
    Notification::fake();

    [$subscriber, , $invoice] = makeSubscriberWithInvoice(['due_date' => now()->subDays(2), 'status' => 'unpaid']);
    $originalAmount = $invoice->amount;
    $originalStatus = $invoice->status;

    $this->actingAs($this->admin)->post(route('admin.billing.remind', $invoice));

    Notification::assertSentTo($subscriber->user, OverdueReminder::class);

    $invoice->refresh();
    expect((float) $invoice->amount)->toBe((float) $originalAmount);
    expect($invoice->status)->toBe($originalStatus);
    expect(Payment::where('invoice_id', $invoice->id)->count())->toBe(0);
});

test('remind-all sends a notification to every currently-overdue subscriber', function () {
    Notification::fake();

    [$subA, , $overdueA] = makeSubscriberWithInvoice(['due_date' => now()->subDays(1), 'status' => 'unpaid']);
    [$subB, , $overdueB] = makeSubscriberWithInvoice(['due_date' => now()->subDays(5), 'status' => 'unpaid']);
    [$subC, , $notOverdue] = makeSubscriberWithInvoice(['due_date' => now()->addDays(5), 'status' => 'unpaid']);

    $this->actingAs($this->admin)->post(route('admin.billing.remind-all'));

    Notification::assertSentTo($subA->user, OverdueReminder::class);
    Notification::assertSentTo($subB->user, OverdueReminder::class);
    Notification::assertNotSentTo($subC->user, OverdueReminder::class);
});

// --- 5. Manual & bulk invoice generation --------------------------------

test('generating invoices creates one per active subscription using locked_price', function () {
    $plan = Plan::create(['name' => 'GenPlan-'.uniqid(), 'speed' => '25 Mbps', 'price' => 999.00, 'billing_cycle' => 'monthly']);
    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $subscriber = Subscriber::create([
        'user_id' => $user->id, 'subscriber_id' => 'SUB-'.uniqid(), 'plan_id' => $plan->id,
        'status' => 'active', 'joined_date' => now()->toDateString(),
    ]);
    // locked_price deliberately differs from the plan's current price.
    Subscription::create([
        'subscriber_id' => $subscriber->id, 'plan_id' => $plan->id, 'locked_price' => 799.00,
        'status' => 'active', 'starts_at' => now()->toDateString(),
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.billing.generate'), [
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
    ]);

    $response->assertRedirect(route('admin.billing.index'));

    $invoice = Invoice::where('subscriber_id', $subscriber->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->amount)->toBe(799.00);
});

test('generating invoices for an overlapping period is skipped, not double-billed', function () {
    [$subscriber, , $existing] = makeSubscriberWithInvoice([
        'billing_period_start' => now()->startOfMonth()->toDateString(),
        'billing_period_end' => now()->endOfMonth()->toDateString(),
    ]);

    // A shifted period that overlaps the one already invoiced above.
    $response = $this->actingAs($this->admin)->post(route('admin.billing.generate'), [
        'period_start' => now()->startOfMonth()->addDays(5)->toDateString(),
        'period_end' => now()->endOfMonth()->addDays(5)->toDateString(),
    ]);

    $response->assertRedirect(route('admin.billing.index'));
    expect(session('skipped_count'))->toBeGreaterThan(0);
    expect(Invoice::where('subscriber_id', $subscriber->id)->count())->toBe(1);
});

test('generating invoices twice for the same period produces no duplicates and reports skips', function () {
    makeSubscriberWithInvoice([], []);
    $period = ['period_start' => now()->startOfMonth()->toDateString(), 'period_end' => now()->endOfMonth()->toDateString()];

    $this->actingAs($this->admin)->post(route('admin.billing.generate'), $period);
    $countAfterFirst = Invoice::count();

    $response = $this->actingAs($this->admin)->post(route('admin.billing.generate'), $period);
    $countAfterSecond = Invoice::count();

    expect($countAfterSecond)->toBe($countAfterFirst);
    expect(session('skipped_count'))->toBeGreaterThan(0);
});

// --- 6. Billed vs Collected ----------------------------------------------

test('the billing page shows billed and collected as two separate figures', function () {
    [, , $invoice] = makeSubscriberWithInvoice(['amount' => 1000.00]);
    $this->actingAs($this->admin)->post(route('admin.billing.mark-paid', $invoice), ['method' => 'cash']);

    $response = $this->actingAs($this->admin)->get(route('admin.billing.index'));

    $response->assertViewHas('summary', function ($summary) {
        return array_key_exists('billed', $summary) && array_key_exists('collected', $summary) && array_key_exists('outstanding', $summary);
    });
});

// --- 7. Refunds must not exist -------------------------------------------

test('no refund route exists anywhere in the app', function () {
    $routes = collect(app('router')->getRoutes())->map(fn ($route) => $route->uri());

    expect($routes->contains(fn ($uri) => str_contains(strtolower($uri), 'refund')))->toBeFalse();
});