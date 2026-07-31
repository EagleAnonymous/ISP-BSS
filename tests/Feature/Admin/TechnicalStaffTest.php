<?php

use App\Models\TechnicalStaff;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view the technical staff list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.technical-staff.index'));

    $response->assertOk();
});

test('admin can create a technical staff account with credentials', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.technical-staff.store'), [
        'name' => 'Jamie Cruz',
        'email' => 'jamie@example.com',
        'phone' => '09171234567',
        'position' => 'Network Technician',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $response->assertRedirect(route('admin.technical-staff.index'));

    $user = User::where('email', 'jamie@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->hasRole('technical_staff'))->toBeTrue();

    $staff = TechnicalStaff::where('user_id', $user->id)->first();

    expect($staff)->not->toBeNull();
    expect($staff->phone)->toBe('09171234567');
    expect($staff->position)->toBe('Network Technician');
});

test('the new technical staff account can log in and lands on the staff dashboard', function () {
    $this->actingAs($this->admin)->post(route('admin.technical-staff.store'), [
        'name' => 'Jamie Cruz',
        'email' => 'jamie@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ]);

    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => 'jamie@example.com',
        'password' => 'Password123',
    ]);

    $response->assertRedirect(route('staff.dashboard', absolute: false));
});

test('a non-admin cannot access the technical staff pages', function () {
    $subscriber = User::factory()->create();
    $subscriber->assignRole('subscriber');

    $this->actingAs($subscriber)->get(route('admin.technical-staff.index'))->assertForbidden();
    $this->actingAs($subscriber)->post(route('admin.technical-staff.store'), [])->assertForbidden();
});

test('technical staff creation requires matching password confirmation', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.technical-staff.store'), [
        'name' => 'Jamie Cruz',
        'email' => 'jamie@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Nope',
    ]);

    $response->assertSessionHasErrors('password');
    expect(User::where('email', 'jamie@example.com')->exists())->toBeFalse();
});
