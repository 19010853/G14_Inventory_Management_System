<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewPurchaseNotification extends Notification
{
    use Queueable;

    private $purchase;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Purchase Created')
            ->line('A new purchase has been created.')
            ->action('View Purchase', route('details.purchase', $this->purchase->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Purchase',
            'message' => 'A new purchase has been created for supplier ' . optional($this->purchase->supplier)->name,
            'user_name' => auth()->user()->name,
            'link' => route('details.purchase', $this->purchase->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Purchase',
            'message' => 'A new purchase has been created.',
            'link' => route('details.purchase', $this->purchase->id),
        ]);
    }
}


