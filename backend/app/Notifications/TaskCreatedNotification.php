<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Generate the correct URL for the task
        $taskUrl = route('tasks.show', $this->task);
        $projectUrl = route('projects.show', $this->task->project);
        
        $mail = (new MailMessage)
            ->subject('🔧 New Task Assigned: ' . $this->task->task_name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have been assigned a new task that requires your attention.')
            ->line('')
            ->line('**📋 Task Details:**')
            ->line('**Task Name:** ' . $this->task->task_name)
            ->line('**Project:** ' . $this->task->project->name)
            ->line('**Description:** ' . ($this->task->description ?: 'No description provided'))
            ->line('**Due Date:** ' . ($this->task->due_date ? $this->task->due_date->format('M d, Y') : 'No due date specified'))
            ->line('**Priority:** ' . ucfirst($this->task->priority ?? 'medium'))
            ->line('**Status:** ' . ucfirst(str_replace('_', ' ', $this->task->status)))
            ->line('**Assigned by:** ' . $this->task->creator->full_name)
            ->line('')
            ->action('📖 View Task Details', $taskUrl)
            ->line('')
            ->line('You can also view the project this task belongs to:')
            ->action('🏗️ View Project', $projectUrl)
            ->line('')
            ->line('**Next Steps:**')
            ->line('• Review the task details and requirements')
            ->line('• Update the task status as you make progress')
            ->line('• Submit regular reports on your progress')
            ->line('• Contact the project manager if you have questions')
            ->line('')
            ->line('Thank you for your dedication to this project!')
            ->salutation('Best regards, ' . config('app.name'));

        return $mail;
    }

    public function toArray($notifiable)
    {
        // Generate the correct URLs
        $taskUrl = route('tasks.show', $this->task);
        $projectUrl = route('projects.show', $this->task->project);
        
        return [
            'type' => 'task_created',
            'title' => 'New Task Assigned',
            'message' => 'You have been assigned a new task: "' . $this->task->task_name . '" in project "' . $this->task->project->name . '"',
            
            // Task details
            'task_id' => $this->task->id,
            'task_name' => $this->task->task_name,
            'task_description' => $this->task->description,
            'task_status' => $this->task->status,
            'task_priority' => $this->task->priority ?? 'medium',
            
            // Project details
            'project_id' => $this->task->project->id,
            'project_name' => $this->task->project->name,
            'project_description' => $this->task->project->description,
            
            // Creator details
            'creator_id' => $this->task->creator->id,
            'creator_name' => $this->task->creator->full_name,
            'creator_email' => $this->task->creator->email,
            
            // Dates
            'due_date' => $this->task->due_date ? $this->task->due_date->format('M d, Y') : null,
            'due_date_raw' => $this->task->due_date ? $this->task->due_date->toDateString() : null,
            'created_at' => $this->task->created_at->format('M d, Y H:i'),
            
            // URLs
            'task_url' => $taskUrl,
            'project_url' => $projectUrl,
            'url' => $taskUrl, // Default URL for notifications
            'action_url' => $taskUrl,
            
            // UI properties
            'icon' => 'fas fa-tasks',
            'color' => $this->getPriorityColor($this->task->priority ?? 'medium'),
            
            // Additional context
            'is_urgent' => $this->isTaskUrgent(),
            'days_until_due' => $this->getDaysUntilDue(),
        ];
    }

    /**
     * Get priority-based color for UI display
     */
    private function getPriorityColor($priority)
    {
        return match(strtolower($priority)) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'primary'
        };
    }

    /**
     * Check if task is urgent (due within 3 days or high priority)
     */
    private function isTaskUrgent()
    {
        $isHighPriority = strtolower($this->task->priority ?? 'medium') === 'high';
        $isDueSoon = false;
        
        if ($this->task->due_date) {
            $daysUntilDue = now()->diffInDays($this->task->due_date, false);
            $isDueSoon = $daysUntilDue >= 0 && $daysUntilDue <= 3;
        }
        
        return $isHighPriority || $isDueSoon;
    }

    /**
     * Get days until due date
     */
    private function getDaysUntilDue()
    {
        if (!$this->task->due_date) {
            return null;
        }
        
        $daysUntilDue = now()->diffInDays($this->task->due_date, false);
        
        if ($daysUntilDue < 0) {
            return 'overdue';
        } elseif ($daysUntilDue === 0) {
            return 'due_today';
        } else {
            return $daysUntilDue;
        }
    }
}