<?php

use App\Models\TechnicalStaff;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function makeStaffUser(string $name = 'Jane Technician', string $email = 'jane@example.com'): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => $email,
        'employee_id' => 'EMP-100',
    ]);
    $user->assignRole('technical_staff');
    TechnicalStaff::create([
        'user_id' => $user->id,
        'phone' => '09171234567',
        'position' => 'Field Technician',
        'department' => 'Network Operations',
        'supervisor' => 'John Smith',
    ]);

    return $user;
}

test('staff can view their My Account panel', function () {
    $user = makeStaffUser();

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('My Account')
        ->assertSee('Jane Technician')
        ->assertSee('Technical Staff')
        ->assertSee('EMP-100')
        ->assertSee('Network Operations')
        ->assertSee('John Smith')
        ->assertSee('My Schedule')
        ->assertSee('Edit Information')
        ->assertSee('Save Changes')
        ->assertSee('Cancel');
});

test('staff can save an editable field and it syncs to the database', function () {
    $user = makeStaffUser();

    $this->actingAs($user)->patchJson(route('profile.update-field'), [
        'field' => 'name',
        'name' => 'Jane Q. Technician',
    ])->assertOk()->assertSee('success');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Jane Q. Technician']);
});

test('staff can update their email and it syncs to the database', function () {
    $user = makeStaffUser();

    $this->actingAs($user)->patchJson(route('profile.update-field'), [
        'field' => 'email',
        'email' => 'jane.updated@example.com',
    ])->assertOk();

    $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'jane.updated@example.com']);
});

test('staff can update their phone number and it syncs to the technical_staff table', function () {
    $user = makeStaffUser();

    $this->actingAs($user)->patchJson(route('profile.update-field'), [
        'field' => 'phone',
        'phone' => '09998887777',
    ])->assertOk();

    $this->assertDatabaseHas('technical_staff', ['user_id' => $user->id, 'phone' => '09998887777']);
});

test('system fields cannot be edited: employee id, role, department, supervisor', function () {
    $user = makeStaffUser();

    foreach (['employee_id', 'role', 'department', 'supervisor'] as $field) {
        $value = $field === 'employee_id' ? 'EMP-999' : 'hacked';
        $this->actingAs($user)->patchJson(route('profile.update-field'), [
            'field' => $field,
            $field => $value,
        ])->assertStatus(422);

        // Nothing was persisted.
        if ($field === 'employee_id') {
            $this->assertDatabaseMissing('users', ['id' => $user->id, 'employee_id' => 'EMP-999']);
        } else {
            $technicalStaff = $user->technicalStaff;
            $this->assertNotEquals($value, $technicalStaff->{$field});
        }
    }
});

test('non-staff roles cannot reach the profile account page through staff-only concerns', function () {
    $admin = User::factory()->create(['employee_id' => 'ADM-1']);
    $admin->assignRole('admin');

    // Profile page is shared across roles, but admin view should not leak staff fields.
    $this->actingAs($admin)->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('My Schedule')
        ->assertDontSee('Department');
});
