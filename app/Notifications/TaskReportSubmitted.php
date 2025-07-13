<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\TaskReport;
use Illuminate\Support\Str;

class TaskReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $taskReport;

    /**
     * Create a new notification instance.
     */
    public function __construct(TaskReport $taskReport)
    {
        $this->taskReport = $taskReport;
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
        // ENHANCED: Customize email content based on recipient role
        $recipientRole = $notifiable->role;
        $recipientTitle = match($recipientRole) {
            'admin' => 'Administrator',
            'pm' => 'Project Manager',
            default => ucfirst($recipientRole)
        };

        $actionUrl = match($recipientRole) {
            'pm', 'admin' => url('/admin/task-reports/' . $this->taskReport->id),
            default => url('/task-reports/' . $this->taskReport->id)
        };

        return (new MailMessage)
                    ->subject('🔔 New Task Report Submitted - ' . $this->taskReport->report_title)
                    ->greeting('Hello ' . $notifiable->first_name . ',')
                    ->line('A new task report has been submitted by **' . $this->taskReport->user->full_name . '** and requires your review.')
                    
                    // ENHANCED: Project and task context
                    ->line('**📋 Report Overview:**')
                    ->line('• **Project:** ' . $this->taskReport->task->project->name)
                    ->line('• **Task:** ' . $this->taskReport->task->task_name)
                    ->line('• **Report Title:** ' . $this->taskReport->report_title)
                    ->line('• **Site Coordinator:** ' . $this->taskReport->user->full_name)
                    ->line('• **Report Date:** ' . $this->taskReport->formatted_report_date)
                    ->line('• **Submission Time:** ' . $this->taskReport->created_at->format('M d, Y g:i A'))
                    
                    // ENHANCED: Progress and status information
                    ->line('')
                    ->line('**📊 Progress Details:**')
                    ->line('• **Task Status:** ' . $this->taskReport->formatted_task_status)
                    ->line('• **Progress:** ' . $this->taskReport->progress_percentage . '% Complete')
                    ->when($this->taskReport->hours_worked, function ($message) {
                        return $message->line('• **Hours Worked:** ' . $this->taskReport->hours_worked . ' hours');
                    })
                    ->when($this->taskReport->weather_conditions, function ($message) {
                        return $message->line('• **Weather:** ' . ucfirst($this->taskReport->weather_conditions));
                    })
                    
                    // ENHANCED: Work summary
                    ->line('')
                    ->line('**🔧 Work Summary:**')
                    ->line(Str::limit($this->taskReport->work_description, 200))
                    
                    // ENHANCED: Issues alert (if any)
                    ->when($this->taskReport->issues_encountered, function ($message) {
                        return $message->line('')
                                      ->line('⚠️  **Issues Encountered:**')
                                      ->line(Str::limit($this->taskReport->issues_encountered, 150))
                                      ->line('*This report contains issues that may require attention.*');
                    })
                    
                    // ENHANCED: Photos indicator
                    ->when($this->taskReport->photos && count($this->taskReport->photos) > 0, function ($message) {
                        $photoCount = count($this->taskReport->photos);
                        return $message->line('')
                                      ->line('📸 **Attachments:** ' . $photoCount . ' photo' . ($photoCount > 1 ? 's' : '') . ' attached');
                    })
                    
                    // ENHANCED: Role-specific action message
                    ->line('')
                    ->line(match($recipientRole) {
                        'pm' => '**As the Project Manager**, please review this report to ensure project progress is on track and address any issues raised.',
                        'admin' => '**As an Administrator**, please review and approve this report to maintain project quality standards.',
                        default => 'Please review this task report at your earliest convenience.'
                    })
                    
                    ->action('Review Report', $actionUrl)
                    
                    // ENHANCED: Priority indicator
                    ->when($this->taskReport->issues_encountered, function ($message) {
                        return $message->line('')
                                      ->line('🚨 **Priority:** This report contains issues and may require immediate attention.');
                    })
                    
                    ->line('')
                    ->line('Thank you for maintaining project quality and team communication.')
                    ->salutation('Best regards,<br>' . config('app.name') . ' Project Management Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        // ENHANCED: Customize database notification based on recipient role
        $recipientRole = $notifiable->role;
        
        $title = match($recipientRole) {
            'pm' => '📋 New Task Report - Project Review Required',
            'admin' => '📋 New Task Report - Admin Review Required',
            default => '📋 New Task Report Submitted'
        };

        $message = match($recipientRole) {
            'pm' => $this->taskReport->user->full_name . ' submitted a task report for your project "' . $this->taskReport->task->project->name . '"',
            'admin' => $this->taskReport->user->full_name . ' submitted a task report requiring administrative review',
            default => $this->taskReport->user->full_name . ' submitted a new task report for "' . $this->taskReport->task->task_name . '"'
        };

        $actionUrl = match($recipientRole) {
            'pm', 'admin' => '/admin/task-reports/' . $this->taskReport->id,
            default => '/task-reports/' . $this->taskReport->id
        };

        // ENHANCED: Determine priority and color based on content
        $priority = 'normal';
        $color = 'primary';
        
        if ($this->taskReport->issues_encountered) {
            $priority = 'high';
            $color = 'warning';
        }
        
        if ($this->taskReport->task_status === 'completed') {
            $color = 'success';
        }

        return [
            'type' => 'task_report_submitted',
            'title' => $title,
            'message' => $message,
            'task_report_id' => $this->taskReport->id,
            'task_id' => $this->taskReport->task_id,
            'project_id' => $this->taskReport->task->project_id,
            'project_name' => $this->taskReport->task->project->name,
            'task_name' => $this->taskReport->task->task_name,
            'submitted_by' => $this->taskReport->user_id,
            'submitted_by_name' => $this->taskReport->user->full_name,
            'progress_percentage' => $this->taskReport->progress_percentage,
            'task_status' => $this->taskReport->task_status,
            'has_issues' => !empty($this->taskReport->issues_encountered),
            'has_photos' => $this->taskReport->photos && count($this->taskReport->photos) > 0,
            'photo_count' => $this->taskReport->photos ? count($this->taskReport->photos) : 0,
            'hours_worked' => $this->taskReport->hours_worked,
            'weather_conditions' => $this->taskReport->weather_conditions,
            'report_date' => $this->taskReport->report_date->format('Y-m-d'),
            'action_url' => $actionUrl,
            'action_text' => 'Review Report',
            'icon' => 'fas fa-file-alt',
            'color' => $color,
            'priority' => $priority,
            'requires_action' => true,
            'recipient_role' => $recipientRole,
            
            // ENHANCED: Additional metadata for frontend processing
            'metadata' => [
                'report_title' => $this->taskReport->report_title,
                'submission_time' => $this->taskReport->created_at->toISOString(),
                'work_summary' => Str::limit($this->taskReport->work_description, 100),
                'issues_summary' => $this->taskReport->issues_encountered ? Str::limit($this->taskReport->issues_encountered, 100) : null,
                'next_steps' => $this->taskReport->next_steps ? Str::limit($this->taskReport->next_steps, 100) : null,
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * ENHANCED: Determine if notification should be sent immediately
     */
    public function shouldSendImmediately(): bool
    {
        // Send immediately if report contains issues or task is completed
        return !empty($this->taskReport->issues_encountered) || 
               $this->taskReport->task_status === 'completed';
    }

    /**
     * ENHANCED: Get notification tags for better organization
     */
    public function tags(): array
    {
        $tags = [
            'task_report',
            'task_report_submitted',
            'task_' . $this->taskReport->task_id,
            'project_' . $this->taskReport->task->project_id,
            'status_' . $this->taskReport->task_status,
            'submitted_by_' . $this->taskReport->user_id,
        ];

        // Add priority-based tags
        if ($this->taskReport->issues_encountered) {
            $tags[] = 'has_issues';
            $tags[] = 'priority_high';
        }

        if ($this->taskReport->task_status === 'completed') {
            $tags[] = 'task_completed';
        }

        return $tags;
    }

    /**
     * ENHANCED: Get notification metadata for analytics
     */
    public function getMetadata(): array
    {
        return [
            'task_report_id' => $this->taskReport->id,
            'task_id' => $this->taskReport->task_id,
            'project_id' => $this->taskReport->task->project_id,
            'submitted_by' => $this->taskReport->user_id,
            'submission_method' => 'web_form',
            'has_issues' => !empty($this->taskReport->issues_encountered),
            'has_photos' => $this->taskReport->photos && count($this->taskReport->photos) > 0,
            'photo_count' => $this->taskReport->photos ? count($this->taskReport->photos) : 0,
            'progress_percentage' => $this->taskReport->progress_percentage,
            'hours_worked' => $this->taskReport->hours_worked,
            'task_status' => $this->taskReport->task_status,
            'weather_conditions' => $this->taskReport->weather_conditions,
            'notification_sent_at' => now()->toISOString(),
            'estimated_review_time' => $this->taskReport->issues_encountered ? '15 minutes' : '10 minutes',
        ];
    }

    /**
     * ENHANCED: Customize notification delivery delay based on content
     */
    /**
     * ENHANCED: Customize notification delivery delay based on content
     * Compatible with PHP < 8 (no union types)
     *
     * @return \DateTimeInterface|\DateInterval|int|null
     */
    public function delay()
    {
        // Send immediately for critical reports
        if ($this->taskReport->issues_encountered || $this->taskReport->task_status === 'completed') {
            return null; // No delay
        }

        // Small delay for regular reports to batch notifications
        return now()->addMinutes(5);
    }
}