<?php

use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view the plans list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.plans.index'));

    $response->assertOk();
});

test('admin can create a plan which defaults to active', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.plans.store'), [
        'name' => 'Home Fiber 100',
        'speed' => '100 Mbps',
        'price' => 1999.00,
        'billing_cycle' => 'monthly',
    ]);

    $response->assertRedirect(route('admin.plans.index'));

    $plan = Plan::where('name', 'Home Fiber 100')->first();
    expect($plan)->not->toBeNull();
    expect($plan->is_active)->toBeTrue();
    expect((float) $plan->price)->toBe(1999.00);
    expect($plan->billing_cycle)->toBe('monthly');
});

test('a non-admin cannot access the plans pages', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $this->actingAs($subscriber)->get(route('admin.plans.index'))->assertForbidden();
    $this->actingAs($subscriber)->post(route('admin.plans.store'), [])->assertForbidden();
});

test('plan creation requires a valid billing cycle and unique name', function () {
    Plan::create(['name' => 'Basic', 'speed' => '25 Mbps', 'price' => 999, 'billing_cycle' => 'monthly']);

    $response = $this->actingAs($this->admin)->post(route('admin.plans.store'), [
        'name' => 'Basic',
        'speed' => '25 Mbps',
        'price' => 999,
        'billing_cycle' => 'weekly',
    ]);

    $response->assertSessionHasErrors(['name', 'billing_cycle']);
});

test('the plans index shows inactive plans too, with a live active-subscriber count', function () {
    $active = Plan::create(['name' => 'Standard', 'speed' => '50 Mbps', 'price' => 1499, 'billing_cycle' => 'monthly']);
    $inactive = Plan::create(['name' => 'Legacy', 'speed' => '10 Mbps', 'price' => 599, 'billing_cycle' => 'monthly', 'is_active' => false]);

    // Two active subscribers and one suspended subscriber on the Standard plan.
    foreach (['active', 'active', 'suspended'] as $i => $status) {
        $user = User::factory()->create(['email' => "sub{$i}@example.com"]);
        $user->assignRole('subscriber');
        Subscriber::create([
            'user_id' => $user->id,
            'subscriber_id' => 'SUB-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
            'plan_id' => $active->id,
            'status' => $status,
            'joined_date' => '2026-01-01',
        ]);
    }

    $response = $this->actingAs($this->admin)->get(route('admin.plans.index'));

    $response->assertOk();
    $response->assertSee('Standard');
    $response->assertSee('Legacy');

    $plans = $response->viewData('plans')->keyBy('name');
    expect($plans['Standard']->active_subscribers_count)->toBe(2);
    expect($plans['Legacy']->active_subscribers_count)->toBe(0);
});

test('admin can delete a plan that has no subscribers on it', function () {
    $plan = Plan::create(['name' => 'Unused Plan', 'speed' => '10 Mbps', 'price' => 499, 'billing_cycle' => 'monthly']);

    $response = $this->actingAs($this->admin)->delete(route('admin.plans.destroy', $plan));

    $response->assertRedirect(route('admin.plans.index'));
    expect(Plan::find($plan->id))->toBeNull();
});

test('a plan with subscribers on it cannot be deleted', function () {
    $plan = Plan::create(['name' => 'In Use Plan', 'speed' => '50 Mbps', 'price' => 1499, 'billing_cycle' => 'monthly']);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $subscriber = Subscriber::create([
        'user_id' => $user->id,
        'subscriber_id' => 'SUB-00001',
        'plan_id' => $plan->id,
        'status' => 'active',
        'joined_date' => '2026-01-01',
    ]);
    Subscription::create([
        'subscriber_id' => $subscriber->id,
        'plan_id' => $plan->id,
        'locked_price' => 1499,
        'status' => 'active',
        'starts_at' => '2026-01-01',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.plans.destroy', $plan));

    $response->assertRedirect(route('admin.plans.index'));
    expect(Plan::find($plan->id))->not->toBeNull();
});

test('a non-admin cannot delete a plan', function () {
    $plan = Plan::create(['name' => 'Protected Plan', 'speed' => '50 Mbps', 'price' => 1499, 'billing_cycle' => 'monthly']);

    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $this->actingAs($subscriber)->delete(route('admin.plans.destroy', $plan))->assertForbidden();
    expect(Plan::find($plan->id))->not->toBeNull();
});
