<?php

use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->plan = Plan::create([
        'name' => 'Standard',
        'speed' => '50 Mbps',
        'price' => 1499.00,
        'billing_cycle' => 'monthly',
    ]);
});

test('admin can view the subscriber list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.subscribers.index'));

    $response->assertOk();
});

test('admin can create a subscriber account with credentials', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.subscribers.store'), [
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'contact' => '09171234567',
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'joined_date' => '2026-01-15',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response->assertRedirect(route('admin.subscribers.index'));

    $user = User::where('email', 'alex@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('subscriber'))->toBeTrue();

    $subscriber = Subscriber::where('user_id', $user->id)->first();
    expect($subscriber)->not->toBeNull();
    expect($subscriber->subscriber_id)->toBe('SUB-00001');
    expect($subscriber->contact)->toBe('09171234567');
    expect($subscriber->plan_id)->toBe($this->plan->id);
    expect($subscriber->status)->toBe('active');
    expect($subscriber->joined_date->toDateString())->toBe('2026-01-15');
});

test('subscriber ids increment sequentially', function () {
    $this->actingAs($this->admin)->post(route('admin.subscribers.store'), [
        'name' => 'First Sub', 'email' => 'first@example.com',
        'plan_id' => $this->plan->id, 'status' => 'active', 'joined_date' => '2026-01-01',
        'password' => 'Password123', 'password_confirmation' => 'Password123',
    ]);

    $this->actingAs($this->admin)->post(route('admin.subscribers.store'), [
        'name' => 'Second Sub', 'email' => 'second@example.com',
        'plan_id' => $this->plan->id, 'status' => 'active', 'joined_date' => '2026-01-01',
        'password' => 'Password123', 'password_confirmation' => 'Password123',
    ]);

    expect(Subscriber::pluck('subscriber_id')->sort()->values()->all())
        ->toBe(['SUB-00001', 'SUB-00002']);
});

test('the new subscriber account can log in and lands on the subscriber dashboard', function () {
    $this->actingAs($this->admin)->post(route('admin.subscribers.store'), [
        'name' => 'Alex Rivera', 'email' => 'alex@example.com',
        'plan_id' => $this->plan->id, 'status' => 'active', 'joined_date' => '2026-01-15',
        'password' => 'Password123', 'password_confirmation' => 'Password123',
    ]);

    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => 'alex@example.com',
        'password' => 'Password123',
    ]);

    $response->assertRedirect(route('subscriber.dashboard', absolute: false));
});

test('a non-admin cannot access the subscriber pages', function () {
    $staff = User::factory()->create();
    $staff->assignRole('technical_staff');

    $this->actingAs($staff)->get(route('admin.subscribers.index'))->assertForbidden();
    $this->actingAs($staff)->post(route('admin.subscribers.store'), [])->assertForbidden();
});

test('subscriber creation requires a valid plan and status', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.subscribers.store'), [
        'name' => 'Alex Rivera',
        'email' => 'alex@example.com',
        'plan_id' => 999999,
        'status' => 'archived',
        'joined_date' => '2026-01-15',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response->assertSessionHasErrors(['plan_id', 'status']);
    expect(User::where('email', 'alex@example.com')->exists())->toBeFalse();
});
