<?php

use App\Models\Subscriber;
use App\Models\TechnicalStaff;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;

function makeSubscriberFixture(): Subscriber
{
    $user = User::factory()->create();
    $user->assignRole('subscriber');

    return Subscriber::create([
        'user_id' => $user->id,
        'subscriber_id' => 'SUB-'.uniqid(),
        'status' => 'active',
        'joined_date' => now()->toDateString(),
    ]);
}

function makeTechnicalStaffFixture(): TechnicalStaff
{
    $user = User::factory()->create();
    $user->assignRole('technical_staff');

    return TechnicalStaff::create(['user_id' => $user->id]);
}

function makeOpenTicket(Subscriber $subscriber, array $overrides = []): Ticket
{
    return Ticket::create(array_merge([
        'ticket_number' => 'TCK-'.uniqid(),
        'subscriber_id' => $subscriber->id,
        'category' => 'no_connection',
        'subject' => 'No internet',
        'description' => 'Reported by phone.',
        'priority' => 'high',
        'status' => 'open',
    ], $overrides));
}

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('staff can see an open ticket in the shared queue', function () {
    $staff = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber);

    $response = $this->actingAs($staff->user)->get(route('staff.tickets.index', ['tab' => 'queue']));

    $response->assertOk();
    $response->assertSee($ticket->ticket_number);
});

test('staff can claim an open ticket, which assigns it to them', function () {
    $staff = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber);

    $response = $this->actingAs($staff->user)->post(route('staff.tickets.claim', $ticket));

    $response->assertRedirect(route('staff.tickets.show', $ticket));
    $ticket->refresh();
    expect($ticket->status)->toBe('assigned');
    expect($ticket->assigned_to)->toBe($staff->id);
    expect($ticket->claimed_at)->not->toBeNull();
});

test('claiming an already-claimed ticket is rejected', function () {
    $staffA = makeTechnicalStaffFixture();
    $staffB = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber);

    // First claim succeeds.
    $this->actingAs($staffA->user)->post(route('staff.tickets.claim', $ticket))->assertRedirect();

    // A second claim attempt on the same ticket must be rejected, not
    // silently reassign it — this is the concurrency-safety guarantee.
    $this->actingAs($staffB->user)->post(route('staff.tickets.claim', $ticket))->assertStatus(422);

    expect($ticket->fresh()->assigned_to)->toBe($staffA->id);
});

test('only the assigned staff member can start an assigned ticket', function () {
    $staffA = makeTechnicalStaffFixture();
    $staffB = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber, ['status' => 'assigned', 'assigned_to' => $staffA->id, 'claimed_at' => now()]);

    $this->actingAs($staffB->user)->patch(route('staff.tickets.start', $ticket))->assertForbidden();
    expect($ticket->fresh()->status)->toBe('assigned');

    $this->actingAs($staffA->user)->patch(route('staff.tickets.start', $ticket))->assertRedirect();
    expect($ticket->fresh()->status)->toBe('in_progress');
});

test('resolving a ticket requires resolution notes', function () {
    $staff = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber, [
        'status' => 'in_progress', 'assigned_to' => $staff->id, 'claimed_at' => now(), 'started_at' => now(),
    ]);

    $response = $this->actingAs($staff->user)->patch(route('staff.tickets.resolve', $ticket), []);

    $response->assertSessionHasErrors('resolution_notes');
    expect($ticket->fresh()->status)->toBe('in_progress');
});

test('the assigned staff member can resolve a ticket with notes', function () {
    $staff = makeTechnicalStaffFixture();
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber, [
        'status' => 'in_progress', 'assigned_to' => $staff->id, 'claimed_at' => now(), 'started_at' => now(),
    ]);

    $response = $this->actingAs($staff->user)->patch(route('staff.tickets.resolve', $ticket), [
        'resolution_notes' => 'Replaced a faulty cable.',
    ]);

    $response->assertRedirect(route('staff.tickets.show', $ticket));
    $ticket->refresh();
    expect($ticket->status)->toBe('resolved');
    expect($ticket->resolution_notes)->toBe('Replaced a faulty cable.');
    expect($ticket->resolved_at)->not->toBeNull();
});

test('an admin cannot reach staff-only ticket actions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $subscriber = makeSubscriberFixture();
    $ticket = makeOpenTicket($subscriber);

    $this->actingAs($admin)->get(route('staff.tickets.index'))->assertForbidden();
    $this->actingAs($admin)->post(route('staff.tickets.claim', $ticket))->assertForbidden();
});
