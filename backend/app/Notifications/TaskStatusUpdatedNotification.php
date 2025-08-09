<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;
use App\Models\User;

class TaskStatusUpdatedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $oldStatus;
    protected $newStatus;
    protected $updatedBy;

    public function __construct(Task $task, $oldStatus, $newStatus, User $updatedBy)
    {
        $this->task = $task;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Task Status Updated')
            ->line('A task status has been updated.')
            ->line('Task: ' . $this->task->task_name)
            ->line('Project: ' . $this->task->project->name)
            ->line('Status changed from: ' . ucfirst(str_replace('_', ' ', $this->oldStatus)))
            ->line('Status changed to: ' . ucfirst(str_replace('_', ' ', $this->newStatus)))
            ->line('Updated by: ' . $this->updatedBy->full_name)
            ->action('View Task', route('tasks.show', $this->task))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'task_status_updated',
            'title' => 'Task Status Updated',
            'message' => 'Task "' . $this->task->task_name . '" status changed to ' . ucfirst(str_replace('_', ' ', $this->newStatus)),
            'task_id' => $this->task->id,
            'task_name' => $this->task->task_name,
            'project_name' => $this->task->project->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_by' => $this->updatedBy->full_name,
            'url' => route('tasks.show', $this->task),
            'action_url' => route('tasks.show', $this->task),
            'icon' => 'fas fa-sync-alt',
            'color' => $this->getStatusColor($this->newStatus)
        ];
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'completed' => 'success',
            'in_progress' => 'warning',
            'pending' => 'secondary',
            default => 'primary'
        };
    }
}