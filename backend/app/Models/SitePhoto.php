<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SitePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'title',
        'description',
        'photo_path',
        'original_filename',
        'file_size',
        'mime_type',
        'photo_date',
        'location',
        'weather_conditions',
        'photo_category',
        'submission_status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'admin_comments',
        'admin_rating',
        'rejection_reason',
        'camera_info',
        'tags',
        'is_featured',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'photo_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'camera_info' => 'array',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'admin_rating' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $dates = [
        'photo_date',
        'submitted_at',
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // RELATIONSHIPS
    /**
     * Get the project this photo belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the task this photo is related to (optional)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user (site coordinator) who uploaded this photo
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who reviewed this photo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all comments for this photo
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SitePhotoComment::class, 'photo_id');
    }

    /**
     * Get only external comments (visible to site coordinator)
     */
    public function externalComments()
    {
        return $this->hasMany(SitePhotoComment::class, 'photo_id')->where('is_internal', false);
    }

    /**
     * Get only internal comments (admin only)
     */
    public function internalComments()
    {
        return $this->hasMany(SitePhotoComment::class, 'photo_id')->where('is_internal', true);
    }

    /**
     * Get photo collections this photo belongs to
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(SitePhotoCollection::class, 'site_photo_collection_items', 'photo_id', 'collection_id')
                    ->withPivot('sort_order', 'added_at')
                    ->withTimestamps();
    }

    // ACCESSORS
    /**
     * Get formatted photo date
     */
    public function getFormattedPhotoDateAttribute()
    {
        return $this->photo_date ? $this->photo_date->format('M d, Y') : null;
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
     * Get photo category badge color
     */
    public function getPhotoCategoryBadgeColorAttribute()
    {
        return match($this->photo_category) {
            'progress' => 'primary',
            'quality' => 'info',
            'safety' => 'danger',
            'equipment' => 'warning',
            'materials' => 'secondary',
            'workers' => 'success',
            'documentation' => 'dark',
            'issues' => 'danger',
            'completion' => 'success',
            'other' => 'light',
            default => 'secondary'
        };
    }

    /**
     * Get weather icon class
     */
    public function getWeatherIconAttribute()
    {
        return match($this->weather_conditions) {
            'sunny' => 'fas fa-sun text-warning',
            'cloudy' => 'fas fa-cloud text-secondary',
            'rainy' => 'fas fa-cloud-rain text-primary',
            'stormy' => 'fas fa-bolt text-warning',
            'windy' => 'fas fa-wind text-info',
            default => 'fas fa-question text-muted'
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
     * Get formatted photo category
     */
    public function getFormattedPhotoCategoryAttribute()
    {
        return ucfirst($this->photo_category);
    }

    /**
     * Get formatted weather conditions
     */
    public function getFormattedWeatherConditionsAttribute()
    {
        return $this->weather_conditions ? ucfirst($this->weather_conditions) : null;
    }

    /**
     * Get file size in human readable format
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute()
    {
        return Storage::url($this->photo_path);
    }

    /**
     * Get thumbnail URL (assuming thumbnails are generated)
     */
    public function getThumbnailUrlAttribute()
    {
        $thumbnailPath = str_replace('/originals/', '/thumbnails/', $this->photo_path);
        return Storage::url($thumbnailPath);
    }

    /**
     * Check if photo is overdue for review
     */
    public function getIsOverdueForReviewAttribute()
    {
        return $this->submission_status === 'submitted' && 
               $this->submitted_at && 
               $this->submitted_at->diffInDays(now()) > 3;
    }

    /**
     * Get photo age in human readable format
     */
    public function getAgeAttribute()
    {
        return $this->photo_date->diffForHumans();
    }

    /**
     * Get days since submission
     */
    public function getDaysSinceSubmissionAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->diffInDays(now()) : null;
    }

    /**
     * Get admin rating stars
     */
    public function getRatingStarsAttribute()
    {
        if (!$this->admin_rating) return '';
        
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->admin_rating) {
                $stars .= '<i class="fas fa-star text-warning"></i>';
            } else {
                $stars .= '<i class="far fa-star text-muted"></i>';
            }
        }
        return $stars;
    }

    /**
     * Check if photo needs attention
     */
    public function getNeedsAttentionAttribute()
    {
        return $this->is_overdue_for_review || 
               ($this->submission_status === 'rejected' && !$this->reviewed_at);
    }

    /**
     * Get image dimensions if available from camera_info
     */
    public function getImageDimensionsAttribute()
    {
        if ($this->camera_info && isset($this->camera_info['width'], $this->camera_info['height'])) {
            return $this->camera_info['width'] . ' × ' . $this->camera_info['height'];
        }
        return null;
    }

    // SCOPES
    /**
     * Scope for photos in draft status
     */
    public function scopeDraft($query)
    {
        return $query->where('submission_status', 'draft');
    }

    /**
     * Scope for submitted photos
     */
    public function scopeSubmitted($query)
    {
        return $query->where('submission_status', 'submitted');
    }

    /**
     * Scope for approved photos
     */
    public function scopeApproved($query)
    {
        return $query->where('submission_status', 'approved');
    }

    /**
     * Scope for rejected photos
     */
    public function scopeRejected($query)
    {
        return $query->where('submission_status', 'rejected');
    }

    /**
     * Scope for photos by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('photo_category', $category);
    }

    /**
     * Scope for featured photos
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for public photos
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for photos uploaded by user
     */
    public function scopeUploadedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for photos in project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for photos related to task
     */
    public function scopeForTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for recent photos
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('photo_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for photos taken within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('photo_date', [$startDate, $endDate]);
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
     * Scope for photos needing review
     */
    public function scopeNeedingReview($query)
    {
        return $query->where('submission_status', 'submitted');
    }

    /**
     * Scope for photos with tags
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // HELPER METHODS
    /**
     * Submit photo for review
     */
    public function submit()
    {
        $this->update([
            'submission_status' => 'submitted',
            'submitted_at' => now()
        ]);
    }

    /**
     * Approve photo
     */
    public function approve($reviewerId, $comments = null, $rating = null, $isFeatured = false, $isPublic = false)
    {
        $this->update([
            'submission_status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $comments,
            'admin_rating' => $rating,
            'is_featured' => $isFeatured,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Reject photo
     */
    public function reject($reviewerId, $rejectionReason)
    {
        $this->update([
            'submission_status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $rejectionReason,
        ]);
    }

    /**
     * Mark as featured
     */
    public function markAsFeatured()
    {
        $this->update(['is_featured' => true]);
    }

    /**
     * Unmark as featured
     */
    public function unmarkAsFeatured()
    {
        $this->update(['is_featured' => false]);
    }

    /**
     * Make public
     */
    public function makePublic()
    {
        $this->update(['is_public' => true]);
    }

    /**
     * Make private
     */
    public function makePrivate()
    {
        $this->update(['is_public' => false]);
    }

    /**
     * Add tag
     */
    public function addTag($tag)
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove tag
     */
    public function removeTag($tag)
    {
        $tags = $this->tags ?? [];
        $tags = array_values(array_filter($tags, fn($t) => $t !== $tag));
        $this->update(['tags' => $tags]);
    }

    /**
     * Check if user can view this photo
     */
    public function canBeViewedBy($user)
    {
        // Photo uploader can always view their photos
        if ($user->id === $this->user_id) {
            return true;
        }

        // Admins and PMs can view all photos
        if (in_array($user->role, ['admin', 'pm'])) {
            return true;
        }

        // Public photos can be viewed by clients in the same project
        if ($this->is_public && $user->role === 'client') {
            // Check if user is client for this project (implement project-client relationship)
            return true;
        }

        // Other site coordinators in the same project can view approved photos
        if ($user->role === 'sc' && $this->submission_status === 'approved') {
            return $user->tasks()->whereHas('project', function($query) {
                $query->where('id', $this->project_id);
            })->exists();
        }

        return false;
    }

    /**
     * Check if user can edit this photo
     */
    public function canBeEditedBy($user)
    {
        // Photo uploader can edit if not yet reviewed or if rejected
        if ($user->id === $this->user_id) {
            return in_array($this->submission_status, ['draft', 'rejected']);
        }

        // Admins can always edit
        return $user->role === 'admin';
    }

    /**
     * Check if user can delete this photo
     */
    public function canBeDeletedBy($user)
    {
        // Photo uploader can delete if not yet submitted or if rejected
        if ($user->id === $this->user_id) {
            return in_array($this->submission_status, ['draft', 'rejected']);
        }

        // Admins can always delete
        return $user->role === 'admin';
    }

    /**
     * Get summary stats for a collection of photos
     */
    public static function getSummaryStats($photos)
    {
        return [
            'total' => $photos->count(),
            'draft' => $photos->where('submission_status', 'draft')->count(),
            'submitted' => $photos->where('submission_status', 'submitted')->count(),
            'approved' => $photos->where('submission_status', 'approved')->count(),
            'rejected' => $photos->where('submission_status', 'rejected')->count(),
            'featured' => $photos->where('is_featured', true)->count(),
            'public' => $photos->where('is_public', true)->count(),
            'categories' => $photos->groupBy('photo_category')->map->count(),
            'total_size' => $photos->sum('file_size'),
            'average_rating' => $photos->where('admin_rating', '>', 0)->avg('admin_rating'),
            'overdue_reviews' => $photos->filter(function($photo) {
                return $photo->is_overdue_for_review;
            })->count(),
        ];
    }

    /**
     * Delete photo file from storage
     */
 public function deletePhotoFile()
{
    try {
        $deletedFiles = [];
        
        // Delete original photo
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            Storage::disk('public')->delete($this->photo_path);
            $deletedFiles[] = $this->photo_path;
        }

        // Delete thumbnail if exists
        $thumbnailPath = str_replace('/originals/', '/thumbnails/', $this->photo_path);
        if (Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
            $deletedFiles[] = $thumbnailPath;
        }
        
        // Try to clean up empty directories
        $this->cleanupEmptyDirectories();
        
        Log::info('Photo files deleted successfully', [
            'photo_id' => $this->id,
            'deleted_files' => $deletedFiles
        ]);
        
        return true;
        
    } catch (\Exception $e) {
        Log::error('Failed to delete photo files: ' . $e->getMessage(), [
            'photo_id' => $this->id,
            'photo_path' => $this->photo_path,
            'error' => $e->getTraceAsString()
        ]);
        
        return false;
    }
}

private function cleanupEmptyDirectories()
{
    try {
        if (!$this->photo_path) return;
        
        $directory = dirname($this->photo_path);
        $fullPath = storage_path('app/public/' . $directory);
        
        // Only clean up if directory exists and is empty
        if (is_dir($fullPath)) {
            $files = array_diff(scandir($fullPath), ['.', '..']);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory($directory);
                Log::debug('Cleaned up empty directory: ' . $directory);
                
                // Also check parent directory (year folder)
                $parentDirectory = dirname($directory);
                $parentFullPath = storage_path('app/public/' . $parentDirectory);
                
                if (is_dir($parentFullPath)) {
                    $parentFiles = array_diff(scandir($parentFullPath), ['.', '..']);
                    if (empty($parentFiles)) {
                        Storage::disk('public')->deleteDirectory($parentDirectory);
                        Log::debug('Cleaned up empty parent directory: ' . $parentDirectory);
                    }
                }
            }
        }
        
    } catch (\Exception $e) {
        Log::debug('Directory cleanup failed (non-critical): ' . $e->getMessage());
        // Silently fail - cleanup is not critical
    }
}

public function forceDeleteWithConfirmation($confirmation = false)
{
    if (!$confirmation) {
        throw new \Exception('Force deletion requires explicit confirmation');
    }
    
    // Log the force deletion
    Log::warning('Force deletion initiated', [
        'photo_id' => $this->id,
        'photo_title' => $this->title,
        'submission_status' => $this->submission_status,
        'initiated_by' => auth()->id()
    ]);
    
    return $this->delete();
}

/**
 * Soft delete equivalent (mark as archived instead of actual deletion)
 */
public function softArchive($reason = null)
{
    $this->update([
        'archived' => true,
        'archived_at' => now(),
        'archive_reason' => $reason
    ]);
    
    Log::info('Site photo archived', [
        'photo_id' => $this->id,
        'reason' => $reason,
        'archived_by' => auth()->id()
    ]);
}

public function canBeSafelyDeleted()
{
    $issues = [];
    
    // Check if it's featured
    if ($this->is_featured) {
        $issues[] = 'Photo is marked as featured';
    }
    
    // Check if it's in collections
    $collectionCount = $this->collections()->count();
    if ($collectionCount > 0) {
        $issues[] = "Photo is in {$collectionCount} collection(s)";
    }
    
    // Check if it's a cover photo for any collection
    $coverPhotoCount = \App\Models\SitePhotoCollection::where('cover_photo_id', $this->id)->count();
    if ($coverPhotoCount > 0) {
        $issues[] = "Photo is used as cover photo for {$coverPhotoCount} collection(s)";
    }
    
    // Check if it has comments
    $commentCount = $this->comments()->count();
    if ($commentCount > 0) {
        $issues[] = "Photo has {$commentCount} comment(s)";
    }
    
    return [
        'can_delete' => empty($issues),
        'issues' => $issues,
        'warning_level' => $this->submission_status === 'approved' ? 'high' : 'medium'
    ];
}

public function getDeletionImpact()
{
    return [
        'photo_title' => $this->title,
        'project_name' => $this->project->name,
        'uploader_name' => $this->uploader->first_name . ' ' . $this->uploader->last_name,
        'submission_status' => $this->submission_status,
        'is_featured' => $this->is_featured,
        'is_public' => $this->is_public,
        'collections_count' => $this->collections()->count(),
        'comments_count' => $this->comments()->count(),
        'file_size' => $this->formatted_file_size,
        'upload_date' => $this->created_at->format('M d, Y'),
        'photo_date' => $this->photo_date->format('M d, Y'),
        'safety_check' => $this->canBeSafelyDeleted()
    ];
}


    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-submit photos when created (unless they're part of a collection)
        static::created(function ($sitePhoto) {
            if ($sitePhoto->submission_status === 'draft') {
                $sitePhoto->update([
                    'submission_status' => 'submitted',
                    'submitted_at' => now()
                ]);
            }
        });

        // Delete photo file when model is deleted
         static::deleting(function ($sitePhoto) {
        try {
            // Remove from any photo collections
            $sitePhoto->collections()->detach();
            
            // Update collection photo counts and cover photos if this was a cover photo
            $collectionsWithThisCover = \App\Models\SitePhotoCollection::where('cover_photo_id', $sitePhoto->id)->get();
            foreach ($collectionsWithThisCover as $collection) {
                $collection->updateCoverPhotoIfNeeded();
            }
            
            // Delete photo files from storage
            $sitePhoto->deletePhotoFile();
            
            // Log the deletion for audit purposes
            Log::info('Site photo being deleted', [
                'photo_id' => $sitePhoto->id,
                'photo_title' => $sitePhoto->title,
                'photo_path' => $sitePhoto->photo_path,
                'deleted_at' => now(),
                'deleted_by' => auth()->id() ?? 'system'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error during site photo deletion cleanup: ' . $e->getMessage(), [
                'photo_id' => $sitePhoto->id,
                'error' => $e->getTraceAsString()
            ]);
            // Don't throw the exception - allow deletion to continue
        }
    });

    // After successful deletion: clean up any remaining references
    static::deleted(function ($sitePhoto) {
        try {
            // Additional cleanup if needed
            Log::info('Site photo successfully deleted', [
                'photo_id' => $sitePhoto->id,
                'photo_title' => $sitePhoto->title
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Post-deletion cleanup issue: ' . $e->getMessage());
        }
    });
}
}