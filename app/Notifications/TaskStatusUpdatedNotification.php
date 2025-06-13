<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
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
        $statusChanged = ucfirst(str_replace('_', ' ', $this->oldStatus)) . 
                        ' → ' . 
                        ucfirst(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage)
                    ->subject('Task Status Updated: ' . $this->task->task_name)
                    ->greeting('Hello ' . $notifiable->first_name . '!')
                    ->line('The status of a task has been updated.')
                    ->line('**Task:** ' . $this->task->task_name)
                    ->line('**Project:** ' . $this->task->project->name)
                    ->line('**Status Change:** ' . $statusChanged)
                    ->line('**Updated by:** ' . $this->updatedBy->full_name)
                    ->line('**Due Date:** ' . ($this->task->formatted_due_date ?: 'Not specified'))
                    ->action('View Task', route('tasks.show', $this->task->id))
                    ->line('Thank you for staying updated on task progress!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'task_status_updated',
            'task_id' => $this->task->id,
            'task_name' => $this->task->task_name,
            'project_name' => $this->task->project->name,
            'project_id' => $this->task->project_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_by_name' => $this->updatedBy->full_name,
            'updated_by_id' => $this->updatedBy->id,
            'due_date' => $this->task->formatted_due_date,
            'message' => 'Task "' . $this->task->task_name . '" status changed from ' . 
                        ucfirst(str_replace('_', ' ', $this->oldStatus)) . ' to ' . 
                        ucfirst(str_replace('_', ' ', $this->newStatus)) . ' by ' . 
                        $this->updatedBy->full_name
        ];
    }
}