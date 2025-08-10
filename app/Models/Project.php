<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'created_by',
        'archived'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function siteIssues()
    {
        return $this->hasMany(SiteIssue::class);
    }

    // Add this relationship for site photos
    public function sitePhotos()
    {
        return $this->hasMany(SitePhoto::class);
    }

    // NEW: Client relationships
    public function projectClients()
    {
        return $this->hasMany(ProjectClient::class);
    }

        // NEW: Add the missing monitoredEquipment relationship
    public function monitoredEquipment()
    {
        return $this->hasMany(MonitoredEquipment::class);
    }

    // NEW: Equipment requests for this project
    public function equipmentRequests()
    {
        return $this->hasMany(EquipmentRequest::class);
    }

    

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_clients', 'project_id', 'client_id')
                    ->withPivot([
                        'access_level',
                        'can_view_photos',
                        'can_view_reports', 
                        'can_view_issues',
                        'can_receive_notifications',
                        'assigned_at',
                        'assigned_by'
                    ])
                    ->withTimestamps()
                    ->using(ProjectClient::class);
    }

    // NEW: Project updates
    public function projectUpdates()
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    public function publicUpdates()
    {
        return $this->hasMany(ProjectUpdate::class)->clientVisible()->orderBy('posted_at', 'desc');
    }

    // Accessor for formatted dates
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('M d, Y') : null;
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('M d, Y') : null;
    }

    // Check if project is overdue
    public function getIsOverdueAttribute()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    // Get project status
    public function getStatusAttribute()
    {
        if ($this->archived) {
            return 'archived';
        }

        $totalTasks = $this->tasks()->where('archived', false)->count();
        
        if ($totalTasks === 0) {
            return 'no_tasks';
        }

        $completedTasks = $this->tasks()->where('archived', false)->where('status', 'completed')->count();
        
        if ($completedTasks === $totalTasks) {
            return 'completed';
        }

        $inProgressTasks = $this->tasks()->where('archived', false)->where('status', 'in_progress')->count();
        
        if ($inProgressTasks > 0 || $completedTasks > 0) {
            return 'in_progress';
        }

        return 'pending';
    }

    // Get completion percentage
    public function getCompletionPercentageAttribute()
    {
        $totalTasks = $this->tasks()->where('archived', false)->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('archived', false)->where('status', 'completed')->count();
        
        return round(($completedTasks / $totalTasks) * 100);
    }

    // NEW: Get client completion percentage (only visible tasks)
    public function getClientCompletionPercentageAttribute()
    {
        // Show a more conservative completion based on major milestones
        $majorMilestones = $this->projectUpdates()
            ->where('is_major_milestone', true)
            ->where('visibility', '!=', 'internal')
            ->count();

        $completedMilestones = $this->projectUpdates()
            ->where('is_major_milestone', true)
            ->where('update_type', 'completion')
            ->where('visibility', '!=', 'internal')
            ->count();

        if ($majorMilestones === 0) {
            return $this->completion_percentage; // Fallback to task-based percentage
        }

        return round(($completedMilestones / $majorMilestones) * 100);
    }

    // Get status badge color
    public function getStatusBadgeColorAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'success';
            case 'in_progress':
                return 'warning';
            case 'pending':
                return 'secondary';
            case 'archived':
                return 'dark';
            case 'no_tasks':
            default:
                return 'light';
        }
    }

    // Get formatted status
    public function getFormattedStatusAttribute()
    {
        switch ($this->status) {
            case 'no_tasks':
                return 'Planning';
            case 'in_progress':
                return 'In Progress';
            default:
                return ucfirst($this->status);
        }
    }

    // Scope for active projects
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    // Scope for archived projects
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    // Scope for overdue projects
    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', Carbon::now())
                    ->where('archived', false);
    }

    // Scope for projects created by a specific user
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // NEW: Scope for projects accessible by client
    public function scopeAccessibleByClient($query, $clientId)
    {
        return $query->whereHas('projectClients', function($q) use ($clientId) {
            $q->where('client_id', $clientId);
        });
    }

    public function scopeEndingSoon($query, int $days = 30)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
                    ->where('end_date', '>=', now());
    }

    // Get recent activity count for this project
    public function getRecentActivityCountAttribute()
    {
        $recentTaskReports = $this->hasMany(TaskReport::class, 'task_id', 'id')
            ->whereHas('task', function($query) {
                $query->where('project_id', $this->id);
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentSiteIssues = $this->siteIssues()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentSitePhotos = $this->sitePhotos()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $recentTaskReports + $recentSiteIssues + $recentSitePhotos;
    }

    // NEW: Get recent client-visible activity count
    public function getRecentClientActivityCountAttribute()
    {
        $recentUpdates = $this->projectUpdates()
            ->clientVisible()
            ->where('posted_at', '>=', now()->subDays(7))
            ->count();

        $recentPhotos = $this->sitePhotos()
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $recentUpdates + $recentPhotos;
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function openSiteIssues()
    {
        return $this->hasMany(SiteIssue::class)->where('status', 'open');
    }

    public function criticalSiteIssues()
    {
        return $this->hasMany(SiteIssue::class)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed']);
    }

    public function getSiteIssuesCountAttribute()
    {
        return $this->siteIssues()->count();
    }

    public function getOpenSiteIssuesCountAttribute()
    {
        return $this->openSiteIssues()->count();
    }

    public function getCriticalSiteIssuesCountAttribute()
    {
        return $this->criticalSiteIssues()->count();
    }

    // NEW: Client-specific counts
    public function getClientVisiblePhotosCountAttribute()
    {
        return $this->sitePhotos()
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->count();
    }

    public function getRecentUpdatesCountAttribute()
    {
        return $this->projectUpdates()
            ->clientVisible()
            ->where('posted_at', '>=', now()->subDays(30))
            ->count();
    }

    // NEW: Check if client has access to this project
    public function clientHasAccess($clientId): bool
    {
        return $this->projectClients()
            ->where('client_id', $clientId)
            ->exists();
    }

    // NEW: Get client's access level for this project
    public function getClientAccess($clientId): ?ProjectClient
    {
        return $this->projectClients()
            ->where('client_id', $clientId)
            ->first();
    }

    // NEW: Get next milestone
    public function getNextMilestoneAttribute()
    {
        return $this->projectUpdates()
            ->where('is_major_milestone', true)
            ->where('update_type', '!=', 'completion')
            ->clientVisible()
            ->orderBy('posted_at', 'desc')
            ->first();
    }

    // NEW: Get project health for clients
    public function getClientHealthStatusAttribute()
    {
        if ($this->status === 'completed') return 'completed';
        if ($this->isOverdue()) return 'delayed';
        if ($this->completion_percentage >= 80) return 'on_track';
        if ($this->completion_percentage >= 50) return 'progressing';
        return 'early_stage';
    }

    public function getClientHealthColorAttribute()
    {
        return match($this->client_health_status) {
            'completed' => 'success',
            'on_track' => 'success',
            'progressing' => 'primary',
            'early_stage' => 'info',
            'delayed' => 'warning',
            default => 'secondary'
        };
    }

    public function hasUserAccess(User $user): bool
    {
        if ($user->isAdmin() || $user->isPM()) {
            return true;
        }

        if ($user->isClient()) {
            return $this->clients()->where('client_id', $user->id)->exists();
        }

        if ($user->isSC()) {
            return $this->tasks()->where('assigned_to', $user->id)->exists();
        }

        return false;
    }

    public function clientsWithAccess(string $accessLevel)
    {
        return $this->clients()->wherePivot('access_level', $accessLevel);
    }
    
    public function clientsWhoCanReceiveNotifications()
    {
        return $this->clients()->wherePivot('can_receive_notifications', true);
    }

    // NEW: Get latest project update for clients
    public function getLatestClientUpdateAttribute()
    {
        return $this->projectUpdates()
            ->clientVisible()
            ->orderBy('posted_at', 'desc')
            ->first();
    }

    // NEW: Get project timeline for clients
    public function getClientTimelineAttribute()
    {
        $timeline = collect();

        // Add project start
        if ($this->start_date) {
            $timeline->push([
                'type' => 'project_start',
                'title' => 'Project Started',
                'description' => 'Project commenced',
                'date' => $this->start_date,
                'icon' => 'fas fa-play-circle',
                'color' => 'success'
            ]);
        }

        // Add milestone updates
        $milestones = $this->projectUpdates()
            ->where('is_major_milestone', true)
            ->clientVisible()
            ->orderBy('posted_at', 'asc')
            ->get();

        foreach ($milestones as $milestone) {
            $timeline->push([
                'type' => 'milestone',
                'title' => $milestone->title,
                'description' => $milestone->description,
                'date' => $milestone->posted_at,
                'icon' => $milestone->update_icon,
                'color' => $milestone->update_type_color
            ]);
        }

        // Add project end if completed
        if ($this->status === 'completed' && $this->end_date) {
            $timeline->push([
                'type' => 'project_end',
                'title' => 'Project Completed',
                'description' => 'Project successfully completed',
                'date' => $this->end_date,
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ]);
        }

        return $timeline->sortBy('date')->values();
    }

    // NEW: Calculate project health score for clients
    public function getClientHealthScoreAttribute(): int
    {
        $score = 100;

        // Deduct points for being overdue
        if ($this->isOverdue()) {
            $score -= 30;
        }

        // Deduct points for low completion percentage
        $completion = $this->client_completion_percentage ?? 0;
        if ($completion < 50) {
            $score -= 20;
        } elseif ($completion < 80) {
            $score -= 10;
        }

        // Check for recent activity
        $hasRecentActivity = $this->recent_client_activity_count > 0;
        if (!$hasRecentActivity) {
            $score -= 15;
        }

        // Check for critical issues (if client can view them)
        if ($this->critical_site_issues_count > 0) {
            $score -= 25;
        }

        return max(0, $score);
    }

    // NEW: Get estimated completion date based on current progress
    public function getEstimatedCompletionDateAttribute()
    {
        if (!$this->start_date || $this->status === 'completed') {
            return $this->end_date;
        }

        $completion = $this->client_completion_percentage ?? 0;
        
        if ($completion <= 0) {
            return $this->end_date;
        }

        $daysElapsed = $this->start_date->diffInDays(now());
        $estimatedTotalDays = ($daysElapsed / $completion) * 100;
        
        return $this->start_date->addDays($estimatedTotalDays);
    }

    // NEW: Check if project is on schedule
    public function isOnSchedule(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return true;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysElapsed = $this->start_date->diffInDays(now());
        
        if ($totalDays <= 0) {
            return true;
        }

        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        $actualProgress = $this->client_completion_percentage ?? 0;
        
        // Allow 10% tolerance
        return $actualProgress >= ($expectedProgress - 10);
    }
}