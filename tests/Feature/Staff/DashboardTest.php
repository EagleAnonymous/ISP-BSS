<?php

use App\Models\Subscriber;
use App\Models\TechnicalStaff;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function makeTechnicalStaffUser(string $name = 'Jane Technician'): User
{
    $user = User::factory()->create([
        'name' => $name,
        'employee_id' => 'EMP-'.uniqid(),
    ]);
    $user->assignRole('technical_staff');
    TechnicalStaff::create(['user_id' => $user->id, 'department' => 'Network', 'supervisor' => 'Jane Smith']);

    return $user;
}

test('staff dashboard renders with the staff role and account info', function () {
    $user = makeTechnicalStaffUser();
    $subscriberUser = User::factory()->create(['name' => 'Customer One']);
    $subscriberUser->assignRole('subscriber');
    $subscriber = Subscriber::create([
        'user_id' => $subscriberUser->id,
        'subscriber_id' => 'SUB-1',
        'status' => 'active',
        'joined_date' => now()->toDateString(),
    ]);

    Ticket::create([
        'ticket_number' => 'TCK-00001',
        'subscriber_id' => $subscriber->id,
        'category' => 'no_connection',
        'subject' => 'No internet',
        'description' => 'Down since morning.',
        'priority' => 'high',
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)->get(route('staff.dashboard'));

    $response->assertOk();
    $response->assertSee('Welcome, Jane!');
    $response->assertSee('Open Tickets');
    $response->assertSee('In Progress');
    $response->assertSee('Pending');
    $response->assertSee('Resolved Today');
    $response->assertSee('Recent Support Tickets');
    $response->assertSee('View All');
    $response->assertSee('TCK-00001');
    $response->assertSee('Customer One');
});

test('staff dashboard stat cards link to the ticket queue destinations', function () {
    $user = makeTechnicalStaffUser();

    $response = $this->actingAs($user)->get(route('staff.dashboard'));

    $response->assertOk();
    $response->assertSee(route('staff.tickets.index', ['tab' => 'queue']));
    $response->assertSee(route('staff.tickets.index', ['tab' => 'mine', 'status' => 'in_progress']));
    $response->assertSee(route('staff.tickets.index', ['tab' => 'mine', 'status' => 'assigned']));
    $response->assertSee(route('staff.tickets.index', ['tab' => 'mine', 'status' => 'resolved']));
});

test('non-staff users cannot reach the staff dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('staff.dashboard'))->assertForbidden();
});

test('unauthenticated users are redirected away from the staff dashboard', function () {
    $this->get(route('staff.dashboard'))->assertRedirect(route('login'));
});
