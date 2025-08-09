<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SiteIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'issue_title',
        'issue_type',
        'priority',
        'status',
        'description',
        'location',
        'affected_areas',
        'immediate_actions_taken',
        'suggested_solutions',
        'estimated_cost',
        'photos',
        'attachments',
        'assigned_to',
        'admin_notes',
        'resolution_description',
        'resolved_at',
        'resolved_by',
        'reported_at',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'photos' => 'array',
        'attachments' => 'array',
        'reported_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $dates = [
        'reported_at',
        'acknowledged_at', 
        'resolved_at',
        'created_at',
        'updated_at'
    ];

    // RELATIONSHIPS
    /**
     * Get the project this issue belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the task this issue is related to (optional)
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user (site coordinator) who reported this issue
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin/PM assigned to handle this issue
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who resolved this issue
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the user who acknowledged this issue
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get all comments for this issue
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SiteIssueComment::class);
    }

    /**
     * Get only external comments (visible to site coordinator)
     */
    public function externalComments()
    {
        return $this->hasMany(SiteIssueComment::class)->where('is_internal', false);
    }

    /**
     * Get only internal comments (admin only)
     */
    public function internalComments()
    {
        return $this->hasMany(SiteIssueComment::class)->where('is_internal', true);
    }

    // ACCESSORS
    /**
     * Get formatted reported date
     */
    public function getFormattedReportedAtAttribute()
    {
        return $this->reported_at ? $this->reported_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get formatted acknowledged date
     */
    public function getFormattedAcknowledgedAtAttribute()
    {
        return $this->acknowledged_at ? $this->acknowledged_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get formatted resolved date
     */
    public function getFormattedResolvedAtAttribute()
    {
        return $this->resolved_at ? $this->resolved_at->format('M d, Y g:i A') : null;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'open' => $this->priority === 'critical' ? 'danger' : 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            'escalated' => 'danger',
            default => 'light'
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute()
    {
        return match($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get issue type badge color
     */
    public function getIssueTypeBadgeColorAttribute()
    {
        return match($this->issue_type) {
            'safety' => 'danger',
            'equipment' => 'warning',
            'environmental' => 'success',
            'personnel' => 'info',
            'quality' => 'primary',
            'timeline' => 'warning',
            'other' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get formatted priority
     */
    public function getFormattedPriorityAttribute()
    {
        return ucfirst($this->priority);
    }

    /**
     * Get formatted issue type
     */
    public function getFormattedIssueTypeAttribute()
    {
        return ucfirst($this->issue_type);
    }

    /**
     * Check if issue is overdue for acknowledgment
     */
    public function getIsOverdueForAcknowledgmentAttribute()
    {
        if ($this->acknowledged_at || $this->status !== 'open') {
            return false;
        }

        $hoursThreshold = match($this->priority) {
            'critical' => 1,  // 1 hour
            'high' => 4,      // 4 hours
            'medium' => 24,   // 1 day
            'low' => 48,      // 2 days
            default => 24
        };

        return $this->reported_at->addHours($hoursThreshold)->isPast();
    }

    /**
     * Check if issue is overdue for resolution
     */
    public function getIsOverdueForResolutionAttribute()
    {
        if ($this->resolved_at || in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }

        $daysThreshold = match($this->priority) {
            'critical' => 1,  // 1 day
            'high' => 3,      // 3 days
            'medium' => 7,    // 1 week
            'low' => 14,      // 2 weeks
            default => 7
        };

        return $this->reported_at->addDays($daysThreshold)->isPast();
    }

    /**
     * Get days since reported
     */
    public function getDaysSinceReportedAttribute()
    {
        return $this->reported_at->diffInDays(now());
    }

    /**
     * Get issue age in human readable format
     */
    public function getAgeAttribute()
    {
        return $this->reported_at->diffForHumans();
    }

    /**
     * Get resolution time in days
     */
    public function getResolutionTimeAttribute()
    {
        if (!$this->resolved_at) return null;
        
        return $this->reported_at->diffInDays($this->resolved_at);
    }

    /**
     * Check if issue needs attention
     */
    public function getNeedsAttentionAttribute()
    {
        return $this->is_overdue_for_acknowledgment || 
               $this->is_overdue_for_resolution ||
               ($this->priority === 'critical' && $this->status === 'open');
    }

    /**
     * Get estimated cost formatted
     */
    public function getFormattedEstimatedCostAttribute()
    {
        return $this->estimated_cost ? '₱' . number_format($this->estimated_cost, 2) : null;
    }

    // SCOPES
    /**
     * Scope for open issues
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for unacknowledged issues
     */
    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    /**
     * Scope for issues by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for issues by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for issues by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('issue_type', $type);
    }

    /**
     * Scope for critical issues
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /**
     * Scope for issues reported by user
     */
    public function scopeReportedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for issues assigned to user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for issues in project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for recent issues
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('reported_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for overdue issues
     */
    public function scopeOverdue($query)
    {
        return $query->where(function($q) {
            $q->whereNull('acknowledged_at')
              ->where(function($subQ) {
                  $subQ->where('priority', 'critical')
                       ->where('reported_at', '<', now()->subHour())
                       ->orWhere('priority', 'high')
                       ->where('reported_at', '<', now()->subHours(4))
                       ->orWhere('priority', 'medium')
                       ->where('reported_at', '<', now()->subDay())
                       ->orWhere('priority', 'low')
                       ->where('reported_at', '<', now()->subDays(2));
              });
        });
    }

    /**
     * Scope for unresolved issues
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    // HELPER METHODS
    /**
     * Mark issue as acknowledged
     */
    public function acknowledge($userId = null)
    {
        $this->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId ?? auth()->id(),
            'status' => $this->status === 'open' ? 'in_progress' : $this->status
        ]);
    }

    /**
     * Assign issue to user
     */
    public function assignTo($userId)
    {
        $this->update([
            'assigned_to' => $userId,
            'status' => $this->status === 'open' ? 'in_progress' : $this->status
        ]);
    }

    /**
     * Resolve issue
     */
    public function resolve($resolutionDescription, $userId = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolution_description' => $resolutionDescription,
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id()
        ]);
    }

    /**
     * Close issue
     */
    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Escalate issue
     */
    public function escalate()
    {
        $this->update([
            'status' => 'escalated',
            'priority' => $this->priority === 'low' ? 'medium' : 
                         ($this->priority === 'medium' ? 'high' : 'critical')
        ]);
    }

    /**
     * Get summary stats for a collection of issues
     */
    public static function getSummaryStats($issues)
    {
        return [
            'total' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'critical' => $issues->where('priority', 'critical')->count(),
            'high' => $issues->where('priority', 'high')->count(),
            'unacknowledged' => $issues->whereNull('acknowledged_at')->count(),
            'overdue' => $issues->filter(function($issue) {
                return $issue->is_overdue_for_acknowledgment || $issue->is_overdue_for_resolution;
            })->count(),
            'avg_resolution_time' => $issues->where('status', 'resolved')
                                           ->filter(function($issue) { return $issue->resolution_time; })
                                           ->avg('resolution_time'),
        ];
    }

    /**
     * Boot method to set defaults
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($siteIssue) {
            if (!$siteIssue->reported_at) {
                $siteIssue->reported_at = now();
            }
        });
    }
}