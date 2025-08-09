<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\TaskReport;

class TaskReportReviewed extends Notification implements ShouldQueue
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
        $statusMessage = match($this->taskReport->review_status) {
            'approved' => 'has been approved! 🎉',
            'needs_revision' => 'needs revision 📝',
            'reviewed' => 'has been reviewed ✅',
            default => 'has been updated'
        };

        $statusColor = match($this->taskReport->review_status) {
            'approved' => '#28a745',
            'needs_revision' => '#ffc107', 
            'reviewed' => '#17a2b8',
            default => '#6c757d'
        };

        $mailMessage = (new MailMessage)
            ->subject('Task Report ' . ucfirst($this->taskReport->review_status) . ' - ' . $this->taskReport->report_title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Your task report **"' . $this->taskReport->report_title . '"** ' . $statusMessage);

        // Add details section
        $mailMessage->line('**📋 Report Details:**')
                   ->line('• **Task:** ' . $this->taskReport->task->task_name)
                   ->line('• **Project:** ' . $this->taskReport->task->project->name)
                   ->line('• **Report Date:** ' . $this->taskReport->formatted_report_date)
                   ->line('• **Progress:** ' . $this->taskReport->progress_percentage . '%')
                   ->line('• **Review Status:** ' . $this->taskReport->formatted_review_status)
                   ->line('• **Reviewed By:** ' . $this->taskReport->reviewer->full_name)
                   ->line('• **Reviewed On:** ' . $this->taskReport->formatted_reviewed_at);

        // Add rating if provided
        if ($this->taskReport->admin_rating) {
            $stars = str_repeat('⭐', $this->taskReport->admin_rating);
            $mailMessage->line('• **Rating:** ' . $stars . ' (' . $this->taskReport->admin_rating . '/5 stars)');
        }

        // Add hours worked if available
        if ($this->taskReport->hours_worked) {
            $mailMessage->line('• **Hours Worked:** ' . $this->taskReport->hours_worked . ' hours');
        }

        // Add admin comments if provided
        if ($this->taskReport->admin_comments) {
            $mailMessage->line('')
                       ->line('**💬 Admin Comments:**')
                       ->line($this->taskReport->admin_comments);
        }

        // Add specific actions based on status
        if ($this->taskReport->review_status === 'approved') {
            $mailMessage->line('')
                       ->line('🎉 **Congratulations!** Your report has been approved. Keep up the excellent work!')
                       ->action('View Approved Report', url('/sc/task-reports/' . $this->taskReport->id));
        } elseif ($this->taskReport->review_status === 'needs_revision') {
            $mailMessage->line('')
                       ->line('📝 **Action Required:** Please review the admin comments and update your report accordingly.')
                       ->action('Edit Your Report', url('/sc/task-reports/' . $this->taskReport->id . '/edit'));
        } else {
            $mailMessage->action('View Report', url('/sc/task-reports/' . $this->taskReport->id));
        }

        // Add footer based on status
        if ($this->taskReport->review_status === 'approved') {
            $mailMessage->line('Thank you for your detailed and accurate reporting. Your professionalism is appreciated!');
        } elseif ($this->taskReport->review_status === 'needs_revision') {
            $mailMessage->line('Please make the necessary revisions and resubmit your report. If you have any questions, feel free to contact your project manager.');
        } else {
            $mailMessage->line('Thank you for submitting your task report. Your contribution to project tracking is valuable.');
        }

        return $mailMessage->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $title = match($this->taskReport->review_status) {
            'approved' => 'Report Approved! 🎉',
            'needs_revision' => 'Report Needs Revision 📝',
            'reviewed' => 'Report Reviewed ✅',
            default => 'Report Updated'
        };

        $message = match($this->taskReport->review_status) {
            'approved' => 'Your task report "' . $this->taskReport->report_title . '" has been approved by ' . $this->taskReport->reviewer->full_name,
            'needs_revision' => 'Your task report "' . $this->taskReport->report_title . '" needs revision. Please check admin comments.',
            'reviewed' => 'Your task report "' . $this->taskReport->report_title . '" has been reviewed by ' . $this->taskReport->reviewer->full_name,
            default => 'Your task report "' . $this->taskReport->report_title . '" has been updated'
        };

        $icon = match($this->taskReport->review_status) {
            'approved' => 'fas fa-check-circle',
            'needs_revision' => 'fas fa-edit',
            'reviewed' => 'fas fa-clipboard-check',
            default => 'fas fa-file-alt'
        };

        $color = match($this->taskReport->review_status) {
            'approved' => 'success',
            'needs_revision' => 'warning',
            'reviewed' => 'info',
            default => 'secondary'
        };

        $actionUrl = match($this->taskReport->review_status) {
            'needs_revision' => '/sc/task-reports/' . $this->taskReport->id . '/edit',
            default => '/sc/task-reports/' . $this->taskReport->id
        };

        return [
            'type' => 'task_report_reviewed',
            'title' => $title,
            'message' => $message,
            'task_report_id' => $this->taskReport->id,
            'task_id' => $this->taskReport->task_id,
            'project_id' => $this->taskReport->task->project_id,
            'project_name' => $this->taskReport->task->project->name,
            'task_name' => $this->taskReport->task->task_name,
            'review_status' => $this->taskReport->review_status,
            'reviewed_by' => $this->taskReport->reviewed_by,
            'reviewer_name' => $this->taskReport->reviewer->full_name,
            'admin_rating' => $this->taskReport->admin_rating,
            'has_comments' => !empty($this->taskReport->admin_comments),
            'progress_percentage' => $this->taskReport->progress_percentage,
            'action_url' => $actionUrl,
            'action_text' => $this->taskReport->review_status === 'needs_revision' ? 'Edit Report' : 'View Report',
            'icon' => $icon,
            'color' => $color,
            'priority' => $this->taskReport->review_status === 'needs_revision' ? 'high' : 'normal',
            'requires_action' => $this->taskReport->review_status === 'needs_revision',
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
     * Determine the notification delivery delay based on status.
     */
    public function shouldSendImmediately(): bool
    {
        // Send immediately for needs_revision status
        return $this->taskReport->review_status === 'needs_revision';
    }

    /**
     * Get notification tags for grouping similar notifications.
     */
    public function tags(): array
    {
        return [
            'task_report',
            'task_report_reviewed',
            'task_' . $this->taskReport->task_id,
            'project_' . $this->taskReport->task->project_id,
            'status_' . $this->taskReport->review_status,
        ];
    }

    /**
     * Get the notification's metadata for tracking.
     */
    public function getMetadata(): array
    {
        return [
            'task_report_id' => $this->taskReport->id,
            'task_id' => $this->taskReport->task_id,
            'project_id' => $this->taskReport->task->project_id,
            'review_status' => $this->taskReport->review_status,
            'reviewer_id' => $this->taskReport->reviewed_by,
            'admin_rating' => $this->taskReport->admin_rating,
            'has_admin_comments' => !empty($this->taskReport->admin_comments),
            'notification_sent_at' => now()->toISOString(),
        ];
    }
}