<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    /*
      @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $subscriber = $ticket->subscriber;
        $user = $subscriber?->user;

        return (new MailMessage)
            ->subject('New Support Ticket '.$ticket->ticket_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new support ticket has been logged through the AI assistant and needs attention.')
            ->line('**Ticket Number:** '.$ticket->ticket_number)
            ->line('**Subscriber:** '.($user?->name ?? '—').' ('.($subscriber?->subscriber_id ?? '—').')')
            ->line('**Category:** '.ucfirst(str_replace('_', ' ', $ticket->category)))
            ->line('**Priority:** '.ucfirst($ticket->priority))
            ->line('**Subject:** '.$ticket->subject)
            ->line('**Description:** '.$ticket->description)
            ->line('**Reported At:** '.$ticket->created_at->format('M j, Y g:i A'))
            ->line('**Status:** Open / Pending Technical Visit')
            ->action('View Ticket', url('/admin/tickets/'.$ticket->id))
            ->line('Please review and assign this ticket so our field technicians can respond promptly.');
    }
}

