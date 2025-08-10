<?php

namespace App\Notifications;

use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRequestDeclined extends Notification implements ShouldQueue
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
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $equipmentRequest = $this->equipmentRequest;
        
        return (new MailMessage)
            ->subject('Equipment Request Update - ' . $equipmentRequest->equipment_name)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('We wanted to update you on the status of your equipment request.')
            ->line('**Equipment Details:**')
            ->line('• Name: ' . $equipmentRequest->equipment_name)
            ->line('• Type: ' . $equipmentRequest->formatted_usage_type)
            ->line('• Quantity: ' . $equipmentRequest->quantity)
            ->when($equipmentRequest->project, function ($message) use ($equipmentRequest) {
                return $message->line('• Project: ' . $equipmentRequest->project->name);
            })
            ->line('**Status:** Unfortunately, your equipment request could not be approved at this time.')
            ->when($equipmentRequest->decline_reason, function ($message) use ($equipmentRequest) {
                return $message->line('**Reason:** ' . $equipmentRequest->decline_reason);
            })
            ->line('If you have questions about this decision or would like to discuss alternative solutions, please contact the project administration team.')
            ->action('View Request Details', route('sc.equipment-monitoring.requests'))
            ->line('You may submit a new request with additional information or modifications if needed.')
            ->salutation('Best regards, Project Management Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'equipment_request_declined',
            'title' => 'Equipment Request Update',
            'message' => 'Your equipment request for "' . $this->equipmentRequest->equipment_name . '" requires attention.',
            'equipment_request_id' => $this->equipmentRequest->id,
            'equipment_name' => $this->equipmentRequest->equipment_name,
            'usage_type' => $this->equipmentRequest->usage_type,
            'project_name' => $this->equipmentRequest->project ? $this->equipmentRequest->project->name : null,
            'declined_by' => $this->equipmentRequest->approvedBy ? $this->equipmentRequest->approvedBy->full_name : null,
            'declined_at' => $this->equipmentRequest->approved_at,
            'decline_reason' => $this->equipmentRequest->decline_reason,
            'action_url' => route('sc.equipment-monitoring.requests'),
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'warning',
        ];
    }
}