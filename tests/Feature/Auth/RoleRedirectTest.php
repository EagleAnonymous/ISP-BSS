<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin is redirected to the admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('technical staff is redirected to the staff dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('technical_staff');

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('staff.dashboard', absolute: false));
});

test('subscriber is redirected to the subscriber dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('subscriber.dashboard', absolute: false));
});

test('a user with no role is rejected back to login and not authenticated', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    $this->assertGuest();
    $response->assertRedirect(route('login', absolute: false));
    $response->assertSessionHasErrors('email');
});

test('a subscriber gets a 403 when visiting the admin dashboard directly', function () {
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertForbidden();
});

test('an admin gets a 403 when visiting the subscriber dashboard directly', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/subscriber/dashboard');

    $response->assertForbidden();
});
