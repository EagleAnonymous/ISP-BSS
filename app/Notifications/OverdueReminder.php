<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueReminder extends Notification
{
    use Queueable;

    /**
     * @param  int|null  $daysOverdue  Set when sent automatically by the overdue-processing
     *                                 job, so the tier can be reflected in the message. Left
     *                                 null for reminders an admin sends manually.
     */
    public function __construct(public Invoice $invoice, public ?int $daysOverdue = null)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Overdue invoice '.$this->invoice->invoice_number)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your invoice '.$this->invoice->invoice_number.' for ₱'.number_format($this->invoice->amount_due, 2).' is overdue.');

        if ($this->daysOverdue !== null) {
            $mail->line('This invoice is now '.$this->daysOverdue.' day(s) past its due date.');
        }

        return $mail
            ->line('Billing period: '.$this->invoice->billing_period_start->format('M j, Y').' - '.$this->invoice->billing_period_end->format('M j, Y'))
            ->line('Due date: '.$this->invoice->due_date->format('M j, Y'))
            ->line('Please settle this invoice as soon as possible to avoid service interruption.');
    }
}