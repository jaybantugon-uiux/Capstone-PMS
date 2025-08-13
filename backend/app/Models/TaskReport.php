<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TaskReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'task_title',
        'reported_at',
        'date_reported',
        'task_status',
        'description',
        'progress_percentage',
        'hours_worked',
        'issues_encountered',
        'next_steps',
        'materials_used',
        'equipment_used',
        'photos',
        'weather_conditions',
        'additional_notes',
        'review_status',
        'reviewed_by',
        'reviewed_at',
        'admin_comments',
        'admin_rating',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'progress_percentage' => 'integer',
        'hours_worked' => 'decimal:2',
        'photos' => 'array',
        'reviewed_at' => 'datetime',
        'admin_rating' => 'integer',
    ];

    // RELATIONSHIPS
    /**
     * Get the task that this report belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user (site coordinator) who created this report
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed this report
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ACCESSORS
    /**
     * Get formatted report date
     */
    public function getFormattedReportDateAttribute()
    {
        return $this->report_date ? Carbon::parse((string) $this->report_date)->format('M d, Y') : null;
    }

    /**
     * Get formatted reviewed date
     */
    public function getFormattedReviewedAtAttribute()
    {
        return $this->reviewed_at ? $this->reviewed_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get review status badge color
     */
    public function getReviewStatusBadgeColorAttribute()
    {
        return match($this->review_status) {
            'pending' => 'warning',
            'reviewed' => 'info',
            'needs_revision' => 'danger',
            'approved' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get task status badge color
     */
    public function getTaskStatusBadgeColorAttribute()
    {
        return match($this->task_status) {
            'pending' => 'secondary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'on_hold' => 'info',
            'cancelled' => 'danger',
            default => 'light'
        };
    }

    /**
     * Get formatted task status
     */
    public function getFormattedTaskStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->task_status));
    }

    /**
     * Get formatted review status
     */
    public function getFormattedReviewStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->review_status));
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
     * Get progress color based on percentage
     */
    public function getProgressColorAttribute()
    {
        if ($this->progress_percentage >= 80) return 'success';
        if ($this->progress_percentage >= 60) return 'info';
        if ($this->progress_percentage >= 40) return 'warning';
        return 'danger';
    }

    /**
     * Check if report is overdue for review
     */
    public function getIsOverdueForReviewAttribute()
    {
        return $this->review_status === 'pending' && 
               $this->created_at->diffInDays(now()) > 2;
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
     * Get the project through the task relationship
     */
    public function getProjectAttribute()
    {
        return $this->task->project;
    }

    // SCOPES
    /**
     * Scope for pending review reports
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    /**
     * Scope for reviewed reports
     */
    public function scopeReviewed($query)
    {
        return $query->where('review_status', 'reviewed');
    }

    /**
     * Scope for approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    /**
     * Scope for reports needing revision
     */
    public function scopeNeedsRevision($query)
    {
        return $query->where('review_status', 'needs_revision');
    }

    /**
     * Scope for reports by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for reports by task
     */
    public function scopeByTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for reports within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /**
     * Scope for overdue reviews
     */
    public function scopeOverdueForReview($query)
    {
        return $query->where('review_status', 'pending')
                    ->where('created_at', '<', now()->subDays(2));
    }

    // HELPER METHODS
    /**
     * Mark report as reviewed
     */
    public function markAsReviewed($reviewerId, $comments = null, $rating = null)
    {
        $this->update([
            'review_status' => 'reviewed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $comments,
            'admin_rating' => $rating,
        ]);
    }

    /**
     * Mark report as needing revision
     */
    public function markAsNeedsRevision($reviewerId, $comments)
    {
        $this->update([
            'review_status' => 'needs_revision',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $comments,
        ]);
    }

    /**
     * Approve report
     */
    public function approve($reviewerId, $comments = null, $rating = null)
    {
        $this->update([
            'review_status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_comments' => $comments,
            'admin_rating' => $rating,
        ]);
    }

    /**
     * Check if user can edit this report
     */
    public function canBeEditedBy($user)
    {
        // Site coordinator can edit if not yet reviewed or if needs revision
        if ($user->id === $this->user_id) {
            return in_array($this->review_status, ['pending', 'needs_revision']);
        }
        
        // Admin can always edit
        return $user->role === 'admin';
    }

    /**
     * Get summary stats for a collection of reports
     */
    public static function getSummaryStats($reports)
    {
        return [
            'total' => $reports->count(),
            'pending' => $reports->where('review_status', 'pending')->count(),
            'reviewed' => $reports->where('review_status', 'reviewed')->count(),
            'approved' => $reports->where('review_status', 'approved')->count(),
            'needs_revision' => $reports->where('review_status', 'needs_revision')->count(),
            'average_progress' => $reports->avg('progress_percentage'),
            'total_hours' => $reports->sum('hours_worked'),
        ];
    }
}