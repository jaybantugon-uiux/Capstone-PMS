<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\SiteIssue;
use Illuminate\Support\Str;

class SiteIssueReported extends Notification implements ShouldQueue
{
    use Queueable;

    protected $siteIssue;

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
            'pm' => url('/pm/site-issues/' . $this->siteIssue->id),
            'admin' => url('/admin/site-issues/' . $this->siteIssue->id),
            default => url('/site-issues/' . $this->siteIssue->id)
        };

        // Get priority indicator
        $priorityIndicator = match($this->siteIssue->priority) {
            'critical' => '🚨 CRITICAL',
            'high' => '⚠️ HIGH',
            'medium' => '📋 MEDIUM',
            'low' => '📝 LOW',
            default => '📋'
        };

        $issueTypeIcon = match($this->siteIssue->issue_type) {
            'safety' => '⚠️',
            'equipment' => '🔧',
            'environmental' => '🌍',
            'personnel' => '👥',
            'quality' => '✅',
            'timeline' => '⏰',
            default => '📋'
        };

        return (new MailMessage)
                    ->subject($priorityIndicator . ' Site Issue Reported - ' . $this->siteIssue->issue_title)
                    ->greeting('Hello ' . $notifiable->first_name . ',')
                    ->line('A new site issue has been reported by **' . $this->siteIssue->reporter->full_name . '** and requires your immediate attention.')
                    
                    // ENHANCED: Issue overview with visual indicators
                    ->line('**' . $issueTypeIcon . ' Issue Overview:**')
                    ->line('• **Project:** ' . $this->siteIssue->project->name)
                    ->line('• **Issue Title:** ' . $this->siteIssue->issue_title)
                    ->line('• **Type:** ' . ucfirst($this->siteIssue->issue_type))
                    ->line('• **Priority:** ' . $priorityIndicator)
                    ->line('• **Status:** ' . ucfirst($this->siteIssue->status))
                    ->line('• **Reported By:** ' . $this->siteIssue->reporter->full_name)
                    ->line('• **Report Time:** ' . $this->siteIssue->reported_at->format('M d, Y g:i A'))
                    ->when($this->siteIssue->task, function ($message) {
                        return $message->line('• **Related Task:** ' . $this->siteIssue->task->task_name);
                    })
                    ->when($this->siteIssue->location, function ($message) {
                        return $message->line('• **Location:** ' . $this->siteIssue->location);
                    })
                    ->when($this->siteIssue->estimated_cost, function ($message) {
                        return $message->line('• **Estimated Cost:** ₱' . number_format($this->siteIssue->estimated_cost, 2));
                    })
                    
                    // ENHANCED: Issue description
                    ->line('')
                    ->line('**📋 Issue Description:**')
                    ->line(Str::limit($this->siteIssue->description, 300))
                    
                    // ENHANCED: Immediate actions taken (if any)
                    ->when($this->siteIssue->immediate_actions_taken, function ($message) {
                        return $message->line('')
                                      ->line('**🔧 Immediate Actions Taken:**')
                                      ->line(Str::limit($this->siteIssue->immediate_actions_taken, 200));
                    })
                    
                    // ENHANCED: Suggested solutions (if any)
                    ->when($this->siteIssue->suggested_solutions, function ($message) {
                        return $message->line('')
                                      ->line('**💡 Suggested Solutions:**')
                                      ->line(Str::limit($this->siteIssue->suggested_solutions, 200));
                    })
                    
                    // ENHANCED: Attachments indicator
                    ->when($this->siteIssue->photos && count($this->siteIssue->photos) > 0, function ($message) {
                        $photoCount = count($this->siteIssue->photos);
                        return $message->line('')
                                      ->line('📸 **Attachments:** ' . $photoCount . ' photo' . ($photoCount > 1 ? 's' : '') . ' attached');
                    })
                    ->when($this->siteIssue->attachments && count($this->siteIssue->attachments) > 0, function ($message) {
                        $attachmentCount = count($this->siteIssue->attachments);
                        return $message->line('📎 **Files:** ' . $attachmentCount . ' file' . ($attachmentCount > 1 ? 's' : '') . ' attached');
                    })
                    
                    // ENHANCED: Role-specific action message
                    ->line('')
                    ->line(match($recipientRole) {
                        'pm' => '**As the Project Manager**, please review this issue to ensure project safety and progress. Consider assigning the issue to appropriate team members or taking immediate action if critical.',
                        'admin' => '**As an Administrator**, please review and manage this site issue to maintain project quality and safety standards. Assign to appropriate personnel if needed.',
                        default => 'Please review this site issue at your earliest convenience and take appropriate action.'
                    })
                    
                    ->action('View Site Issue', $actionUrl)
                    
                    // ENHANCED: Priority-based urgency message
                    ->when($this->siteIssue->priority === 'critical', function ($message) {
                        return $message->line('')
                                      ->line('🚨 **URGENT:** This is a critical issue that may affect project safety, timeline, or quality. Immediate attention is required.');
                    })
                    ->when($this->siteIssue->priority === 'high', function ($message) {
                        return $message->line('')
                                      ->line('⚠️ **HIGH PRIORITY:** This issue requires prompt attention to prevent potential project delays or complications.');
                    })
                    
                    // ENHANCED: Safety-specific warnings
                    ->when($this->siteIssue->issue_type === 'safety', function ($message) {
                        return $message->line('')
                                      ->line('⚠️ **SAFETY ALERT:** This issue involves safety concerns. Please ensure all safety protocols are followed and consider stopping work in affected areas if necessary.');
                    })
                    
                    ->line('')
                    ->line('Thank you for maintaining project safety and quality standards.')
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
            'pm' => '🚨 New Site Issue - PM Review Required',
            'admin' => '🚨 New Site Issue - Admin Review Required',
            default => '🚨 New Site Issue Reported'
        };

        $message = match($recipientRole) {
            'pm' => $this->siteIssue->reporter->full_name . ' reported a site issue in your project "' . $this->siteIssue->project->name . '"',
            'admin' => $this->siteIssue->reporter->full_name . ' reported a site issue requiring administrative review',
            default => $this->siteIssue->reporter->full_name . ' reported a new site issue: "' . $this->siteIssue->issue_title . '"'
        };

        $actionUrl = match($recipientRole) {
            'pm' => '/pm/site-issues/' . $this->siteIssue->id,
            'admin' => '/admin/site-issues/' . $this->siteIssue->id,
            default => '/site-issues/' . $this->siteIssue->id
        };

        // ENHANCED: Determine priority and color based on issue details
        $priority = match($this->siteIssue->priority) {
            'critical' => 'critical',
            'high' => 'high',
            'medium' => 'normal',
            'low' => 'low',
            default => 'normal'
        };

        $color = match($this->siteIssue->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'primary'
        };

        // Override color for safety issues
        if ($this->siteIssue->issue_type === 'safety') {
            $color = 'danger';
            $priority = 'critical';
        }

        return [
            'type' => 'site_issue_reported',
            'title' => $title,
            'message' => $message,
            'site_issue_id' => $this->siteIssue->id,
            'project_id' => $this->siteIssue->project_id,
            'project_name' => $this->siteIssue->project->name,
            'task_id' => $this->siteIssue->task_id,
            'task_name' => $this->siteIssue->task ? $this->siteIssue->task->task_name : null,
            'reported_by' => $this->siteIssue->user_id,
            'reported_by_name' => $this->siteIssue->reporter->full_name,
            'issue_title' => $this->siteIssue->issue_title,
            'issue_type' => $this->siteIssue->issue_type,
            'priority' => $this->siteIssue->priority,
            'status' => $this->siteIssue->status,
            'location' => $this->siteIssue->location,
            'estimated_cost' => $this->siteIssue->estimated_cost,
            'has_photos' => $this->siteIssue->photos && count($this->siteIssue->photos) > 0,
            'photo_count' => $this->siteIssue->photos ? count($this->siteIssue->photos) : 0,
            'has_attachments' => $this->siteIssue->attachments && count($this->siteIssue->attachments) > 0,
            'attachment_count' => $this->siteIssue->attachments ? count($this->siteIssue->attachments) : 0,
            'reported_at' => $this->siteIssue->reported_at->format('Y-m-d H:i:s'),
            'action_url' => $actionUrl,
            'action_text' => 'View Issue',
            'icon' => 'fas fa-exclamation-triangle',
            'color' => $color,
            'priority' => $priority,
            'requires_action' => true,
            'recipient_role' => $recipientRole,
            'is_safety_issue' => $this->siteIssue->issue_type === 'safety',
            'is_critical' => $this->siteIssue->priority === 'critical',
            
            // ENHANCED: Additional metadata for frontend processing
            'metadata' => [
                'description_preview' => Str::limit($this->siteIssue->description, 100),
                'immediate_actions' => $this->siteIssue->immediate_actions_taken ? Str::limit($this->siteIssue->immediate_actions_taken, 100) : null,
                'suggested_solutions' => $this->siteIssue->suggested_solutions ? Str::limit($this->siteIssue->suggested_solutions, 100) : null,
                'issue_type_icon' => match($this->siteIssue->issue_type) {
                    'safety' => 'fas fa-hard-hat',
                    'equipment' => 'fas fa-tools',
                    'environmental' => 'fas fa-leaf',
                    'personnel' => 'fas fa-users',
                    'quality' => 'fas fa-check-circle',
                    'timeline' => 'fas fa-clock',
                    default => 'fas fa-exclamation-circle'
                },
                'estimated_resolution_time' => match($this->siteIssue->priority) {
                    'critical' => '1-2 hours',
                    'high' => '4-8 hours',
                    'medium' => '1-2 days',
                    'low' => '3-5 days',
                    default => '1-2 days'
                }
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
        // Send immediately for critical issues or safety issues
        return $this->siteIssue->priority === 'critical' || 
               $this->siteIssue->issue_type === 'safety';
    }

    /**
     * ENHANCED: Get notification tags for better organization
     */
    public function tags(): array
    {
        $tags = [
            'site_issue',
            'site_issue_reported',
            'project_' . $this->siteIssue->project_id,
            'priority_' . $this->siteIssue->priority,
            'type_' . $this->siteIssue->issue_type,
            'status_' . $this->siteIssue->status,
            'reported_by_' . $this->siteIssue->user_id,
        ];

        // Add priority-based tags
        if ($this->siteIssue->priority === 'critical') {
            $tags[] = 'critical_issue';
            $tags[] = 'immediate_attention';
        }

        if ($this->siteIssue->issue_type === 'safety') {
            $tags[] = 'safety_issue';
            $tags[] = 'safety_alert';
        }

        if ($this->siteIssue->task_id) {
            $tags[] = 'task_' . $this->siteIssue->task_id;
        }

        return $tags;
    }

    /**
     * ENHANCED: Get notification metadata for analytics
     */
    public function getMetadata(): array
    {
        return [
            'site_issue_id' => $this->siteIssue->id,
            'project_id' => $this->siteIssue->project_id,
            'task_id' => $this->siteIssue->task_id,
            'reported_by' => $this->siteIssue->user_id,
            'issue_type' => $this->siteIssue->issue_type,
            'priority' => $this->siteIssue->priority,
            'status' => $this->siteIssue->status,
            'has_photos' => $this->siteIssue->photos && count($this->siteIssue->photos) > 0,
            'has_attachments' => $this->siteIssue->attachments && count($this->siteIssue->attachments) > 0,
            'has_estimated_cost' => $this->siteIssue->estimated_cost !== null,
            'notification_sent_at' => now()->toISOString(),
            'requires_immediate_attention' => $this->shouldSendImmediately(),
        ];
    }

    /**
     * ENHANCED: Customize notification delivery delay based on priority
     */
    public function delay()
    {
        // Send immediately for critical and safety issues
        if ($this->shouldSendImmediately()) {
            return null; // No delay
        }

        // Small delay for other issues to batch notifications
        return now()->addMinutes(2);
    }
}