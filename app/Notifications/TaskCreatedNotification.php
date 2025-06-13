<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskCreatedNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Task Assigned: ' . $this->task->task_name)
                    ->greeting('Hello ' . $notifiable->first_name . '!')
                    ->line('A new task has been assigned to you.')
                    ->line('**Task:** ' . $this->task->task_name)
                    ->line('**Description:** ' . ($this->task->description ?: 'No description provided'))
                    ->line('**Project:** ' . $this->task->project->name)
                    ->line('**Due Date:** ' . ($this->task->formatted_due_date ?: 'Not specified'))
                    ->line('**Priority:** ' . ucfirst($this->task->priority))
                    ->line('**Status:** ' . $this->task->formatted_status)
                    ->line('**Assigned by:** ' . $this->task->creator->full_name)
                    ->action('View Task', route('tasks.show', $this->task->id))
                    ->line('Please review the task details and update the status accordingly.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'task_created',
            'task_id' => $this->task->id,
            'task_name' => $this->task->task_name,
            'task_description' => $this->task->description,
            'project_name' => $this->task->project->name,
            'project_id' => $this->task->project_id,
            'creator_name' => $this->task->creator->full_name,
            'due_date' => $this->task->formatted_due_date,
            'priority' => $this->task->priority,
            'status' => $this->task->status,
            'message' => 'New task "' . $this->task->task_name . '" has been assigned to you by ' . $this->task->creator->full_name,
            'action_url' => route('tasks.show', $this->task->id),
        ];
    }
}