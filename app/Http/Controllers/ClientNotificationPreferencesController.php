<?php

namespace App\Http\Controllers;

use App\Models\ClientNotificationPreferences;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientNotificationPreferencesController extends Controller
{
    /**
     * Display the client's notification preferences
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Get global preferences
        $globalPreferences = ClientNotificationPreferences::getOrCreateForUser($user->id);
        
        // Get project-specific preferences
        $projectPreferences = ClientNotificationPreferences::where('user_id', $user->id)
            ->whereNotNull('project_id')
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get accessible projects for creating new project-specific preferences
        $availableProjects = $user->clientProjects()
            ->whereNotIn('id', $projectPreferences->pluck('project_id'))
            ->orderBy('name')
            ->get();

        return view('client.notification-preferences.index', compact(
            'globalPreferences',
            'projectPreferences', 
            'availableProjects'
        ));
    }

    /**
     * Update global notification preferences
     */
    public function updateGlobal(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
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
            'digest_frequency' => 'required|in:disabled,daily,weekly,monthly',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'timezone' => 'required|string|max:50',
        ]);

        try {
            $preferences = ClientNotificationPreferences::getOrCreateForUser($user->id);
            $preferences->updatePreferences($validated);

            // Log the change
            Log::info('Client notification preferences updated', [
                'user_id' => $user->id,
                'preferences_id' => $preferences->id,
                'changes' => $preferences->getChanges()
            ]);

            return redirect()->route('client.notification-preferences.index')
                ->with('success', 'Your notification preferences have been updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update notification preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->withErrors(['general' => 'Failed to update preferences. Please try again.']);
        }
    }

    /**
     * Create project-specific preferences
     */
    public function createProjectPreferences(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        // Verify user has access to this project
        $project = Project::findOrFail($validated['project_id']);
        if (!$user->clientProjects()->where('project_id', $project->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        // Check if preferences already exist
        if (ClientNotificationPreferences::where('user_id', $user->id)
            ->where('project_id', $project->id)->exists()) {
            return back()->withErrors(['project_id' => 'Preferences for this project already exist.']);
        }

        try {
            // Create project-specific preferences by copying global ones
            $projectPreferences = ClientNotificationPreferences::getOrCreateForUserProject($user->id, $project->id);
            $projectPreferences->copyFromGlobal($user->id);

            return redirect()->route('client.notification-preferences.index')
                ->with('success', 'Project-specific preferences created for ' . $project->name . '.');

        } catch (\Exception $e) {
            Log::error('Failed to create project-specific preferences', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Failed to create project preferences.']);
        }
    }

    /**
     * Update project-specific preferences
     */
    public function updateProject(Request $request, ClientNotificationPreferences $preferences)
    {
        $user = auth()->user();
        
        // Verify ownership and access
        if ($user->role !== 'client' || $preferences->user_id !== $user->id) {
            abort(403, 'Access denied.');
        }

        if (!$preferences->project_id) {
            abort(404, 'These are not project-specific preferences.');
        }

        $validated = $request->validate([
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
            'digest_frequency' => 'required|in:disabled,daily,weekly,monthly',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'timezone' => 'required|string|max:50',
        ]);

        try {
            $preferences->updatePreferences($validated);

            Log::info('Project-specific notification preferences updated', [
                'user_id' => $user->id,
                'project_id' => $preferences->project_id,
                'preferences_id' => $preferences->id,
                'changes' => $preferences->getChanges()
            ]);

            return redirect()->route('client.notification-preferences.index')
                ->with('success', 'Project preferences for ' . $preferences->project->name . ' have been updated.');

        } catch (\Exception $e) {
            Log::error('Failed to update project-specific notification preferences', [
                'user_id' => $user->id,
                'project_id' => $preferences->project_id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->withErrors(['general' => 'Failed to update project preferences.']);
        }
    }

    /**
     * Delete project-specific preferences (revert to global)
     */
    public function deleteProject(ClientNotificationPreferences $preferences)
    {
        $user = auth()->user();
        
        // Verify ownership and access
        if ($user->role !== 'client' || $preferences->user_id !== $user->id) {
            abort(403, 'Access denied.');
        }

        if (!$preferences->project_id) {
            abort(404, 'Cannot delete global preferences.');
        }

        try {
            $projectName = $preferences->project->name;
            $preferences->delete();

            Log::info('Project-specific notification preferences deleted', [
                'user_id' => $user->id,
                'project_id' => $preferences->project_id,
                'project_name' => $projectName
            ]);

            return redirect()->route('client.notification-preferences.index')
                ->with('success', 'Project-specific preferences for ' . $projectName . ' have been removed. Global preferences will now apply.');

        } catch (\Exception $e) {
            Log::error('Failed to delete project-specific notification preferences', [
                'user_id' => $user->id,
                'project_id' => $preferences->project_id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Failed to delete project preferences.']);
        }
    }

    /**
     * Get notification preferences for a specific user and context
     * Used by notification system to check if user should receive notifications
     */
    public static function getPreferencesForNotification(int $userId, ?int $projectId = null): ?ClientNotificationPreferences
    {
        // Try to get project-specific preferences first
        if ($projectId) {
            $projectPrefs = ClientNotificationPreferences::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->first();
            
            if ($projectPrefs) {
                return $projectPrefs;
            }
        }

        // Fall back to global preferences
        return ClientNotificationPreferences::getOrCreateForUser($userId);
    }

    /**
     * Check if user should receive a specific type of notification
     */
    public static function shouldReceiveNotification(
        int $userId, 
        string $notificationType, 
        string $channel = 'email', 
        ?int $projectId = null
    ): bool {
        $preferences = static::getPreferencesForNotification($userId, $projectId);
        
        if (!$preferences) {
            return false;
        }

        return $preferences->shouldReceiveNotification($notificationType, $channel);
    }

    /**
     * Bulk update preferences for multiple notification types
     */
    public function bulkUpdate(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            'email_notifications' => 'required|in:all,none,custom',
            'app_notifications' => 'required|in:all,none,custom',
            'apply_to_projects' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $globalPrefs = ClientNotificationPreferences::getOrCreateForUser($user->id);
            
            // Handle email notifications
            $emailSettings = match($validated['email_notifications']) {
                'all' => [
                    'progress_reports_email' => true,
                    'project_updates_email' => true,
                    'task_completion_email' => true,
                    'milestone_email' => true,
                    'issue_notifications_email' => true,
                    'photo_upload_email' => true,
                    'general_announcements_email' => true,
                ],
                'none' => [
                    'progress_reports_email' => false,
                    'project_updates_email' => false,
                    'task_completion_email' => false,
                    'milestone_email' => false,
                    'issue_notifications_email' => false,
                    'photo_upload_email' => false,
                    'general_announcements_email' => false,
                    'marketing_email' => false,
                ],
                'custom' => [] // Don't change for custom
            };

            // Handle app notifications
            $appSettings = match($validated['app_notifications']) {
                'all' => [
                    'progress_reports_app' => true,
                    'project_updates_app' => true,
                    'task_completion_app' => true,
                    'milestone_app' => true,
                    'issue_notifications_app' => true,
                    'photo_upload_app' => true,
                    'general_announcements_app' => true,
                ],
                'none' => [
                    'progress_reports_app' => false,
                    'project_updates_app' => false,
                    'task_completion_app' => false,
                    'milestone_app' => false,
                    'issue_notifications_app' => false,
                    'photo_upload_app' => false,
                    'general_announcements_app' => false,
                ],
                'custom' => [] // Don't change for custom
            };

            $updateData = array_merge($emailSettings, $appSettings);
            
            if (!empty($updateData)) {
                $globalPrefs->update($updateData);

                // Apply to project-specific preferences if requested
                if ($validated['apply_to_projects']) {
                    ClientNotificationPreferences::where('user_id', $user->id)
                        ->whereNotNull('project_id')
                        ->update($updateData);
                }
            }

            DB::commit();

            $message = 'Notification preferences updated successfully.';
            if ($validated['apply_to_projects']) {
                $message .= ' Changes have been applied to all your project-specific preferences.';
            }

            return redirect()->route('client.notification-preferences.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to bulk update notification preferences', [
                'user_id' => $user->id,
                'settings' => $validated,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Failed to update preferences.']);
        }
    }

    /**
     * Export notification preferences as JSON
     */
    public function export()
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied.');
        }

        $globalPrefs = ClientNotificationPreferences::getOrCreateForUser($user->id);
        $projectPrefs = ClientNotificationPreferences::where('user_id', $user->id)
            ->whereNotNull('project_id')
            ->with('project:id,name')
            ->get();

        $export = [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
            ],
            'exported_at' => now()->toISOString(),
            'global_preferences' => $globalPrefs->notification_summary,
            'project_preferences' => $projectPrefs->map(function ($prefs) {
                return [
                    'project' => [
                        'id' => $prefs->project->id,
                        'name' => $prefs->project->name,
                    ],
                    'preferences' => $prefs->notification_summary,
                ];
            }),
        ];

        $filename = 'notification_preferences_' . $user->username . '_' . now()->format('Y-m-d') . '.json';

        return response()->json($export, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }


    /**
     * Get notification statistics for the client
     */
    public function getStatistics()
    {
        $user = auth()->user();
        
        if ($user->role !== 'client') {
            abort(403, 'Access denied.');
        }

        $stats = [
            'total_notifications_sent' => $user->notifications()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'notifications_last_30_days' => $user->notifications()
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'progress_report_notifications' => $user->notifications()
                ->where('type', 'App\Notifications\ProgressReportShared')
                ->count(),
            'project_update_notifications' => $user->notifications()
                ->where('type', 'like', '%ProjectUpdate%')
                ->count(),
            'most_active_notification_day' => $user->notifications()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderByDesc('count')
                ->first(),
        ];

        return response()->json($stats);
    }
}