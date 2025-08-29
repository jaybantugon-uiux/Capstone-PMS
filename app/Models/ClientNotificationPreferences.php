<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ClientNotificationPreferences extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'progress_reports_email',
        'progress_reports_app',
        'digest_frequency',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
    ];

    protected $casts = [
        'progress_reports_email' => 'boolean',
        'progress_reports_app' => 'boolean',
        'project_updates_email' => 'boolean',
        'project_updates_app' => 'boolean',
        'task_completion_email' => 'boolean',
        'task_completion_app' => 'boolean',
        'milestone_email' => 'boolean',
        'milestone_app' => 'boolean',
        'issue_notifications_email' => 'boolean',
        'issue_notifications_app' => 'boolean',
        'photo_upload_email' => 'boolean',
        'photo_upload_app' => 'boolean',
        'general_announcements_email' => 'boolean',
        'general_announcements_app' => 'boolean',
        'marketing_email' => 'boolean',
        'site_issue_notifications_email' => 'boolean',
        'site_issue_notifications_app' => 'boolean',
        'site_issue_email_preferences' => 'array',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Get the user that owns the notification preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project these preferences are specific to (null for global)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // ====================================================================
    // STATIC HELPER METHODS
    // ====================================================================

    /**
     * Get or create notification preferences for a user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'project_id' => null, // Global preferences
            ],
            static::getDefaultPreferences($userId)
        );
    }

    /**
     * Get or create project-specific notification preferences
     */
    public static function getOrCreateForUserProject(int $userId, int $projectId): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'project_id' => $projectId,
            ],
            static::getDefaultPreferences($userId)
        );
    }

    /**
     * UPDATED: Get default preferences based on user role
     * UPDATED: Clients are excluded from project/task notifications
     */
    public static function getDefaultPreferences(int $userId): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found");
        }

        // Base defaults
        $defaults = [
            'digest_frequency' => 'weekly',
            'quiet_hours_start' => '22:00',
            'quiet_hours_end' => '08:00',
            'timezone' => 'UTC',
            'marketing_email' => false,
        ];

        // Role-specific defaults
        switch ($user->role) {
            case 'client':
                return array_merge($defaults, [
                    // Clients receive ONLY progress report notifications
                    'progress_reports_email' => true,
                    'progress_reports_app' => true,
                ]);

            case 'admin':
                return array_merge($defaults, [
                    // Admins receive all operational notifications
                    'progress_reports_email' => false, // Admins don't receive progress reports
                    'progress_reports_app' => false,
                    'project_updates_email' => true,   // Mapped to task reports for admins
                    'project_updates_app' => true,
                    'task_completion_email' => true,   // Mapped to project notifications for admins
                    'task_completion_app' => true,
                    'milestone_email' => true,
                    'milestone_app' => true,
                    'issue_notifications_email' => true,
                    'issue_notifications_app' => true,
                    'photo_upload_email' => true,     // Mapped to site photos for admins
                    'photo_upload_app' => true,
                    'general_announcements_email' => true,
                    'general_announcements_app' => true,
                    'site_issue_notifications_email' => true,
                    'site_issue_notifications_app' => true,
                ]);

            case 'pm':
                return array_merge($defaults, [
                    // PMs receive project management notifications
                    'progress_reports_email' => false, // PMs don't receive progress reports
                    'progress_reports_app' => false,
                    'project_updates_email' => true,   // Mapped to task reports for PMs
                    'project_updates_app' => true,
                    'task_completion_email' => true,   // Mapped to project notifications for PMs
                    'task_completion_app' => true,
                    'milestone_email' => true,
                    'milestone_app' => true,
                    'issue_notifications_email' => true,
                    'issue_notifications_app' => true,
                    'photo_upload_email' => true,     // Mapped to site photos for PMs
                    'photo_upload_app' => true,
                    'general_announcements_email' => true,
                    'general_announcements_app' => true,
                    'site_issue_notifications_email' => true,
                    'site_issue_notifications_app' => true,
                ]);

            case 'sc':
                return array_merge($defaults, [
                    // Site coordinators receive task and site notifications
                    'progress_reports_email' => false, // SCs don't receive progress reports
                    'progress_reports_app' => false,
                    'project_updates_email' => true,   // Mapped to task reports for SCs
                    'project_updates_app' => true,
                    'task_completion_email' => true,   // Mapped to project notifications for SCs
                    'task_completion_app' => true,
                    'milestone_email' => true,
                    'milestone_app' => true,
                    'issue_notifications_email' => true,
                    'issue_notifications_app' => true,
                    'photo_upload_email' => false,    // SCs don't need photo review notifications
                    'photo_upload_app' => true,
                    'general_announcements_email' => true,
                    'general_announcements_app' => true,
                    'site_issue_notifications_email' => true,
                    'site_issue_notifications_app' => true,
                ]);

            default:
                return array_merge($defaults, [
                    // Default conservative settings
                    'progress_reports_email' => false,
                    'progress_reports_app' => false,
                    'project_updates_email' => false,
                    'project_updates_app' => false,
                    'task_completion_email' => false,
                    'task_completion_app' => false,
                    'milestone_email' => true,
                    'milestone_app' => true,
                    'issue_notifications_email' => false,
                    'issue_notifications_app' => false,
                    'photo_upload_email' => false,
                    'photo_upload_app' => false,
                    'general_announcements_email' => true,
                    'general_announcements_app' => true,
                    'site_issue_notifications_email' => false,
                    'site_issue_notifications_app' => false,
                ]);
        }
    }

    // ====================================================================
    // INSTANCE METHODS
    // ====================================================================

    /**
     * Update preferences with validation
     */
    public function updatePreferences(array $preferences): bool
    {
        try {
            // UPDATED: Validate that clients cannot enable project/task notifications
            $user = $this->user;
            
            if ($user && $user->role === 'client') {
                // Force disable project/task notifications for clients
                $preferences['project_updates_email'] = false;
                $preferences['project_updates_app'] = false;
                $preferences['task_completion_email'] = false;
                $preferences['task_completion_app'] = false;
                $preferences['issue_notifications_email'] = false;
                $preferences['issue_notifications_app'] = false;
                $preferences['photo_upload_email'] = false;
                $preferences['photo_upload_app'] = false;
                $preferences['site_issue_notifications_email'] = false;
                $preferences['site_issue_notifications_app'] = false;
                
                Log::info('Forced disable project/task notifications for client', [
                    'user_id' => $user->id,
                    'preferences_id' => $this->id
                ]);
            }
            
            // Filter out null values and validate
            $filteredPreferences = array_filter($preferences, function($value) {
                return $value !== null;
            });

            $this->fill($filteredPreferences);
            return $this->save();

        } catch (\Exception $e) {
            Log::error('Failed to update notification preferences', [
                'preferences_id' => $this->id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Copy preferences from global to project-specific
     */
    public function copyFromGlobal(int $userId): bool
    {
        try {
            $globalPrefs = static::getOrCreateForUser($userId);
            
            $preferenceFields = [
                'progress_reports_email',
                'progress_reports_app',
                'project_updates_email',
                'project_updates_app',
                'task_completion_email',
                'task_completion_app',
                'milestone_email',
                'milestone_app',
                'issue_notifications_email',
                'issue_notifications_app',
                'photo_upload_email',
                'photo_upload_app',
                'general_announcements_email',
                'general_announcements_app',
                'marketing_email',
                'digest_frequency',
                'quiet_hours_start',
                'quiet_hours_end',
                'timezone',
                'site_issue_notifications_email',
                'site_issue_notifications_app',
            ];

            foreach ($preferenceFields as $field) {
                $this->$field = $globalPrefs->$field;
            }

            return $this->save();

        } catch (\Exception $e) {
            Log::error('Failed to copy global preferences', [
                'preferences_id' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * UPDATED: Check if user should receive a specific notification type via specific channel
     * UPDATED: Enforces client restrictions on project/task notifications
     */
    public function shouldReceiveNotification(string $notificationType, string $channel = 'email'): bool
    {
        $user = $this->user;
        
        if (!$user || $user->status !== 'active') {
            return false;
        }

        // ENFORCE CLIENT RESTRICTIONS: Clients can ONLY receive progress report notifications
        if ($user->role === 'client') {
            $allowedNotifications = ['progress_reports'];
            
            if (!in_array($notificationType, $allowedNotifications)) {
                Log::debug('Client notification blocked', [
                    'user_id' => $user->id,
                    'notification_type' => $notificationType,
                    'reason' => 'clients_can_only_receive_progress_report_notifications'
                ]);
                return false;
            }
        }

        // Map notification types to preference fields based on user role
        $preferenceField = $this->getPreferenceFieldForNotification($notificationType, $channel, $user->role);
        
        if (!$preferenceField) {
            return false;
        }

        return (bool) $this->$preferenceField;
    }

    /**
     * UPDATED: Get the preference field for a notification type based on user role
     * UPDATED: Enforces that clients only get specific notification types
     */
    private function getPreferenceFieldForNotification(string $notificationType, string $channel, string $userRole): ?string
    {
        $suffix = $channel === 'email' ? '_email' : '_app';
        
        // Role-specific notification mapping
        switch ($userRole) {
            case 'client':
                // CLIENTS CAN ONLY RECEIVE PROGRESS REPORT NOTIFICATIONS
                return match ($notificationType) {
                    'progress_reports' => 'progress_reports' . $suffix,
                    default => null, // All other notifications are blocked for clients
                };

            case 'admin':
            case 'pm':
            case 'sc':
                return match ($notificationType) {
                    'task_reports', 'task_report' => 'project_updates' . $suffix, // Mapped to project_updates for staff
                    'project_updates', 'project_notifications' => 'task_completion' . $suffix, // Mapped to task_completion for staff
                    'site_issues', 'site_issue' => 'site_issue_notifications' . $suffix,
                    'site_photos', 'photo_reviews' => 'photo_upload' . $suffix, // Mapped to photo_upload for staff
                    'milestones', 'milestone' => 'milestone' . $suffix,
                    'issues', 'issue_notifications' => 'issue_notifications' . $suffix,
                    'announcements', 'general_announcements' => 'general_announcements' . $suffix,
                    default => null,
                };

            default:
                return null;
        }
    }

    /**
     * Check if user is in quiet hours
     */
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $timezone = $this->timezone ?: 'UTC';
        $now = now($timezone);
        $start = $now->copy()->setTimeFromTimeString($this->quiet_hours_start);
        $end = $now->copy()->setTimeFromTimeString($this->quiet_hours_end);

        // Handle overnight quiet hours (e.g., 22:00 to 08:00)
        if ($start->greaterThan($end)) {
            return $now->greaterThanOrEqualTo($start) || $now->lessThanOrEqualTo($end);
        }

        return $now->between($start, $end);
    }

    /**
     * UPDATED: Get notification summary for display
     * UPDATED: Shows client restrictions clearly
     */
    public function getNotificationSummaryAttribute(): array
    {
        $user = $this->user;
        
        if (!$user) {
            return [];
        }

        $summary = [
            'user_role' => $user->role,
            'preference_type' => $this->project_id ? 'project_specific' : 'global',
            'project_name' => $this->project ? $this->project->name : null,
            'email_enabled_count' => 0,
            'app_enabled_count' => 0,
            'digest_frequency' => $this->digest_frequency,
            'quiet_hours' => $this->quiet_hours_start && $this->quiet_hours_end 
                ? $this->quiet_hours_start . ' - ' . $this->quiet_hours_end 
                : 'None',
            'timezone' => $this->timezone,
        ];

        // Count enabled notifications based on user role
        if ($user->role === 'client') {
            // UPDATED: Only count progress report notifications for clients
            $clientEmailFields = [
                'progress_reports_email'
            ];
            
            $clientAppFields = [
                'progress_reports_app'
            ];

            $summary['email_enabled_count'] = collect($clientEmailFields)->sum(fn($field) => $this->$field ? 1 : 0);
            $summary['app_enabled_count'] = collect($clientAppFields)->sum(fn($field) => $this->$field ? 1 : 0);
            
            $summary['enabled_notifications'] = [
                'Progress Reports' => $this->progress_reports_email || $this->progress_reports_app,
            ];
            
            // UPDATED: Add restriction notice for clients
            $summary['restrictions'] = [
                'project_updates' => 'Disabled - Clients only receive progress report notifications',
                'task_notifications' => 'Disabled - Clients only receive progress report notifications',
                'site_issues' => 'Disabled - Clients only receive progress report notifications',
                'site_photos' => 'Disabled - Clients only receive progress report notifications',
                'milestones' => 'Disabled - Clients only receive progress report notifications',
                'announcements' => 'Disabled - Clients only receive progress report notifications',
                'marketing' => 'Disabled - Clients only receive progress report notifications',
                'note' => 'Clients can view public photos and project information through the dashboard'
            ];

        } else {
            // For staff users (admin, pm, sc)
            $staffEmailFields = [
                'project_updates_email',    // Task reports
                'task_completion_email',    // Project notifications
                'site_issue_notifications_email',
                'photo_upload_email',       // Site photos
                'milestone_email',
                'general_announcements_email'
            ];
            
            $staffAppFields = [
                'project_updates_app',      // Task reports
                'task_completion_app',      // Project notifications
                'site_issue_notifications_app',
                'photo_upload_app',         // Site photos
                'milestone_app',
                'general_announcements_app'
            ];

            $summary['email_enabled_count'] = collect($staffEmailFields)->sum(fn($field) => $this->$field ? 1 : 0);
            $summary['app_enabled_count'] = collect($staffAppFields)->sum(fn($field) => $this->$field ? 1 : 0);
            
            $summary['enabled_notifications'] = [
                'Task Reports' => $this->project_updates_email || $this->project_updates_app,
                'Project Updates' => $this->task_completion_email || $this->task_completion_app,
                'Site Issues' => $this->site_issue_notifications_email || $this->site_issue_notifications_app,
                'Site Photos' => $this->photo_upload_email || $this->photo_upload_app,
                'Milestones' => $this->milestone_email || $this->milestone_app,
                'Announcements' => $this->general_announcements_email || $this->general_announcements_app,
            ];
        }

        return $summary;
    }

    /**
     * Get preference display for specific notification type
     */
    public function getNotificationPreference(string $notificationType): array
    {
        $user = $this->user;
        
        if (!$user) {
            return ['email' => false, 'app' => false];
        }

        $emailField = $this->getPreferenceFieldForNotification($notificationType, 'email', $user->role);
        $appField = $this->getPreferenceFieldForNotification($notificationType, 'app', $user->role);

        return [
            'email' => $emailField ? (bool) $this->$emailField : false,
            'app' => $appField ? (bool) $this->$appField : false,
        ];
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope for global preferences (not project-specific)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }

    /**
     * Scope for project-specific preferences
     */
    public function scopeProjectSpecific($query)
    {
        return $query->whereNotNull('project_id');
    }

    /**
     * UPDATED: Scope for users who want email notifications of a specific type
     * UPDATED: Enforces client restrictions
     */
    public function scopeWantsEmailNotification($query, string $notificationType)
    {
        return $query->whereHas('user', function($userQuery) use ($notificationType) {
            $userQuery->where('status', 'active');
        })->where(function($prefQuery) use ($notificationType) {
            // This method now enforces role-based restrictions
            switch ($notificationType) {
                case 'progress_reports':
                    $prefQuery->where('progress_reports_email', true)
                             ->whereHas('user', fn($q) => $q->where('role', 'client')); // ONLY clients
                    break;
                case 'task_reports':
                    $prefQuery->where('project_updates_email', true)
                             ->whereHas('user', fn($q) => $q->whereIn('role', ['admin', 'pm', 'sc'])); // EXCLUDE clients
                    break;
                case 'site_issues':
                    $prefQuery->where('site_issue_notifications_email', true)
                             ->whereHas('user', fn($q) => $q->whereIn('role', ['admin', 'pm', 'sc'])); // EXCLUDE clients
                                    break;
                                // Add more cases as needed for other notification types
                                default:
                                    // Optionally handle other notification types
                                    break;
                            }
                        });
                    }
                }