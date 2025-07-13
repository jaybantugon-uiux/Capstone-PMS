<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SitePhoto;

class SitePhotoSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $sitePhoto;

    public function __construct(SitePhoto $sitePhoto)
    {
        $this->sitePhoto = $sitePhoto;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Site Photo Submitted for Review')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new site photo has been submitted for your review.')
            ->line('**Photo Details:**')
            ->line('Title: ' . $this->sitePhoto->title)
            ->line('Project: ' . $this->sitePhoto->project->name)
            ->line('Uploaded by: ' . $this->sitePhoto->uploader->first_name . ' ' . $this->sitePhoto->uploader->last_name)
            ->line('Photo Date: ' . $this->sitePhoto->formatted_photo_date)
            ->line('Category: ' . $this->sitePhoto->formatted_photo_category)
            ->action('Review Photo', route('admin.site-photos.show', $this->sitePhoto))
            ->line('Please review and approve/reject this photo submission.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'site_photo_submitted',
            'title' => 'New Site Photo Submitted',
            'message' => $this->sitePhoto->uploader->first_name . ' ' . $this->sitePhoto->uploader->last_name . 
                        ' submitted a photo for project "' . $this->sitePhoto->project->name . '"',
            'photo_id' => $this->sitePhoto->id,
            'photo_title' => $this->sitePhoto->title,
            'project_id' => $this->sitePhoto->project_id,
            'project_name' => $this->sitePhoto->project->name,
            'uploader_name' => $this->sitePhoto->uploader->first_name . ' ' . $this->sitePhoto->uploader->last_name,
            'action_url' => route('admin.site-photos.show', $this->sitePhoto),
        ];
    }
}