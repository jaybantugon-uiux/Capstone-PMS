<?php

namespace App\Notifications;

use App\Models\EquipmentMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenance;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentMaintenance $maintenance)
    {
        $this->maintenance = $maintenance;
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
        $maintenance = $this->maintenance;
        $equipment = $maintenance->monitoredEquipment;
        $sc = $equipment->user;
        
        return (new MailMessage)
            ->subject('Equipment Maintenance Completed - ' . $equipment->equipment_name)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Equipment maintenance has been completed by a Site Coordinator.')
            ->line('**Equipment Details:**')
            ->line('• Name: ' . $equipment->equipment_name)
            ->line('• Site Coordinator: ' . $sc->full_name)
            ->when($equipment->project, function ($message) use ($equipment) {
                return $message->line('• Project: ' . $equipment->project->name);
            })
            ->line('**Maintenance Details:**')
            ->line('• Type: ' . $maintenance->formatted_maintenance_type)
            ->line('• Scheduled Date: ' . $maintenance->scheduled_date->format('M d, Y g:i A'))
            ->line('• Completed Date: ' . $maintenance->completed_date->format('M d, Y g:i A'))
            ->line('• Duration: ' . ($maintenance->actual_duration_hours ?: $maintenance->estimated_duration_hours) . ' hours')
            ->when($maintenance->cost, function ($message) use ($maintenance) {
                return $message->line('• Cost: $' . number_format($maintenance->cost, 2));
            })
            ->when($maintenance->completion_notes, function ($message) use ($maintenance) {
                return $message->line('• Notes: ' . $maintenance->completion_notes);
            })
            ->action('View Maintenance Details', route('admin.equipment-monitoring.maintenance-list'))
            ->line('The equipment is now available for continued use.')
            ->salutation('Best regards, Equipment Management System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $equipment = $this->maintenance->monitoredEquipment;
        
        return [
            'type' => 'maintenance_completed',
            'title' => 'Maintenance Completed',
            'message' => 'Maintenance completed for "' . $equipment->equipment_name . '" by ' . $equipment->user->full_name,
            'maintenance_id' => $this->maintenance->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->equipment_name,
            'maintenance_type' => $this->maintenance->maintenance_type,
            'completed_date' => $this->maintenance->completed_date,
            'performed_by' => $equipment->user->full_name,
            'cost' => $this->maintenance->cost,
            'project_name' => $equipment->project ? $equipment->project->name : null,
            'action_url' => route('admin.equipment-monitoring.maintenance-list'),
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
        ];
    }
}