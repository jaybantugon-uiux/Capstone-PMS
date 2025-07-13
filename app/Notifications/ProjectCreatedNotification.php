<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class ProjectCreatedNotification extends Notification
{
    use Queueable;

    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        // Generate the correct URL for the project
        $projectUrl = route('projects.show', $this->project);
        
        $mail = (new MailMessage)
            ->subject('🏗️ New Project Created: ' . $this->project->name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new project has been created that may require your attention. You may be assigned tasks in this project soon.')
            ->line('')
            ->line('**📊 Project Details:**')
            ->line('**Project Name:** ' . $this->project->name)
            ->line('**Description:** ' . ($this->project->description ?: 'No description provided'))
            ->line('**Start Date:** ' . $this->project->formatted_start_date)
            ->line('**End Date:** ' . ($this->project->formatted_end_date ?: 'Not specified'))
            ->line('**Created by:** ' . $this->project->creator->full_name)
            ->line('')
            ->action('🔍 View Project Details', $projectUrl)
            ->line('')
            ->line('**What to expect:**')
            ->line('• Tasks may be assigned to you in this project')
            ->line('• You will receive notifications for any task assignments')
            ->line('• You can view project progress and updates')
            ->line('• You can submit progress reports and site issues')
            ->line('')
            ->line('Stay tuned for task assignments and project updates!')
            ->salutation('Best regards, ' . config('app.name'));

        return $mail;
    }

    public function toArray($notifiable)
    {
        // Generate the correct URL for the project
        $projectUrl = route('projects.show', $this->project);
        
        return [
            'type' => 'project_created',
            'title' => 'New Project Created',
            'message' => 'New project "' . $this->project->name . '" has been created by ' . $this->project->creator->full_name . '. You may be assigned tasks in this project.',
            
            // Project details
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'project_description' => $this->project->description,
            
            // Creator details
            'creator_id' => $this->project->creator->id,
            'creator_name' => $this->project->creator->full_name,
            'creator_email' => $this->project->creator->email,
            
            // Dates
            'start_date' => $this->project->formatted_start_date,
            'end_date' => $this->project->formatted_end_date,
            'start_date_raw' => $this->project->start_date ? $this->project->start_date->toDateString() : null,
            'end_date_raw' => $this->project->end_date ? $this->project->end_date->toDateString() : null,
            'created_at' => $this->project->created_at->format('M d, Y H:i'),
            
            // URLs
            'project_url' => $projectUrl,
            'url' => $projectUrl, // Default URL for notifications
            'action_url' => $projectUrl,
            
            // UI properties
            'icon' => 'fas fa-project-diagram',
            'color' => $this->getProjectUrgencyColor(),
            
            // Additional context
            'is_starting_soon' => $this->isProjectStartingSoon(),
            'days_until_start' => $this->getDaysUntilStart(),
            'project_duration_days' => $this->getProjectDurationDays(),
        ];
    }

    /**
     * Get urgency-based color for UI display
     */
    private function getProjectUrgencyColor()
    {
        if ($this->isProjectStartingSoon()) {
            return 'warning'; // Starting soon
        } elseif ($this->project->start_date && $this->project->start_date->isPast()) {
            return 'info'; // Already started
        } else {
            return 'success'; // Future project
        }
    }

    /**
     * Check if project is starting soon (within 7 days)
     */
    private function isProjectStartingSoon()
    {
        if (!$this->project->start_date) {
            return false;
        }
        
        $daysUntilStart = now()->diffInDays($this->project->start_date, false);
        return $daysUntilStart >= 0 && $daysUntilStart <= 7;
    }

    /**
     * Get days until project start
     */
    private function getDaysUntilStart()
    {
        if (!$this->project->start_date) {
            return null;
        }
        
        $daysUntilStart = now()->diffInDays($this->project->start_date, false);
        
        if ($daysUntilStart < 0) {
            return 'started';
        } elseif ($daysUntilStart === 0) {
            return 'starting_today';
        } else {
            return $daysUntilStart;
        }
    }

    /**
     * Get project duration in days
     */
    private function getProjectDurationDays()
    {
        if (!$this->project->start_date || !$this->project->end_date) {
            return null;
        }
        
        return $this->project->start_date->diffInDays($this->project->end_date);
    }
}