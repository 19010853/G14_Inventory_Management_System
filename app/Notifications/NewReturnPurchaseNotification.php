<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewReturnPurchaseNotification extends Notification
{
    use Queueable;

    private $returnPurchase;

    public function __construct($returnPurchase)
    {
        $this->returnPurchase = $returnPurchase;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Purchase Return Created')
            ->line('A new purchase return has been created.')
            ->action('View Purchase Return', route('details.return.purchase', $this->returnPurchase->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Purchase Return',
            'message' => 'A new purchase return has been created for supplier ' . optional($this->returnPurchase->supplier)->name,
            'user_name' => auth()->user()->name,
            'link' => route('details.return.purchase', $this->returnPurchase->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Purchase Return',
            'message' => 'A new purchase return has been created.',
            'link' => route('details.return.purchase', $this->returnPurchase->id),
        ]);
    }
}


