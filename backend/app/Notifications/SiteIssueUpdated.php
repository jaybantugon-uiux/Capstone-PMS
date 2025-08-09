<?php

namespace App\Notifications;

use App\Models\SiteIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteIssueUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $siteIssue;
    public $updateType;

    /**
     * Create a new notification instance.
     */
    public function __construct(SiteIssue $siteIssue, $updateType = 'general')
    {
        $this->siteIssue = $siteIssue;
        $this->updateType = $updateType;
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
        $statusIcon = match($this->siteIssue->status) {
            'open' => '🔴',
            'in_progress' => '🟡',
            'resolved' => '✅',
            'closed' => '⚫',
            'escalated' => '🚨',
            default => '📝'
        };

        $subject = match($this->siteIssue->status) {
            'resolved' => "✅ Site Issue Resolved: {$this->siteIssue->issue_title}",
            'escalated' => "🚨 Site Issue Escalated: {$this->siteIssue->issue_title}",
            'in_progress' => "🔄 Site Issue Update: {$this->siteIssue->issue_title}",
            default => "📝 Site Issue Updated: {$this->siteIssue->issue_title}"
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("Your reported site issue has been updated.");

        if ($this->siteIssue->status === 'resolved') {
            $message->line("🎉 **Good news!** Your issue has been marked as resolved.")
                ->when($this->siteIssue->resolution_description, function ($msg) {
                    return $msg->line("**Resolution Details:**")
                        ->line($this->siteIssue->resolution_description);
                });
        } elseif ($this->siteIssue->status === 'escalated') {
            $message->line("⚠️ Your issue has been escalated due to its priority or complexity.");
        } else {
            $message->line("The status has been updated and progress is being made on your issue.");
        }

        return $message
            ->line("**Current Status:** {$statusIcon} " . ucfirst(str_replace('_', ' ', $this->siteIssue->status)))
            ->line("**Issue:** {$this->siteIssue->issue_title}")
            ->line("**Project:** {$this->siteIssue->project->name}")
            ->when($this->siteIssue->assignedTo, function ($msg) {
                return $msg->line("**Assigned to:** {$this->siteIssue->assignedTo->first_name} {$this->siteIssue->assignedTo->last_name}");
            })
            ->when($this->siteIssue->admin_notes, function ($msg) {
                return $msg->line("**Admin Notes:**")
                    ->line($this->siteIssue->admin_notes);
            })
            ->action('View Issue Details', route('sc.site-issues.show', $this->siteIssue))
            ->line('Thank you for reporting this issue and helping us maintain a safe and efficient work environment.')
            ->salutation('Best regards, ' . config('app.name') . ' Management Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'site_issue_updated',
            'site_issue_id' => $this->siteIssue->id,
            'title' => $this->siteIssue->issue_title,
            'status' => $this->siteIssue->status,
            'priority' => $this->siteIssue->priority,
            'project_name' => $this->siteIssue->project->name,
            'updated_at' => $this->siteIssue->updated_at,
            'message' => "Your site issue '{$this->siteIssue->issue_title}' has been updated to: " . ucfirst(str_replace('_', ' ', $this->siteIssue->status)),
            'action_url' => route('sc.site-issues.show', $this->siteIssue),
                ];
            }
        }