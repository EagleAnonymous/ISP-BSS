<?php

use App\Models\Subscriber;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;

function makeSubscriberForTicket(): Subscriber
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

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can log a ticket for a subscriber, starting open and unassigned', function () {
    $subscriber = makeSubscriberForTicket();

    $response = $this->actingAs($this->admin)->post(route('admin.tickets.store'), [
        'subscriber_id' => $subscriber->id,
        'category' => 'no_connection',
        'subject' => 'No internet since this morning',
        'description' => 'Subscriber reports the modem lights are all off.',
        'priority' => 'high',
    ]);

    $ticket = Ticket::first();

    $response->assertRedirect(route('admin.tickets.show', $ticket));
    expect($ticket)->not->toBeNull();
    expect($ticket->status)->toBe('open');
    expect($ticket->assigned_to)->toBeNull();
    expect($ticket->subscriber_id)->toBe($subscriber->id);
    expect($ticket->created_by)->toBe($this->admin->id);
});

test('admin ticket list filters by status', function () {
    $subscriberA = makeSubscriberForTicket();
    $subscriberB = makeSubscriberForTicket();

    $open = Ticket::create([
        'ticket_number' => 'TCK-00001', 'subscriber_id' => $subscriberA->id, 'category' => 'other',
        'subject' => 'Open one', 'description' => 'x', 'priority' => 'medium', 'status' => 'open',
    ]);
    $closed = Ticket::create([
        'ticket_number' => 'TCK-00002', 'subscriber_id' => $subscriberB->id, 'category' => 'other',
        'subject' => 'Closed one', 'description' => 'x', 'priority' => 'medium', 'status' => 'closed',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.tickets.index', ['status' => 'open']));

    $response->assertOk();
    $response->assertSee($open->ticket_number);
    $response->assertDontSee($closed->ticket_number);
});

test('a subscriber cannot reach any admin ticket route', function () {
    $subscriberUser = User::factory()->create();
    $subscriberUser->assignRole('subscriber');

    $this->actingAs($subscriberUser)->get(route('admin.tickets.index'))->assertForbidden();
    $this->actingAs($subscriberUser)->get(route('admin.tickets.create'))->assertForbidden();
});

test('ticket numbers stay unique across several tickets created back-to-back', function () {
    $subscriber = makeSubscriberForTicket();

    foreach (range(1, 5) as $i) {
        $this->actingAs($this->admin)->post(route('admin.tickets.store'), [
            'subscriber_id' => $subscriber->id,
            'category' => 'other',
            'subject' => "Ticket $i",
            'description' => 'x',
            'priority' => 'low',
        ]);
    }

    $numbers = Ticket::pluck('ticket_number');
    expect($numbers)->toHaveCount(5);
    expect($numbers->unique())->toHaveCount(5);
});

test('only admin can close a resolved ticket', function () {
    $subscriber = makeSubscriberForTicket();
    $ticket = Ticket::create([
        'ticket_number' => 'TCK-00099', 'subscriber_id' => $subscriber->id, 'category' => 'other',
        'subject' => 'Fixed already', 'description' => 'x', 'priority' => 'medium',
        'status' => 'resolved', 'resolution_notes' => 'Replaced the router.',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.tickets.close', $ticket));

    $response->assertRedirect(route('admin.tickets.show', $ticket));
    expect($ticket->fresh()->status)->toBe('closed');
});

test('a ticket that is not yet resolved cannot be closed', function () {
    $subscriber = makeSubscriberForTicket();
    $ticket = Ticket::create([
        'ticket_number' => 'TCK-00098', 'subscriber_id' => $subscriber->id, 'category' => 'other',
        'subject' => 'Still open', 'description' => 'x', 'priority' => 'medium', 'status' => 'open',
    ]);

    $this->actingAs($this->admin)->post(route('admin.tickets.close', $ticket))->assertStatus(422);

    expect($ticket->fresh()->status)->toBe('open');
});
