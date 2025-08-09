<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'created_by',
        'title',
        'description',
        'update_type',
        'visibility',
        'is_major_milestone',
        'progress_percentage',
        'attachments',
        'tags',
        'posted_at',
        'notify_clients',
    ];

    protected $casts = [
        'attachments' => 'array',
        'tags' => 'array',
        'posted_at' => 'datetime',
        'is_major_milestone' => 'boolean',
        'notify_clients' => 'boolean',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the project this update belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created this update
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for client-visible updates
     */
    public function scopeClientVisible($query)
    {
        return $query->whereIn('visibility', ['public', 'client']);
    }

    /**
     * Scope for public updates
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for major milestones
     */
    public function scopeMajorMilestones($query)
    {
        return $query->where('is_major_milestone', true);
    }

    /**
     * Scope for recent updates
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('posted_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted update type
     */
    public function getFormattedUpdateTypeAttribute(): string
    {
        return match($this->update_type) {
            'progress' => 'Progress Update',
            'milestone' => 'Milestone',
            'completion' => 'Completion',
            'issue' => 'Issue Update',
            'announcement' => 'Announcement',
            'other' => 'Update',
            default => ucfirst($this->update_type)
        };
    }

    /**
     * Get update type color for badges
     */
    public function getUpdateTypeColorAttribute(): string
    {
        return match($this->update_type) {
            'progress' => 'primary',
            'milestone' => 'success',
            'completion' => 'success',
            'issue' => 'warning',
            'announcement' => 'info',
            'other' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get visibility display text
     */
    public function getVisibilityDisplayAttribute(): string
    {
        return match($this->visibility) {
            'public' => 'Public',
            'client' => 'Client Visible',
            'internal' => 'Internal Only',
            default => ucfirst($this->visibility)
        };
    }

    /**
     * Get visibility color for badges
     */
    public function getVisibilityColorAttribute(): string
    {
        return match($this->visibility) {
            'public' => 'success',
            'client' => 'primary',
            'internal' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Check if update has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) && is_array($this->attachments) && count($this->attachments) > 0;
    }

    /**
     * Get attachment count
     */
    public function getAttachmentCountAttribute(): int
    {
        return $this->hasAttachments() ? count($this->attachments) : 0;
    }

    /**
     * Check if update has tags
     */
    public function hasTags(): bool
    {
        return !empty($this->tags) && is_array($this->tags) && count($this->tags) > 0;
    }

    /**
     * Get tags as a comma-separated string
     */
    public function getTagsStringAttribute(): string
    {
        return $this->hasTags() ? implode(', ', $this->tags) : '';
    }

    /**
     * Get formatted posted date
     */
    public function getFormattedPostedDateAttribute(): string
    {
        return $this->posted_at->format('M d, Y');
    }

    /**
     * Get time since posted
     */
    public function getTimeSincePostedAttribute(): string
    {
        return $this->posted_at->diffForHumans();
    }

    /**
     * Check if update is recent (within last 7 days)
     */
    public function isRecent(): bool
    {
        return $this->posted_at->isAfter(now()->subDays(7));
    }

    /**
     * Check if update is a milestone
     */
    public function isMilestone(): bool
    {
        return $this->is_major_milestone || $this->update_type === 'milestone';
    }

    /**
     * Get progress percentage with fallback
     */
    public function getProgressPercentageAttribute($value): int
    {
        return $value ?? 0;
    }

    /**
     * Get icon for update type
     */
    public function getUpdateIconAttribute(): string
    {
        return match($this->update_type) {
            'progress' => 'fas fa-chart-line',
            'milestone' => 'fas fa-flag-checkered',
            'completion' => 'fas fa-check-circle',
            'issue' => 'fas fa-exclamation-triangle',
            'announcement' => 'fas fa-bullhorn',
            'other' => 'fas fa-info-circle',
            default => 'fas fa-clipboard'
        };
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($update) {
            if (!$update->posted_at) {
                $update->posted_at = now();
            }
        });
    }
}