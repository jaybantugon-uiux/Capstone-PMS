<?php

namespace App\Notifications;

use App\Models\EquipmentMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $maintenance;
    protected $reminderType;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentMaintenance $maintenance, $reminderType = 'upcoming')
    {
        $this->maintenance = $maintenance;
        $this->reminderType = $reminderType; // 'upcoming' or 'overdue'
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
        
        $subject = $this->reminderType === 'overdue' 
            ? 'Overdue Equipment Maintenance - ' . $equipment->equipment_name
            : 'Upcoming Equipment Maintenance - ' . $equipment->equipment_name;
            
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->first_name . ',');
            
        if ($this->reminderType === 'overdue') {
            $message->line('**URGENT:** Equipment maintenance is overdue and requires immediate attention.');
        } else {
            $message->line('You have upcoming equipment maintenance scheduled.');
        }
        
        return $message
            ->line('**Equipment Details:**')
            ->line('• Name: ' . $equipment->equipment_name)
            ->line('• Type: ' . $equipment->formatted_usage_type)
            ->line('• Location: ' . ($equipment->location ?: 'Not specified'))
            ->when($equipment->project, function ($message) use ($equipment) {
                return $message->line('• Project: ' . $equipment->project->name);
            })
            ->line('**Maintenance Details:**')
            ->line('• Type: ' . $maintenance->formatted_maintenance_type)
            ->line('• Scheduled Date: ' . $maintenance->scheduled_date->format('M d, Y g:i A'))
            ->line('• Priority: ' . $maintenance->formatted_priority)
            ->line('• Estimated Duration: ' . $maintenance->estimated_duration_hours . ' hours')
            ->when($maintenance->description, function ($message) use ($maintenance) {
                return $message->line('• Description: ' . $maintenance->description);
            })
            ->action('View Maintenance Schedule', route('sc.equipment-monitoring.maintenance'))
            ->line('Please ensure this maintenance is completed as scheduled to keep equipment in optimal condition.')
            ->salutation('Best regards, Equipment Management System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $equipment = $this->maintenance->monitoredEquipment;
        
        return [
            'type' => 'maintenance_reminder',
            'reminder_type' => $this->reminderType,
            'title' => $this->reminderType === 'overdue' ? 'Overdue Maintenance' : 'Upcoming Maintenance',
            'message' => ($this->reminderType === 'overdue' ? 'Overdue' : 'Upcoming') . ' maintenance for "' . $equipment->equipment_name . '"',
            'maintenance_id' => $this->maintenance->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->equipment_name,
            'maintenance_type' => $this->maintenance->maintenance_type,
            'scheduled_date' => $this->maintenance->scheduled_date,
            'priority' => $this->maintenance->priority,
            'project_name' => $equipment->project ? $equipment->project->name : null,
            'action_url' => route('sc.equipment-monitoring.maintenance'),
            'icon' => $this->reminderType === 'overdue' ? 'fas fa-exclamation-triangle' : 'fas fa-tools',
            'color' => $this->reminderType === 'overdue' ? 'danger' : 'warning',
        ];
    }
}
