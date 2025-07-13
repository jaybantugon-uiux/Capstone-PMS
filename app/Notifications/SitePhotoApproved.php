<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SitePhoto;

class SitePhotoApproved extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Your Site Photo Has Been Approved')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Great news! Your site photo has been approved.')
            ->line('**Photo Details:**')
            ->line('Title: ' . $this->sitePhoto->title)
            ->line('Project: ' . $this->sitePhoto->project->name)
            ->line('Reviewed by: ' . $this->reviewerName)
            ->line('Review Date: ' . $this->sitePhoto->formatted_reviewed_at);

        if ($this->sitePhoto->admin_rating) {
            $message->line('Rating: ' . $this->sitePhoto->admin_rating . '/5 stars');
        }

        if ($this->sitePhoto->admin_comments) {
            $message->line('**Admin Comments:**')
                   ->line($this->sitePhoto->admin_comments);
        }

        if ($this->sitePhoto->is_featured) {
            $message->line('🌟 **Your photo has been marked as featured!**');
        }

        return $message->action('View Photo', route('sc.site-photos.show', $this->sitePhoto))
                      ->line('Thank you for your excellent work documenting the project progress!')
                      ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'site_photo_approved',
            'title' => 'Site Photo Approved',
            'message' => 'Your photo "' . $this->sitePhoto->title . '" has been approved' .
                        ($this->sitePhoto->is_featured ? ' and marked as featured!' : '!'),
            'photo_id' => $this->sitePhoto->id,
            'photo_title' => $this->sitePhoto->title,
            'project_id' => $this->sitePhoto->project_id,
            'project_name' => $this->sitePhoto->project->name,
            'reviewer_name' => $this->reviewerName,
            'admin_rating' => $this->sitePhoto->admin_rating,
            'is_featured' => $this->sitePhoto->is_featured,
            'action_url' => route('sc.site-photos.show', $this->sitePhoto),
        ];
    }
}