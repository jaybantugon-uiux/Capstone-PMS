<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deactivated_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',
    ];

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Determine role based on email domain
     */
    public static function determineRole($email)
    {
        $email = strtolower($email);
        
        if (str_contains($email, 'main.dru@gmail.com') || str_contains($email, 'bantugonjaymain.dru@gmail.com')) {
            return 'admin';
        } elseif (str_contains($email, 'emp.dru@gmail.com') || str_contains($email, 'bantugonjayemp.dru@gmail.com')) {
            return 'emp';
        } elseif (str_contains($email, 'finance.dru@gmail.com') || str_contains($email, 'bantugonjayfinance.dru@gmail.com')) {
            return 'finance';
        } elseif (str_contains($email, 'pm.dru@gmail.com') || str_contains($email, 'bantugonjaypm.dru@gmail.com')) {
            return 'pm';
        } elseif (str_contains($email, 'sc.dru@gmail.com') || str_contains($email, 'bantugonjaysc.dru@gmail.com')) {
            return 'sc';
        } else {
            return 'client';
        }
    }

    /**
     * Account status methods
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDeactivated()
    {
        return $this->status === 'deactivated';
    }

    public function deactivate()
    {
        try {
            $this->status = 'deactivated';
            $this->deactivated_at = now();
            $this->tokens()->delete();
            $this->save();
            
            Log::info('User account deactivated', [
                'user_id' => $this->id,
                'email' => $this->email,
                'role' => $this->role
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deactivate user', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function reactivate()
    {
        try {
            $this->status = 'active';
            $this->deactivated_at = null;
            $success = $this->save();

            if ($success) {
                Log::info('User account reactivated', [
                    'user_id' => $this->id,
                    'email' => $this->email,
                    'role' => $this->role
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Failed to reactivate user account', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Email verification methods
     */
    public function canResendVerification()
    {
        return !$this->email_verification_sent_at || 
               $this->email_verification_sent_at->diffInMinutes(now()) >= 1;
    }

    public function markVerificationEmailSent()
    {
        $this->email_verification_sent_at = now();
        $this->save();
    }

    public function isVerificationExpired()
    {
        return $this->email_verification_sent_at && 
               $this->email_verification_sent_at->diffInMinutes(now()) > 60;
    }

    /**
     * Accessors and mutators
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDeactivated($query)
    {
        return $query->where('status', 'deactivated');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    // ===============================
    // ENHANCED NOTIFICATION METHODS
    // ===============================

    /**
     * Scope to get admin and PM users for notifications
     */
    public function scopeAdminsAndPMs($query)
    {
        return $query->whereIn('role', ['admin', 'pm'])->where('status', 'active');
    }

    /**
     * Enhanced method to check if user should receive project notifications
     * Now includes specific logic for project managers
     */
    public function shouldReceiveProjectNotifications($project = null)
    {
        // Admins receive all notifications
        if ($this->role === 'admin') {
            return true;
        }

        // Enhanced PM notification logic
        if ($this->role === 'pm') {
            if ($project) {
                // PM receives notifications if they:
                // 1. Created the project
                // 2. Created any tasks in the project
                // 3. Have tasks assigned to their team members in the project
                return $project->created_by === $this->id || 
                       $project->tasks()->where('created_by', $this->id)->exists() ||
                       $this->isProjectManager($project);
            }
            return true; // PMs receive all notifications by default
        }

        return false;
    }

    /**
     * Check if this PM is the project manager for a specific project
     */
    public function isProjectManager($project)
    {
        // Check if PM created the project
        if ($project->created_by === $this->id) {
            return true;
        }

        // Check if PM created any tasks in the project
        if ($project->tasks()->where('created_by', $this->id)->exists()) {
            return true;
        }

        return false;
    }

 /**
     * Get projects managed by this PM user
     * This method determines which projects a PM has management access to
     */
    public function getManagedProjects()
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        return Project::where(function($query) {
            // Projects created by this PM
            $query->where('created_by', $this->id)
                  // OR projects that have tasks created by this PM
                  ->orWhereHas('tasks', function($taskQuery) {
                      $taskQuery->where('created_by', $this->id);
                  });
        })->get();
    }

    /**
     * Check if user can manage a specific project
     */
    public function canManageProject($projectId)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role === 'pm') {
            return $this->getManagedProjects()->contains('id', $projectId);
        }

        return false;
    }

    /**
     * Check if PM should receive notifications for a specific task report
     */
    public function shouldReceiveTaskReportNotification($taskReport)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role === 'pm') {
            $project = $taskReport->task->project;
            return $this->shouldReceiveProjectNotifications($project);
        }

        return false;
    }

    /**
     * Check if PM should receive notifications for a specific site issue
     */
    public function shouldReceiveSiteIssueNotification($siteIssue)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role === 'pm') {
            $project = $siteIssue->project;
            return $this->shouldReceiveProjectNotifications($project);
        }

        return false;
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotificationsAttribute()
    {
        return $this->notifications()
                   ->orderBy('created_at', 'desc')
                   ->limit(10)
                   ->get();
    }

    /**
     * Get task report notifications for this PM
     */
    public function getTaskReportNotifications()
    {
        return $this->notifications()
                   ->where('type', 'like', '%TaskReport%')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    /**
     * Get site issue notifications for this PM
     */
    public function getSiteIssueNotifications()
    {
        return $this->notifications()
                   ->where('type', 'like', '%SiteIssue%')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }
    // Existing relationships
    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskReports()
    {
        return $this->hasMany(TaskReport::class);
    }

    public function siteIssues()
    {
        return $this->hasMany(SiteIssue::class);
    }

    public function sitePhotos()
    {
        return $this->hasMany(SitePhoto::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->status)) {
                $user->status = 'active';
            }
        });
    }
public function reportedSiteIssues()
{
    return $this->hasMany(SiteIssue::class, 'user_id');
}

public function assignedSiteIssues()
{
    return $this->hasMany(SiteIssue::class, 'assigned_to');
}

public function resolvedSiteIssues()
{
    return $this->hasMany(SiteIssue::class, 'resolved_by');
}

public function siteIssueComments()
{
    return $this->hasMany(SiteIssueComment::class);
}

public function reviewedSitePhotos()
{
    return $this->hasMany(SitePhoto::class, 'reviewed_by');
}

public function sitePhotoComments()
{
    return $this->hasMany(SitePhotoComment::class);
}

public function getSitePhotoStatsAttribute()
{
    if ($this->role !== 'sc') {
        return null;
    }

    return [
        'total_photos' => $this->sitePhotos()->count(),
        'submitted_photos' => $this->sitePhotos()->where('submission_status', 'submitted')->count(),
        'approved_photos' => $this->sitePhotos()->where('submission_status', 'approved')->count(),
        'rejected_photos' => $this->sitePhotos()->where('submission_status', 'rejected')->count(),
        'featured_photos' => $this->sitePhotos()->where('is_featured', true)->count(),
        'average_rating' => $this->sitePhotos()->whereNotNull('admin_rating')->avg('admin_rating'),
        'total_file_size' => $this->sitePhotos()->sum('file_size'),
        'recent_photos' => $this->sitePhotos()->where('created_at', '>=', now()->subDays(30))->count(),
    ];
}

public function hasRecentSitePhotos($days = 7)
{
    return $this->sitePhotos()->where('created_at', '>=', now()->subDays($days))->exists();
}

public function latestSitePhoto()
{
    return $this->sitePhotos()->latest()->first();
}

public function sitePhotosNeedingAttention()
{
    return $this->sitePhotos()->where(function($query) {
        $query->where('submission_status', 'rejected')
              ->orWhere(function($q) {
                  $q->where('submission_status', 'submitted')
                    ->where('submitted_at', '<', now()->subDays(3));
              });
    });
}
public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'assigned_to');
    }
/**
 * Get progress reports created by this user (admin or PM)
 */
public function createdProgressReports()
{
    return $this->hasMany(ProgressReport::class, 'created_by');
}

/**
 * Get progress reports received by this client
 */
public function receivedProgressReports()
{
    return $this->hasMany(ProgressReport::class, 'client_id');
}

/**
 * Get unread progress reports for this client
 */
public function unreadProgressReports()
{
    return $this->receivedProgressReports()->where('status', 'sent');
}

/**
 * Get recent progress reports for this client (last 30 days)
 */
public function recentProgressReports($days = 30)
{
    return $this->receivedProgressReports()
        ->where('created_at', '>=', now()->subDays($days))
        ->orderBy('created_at', 'desc');
}

/**
 * Check if user should receive progress report notifications
 */
public function shouldReceiveProgressReportNotifications()
{
    if ($this->role === 'client') {
        // Check notification preferences
        $preferences = $this->notificationPreferences()->first();
        return $preferences ? $preferences->progress_reports_email : true;
    }
    
    return false; // Only clients receive progress report notifications
}

/**
 * Get progress report statistics for admins and PMs
 */
public function getProgressReportStatsAttribute()
{
    if (!in_array($this->role, ['admin', 'pm'])) {
        return null;
    }

    $query = $this->createdProgressReports();

    return [
        'total_reports' => $query->count(),
        'reports_this_month' => $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count(),
        'unread_reports' => $query->where('status', 'sent')->count(),
        'viewed_reports' => $query->where('status', 'viewed')->count(),
        'average_views' => $query->avg('view_count') ?? 0,
        'role' => $this->role,
        'can_view_all' => $this->role === 'admin',
    ];
}

/**
 * Get client progress report summary
 */
public function getClientReportSummaryAttribute()
{
    if ($this->role !== 'client') {
        return null;
    }

    return [
        'total_reports' => $this->receivedProgressReports()->count(),
        'unread_reports' => $this->unreadProgressReports()->count(),
        'recent_reports' => $this->recentProgressReports(7)->count(),
        'reports_with_attachments' => $this->receivedProgressReports()
            ->whereNotNull('attachment_path')
            ->count(),
        'latest_report' => $this->receivedProgressReports()
            ->latest('created_at')
            ->first(),
    ];
}

/**
 * Notification preferences relationship
 */
public function notificationPreferences()
{
    return $this->hasOne(ClientNotificationPreferences::class);
}

/**
 * Get or create notification preferences for this user
 */
public function getNotificationPreferences()
{
    return ClientNotificationPreferences::getOrCreateForUser($this->id);
}

/**
 * Check if user has unread progress reports
 */
public function hasUnreadProgressReports()
{
    return $this->role === 'client' && $this->unreadProgressReports()->exists();
}

/**
 * Mark all progress reports as read for this client
 */
public function markAllProgressReportsAsRead()
{
    if ($this->role !== 'client') {
        return false;
    }

    return $this->receivedProgressReports()
        ->where('status', 'sent')
        ->update(['status' => 'viewed']);
}

/**
 * Get progress report notification count for dashboard
 */
public function getProgressReportNotificationCountAttribute()
{
    if ($this->role !== 'client') {
        return 0;
    }

    return $this->notifications()
        ->where('type', 'App\Notifications\ProgressReportShared')
        ->whereNull('read_at')
        ->count();
}

/**
 * Enhanced dashboard statistics including progress reports
 */
public function getDashboardStatsAttribute()
{
    $baseStats = [];

    switch ($this->role) {
        case 'admin':
            $baseStats = [
                'total_projects' => $this->createdProjects()->count(),
                'total_tasks' => $this->createdTasks()->count(),
                'progress_reports' => $this->progress_report_stats,
                'can_manage_all_reports' => true,
            ];
            break;

        case 'pm':
            $baseStats = [
                'managed_projects' => $this->getManagedProjects()->count(),
                'created_tasks' => $this->createdTasks()->count(),
                'progress_reports' => $this->progress_report_stats,
                'can_manage_all_reports' => false,
            ];
            break;

        case 'client':
            $baseStats = [
                'accessible_projects' => $this->clientProjects()->count(),
                'progress_reports' => $this->client_report_summary,
                'unread_notifications' => $this->unreadNotifications()->count(),
            ];
            break;

        case 'sc':
            $baseStats = [
                'assigned_tasks' => $this->assignedTasks()->count(),
                'completed_tasks' => $this->assignedTasks()->where('status', 'completed')->count(),
                'site_issues_reported' => $this->reportedSiteIssues()->count(),
                'site_photos_uploaded' => $this->sitePhotos()->count(),
            ];
            break;
    }

    return $baseStats;
}

/**
 * Scope for clients who should receive progress report notifications
 */
public function scopeProgressReportSubscribers($query)
{
    return $query->where('role', 'client')
        ->where('status', 'active')
        ->whereHas('notificationPreferences', function($q) {
            $q->where('progress_reports_email', true);
        });
}

/**
 * Get recent activity including progress reports
 */
public function getRecentActivityAttribute()
{
    $activities = collect();

    if ($this->role === 'client') {
        // Recent progress reports
        $recentReports = $this->receivedProgressReports()
            ->with('admin', 'project')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function($report) {
                return [
                    'type' => 'progress_report',
                    'title' => 'Progress Report: ' . $report->title,
                    'description' => 'From ' . $report->admin->first_name . ' ' . $report->admin->last_name,
                    'date' => $report->created_at,
                    'url' => route('client.reports.show', $report->id),
                    'icon' => 'fas fa-file-alt',
                    'color' => 'primary',
                    'is_new' => $report->status === 'sent',
                ];
            });

        $activities = $activities->merge($recentReports);
    }

    if (in_array($this->role, ['admin', 'pm'])) {
        // Recent reports created
        $createdReports = $this->createdProgressReports()
            ->with('client', 'project')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function($report) {
                return [
                    'type' => 'report_created',
                    'title' => 'Created Report: ' . $report->title,
                    'description' => 'For ' . $report->client->first_name . ' ' . $report->client->last_name,
                    'date' => $report->created_at,
                    'url' => route('admin.progress-reports.show', $report->id),
                    'icon' => 'fas fa-plus-circle',
                    'color' => $this->role === 'admin' ? 'danger' : 'primary',
                    'is_new' => $report->isRecent(),
                    'role_badge' => $this->role === 'admin' ? 'Admin' : 'PM',
                ];
            });

        $activities = $activities->merge($createdReports);
    }

    // Sort by date and return most recent
    return $activities->sortByDesc('date')->take(10)->values();
}

    public function clientProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_clients', 'client_id', 'project_id')
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
        public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class, 'client_id');
    }

        public function progressReportViews(): HasMany
    {
        return $this->hasMany(ProgressReportView::class, 'client_id');
    }
    // Scope methods
    public function scopeClients($query)
    {
        return $query->where('role', 'client');
    }

    public function scopeNotArchived($query)
    {
        return $query->where('archived', false);
    }

    // Helper methods
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPM(): bool
    {
        return $this->role === 'pm';
    }

    public function isSC(): bool
    {
        return $this->role === 'sc';
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }
    /**
     * Get the user's accessible projects based on their role
     */
    public function getAccessibleProjects()
    {
        if ($this->isAdmin() || $this->isPM()) {
            return Project::query();
        }

        if ($this->isClient()) {
            return $this->clientProjects();
        }

        if ($this->isSC()) {
            // Site coordinators can access projects where they have tasks
            return Project::whereHas('tasks', function($query) {
                $query->where('assigned_to', $this->id);
            });
        }

        return collect();
    }
    // ====================================================================
    // ENHANCED SITE ISSUE NOTIFICATION METHODS FOR PROJECT MANAGERS
    // ====================================================================

    /**
     * Get unread site issue notifications count
     */
    public function getUnreadSiteIssueNotificationsCountAttribute()
    {
        return $this->notifications()
                   ->whereIn('type', [
                       'App\Notifications\SiteIssueReported',
                       'App\Notifications\SiteIssueUpdated',
                       'App\Notifications\SiteIssueAssigned'
                   ])
                   ->whereNull('read_at')
                   ->count();
    }

    /**
     * Get recent site issue notifications for dashboard
     */
    public function getRecentSiteIssueNotifications($limit = 5)
    {
        return $this->notifications()
                   ->whereIn('type', [
                       'App\Notifications\SiteIssueReported',
                       'App\Notifications\SiteIssueUpdated',
                       'App\Notifications\SiteIssueAssigned'
                   ])
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Mark all site issue notifications as read
     */
    public function markAllSiteIssueNotificationsAsRead()
    {
        return $this->notifications()
                   ->whereIn('type', [
                       'App\Notifications\SiteIssueReported',
                       'App\Notifications\SiteIssueUpdated',
                       'App\Notifications\SiteIssueAssigned'
                   ])
                   ->whereNull('read_at')
                   ->update(['read_at' => now()]);
    }

    // ====================================================================
    // ENHANCED SITE ISSUE MANAGEMENT METHODS FOR PROJECT MANAGERS
    // ====================================================================

    /**
     * Get site issues managed by this PM (from their projects)
     */
    public function getManagedSiteIssues()
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();

        return \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
            ->with(['project', 'task', 'reporter', 'assignedTo'])
            ->get();
    }

    /**
     * Get critical site issues requiring PM attention
     */
    public function getCriticalSiteIssues()
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();

        return \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
            ->where('priority', 'critical')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['project', 'task', 'reporter'])
            ->orderBy('reported_at', 'desc')
            ->get();
    }

    /**
     * Get unacknowledged site issues for this PM
     */
    public function getUnacknowledgedSiteIssues()
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();

        return \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
            ->whereNull('acknowledged_at')
            ->with(['project', 'task', 'reporter'])
            ->orderBy('reported_at', 'desc')
            ->get();
    }

    /**
     * Get site issues assigned to this PM
     */
    public function getAssignedSiteIssues()
    {
        return \App\Models\SiteIssue::where('assigned_to', $this->id)
            ->with(['project', 'task', 'reporter'])
            ->orderBy('reported_at', 'desc')
            ->get();
    }

    /**
     * Get site issue statistics for PM dashboard
     */
    public function getSiteIssueStatsAttribute()
    {
        if ($this->role !== 'pm') {
            return null;
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();

        return [
            'total_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->count(),
            'open_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'open')->count(),
            'in_progress_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'in_progress')->count(),
            'resolved_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'resolved')->count(),
            'critical_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unacknowledged_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->whereNull('acknowledged_at')->count(),
            'assigned_to_me' => \App\Models\SiteIssue::where('assigned_to', $this->id)->whereNotIn('status', ['resolved', 'closed'])->count(),
            'recent_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('reported_at', '>=', now()->subDays(7))->count(),
            'safety_issues' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)->where('issue_type', 'safety')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];
    }

    /**
     * Get recent activity including site issues for PM dashboard
     */
    public function getEnhancedRecentActivityAttribute()
    {
        $activities = collect();

        if ($this->role === 'pm') {
            // Get existing activity
            $existingActivity = $this->recent_activity;
            if ($existingActivity) {
                $activities = $activities->merge($existingActivity);
            }

            // Add recent site issue activity
            $siteIssueActivity = $this->getRecentSiteIssueActivity(7);
            $activities = $activities->merge($siteIssueActivity);

            // Add site issue notifications
            $recentSiteIssueNotifications = $this->getRecentSiteIssueNotifications(5)
                ->map(function($notification) {
                    $data = $notification->data;
                    return [
                        'type' => 'notification',
                        'subtype' => 'site_issue_notification',
                        'title' => $data['title'] ?? 'Site Issue Notification',
                        'description' => $data['message'] ?? '',
                        'date' => $notification->created_at,
                        'url' => $data['action_url'] ?? '#',
                        'icon' => $data['icon'] ?? 'fas fa-bell',
                        'color' => $data['color'] ?? 'primary',
                        'is_unread' => !$notification->read_at,
                        'priority' => $data['priority'] ?? 'normal',
                    ];
                });

            $activities = $activities->merge($recentSiteIssueNotifications);
        }

        // Sort by date and return most recent
        return $activities->sortByDesc('date')->take(15)->values();
    }

    // ====================================================================
    // NOTIFICATION PREFERENCES FOR SITE ISSUES
    // ====================================================================

    /**
     * Check if user should receive email notifications for site issues
     */
    public function shouldReceiveSiteIssueEmailNotifications()
    {
        if (in_array($this->role, ['admin', 'pm'])) {
            // Check if user has notification preferences
            $preferences = $this->notificationPreferences()->first();
            
            // Default to true if no preferences set
            return $preferences ? ($preferences->site_issue_notifications_email ?? true) : true;
        }

        return false;
    }

    /**
     * Check if user should receive in-app notifications for site issues
     */
    public function shouldReceiveSiteIssueAppNotifications()
    {
        if (in_array($this->role, ['admin', 'pm'])) {
            // Check if user has notification preferences
            $preferences = $this->notificationPreferences()->first();
            
            // Default to true if no preferences set
            return $preferences ? ($preferences->site_issue_notifications_app ?? true) : true;
        }

        return false;
    }

    // ====================================================================
    // BULK ACTION METHODS FOR SITE ISSUES
    // ====================================================================

    /**
     * Check if PM can perform bulk actions on site issues
     */
    public function canPerformSiteIssueBulkActions(array $siteIssueIds)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role === 'pm') {
            $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();
            
            $unauthorizedIssues = \App\Models\SiteIssue::whereIn('id', $siteIssueIds)
                ->whereNotIn('project_id', $managedProjectIds)
                ->count();

            return $unauthorizedIssues === 0;
        }

        return false;
    }

    /**
     * Get site issues that PM can bulk edit
     */
    public function getBulkEditableSiteIssues(array $siteIssueIds)
    {
        if ($this->role === 'admin') {
            return \App\Models\SiteIssue::whereIn('id', $siteIssueIds)->get();
        }

        if ($this->role === 'pm') {
            $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();
            
            return \App\Models\SiteIssue::whereIn('id', $siteIssueIds)
                ->whereIn('project_id', $managedProjectIds)
                ->get();
        }

        return collect();
    }

    // ====================================================================
    // SITE ISSUE REPORTING AND ANALYTICS METHODS
    // ====================================================================

    /**
     * Get site issue resolution statistics for PM
     */
    public function getSiteIssueResolutionStatsAttribute()
    {
        if ($this->role !== 'pm') {
            return null;
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();
        
        $resolvedIssues = \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
            ->where('status', 'resolved')
            ->where('resolved_by', $this->id)
            ->get();

        if ($resolvedIssues->isEmpty()) {
            return [
                'total_resolved' => 0,
                'average_resolution_time' => 0,
                'fastest_resolution' => 0,
                'slowest_resolution' => 0,
            ];
        }

        $resolutionTimes = $resolvedIssues->map(function($issue) {
            return $issue->reported_at->diffInHours($issue->resolved_at);
        });

        return [
            'total_resolved' => $resolvedIssues->count(),
            'average_resolution_time' => round($resolutionTimes->avg(), 2),
            'fastest_resolution' => $resolutionTimes->min(),
            'slowest_resolution' => $resolutionTimes->max(),
            'issues_resolved_this_month' => $resolvedIssues->where('resolved_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get site issue trends for PM dashboard
     */
    public function getSiteIssueTrends($days = 30)
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();
        
        $trends = collect();
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $dayStats = [
                'date' => $date->format('Y-m-d'),
                'reported' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
                    ->whereDate('reported_at', $date)
                    ->count(),
                'resolved' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
                    ->whereDate('resolved_at', $date)
                    ->count(),
                'critical' => \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
                    ->whereDate('reported_at', $date)
                    ->where('priority', 'critical')
                    ->count(),
            ];
            
            $trends->push($dayStats);
        }
        
        return $trends;
    }

    // ====================================================================
    // SCOPE METHODS FOR SITE ISSUE MANAGEMENT
    // ====================================================================

    /**
     * Scope for PMs who should receive site issue notifications
     */
    public function scopeSiteIssueNotificationRecipients($query, $projectId = null)
    {
        $query = $query->where('status', 'active')
                      ->where(function($q) use ($projectId) {
                          // Always include admins
                          $q->where('role', 'admin');
                          
                          // Include PMs who manage projects
                          if ($projectId) {
                              $q->orWhere(function($pmQuery) use ($projectId) {
                                  $pmQuery->where('role', 'pm')
                                         ->where(function($projectQuery) use ($projectId) {
                                             // PM created the project
                                             $projectQuery->where('id', function($subQuery) use ($projectId) {
                                                 $subQuery->select('created_by')
                                                         ->from('projects')
                                                         ->where('id', $projectId);
                                             })
                                             // OR PM created tasks in this project
                                             ->orWhereHas('createdTasks', function($taskQuery) use ($projectId) {
                                                 $taskQuery->where('project_id', $projectId);
                                             });
                                         });
                              });
                          } else {
                              // If no specific project, include all PMs
                              $q->orWhere('role', 'pm');
                          }
                      });

        return $query;
    }

    // ====================================================================
    // EMAIL NOTIFICATION CUSTOMIZATION METHODS
    // ====================================================================

    /**
     * Get email preferences for site issue notifications
     */
    public function getSiteIssueEmailPreferences()
    {
        $preferences = [
            'immediate_critical' => true, // Immediate email for critical issues
            'immediate_safety' => true,   // Immediate email for safety issues
            'daily_digest' => false,      // Daily digest for non-critical issues
            'weekly_summary' => true,     // Weekly summary report
        ];

        // Check if user has custom preferences stored
        $customPreferences = $this->notificationPreferences()->first();
        if ($customPreferences && isset($customPreferences->site_issue_email_preferences)) {
            return array_merge($preferences, $customPreferences->site_issue_email_preferences);
        }

        return $preferences;
    }

    /**
     * Should send immediate email notification for site issue
     */
    public function shouldSendImmediateSiteIssueEmail($siteIssue)
    {
        $preferences = $this->getSiteIssueEmailPreferences();
        
        // Always send immediate email for critical issues
        if ($siteIssue->priority === 'critical' && $preferences['immediate_critical']) {
            return true;
        }
        
        // Always send immediate email for safety issues
        if ($siteIssue->issue_type === 'safety' && $preferences['immediate_safety']) {
            return true;
        }
        
        return false;
    }

    // ====================================================================
    // PERFORMANCE OPTIMIZATION METHODS
    // ====================================================================

    /**
     * Cache frequently accessed site issue data for PM
     */
    public function getCachedSiteIssueStats()
    {
        if ($this->role !== 'pm') {
            return null;
        }

        $cacheKey = 'pm_site_issue_stats_' . $this->id;
        
        return cache()->remember($cacheKey, 300, function() { // Cache for 5 minutes
            return $this->site_issue_stats;
        });
    }

    /**
     * Clear cached site issue data
     */
    public function clearSiteIssueCache()
    {
        $cacheKeys = [
            'pm_site_issue_stats_' . $this->id,
            'pm_managed_projects_' . $this->id,
            'pm_critical_issues_' . $this->id,
        ];

        foreach ($cacheKeys as $key) {
            cache()->forget($key);
        }
    }

    // ====================================================================
    // INTEGRATION WITH EXISTING USER MODEL METHODS
    // ====================================================================

    /**
     * Enhanced method to check if user should receive project notifications
     * Now includes site issue notifications
     */
    public function shouldReceiveProjectNotificationsEnhanced($project = null, $notificationType = 'all')
    {
        // Use existing method as base
        $baseCheck = $this->shouldReceiveProjectNotifications($project);
        
        if (!$baseCheck) {
            return false;
        }

        // Additional checks for site issue notifications
        if ($notificationType === 'site_issue') {
            return $this->shouldReceiveSiteIssueAppNotifications();
        }

        return $baseCheck;
    }

    /**
     * Check if PM can manage a specific site issue
     */
    public function canManageSiteIssue($siteIssueId)
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role === 'pm') {
            $siteIssue = \App\Models\SiteIssue::find($siteIssueId);
            if (!$siteIssue) {
                return false;
            }

            return $this->canManageProject($siteIssue->project_id);
        }

        return false;
    }

    /**
     * Get recent site issue activity for this PM
     */
    public function getRecentSiteIssueActivity($days = 7)
    {
        if ($this->role !== 'pm') {
            return collect();
        }

        $managedProjectIds = $this->getManagedProjects()->pluck('id')->toArray();

        return \App\Models\SiteIssue::whereIn('project_id', $managedProjectIds)
            ->where('created_at', '>=', now()->subDays($days))
            ->with(['project', 'task', 'reporter', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($issue) {
                return [
                    'type' => 'site_issue',
                    'action' => 'reported',
                    'title' => 'Site Issue: ' . $issue->issue_title,
                    'description' => 'Reported by ' . $issue->reporter->full_name . ' in ' . $issue->project->name,
                    'date' => $issue->created_at,
                    'url' => route('pm.site-issues.show', $issue->id),
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => $issue->priority_badge_color,
                    'priority' => $issue->priority,
                    'is_critical' => $issue->priority === 'critical',
                    'is_safety' => $issue->issue_type === 'safety',
                ];
            });
    }

    // ====================================================================
    // ENHANCED DASHBOARD METHODS INCLUDING SITE ISSUES
    // ====================================================================

    /**
     * Enhanced dashboard statistics including site issues for PMs
     */
    public function getEnhancedDashboardStatsAttribute()
    {
        $baseStats = $this->dashboard_stats; // Get existing dashboard stats

        if ($this->role === 'pm') {
            $baseStats['site_issues'] = $this->site_issue_stats;
            $baseStats['unread_site_issue_notifications'] = $this->unread_site_issue_notifications_count;
        }

        return $baseStats;
    }

    /**
     * Get comprehensive notification counts for PM dashboard
     */
    public function getNotificationSummaryAttribute()
    {
        return [
            'total_unread' => $this->unreadNotifications()->count(),
            'task_reports' => $this->notifications()
                ->where('type', 'App\Notifications\TaskReportSubmitted')
                ->whereNull('read_at')
                ->count(),
            'site_issues' => $this->unread_site_issue_notifications_count,
            'progress_reports' => $this->notifications()
                ->where('type', 'App\Notifications\ProgressReportShared')
                ->whereNull('read_at')
                ->count(),
            'site_photos' => $this->notifications()
                ->whereIn('type', [
                    'App\Notifications\SitePhotoSubmitted',
                    'App\Notifications\SitePhotoApproved',
                    'App\Notifications\SitePhotoRejected'
                ])
                ->whereNull('read_at')
                ->count(),
        ];
}

}