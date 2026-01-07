<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewTransferNotification extends Notification
{
    use Queueable;

    private $transfer;

    public function __construct($transfer)
    {
        $this->transfer = $transfer;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Transfer Created')
            ->line('A new stock transfer has been created.')
            ->action('View Transfer', route('details.transfer', $this->transfer->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Transfer',
            'message' => 'A new transfer from warehouse #' . $this->transfer->from_warehouse_id . ' to warehouse #' . $this->transfer->to_warehouse_id . ' has been created.',
            'user_name' => auth()->user()->name,
            'link' => route('details.transfer', $this->transfer->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Transfer',
            'message' => 'A new stock transfer has been created.',
            'link' => route('details.transfer', $this->transfer->id),
        ]);
    }
}


