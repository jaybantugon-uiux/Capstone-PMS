<?php

namespace App\Notifications;

use App\Models\ProgressReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgressReportShared extends Notification implements ShouldQueue
{
    use Queueable;

    protected $progressReport;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProgressReport $progressReport)
    {
        $this->progressReport = $progressReport;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $report = $this->progressReport;
        $creatorName = $report->creator->first_name . ' ' . $report->creator->last_name;
        $creatorRole = $report->formatted_creator_role;
        $projectName = $report->project ? $report->project->name : 'General';

        $mailMessage = (new MailMessage)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->subject('New Progress Report: ' . $report->title)
            ->line('You have received a new progress report from ' . $creatorName . ' (' . $creatorRole . ').')
            ->line('**Report Title:** ' . $report->title)
            ->line('**Project:** ' . $projectName)
            ->line('**From:** ' . $creatorRole)
            ->line('**Description:**')
            ->line($report->description);

        // Add attachment information if present
        if ($report->hasAttachment()) {
            $mailMessage->line('📎 **Attachment:** ' . $report->original_filename . ' (' . $report->formatted_file_size . ')');
        }

        $mailMessage->action('View Report', route('client.reports.show', $report->id))
            ->line('You can view this report and any attachments by clicking the button above.')
            ->line('Thank you for working with us!');

        return $mailMessage;
    }

    /**
     * Get the database/in-app representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $report = $this->progressReport;
        
        return [
            'type' => 'progress_report_shared',
            'progress_report_id' => $report->id,
            'title' => $report->title,
            'creator_name' => $report->creator->first_name . ' ' . $report->creator->last_name,
            'creator_role' => $report->formatted_creator_role,
            'creator_role_badge' => $report->creator_role_badge_color,
            'project_name' => $report->project ? $report->project->name : 'General',
            'has_attachment' => $report->hasAttachment(),
            'attachment_filename' => $report->original_filename,
            'created_at' => $report->created_at->toISOString(),
            'message' => 'You have received a new progress report: ' . $report->title . ' from ' . $report->formatted_creator_role,
            'action_url' => route('client.reports.show', $report->id),
            'action_text' => 'View Report'
        ];
    }

    /**
     * Get the notification's database type for filtering
     */
    public function getDatabaseType(): string
    {
        return 'progress_report_shared';
    }
}