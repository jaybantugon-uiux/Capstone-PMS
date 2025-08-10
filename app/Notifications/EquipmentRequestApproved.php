<?php

namespace App\Notifications;

use App\Models\EquipmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRequestApproved extends Notification implements ShouldQueue
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
            ->subject('Equipment Request Approved - ' . $equipmentRequest->equipment_name)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Great news! Your equipment request has been approved.')
            ->line('**Equipment Details:**')
            ->line('• Name: ' . $equipmentRequest->equipment_name)
            ->line('• Type: ' . $equipmentRequest->formatted_usage_type)
            ->line('• Quantity: ' . $equipmentRequest->quantity)
            ->when($equipmentRequest->project, function ($message) use ($equipmentRequest) {
                return $message->line('• Project: ' . $equipmentRequest->project->name);
            })
            ->when($equipmentRequest->admin_notes, function ($message) use ($equipmentRequest) {
                return $message->line('**Admin Notes:** ' . $equipmentRequest->admin_notes);
            })
            ->line('You can now start using this equipment for your assigned tasks.')
            ->action('View Equipment Details', route('sc.equipment-monitoring.requests'))
            ->line('If you have any questions about your approved equipment, please contact the project administration team.')
            ->salutation('Best regards, Project Management Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'equipment_request_approved',
            'title' => 'Equipment Request Approved',
            'message' => 'Your equipment request for "' . $this->equipmentRequest->equipment_name . '" has been approved.',
            'equipment_request_id' => $this->equipmentRequest->id,
            'equipment_name' => $this->equipmentRequest->equipment_name,
            'usage_type' => $this->equipmentRequest->usage_type,
            'project_name' => $this->equipmentRequest->project ? $this->equipmentRequest->project->name : null,
            'approved_by' => $this->equipmentRequest->approvedBy ? $this->equipmentRequest->approvedBy->full_name : null,
            'approved_at' => $this->equipmentRequest->approved_at,
            'admin_notes' => $this->equipmentRequest->admin_notes,
            'action_url' => route('sc.equipment-monitoring.requests'),
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
        ];
    }
}

