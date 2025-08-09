<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SitePhoto;
use App\Models\SitePhotoComment;
use Illuminate\Support\Str;

class SitePhotoCommentAdded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $sitePhoto;
    protected $comment;
    protected $commenterName;

    public function __construct(SitePhoto $sitePhoto, SitePhotoComment $comment, $commenterName)
    {
        $this->sitePhoto = $sitePhoto;
        $this->comment = $comment;
        $this->commenterName = $commenterName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Comment on Your Site Photo')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new comment has been added to your site photo.')
            ->line('**Photo:** ' . $this->sitePhoto->title)
            ->line('**Project:** ' . $this->sitePhoto->project->name)
            ->line('**Comment by:** ' . $this->commenterName)
            ->line('**Comment:**')
            ->line('"' . $this->comment->comment . '"')
            ->action('View Photo', route('sc.site-photos.show', $this->sitePhoto))
            ->line('You can reply to this comment or take any necessary action.')
            ->salutation('Best regards, ' . config('app.name') . ' Team');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'site_photo_comment_added',
            'title' => 'New Comment on Site Photo',
            'message' => $this->commenterName . ' commented on your photo "' . $this->sitePhoto->title . '"',
            'photo_id' => $this->sitePhoto->id,
            'photo_title' => $this->sitePhoto->title,
            'project_id' => $this->sitePhoto->project_id,
            'project_name' => $this->sitePhoto->project->name,
            'comment_id' => $this->comment->id,
            'commenter_name' => $this->commenterName,
            'comment_preview' => Str::limit($this->comment->comment, 100),
            'action_url' => route('sc.site-photos.show', $this->sitePhoto),
        ];
    }
}