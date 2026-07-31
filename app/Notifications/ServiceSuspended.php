<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceSuspended extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice, public int $daysOverdue)
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
        return (new MailMessage)
            ->subject('Your service has been suspended')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your account has been suspended due to a non-payment of invoice '.$this->invoice->invoice_number.', now '.$this->daysOverdue.' day(s) overdue.')
            ->line('Amount due: ₱'.number_format($this->invoice->amount_due, 2))
            ->line('Please settle this invoice to have your service restored.');
    }
}