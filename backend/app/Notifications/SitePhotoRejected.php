<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SitePhoto;
use Illuminate\Support\Str;

class SitePhotoRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $sitePhoto;
    protected $reviewerName;

    public function __construct(SitePhoto $sitePhoto, $reviewerName)
    {
        $this->sitePhoto = $sitePhoto;
        $this->reviewerName = $reviewerName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Site Photo Requires Revision')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Your site photo submission requires revision before approval.')
            ->line('**Photo Details:**')
            ->line('Title: ' . $this->sitePhoto->title)
            ->line('Project: ' . $this->sitePhoto->project->name)
            ->line('Reviewed by: ' . $this->reviewerName)
            ->line('Review Date: ' . $this->sitePhoto->formatted_reviewed_at)
            ->line('**Rejection Reason:**')
            ->line($this->sitePhoto->rejection_reason)
            ->action('Edit Photo', route('sc.site-photos.edit', $this->sitePhoto))
            ->line('Please make the necessary revisions and resubmit your photo.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'site_photo_rejected',
            'title' => 'Site Photo Requires Revision',
            'message' => 'Your photo "' . $this->sitePhoto->title . '" requires revision. Reason: ' . 
                        Str::limit($this->sitePhoto->rejection_reason, 100),
            'photo_id' => $this->sitePhoto->id,
            'photo_title' => $this->sitePhoto->title,
            'project_id' => $this->sitePhoto->project_id,
            'project_name' => $this->sitePhoto->project->name,
            'reviewer_name' => $this->reviewerName,
            'rejection_reason' => $this->sitePhoto->rejection_reason,
            'action_url' => route('sc.site-photos.edit', $this->sitePhoto),
        ];
    }
}