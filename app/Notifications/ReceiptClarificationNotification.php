<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptClarificationNotification extends Notification
{
    use Queueable;

    public $notificationData;

    /**
     * Create a new notification instance.
     */
    public function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Receipt Clarification Request')
            ->line($this->notificationData['message'])
            ->action('View Receipt', $this->notificationData['link'])
            ->line('Please review the receipt and provide the necessary clarification.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'receipt_id' => $this->notificationData['receipt_id'],
            'message' => $this->notificationData['message'],
            'link' => $this->notificationData['link'],
            'type' => $this->notificationData['type'],
            'related_user_id' => $this->notificationData['related_user_id'],
            'created_at' => now(),
        ];
    }
}
