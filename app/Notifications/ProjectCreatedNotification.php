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
        return (new MailMessage)
                    ->subject('New Project Created: ' . $this->project->name)
                    ->greeting('Hello ' . $notifiable->first_name . '!')
                    ->line('A new project has been created that may require your attention.')
                    ->line('**Project:** ' . $this->project->name)
                    ->line('**Description:** ' . ($this->project->description ?: 'No description provided'))
                    ->line('**Start Date:** ' . $this->project->formatted_start_date)
                    ->line('**End Date:** ' . ($this->project->formatted_end_date ?: 'Not specified'))
                    ->line('**Created by:** ' . $this->project->creator->full_name)
                    ->action('View Project', route('projects.show', $this->project->id))
                    ->line('Thank you for your attention to this project!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'project_created',
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'project_description' => $this->project->description,
            'creator_name' => $this->project->creator->full_name,
            'start_date' => $this->project->formatted_start_date,
            'end_date' => $this->project->formatted_end_date,
            'message' => 'New project "' . $this->project->name . '" has been created by ' . $this->project->creator->full_name,
            'action_url' => route('projects.show', $this->project->id), 
        ];
    }
}