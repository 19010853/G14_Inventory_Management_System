<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewSaleReturnDueNotification extends Notification
{
    use Queueable;

    private $saleReturn;

    public function __construct($saleReturn)
    {
        $this->saleReturn = $saleReturn;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Sale Return Due Created')
            ->line('A new sale return with due amount has been created.')
            ->action('View Sale Return', route('details.return.sale', $this->saleReturn->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Sale Return Due',
            'message' => 'Sale return for customer ' . optional($this->saleReturn->customer)->name . ' has a due amount of ' . $this->saleReturn->due_amount,
            'user_name' => auth()->user()->name,
            'link' => route('details.return.sale', $this->saleReturn->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Sale Return Due',
            'message' => 'A new sale return with due amount has been created.',
            'link' => route('details.return.sale', $this->saleReturn->id),
        ]);
    }
}


