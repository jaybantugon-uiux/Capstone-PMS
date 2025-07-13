<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name',
        'description',
        'assigned_to',
        'created_by',
        'project_id',
        'parent_task_id',
        'start_date',
        'due_date',
        'actual_start_date',
        'actual_end_date',
        'estimated_hours',
        'actual_hours',
        'status',
        'priority',
        'progress_percentage',
        'task_type',
        'requires_approval',
        'approved_by',
        'approved_at',
        'task_order',
        'depends_on',
        'archived',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'integer',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'depends_on' => 'array',
        'archived' => 'boolean',
    ];

    // EXISTING RELATIONSHIPS - PRESERVED
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function siteCoordinator()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ADDITIONAL RELATIONSHIPS FOR ENHANCED FUNCTIONALITY
    /**
     * Get the user assigned to this task (alias for siteCoordinator for consistency)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this task (alias for creator for consistency)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent task
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Get the child tasks
     */
    public function childTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Get the user who approved this task
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // TASK REPORT RELATIONSHIPS
    /**
     * Get all task reports for this task
     */
    public function taskReports(): HasMany
    {
        return $this->hasMany(TaskReport::class);
    }

    /**
     * Get the latest task report
     */
    public function latestReport(): HasOne
    {
        return $this->hasOne(TaskReport::class)->latestOfMany('report_date');
    }

    /**
     * Get recent task reports (last 7 days)
     */
    public function recentReports()
    {
        return $this->hasMany(TaskReport::class)
            ->where('report_date', '>=', now()->subDays(7))
            ->orderBy('report_date', 'desc');
    }

    /**
     * Get site issues related to this task
     */
    public function siteIssues(): HasMany
    {
        return $this->hasMany(SiteIssue::class);
    }

    /**
     * Get site photos related to this task
     */
    public function sitePhotos(): HasMany
    {
        return $this->hasMany(SitePhoto::class);
    }

    // EXISTING ACCESSORS - PRESERVED
    /**
     * Accessor for formatted due date
     */
    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('M d, Y') : null;
    }

    /**
     * Check if task is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'success';
            case 'in_progress':
                return 'warning';
            case 'pending':
            default:
                return 'secondary';
        }
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get priority based on due date
     */
    public function getPriorityAttribute()
    {
        if (!$this->due_date || $this->status === 'completed') {
            return 'normal';
        }

        $daysUntilDue = Carbon::now()->diffInDays($this->due_date, false);

        if ($daysUntilDue < 0) {
            return 'overdue';
        } elseif ($daysUntilDue <= 3) {
            return 'high';
        } elseif ($daysUntilDue <= 7) {
            return 'medium';
        } else {
            return 'normal';
        }
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute()
    {
        switch ($this->priority) {
            case 'overdue':
                return 'danger';
            case 'high':
                return 'warning';
            case 'medium':
                return 'info';
            case 'normal':
            default:
                return 'light';
        }
    }

    /**
     * Get last report date attribute
     */
    public function getLastReportDateAttribute()
    {
        return $this->taskReports()->latest('report_date')->value('report_date');
    }

    /**
     * Check if task needs a report
     */
    public function getNeedsReportAttribute()
    {
        $lastReport = $this->last_report_date;
        
        if (!$lastReport) {
            return true; // No reports yet
        }
        
        // Needs report if last report is older than 3 days and task is not completed
        return $lastReport < now()->subDays(3) && $this->status !== 'completed';
    }

    // ENHANCED ACCESSORS FOR REPORTING SYSTEM
    /**
     * Check if task is overdue (enhanced version)
     */
    public function isOverdue()
    {
        return $this->due_date && 
               $this->due_date < now() && 
               $this->status !== 'completed';
    }

    /**
     * Get days until due date
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) return null;
        
        $days = now()->diffInDays($this->due_date, false);
        return $days;
    }

    /**
     * Get progress color based on percentage
     */
    public function getProgressColorAttribute()
    {
        $progress = $this->progress_percentage ?? 0;
        if ($progress >= 80) return 'success';
        if ($progress >= 60) return 'info';
        if ($progress >= 40) return 'warning';
        return 'danger';
    }

    /**
     * Check if task needs a report (enhanced for reporting system)
     */
    public function needsReport()
    {
        if ($this->status !== 'in_progress') return false;
        
        return !$this->taskReports()
            ->where('report_date', '>=', now()->subDays(7))
            ->exists();
    }

    /**
     * Get completion percentage based on reports
     */
    public function getReportedProgressAttribute()
    {
        $latestReport = $this->latestReport;
        return $latestReport ? $latestReport->progress_percentage : ($this->progress_percentage ?? 0);
    }

    /**
     * Get the duration in days
     */
    public function getDurationInDaysAttribute()
    {
        if (!$this->start_date || !$this->due_date) return null;
        
        return $this->start_date->diffInDays($this->due_date);
    }

    /**
     * Get estimated completion date based on current progress
     */
    public function getEstimatedCompletionAttribute()
    {
        if (!$this->start_date || !$this->due_date || ($this->progress_percentage ?? 0) <= 0) {
            return $this->due_date;
        }

        $totalDays = $this->start_date->diffInDays($this->due_date);
        $expectedDaysForProgress = ($totalDays * ($this->progress_percentage ?? 0)) / 100;
        $actualDaysSpent = $this->start_date->diffInDays(now());
        
        if ($actualDaysSpent <= $expectedDaysForProgress) {
            return $this->due_date; // On track
        }
        
        // Calculate projected completion based on current pace
        $dailyProgress = ($this->progress_percentage ?? 0) / max($actualDaysSpent, 1);
        $remainingDays = (100 - ($this->progress_percentage ?? 0)) / max($dailyProgress, 1);
        
        return now()->addDays($remainingDays);
    }

    /**
     * Check if task is on track based on progress vs time elapsed
     */
    public function isOnTrack()
    {
        if (!$this->start_date || !$this->due_date) return true;
        
        $totalDays = $this->start_date->diffInDays($this->due_date);
        $daysElapsed = $this->start_date->diffInDays(now());
        
        if ($totalDays <= 0) return true;
        
        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        
        return ($this->progress_percentage ?? 0) >= ($expectedProgress - 10); // 10% tolerance
    }

    /**
     * Get task health status
     */
    public function getHealthStatusAttribute()
    {
        if ($this->status === 'completed') return 'completed';
        if ($this->status === 'cancelled') return 'cancelled';
        if ($this->isOverdue()) return 'overdue';
        if (!$this->isOnTrack()) return 'behind';
        
        return 'on_track';
    }

    /**
     * Get health status color
     */
    public function getHealthColorAttribute()
    {
        return match($this->health_status) {
            'completed' => 'success',
            'on_track' => 'success',
            'behind' => 'warning',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'info'
        };
    }

    // EXISTING SCOPES - PRESERVED
    /**
     * Scope for active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    /**
     * Scope for archived tasks
     */
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    /**
     * Scope for overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
                    ->where('status', '!=', 'completed')
                    ->where('archived', false);
    }

    /**
     * Scope for tasks by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for tasks assigned to a specific user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for tasks created by a specific user
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // ADDITIONAL ENHANCED SCOPES
    /**
     * Scope for tasks in project
     */
    public function scopeInProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for tasks needing reports
     */
    public function scopeNeedingReports($query)
    {
        return $query->where('status', 'in_progress')
            ->where('archived', false)
            ->whereDoesntHave('taskReports', function($q) {
                $q->where('report_date', '>=', now()->subDays(7));
            });
    }

    /**
     * Scope for tasks with recent activity
     */
    public function scopeWithRecentActivity($query, $days = 7)
    {
        return $query->whereHas('taskReports', function($q) use ($days) {
            $q->where('report_date', '>=', now()->subDays($days));
        });
    }
public function openSiteIssues()
{
    return $this->hasMany(SiteIssue::class)->where('status', 'open');
}

public function hasCriticalSiteIssues()
{
    return $this->siteIssues()
        ->where('priority', 'critical')
        ->whereNotIn('status', ['resolved', 'closed'])
        ->exists();
}

public function getSiteIssuesCountAttribute()
{
    return $this->siteIssues()->count();
}

public function getRecentSiteIssuesCountAttribute()
{
    return $this->siteIssues()
        ->where('reported_at', '>=', now()->subDays(7))
        ->count();
}

}
