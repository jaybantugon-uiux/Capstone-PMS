<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteIssueComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_issue_id',
        'user_id',
        'comment',
        'is_internal',
        'attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    // RELATIONSHIPS
    /**
     * Get the site issue this comment belongs to
     */
    public function siteIssue(): BelongsTo
    {
        return $this->belongsTo(SiteIssue::class);
    }

    /**
     * Get the user who made this comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSORS
    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get comment type badge
     */
    public function getTypeBadgeAttribute()
    {
        return $this->is_internal ? 
            '<span class="badge bg-warning">Internal</span>' : 
            '<span class="badge bg-info">External</span>';
    }

    /**
     * Get comment visibility text
     */
    public function getVisibilityTextAttribute()
    {
        return $this->is_internal ? 'Internal (Admin Only)' : 'External (Visible to Site Coordinator)';
    }

    // SCOPES
    /**
     * Scope for external comments
     */
    public function scopeExternal($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal comments
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Scope for comments by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent comments
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if user can view this comment
     */
    public function canBeViewedBy($user)
    {
        // Internal comments only visible to admins/PMs
        if ($this->is_internal) {
            return in_array($user->role, ['admin', 'pm']);
        }
        
        // External comments visible to everyone involved in the issue
        return true;
    }

    /**
     * Check if user can edit this comment
     */
    public function canBeEditedBy($user)
    {
        // Users can edit their own comments within 24 hours
        if ($user->id === $this->user_id) {
            return $this->created_at->diffInHours(now()) <= 24;
        }
        
        // Admins can always edit
        return $user->role === 'admin';
    }
}