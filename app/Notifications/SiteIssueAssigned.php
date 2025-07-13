<?php

namespace App\Notifications;

use App\Models\SiteIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteIssueAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $siteIssue;

    /**
     * Create a new notification instance.
     */
    public function __construct(SiteIssue $siteIssue)
    {
        $this->siteIssue = $siteIssue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $priorityColor = match($this->siteIssue->priority) {
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#0dcaf0',
            'low' => '#198754',
            default => '#6c757d'
        };

        return (new MailMessage)
            ->subject("📋 Site Issue Assigned to You: {$this->siteIssue->issue_title}")
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("A site issue has been assigned to you for resolution.")
            ->line("**Issue Details:**")
            ->line("📋 **Title:** {$this->siteIssue->issue_title}")
            ->line("🔴 **Priority:** " . ucfirst($this->siteIssue->priority))
            ->line("📝 **Type:** " . ucfirst($this->siteIssue->issue_type))
            ->line("🏗️ **Project:** {$this->siteIssue->project->name}")
            ->line("👷 **Reported by:** {$this->siteIssue->reporter->first_name} {$this->siteIssue->reporter->last_name}")
            ->line("📅 **Reported:** {$this->siteIssue->formatted_reported_at}")
            ->line('')
            ->line("**Description:**")
            ->line($this->siteIssue->description)
            ->action('View & Manage Issue', route('admin.site-issues.show', $this->siteIssue))
            ->line('Please review this issue and take appropriate action.')
            ->salutation('Best regards, ' . config('app.name') . ' System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'site_issue_assigned',
            'site_issue_id' => $this->siteIssue->id,
            'title' => $this->siteIssue->issue_title,
            'priority' => $this->siteIssue->priority,
            'issue_type' => $this->siteIssue->issue_type,
            'project_name' => $this->siteIssue->project->name,
            'reporter_name' => $this->siteIssue->reporter->first_name . ' ' . $this->siteIssue->reporter->last_name,
            'assigned_at' => now(),
            'message' => "Site issue '{$this->siteIssue->issue_title}' has been assigned to you",
            'action_url' => route('admin.site-issues.show', $this->siteIssue),
        ];
    }
}