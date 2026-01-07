<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewSaleDueNotification extends Notification
{
    use Queueable;

    private $sale;

    public function __construct($sale)
    {
        $this->sale = $sale;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Sale Due Created')
            ->line('A new sale with due amount has been created.')
            ->action('View Sale', route('details.sale', $this->sale->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Sale Due',
            'message' => 'Sale for customer ' . optional($this->sale->customer)->name . ' has a due amount of ' . $this->sale->due_amount,
            'user_name' => auth()->user()->name,
            'link' => route('details.sale', $this->sale->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Sale Due',
            'message' => 'A new sale with due amount has been created.',
            'link' => route('details.sale', $this->sale->id),
        ]);
    }
}


