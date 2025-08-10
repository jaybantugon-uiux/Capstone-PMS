<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EquipmentRequest;

class EquipmentRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $equipmentRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentRequest $equipmentRequest)
    {
        $this->equipmentRequest = $equipmentRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Equipment Request Submitted')
                    ->greeting('Hello ' . $notifiable->first_name . ',')
                    ->line('A new equipment request has been submitted and requires your review.')
                    ->line('**Equipment:** ' . $this->equipmentRequest->equipment_name)
                    ->line('**Requested by:** ' . $this->equipmentRequest->user->first_name . ' ' . $this->equipmentRequest->user->last_name)
                    ->line('**Project:** ' . ($this->equipmentRequest->project->name ?? 'Personal Use'))
                    ->line('**Urgency:** ' . ucfirst($this->equipmentRequest->urgency_level))
                    ->line('**Quantity:** ' . $this->equipmentRequest->quantity)
                    ->action('Review Request', route('admin.equipment-monitoring.show-request', $this->equipmentRequest))
                    ->line('Please review this request promptly, especially if marked as high priority.')
                    ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'equipment_request_id' => $this->equipmentRequest->id,
            'equipment_name' => $this->equipmentRequest->equipment_name,
            'requester_name' => $this->equipmentRequest->user->first_name . ' ' . $this->equipmentRequest->user->last_name,
            'project_name' => $this->equipmentRequest->project->name ?? 'Personal Use',
            'urgency_level' => $this->equipmentRequest->urgency_level,
            'quantity' => $this->equipmentRequest->quantity,
            'message' => 'New equipment request submitted for review'
        ];
    }
}