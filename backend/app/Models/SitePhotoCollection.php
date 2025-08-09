<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SitePhotoCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'collection_name',
        'description',
        'collection_date',
        'cover_photo_id',
        'submission_status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'admin_comments',
        'total_photos',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'total_photos' => 'integer',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $dates = [
        'collection_date',
        'submitted_at',
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // RELATIONSHIPS
    /**
     * Get the project this collection belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user (site coordinator) who created this collection
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the cover photo for this collection
     */
    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(SitePhoto::class, 'cover_photo_id');
    }

    /**
     * Get the admin who reviewed this collection
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all photos in this collection
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(SitePhoto::class, 'site_photo_collection_items', 'collection_id', 'photo_id')
                    ->withPivot('sort_order', 'added_at')
                    ->orderByPivot('sort_order');
    }

    /**
     * Get collection items (junction records)
     */
    public function collectionItems(): HasMany
    {
        return $this->hasMany(SitePhotoCollectionItem::class, 'collection_id');
    }

    // ACCESSORS
    /**
     * Get formatted collection date
     */
    public function getFormattedCollectionDateAttribute()
    {
        return $this->collection_date ? $this->collection_date->format('M d, Y') : null;
    }

    /**
     * Get formatted submitted date
     */
    public function getFormattedSubmittedAtAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get formatted reviewed date
     */
    public function getFormattedReviewedAtAttribute()
    {
        return $this->reviewed_at ? $this->reviewed_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get submission status badge color
     */
    public function getSubmissionStatusBadgeColorAttribute()
    {
        return match($this->submission_status) {
            'draft' => 'secondary',
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'light'
        };
    }

    /**
     * Get formatted submission status
     */
    public function getFormattedSubmissionStatusAttribute()
    {
        return ucfirst($this->submission_status);
    }

    /**
     * Check if collection is overdue for review
     */
    public function getIsOverdueForReviewAttribute()
    {
        return $this->submission_status === 'submitted' && 
               $this->submitted_at && 
               $this->submitted_at->diffInDays(now()) > 3;
    }

    /**
     * Get collection age in human readable format
     */
    public function getAgeAttribute()
    {
        return $this->collection_date->diffForHumans();
    }

    /**
     * Get days since submission
     */
    public function getDaysSinceSubmissionAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->diffInDays(now()) : null;
    }

    /**
     * Get cover photo URL or default
     */
    public function getCoverPhotoUrlAttribute()
    {
        if ($this->coverPhoto) {
            return $this->coverPhoto->thumbnail_url;
        }
        
        // Use first photo as cover if no cover photo set
        $firstPhoto = $this->photos()->first();
        return $firstPhoto ? $firstPhoto->thumbnail_url : '/images/default-collection-cover.jpg';
    }

    /**
     * Check if collection needs attention
     */
    public function getNeedsAttentionAttribute()
    {
        return $this->is_overdue_for_review || 
               ($this->submission_status === 'rejected' && !$this->reviewed_at);
    }

    // SCOPES
    /**
     * Scope for collections in draft status
     */
    public function scopeDraft($query)
    {
        return $query->where('submission_status', 'draft');
    }

    /**
     * Scope for submitted collections
     */
    public function scopeSubmitted($query)
    {
        return $query->where('submission_status', 'submitted');
    }

    /**
     * Scope for approved collections
     */
    public function scopeApproved($query)
    {
        return $query->where('submission_status', 'approved');
    }

    /**
     * Scope for rejected collections
     */
    public function scopeRejected($query)
    {
        return $query->where('submission_status', 'rejected');
    }

    /**
     * Scope for public collections
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for collections created by user
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for collections in project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for recent collections
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('collection_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for collections within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('collection_date', [$startDate, $endDate]);
    }

    /**
     * Scope for overdue reviews
     */
    public function scopeOverdueForReview($query)
    {
        return $query->where('submission_status', 'submitted')
                    ->where('submitted_at', '<', now()->subDays(3));
    }

    /**
     * Scope for collections needing review
     */
    public function scopeNeedingReview($query)
    {
        return $query->where('submission_status', 'submitted');
    }

    // HELPER METHODS
    /**
     * Submit collection for review
     */
    public function submit()
    {
        $this->update([
            'submission_status' => 'submitted',
            'submitted_at' => now()
        ]);

        // Also submit all photos in the collection
        $this->photos()->where('submission_status', 'draft')->update([
            'submission_status' => 'submitted',
            'submitted_at' => now()
        ]);
    }

    /**
     * Approve collection
     */
    public function approve($reviewerId, $comments = null, $isPublic = false)
    {
        $this->update([
            'submission_status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $comments,
            'is_public' => $isPublic,
        ]);

        // Also approve all photos in the collection
        $this->photos()->where('submission_status', 'submitted')->update([
            'submission_status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Reject collection
     */
    public function reject($reviewerId, $rejectionReason)
    {
        $this->update([
            'submission_status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $rejectionReason,
        ]);
    }

    /**
     * Add photo to collection
     */
    public function addPhoto(SitePhoto $photo, $sortOrder = null)
    {
        if ($sortOrder === null) {
            $sortOrder = $this->photos()->count() + 1;
        }

        $this->photos()->attach($photo->id, [
            'sort_order' => $sortOrder,
            'added_at' => now()
        ]);

        $this->updatePhotoCount();
        $this->updateCoverPhotoIfNeeded();
    }

    /**
     * Remove photo from collection
     */
    public function removePhoto(SitePhoto $photo)
    {
        $this->photos()->detach($photo->id);
        $this->updatePhotoCount();
        $this->updateCoverPhotoIfNeeded();
    }

    /**
     * Update photo count
     */
    public function updatePhotoCount()
    {
        $this->update(['total_photos' => $this->photos()->count()]);
    }

    /**
     * Update cover photo if needed
     */
    public function updateCoverPhotoIfNeeded()
    {
        // If cover photo is not set or no longer in collection, set first photo as cover
        if (!$this->cover_photo_id || !$this->photos()->where('site_photos.id', $this->cover_photo_id)->exists()) {
            $firstPhoto = $this->photos()->first();
            $this->update(['cover_photo_id' => $firstPhoto?->id]);
        }
    }

    /**
     * Set cover photo
     */
    public function setCoverPhoto(SitePhoto $photo)
    {
        // Ensure photo is in this collection
        if ($this->photos()->where('site_photos.id', $photo->id)->exists()) {
            $this->update(['cover_photo_id' => $photo->id]);
        }
    }

    /**
     * Reorder photos in collection
     */
    public function reorderPhotos(array $photoIds)
    {
        foreach ($photoIds as $index => $photoId) {
            $this->photos()->updateExistingPivot($photoId, ['sort_order' => $index + 1]);
        }
    }

    /**
     * Make collection public
     */
    public function makePublic()
    {
        $this->update(['is_public' => true]);
        
        // Also make all approved photos in collection public
        $this->photos()->where('submission_status', 'approved')->update(['is_public' => true]);
    }

    /**
     * Make collection private
     */
    public function makePrivate()
    {
        $this->update(['is_public' => false]);
    }

    /**
     * Check if user can view this collection
     */
    public function canBeViewedBy($user)
    {
        // Collection creator can always view their collections
        if ($user->id === $this->user_id) {
            return true;
        }

        // Admins and PMs can view all collections
        if (in_array($user->role, ['admin', 'pm'])) {
            return true;
        }

        // Public collections can be viewed by clients in the same project
        if ($this->is_public && $user->role === 'client') {
            return true;
        }

        // Other site coordinators in the same project can view approved collections
        if ($user->role === 'sc' && $this->submission_status === 'approved') {
            return $user->tasks()->whereHas('project', function($query) {
                $query->where('id', $this->project_id);
            })->exists();
        }

        return false;
    }

    /**
     * Check if user can edit this collection
     */
    public function canBeEditedBy($user)
    {
        // Collection creator can edit if not yet reviewed or if rejected
        if ($user->id === $this->user_id) {
            return in_array($this->submission_status, ['draft', 'rejected']);
        }

        // Admins can always edit
        return $user->role === 'admin';
    }

    /**
     * Check if user can delete this collection
     */
    public function canBeDeletedBy($user)
    {
        // Collection creator can delete if not yet submitted or if rejected
        if ($user->id === $this->user_id) {
            return in_array($this->submission_status, ['draft', 'rejected']);
        }

        // Admins can always delete
        return $user->role === 'admin';
    }

    /**
     * Get summary stats for a collection of collections
     */
    public static function getSummaryStats($collections)
    {
        return [
            'total' => $collections->count(),
            'draft' => $collections->where('submission_status', 'draft')->count(),
            'submitted' => $collections->where('submission_status', 'submitted')->count(),
            'approved' => $collections->where('submission_status', 'approved')->count(),
            'rejected' => $collections->where('submission_status', 'rejected')->count(),
            'public' => $collections->where('is_public', true)->count(),
            'total_photos' => $collections->sum('total_photos'),
            'avg_photos_per_collection' => $collections->avg('total_photos'),
            'overdue_reviews' => $collections->filter(function($collection) {
                return $collection->is_overdue_for_review;
            })->count(),
        ];
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update photo count when collection is saved
        static::saved(function ($collection) {
            // This will be handled by triggers or explicit calls
        });

        // Clean up collection items when collection is deleted
        static::deleting(function ($collection) {
            $collection->collectionItems()->delete();
        });
    }
}