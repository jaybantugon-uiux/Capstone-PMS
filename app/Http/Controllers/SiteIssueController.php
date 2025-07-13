<?php

namespace App\Http\Controllers;

use App\Models\SiteIssue;
use App\Models\SiteIssueComment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SiteIssueReported;
use App\Notifications\SiteIssueUpdated;
use App\Notifications\SiteIssueAssigned;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SiteIssueController extends Controller
{
    /**
     * Display a listing of site issues for site coordinators
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Site coordinators can only see their own reported issues
        if ($user->role === 'sc') {
            $query = SiteIssue::with(['project', 'task', 'assignedTo'])
                ->where('user_id', $user->id);
        } else {
            // Admins and PMs can see all issues
            $query = SiteIssue::with(['project', 'task', 'reporter', 'assignedTo']);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('issue_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $siteIssues = $query->orderBy('reported_at', 'desc')->paginate(15);

        // Get data for filters
        $projects = Project::where('archived', false)->get();
        $statusOptions = ['open', 'in_progress', 'resolved', 'closed', 'escalated'];
        $priorityOptions = ['low', 'medium', 'high', 'critical'];
        $typeOptions = ['safety', 'equipment', 'environmental', 'personnel', 'quality', 'timeline', 'other'];

        return view('site-issues.index', compact(
            'siteIssues', 
            'projects', 
            'statusOptions', 
            'priorityOptions', 
            'typeOptions'
        ));
    }

    /**
     * Show the form for creating a new site issue
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Only site coordinators can create issues
        if ($user->role !== 'sc') {
            abort(403, 'Only site coordinators can create site issues.');
        }

        // Get projects where user has assigned tasks
        $projects = Project::whereHas('tasks', function ($query) use ($user) {
            $query->where('assigned_to', $user->id)->where('archived', false);
        })->where('archived', false)->get();

        $selectedProject = null;
        $availableTasks = collect();

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            if ($selectedProject) {
                $availableTasks = $selectedProject->tasks()
                    ->where('assigned_to', $user->id)
                    ->where('archived', false)
                    ->get();
            }
        }

        return view('site-issues.create', compact('projects', 'selectedProject', 'availableTasks'));
    }

    /**
     * Store a newly created site issue
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only site coordinators can create issues
        if ($user->role !== 'sc') {
            abort(403);
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'issue_title' => 'required|string|max:255',
            'issue_type' => 'required|in:safety,equipment,environmental,personnel,quality,timeline,other',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'affected_areas' => 'nullable|string',
            'immediate_actions_taken' => 'nullable|string',
            'suggested_solutions' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'photos.*' => 'nullable|image|max:5120', // 5MB max per image
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        // Verify user has access to the project/task
        $project = Project::findOrFail($request->project_id);
        
        if ($request->task_id) {
            $task = Task::where('id', $request->task_id)
                ->where('assigned_to', $user->id)
                ->where('project_id', $project->id)
                ->firstOrFail();
        }

        // Handle file uploads
        $photos = [];
        $attachments = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('site-issues/photos', $filename, 'public');
                $photos[] = $path;
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $filename = time() . '_' . Str::random(10) . '.' . $attachment->getClientOriginalExtension();
                $path = $attachment->storeAs('site-issues/attachments', $filename, 'public');
                $attachments[] = [
                    'path' => $path,
                    'original_name' => $attachment->getClientOriginalName(),
                    'size' => $attachment->getSize()
                ];
            }
        }

        // Create the site issue
        $siteIssue = SiteIssue::create([
            'project_id' => $request->project_id,
            'task_id' => $request->task_id,
            'user_id' => $user->id,
            'issue_title' => $request->issue_title,
            'issue_type' => $request->issue_type,
            'priority' => $request->priority,
            'description' => $request->description,
            'location' => $request->location,
            'affected_areas' => $request->affected_areas,
            'immediate_actions_taken' => $request->immediate_actions_taken,
            'suggested_solutions' => $request->suggested_solutions,
            'estimated_cost' => $request->estimated_cost,
            'photos' => $photos,
            'attachments' => $attachments,
            'status' => 'open',
            'reported_at' => now(),
        ]);

        // ENHANCED: Notify both admins and project managers
        $this->notifyAdminsAndPMsOfNewIssue($siteIssue);

        return redirect()->route('sc.site-issues.show', $siteIssue)
            ->with('success', 'Site issue reported successfully. Administrators and project managers have been notified.');
    }

    /**
     * Display the specified site issue
     */
    public function show(SiteIssue $siteIssue)
    {
        $user = Auth::user();

        // Check permissions
        if ($user->role === 'sc' && $siteIssue->user_id !== $user->id) {
            abort(403);
        }

        $siteIssue->load([
            'project', 
            'task', 
            'reporter', 
            'assignedTo', 
            'resolvedBy',
            'acknowledgedBy',
            'comments' => function ($query) use ($user) {
                // Filter comments based on user role
                if ($user->role === 'sc') {
                    $query->where('is_internal', false);
                }
                $query->with('user')->orderBy('created_at', 'asc');
            }
        ]);

        return view('site-issues.show', compact('siteIssue'));
    }

    /**
     * Show the form for editing the specified site issue
     */
    public function edit(SiteIssue $siteIssue)
    {
        $user = Auth::user();

        // Check permissions - only reporter (if not resolved) or admin/PM can edit
        if ($user->role === 'sc') {
            if ($siteIssue->user_id !== $user->id || in_array($siteIssue->status, ['resolved', 'closed'])) {
                abort(403);
            }
        } elseif (!in_array($user->role, ['admin', 'pm'])) {
            abort(403);
        }

        $projects = Project::where('archived', false)->get();
        $availableTasks = [];

        if ($siteIssue->project_id) {
            $availableTasks = Task::where('project_id', $siteIssue->project_id)
                ->where('archived', false)
                ->get();
        }

        return view('site-issues.edit', compact('siteIssue', 'projects', 'availableTasks'));
    }

    /**
     * Update the specified site issue
     */
    public function update(Request $request, SiteIssue $siteIssue)
    {
        $user = Auth::user();

        // Check permissions
        if ($user->role === 'sc') {
            if ($siteIssue->user_id !== $user->id || in_array($siteIssue->status, ['resolved', 'closed'])) {
                abort(403);
            }
        } elseif (!in_array($user->role, ['admin', 'pm'])) {
            abort(403);
        }

        $rules = [
            'issue_title' => 'required|string|max:255',
            'issue_type' => 'required|in:safety,equipment,environmental,personnel,quality,timeline,other',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'affected_areas' => 'nullable|string',
            'immediate_actions_taken' => 'nullable|string',
            'suggested_solutions' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
        ];

        // Admin/PM-specific fields
        if (in_array($user->role, ['admin', 'pm'])) {
            $rules = array_merge($rules, [
                'status' => 'required|in:open,in_progress,resolved,closed,escalated',
                'assigned_to' => 'nullable|exists:users,id',
                'admin_notes' => 'nullable|string',
                'resolution_description' => 'nullable|string',
            ]);
        }

        $request->validate($rules);

        $updateData = [
            'issue_title' => $request->issue_title,
            'issue_type' => $request->issue_type,
            'priority' => $request->priority,
            'description' => $request->description,
            'location' => $request->location,
            'affected_areas' => $request->affected_areas,
            'immediate_actions_taken' => $request->immediate_actions_taken,
            'suggested_solutions' => $request->suggested_solutions,
            'estimated_cost' => $request->estimated_cost,
        ];

        // Admin/PM-specific updates
        if (in_array($user->role, ['admin', 'pm'])) {
            $oldStatus = $siteIssue->status;
            
            $updateData = array_merge($updateData, [
                'status' => $request->status,
                'assigned_to' => $request->assigned_to,
                'admin_notes' => $request->admin_notes,
                'resolution_description' => $request->resolution_description,
            ]);

            // Handle status changes
            if ($request->status === 'resolved' && $oldStatus !== 'resolved') {
                $updateData['resolved_at'] = now();
                $updateData['resolved_by'] = $user->id;
            } elseif ($request->status !== 'resolved') {
                $updateData['resolved_at'] = null;
                $updateData['resolved_by'] = null;
            }

            // Auto-acknowledge if assigning or changing status
            if (!$siteIssue->acknowledged_at && ($request->assigned_to || $request->status !== 'open')) {
                $updateData['acknowledged_at'] = now();
                $updateData['acknowledged_by'] = $user->id;
            }
        }

        $siteIssue->update($updateData);

        // Notify relevant users of updates
        if (in_array($user->role, ['admin', 'pm'])) {
            $this->notifyReporterOfUpdate($siteIssue);
        }

        $route = $user->role === 'sc' ? 'sc.site-issues.show' : 
                 ($user->role === 'pm' ? 'pm.site-issues.show' : 'admin.site-issues.show');
        
        return redirect()->route($route, $siteIssue)
            ->with('success', 'Site issue updated successfully.');
    }

    /**
     * Add a comment to the site issue
     */
    public function addComment(Request $request, SiteIssue $siteIssue)
    {
        $user = Auth::user();

        // Check permissions
        if ($user->role === 'sc' && $siteIssue->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'comment' => 'required|string',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        // Only admins/PMs can create internal comments
        $isInternal = $request->boolean('is_internal') && in_array($user->role, ['admin', 'pm']);

        // Handle attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $filename = time() . '_' . Str::random(10) . '.' . $attachment->getClientOriginalExtension();
                $path = $attachment->storeAs('site-issues/comments', $filename, 'public');
                $attachments[] = [
                    'path' => $path,
                    'original_name' => $attachment->getClientOriginalName(),
                    'size' => $attachment->getSize()
                ];
            }
        }

        SiteIssueComment::create([
            'site_issue_id' => $siteIssue->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'is_internal' => $isInternal,
            'attachments' => $attachments,
        ]);

        $route = $user->role === 'sc' ? 'sc.site-issues.show' : 
                 ($user->role === 'pm' ? 'pm.site-issues.show' : 'admin.site-issues.show');
        
        return redirect()->route($route, $siteIssue)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Get tasks for a specific project (AJAX)
     */
    public function getProjectTasks(Request $request)
    {
        $user = Auth::user();
        $projectId = $request->get('project_id');

        if ($user->role === 'sc') {
            $tasks = Task::where('project_id', $projectId)
                ->where('assigned_to', $user->id)
                ->where('archived', false)
                ->get(['id', 'task_name']);
        } else {
            $tasks = Task::where('project_id', $projectId)
                ->where('archived', false)
                ->get(['id', 'task_name']);
        }

        return response()->json($tasks);
    }

    /**
     * ENHANCED: Notify both admins and project managers of new site issue
     */
    private function notifyAdminsAndPMsOfNewIssue(SiteIssue $siteIssue)
    {
        // Get all users who should receive site issue notifications
        $usersToNotify = User::where(function($query) use ($siteIssue) {
            // Always include admins
            $query->where('role', 'admin')
                  ->where('status', 'active');
        })
        ->orWhere(function($query) use ($siteIssue) {
            // Include PMs who should receive notifications for this project
            $query->where('role', 'pm')
                  ->where('status', 'active')
                  ->where(function($pmQuery) use ($siteIssue) {
                      // PM created the project
                      $pmQuery->where('id', $siteIssue->project->created_by)
                              // OR PM created tasks in this project
                              ->orWhereHas('createdTasks', function($taskQuery) use ($siteIssue) {
                                  $taskQuery->where('project_id', $siteIssue->project_id);
                              });
                  });
        })->get();

        // Send notifications to each eligible user
        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new SiteIssueReported($siteIssue));
            } catch (\Exception $e) {
                Log::error('Failed to send site issue notification to user ' . $user->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify reporter of issue updates
     */
    private function notifyReporterOfUpdate(SiteIssue $siteIssue)
    {
        if ($siteIssue->reporter) {
            try {
                $siteIssue->reporter->notify(new SiteIssueUpdated($siteIssue));
            } catch (\Exception $e) {
                Log::error('Failed to send site issue update notification: ' . $e->getMessage());
            }
        }
    }

    // ====================================================================
    // PM-SPECIFIC METHODS (ENHANCED ADMIN FUNCTIONALITY FOR PMS)
    // ====================================================================

    /**
     * PM dashboard for site issues (similar to admin but filtered for PM projects)
     */
    public function pmIndex(Request $request)
    {
        $user = Auth::user();
        
        // Ensure only PMs can access this
        if ($user->role !== 'pm') {
            abort(403);
        }

        // Get PM's managed projects
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        $query = SiteIssue::with(['project', 'task', 'reporter', 'assignedTo'])
            ->whereIn('project_id', $managedProjectIds);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('project_id') && in_array($request->project_id, $managedProjectIds)) {
            $query->where('project_id', $request->project_id);
        }

        $siteIssues = $query->orderByRaw("
            CASE status 
                WHEN 'open' THEN 1 
                WHEN 'in_progress' THEN 2 
                WHEN 'escalated' THEN 3 
                WHEN 'resolved' THEN 4 
                WHEN 'closed' THEN 5 
            END
        ")->orderByRaw("
            CASE priority 
                WHEN 'critical' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
            END
        ")->orderBy('reported_at', 'desc')->paginate(20);

        // Get stats for PM's projects only
        $stats = [
            'total' => SiteIssue::whereIn('project_id', $managedProjectIds)->count(),
            'open' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'open')->count(),
            'in_progress' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'in_progress')->count(),
            'resolved' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'resolved')->count(),
            'critical' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unacknowledged' => SiteIssue::whereIn('project_id', $managedProjectIds)->whereNull('acknowledged_at')->count(),
        ];

        // Get filter options (only for PM's projects)
        $projects = Project::whereIn('id', $managedProjectIds)->where('archived', false)->get();
        $assignableUsers = User::whereIn('role', ['admin', 'pm'])->get();
        
        return view('pm.site-issues.index', compact('siteIssues', 'stats', 'projects', 'assignableUsers'));
    }

    /**
     * PM view of specific site issue
     */
    public function pmShow(SiteIssue $siteIssue)
    {
        $user = Auth::user();
        
        // Ensure only PMs can access this and they manage the project
        if ($user->role !== 'pm' || !$user->canManageProject($siteIssue->project_id)) {
            abort(403);
        }

        $siteIssue->load([
            'project', 
            'task', 
            'reporter', 
            'assignedTo', 
            'resolvedBy',
            'acknowledgedBy',
            'comments.user'
        ]);

        $assignableUsers = User::whereIn('role', ['admin', 'pm'])->get();

        return view('pm.site-issues.show', compact('siteIssue', 'assignableUsers'));
    }

    /**
     * Admin dashboard for site issues (existing functionality preserved)
     */
    public function adminIndex(Request $request)
    {
        $query = SiteIssue::with(['project', 'task', 'reporter', 'assignedTo']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('issue_type')) {
            $query->where('issue_type', $request->issue_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $siteIssues = $query->orderByRaw("
            CASE status 
                WHEN 'open' THEN 1 
                WHEN 'in_progress' THEN 2 
                WHEN 'escalated' THEN 3 
                WHEN 'resolved' THEN 4 
                WHEN 'closed' THEN 5 
            END
        ")->orderByRaw("
            CASE priority 
                WHEN 'critical' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
            END
        ")->orderBy('reported_at', 'desc')->paginate(20);

        // Get stats
        $stats = [
            'total' => SiteIssue::count(),
            'open' => SiteIssue::where('status', 'open')->count(),
            'in_progress' => SiteIssue::where('status', 'in_progress')->count(),
            'resolved' => SiteIssue::where('status', 'resolved')->count(),
            'critical' => SiteIssue::where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unacknowledged' => SiteIssue::whereNull('acknowledged_at')->count(),
        ];

        // Get filter options
        $projects = Project::where('archived', false)->get();
        $assignableUsers = User::whereIn('role', ['admin', 'pm'])->get();
        
        return view('admin.site-issues.index', compact('siteIssues', 'stats', 'projects', 'assignableUsers'));
    }

    /**
     * Admin view of specific site issue (existing functionality preserved)
     */
    public function adminShow(SiteIssue $siteIssue)
    {
        $siteIssue->load([
            'project', 
            'task', 
            'reporter', 
            'assignedTo', 
            'resolvedBy',
            'acknowledgedBy',
            'comments.user'
        ]);

        $assignableUsers = User::whereIn('role', ['admin', 'pm'])->get();

        return view('admin.site-issues.show', compact('siteIssue', 'assignableUsers'));
    }

    /**
     * ENHANCED: Get site issue statistics for PM dashboard
     */
    public function getPMStats()
    {
        $user = Auth::user();
        
        if ($user->role !== 'pm') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        $stats = [
            'total' => SiteIssue::whereIn('project_id', $managedProjectIds)->count(),
            'open' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'open')->count(),
            'in_progress' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'in_progress')->count(),
            'resolved' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('status', 'resolved')->count(),
            'critical' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('priority', 'critical')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'unacknowledged' => SiteIssue::whereIn('project_id', $managedProjectIds)->whereNull('acknowledged_at')->count(),
            'recent' => SiteIssue::whereIn('project_id', $managedProjectIds)->where('reported_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json($stats);
    }

    /**
     * ENHANCED: Bulk actions for PMs (assign, acknowledge, etc.)
     */
    public function pmBulkAction(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'pm') {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:assign,acknowledge,change_status,change_priority',
            'ids' => 'required|array',
            'ids.*' => 'exists:site_issues,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|in:open,in_progress,resolved,closed,escalated',
            'priority' => 'nullable|in:low,medium,high,critical',
        ]);

        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        $siteIssues = SiteIssue::whereIn('id', $request->ids)
            ->whereIn('project_id', $managedProjectIds)
            ->get();

        if ($siteIssues->isEmpty()) {
            return back()->withErrors(['error' => 'No issues found or you do not have permission to modify these issues.']);
        }

        $message = '';

        switch ($request->action) {
            case 'assign':
                if (!$request->assigned_to) {
                    return back()->withErrors(['error' => 'Please select a user to assign.']);
                }
                
                foreach ($siteIssues as $issue) {
                    $issue->update([
                        'assigned_to' => $request->assigned_to,
                        'status' => $issue->status === 'open' ? 'in_progress' : $issue->status,
                        'acknowledged_at' => $issue->acknowledged_at ?: now(),
                        'acknowledged_by' => $issue->acknowledged_by ?: $user->id,
                    ]);
                    
                    // Notify assigned user
                    $assignedUser = User::find($request->assigned_to);
                    if ($assignedUser) {
                        $assignedUser->notify(new SiteIssueAssigned($issue));
                    }
                }
                $message = 'Issues assigned successfully.';
                break;

            case 'acknowledge':
                foreach ($siteIssues as $issue) {
                    if (!$issue->acknowledged_at) {
                        $issue->update([
                            'acknowledged_at' => now(),
                            'acknowledged_by' => $user->id,
                        ]);
                    }
                }
                $message = 'Issues acknowledged successfully.';
                break;

            case 'change_status':
                if (!$request->status) {
                    return back()->withErrors(['error' => 'Please select a status.']);
                }
                
                foreach ($siteIssues as $issue) {
                    $updateData = ['status' => $request->status];
                    
                    if ($request->status === 'resolved') {
                        $updateData['resolved_at'] = now();
                        $updateData['resolved_by'] = $user->id;
                    }
                    
                    $issue->update($updateData);
                    
                    // Notify reporter of status change
                    if ($issue->reporter) {
                        $issue->reporter->notify(new SiteIssueUpdated($issue));
                    }
                }
                $message = 'Issues status updated successfully.';
                break;

            case 'change_priority':
                if (!$request->priority) {
                    return back()->withErrors(['error' => 'Please select a priority.']);
                }
                
                foreach ($siteIssues as $issue) {
                    $issue->update(['priority' => $request->priority]);
                }
                $message = 'Issues priority updated successfully.';
                break;

            default:
                return back()->withErrors(['error' => 'Invalid action.']);
        }

        return back()->with('success', $message);
    }

    /**
     * ENHANCED: Export functionality for PMs
     */
    public function pmExport(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'pm') {
            abort(403);
        }

        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        $filename = 'site_issues_pm_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($request, $managedProjectIds) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID', 'Title', 'Type', 'Priority', 'Status', 'Project', 'Task', 
                'Reporter', 'Reporter Email', 'Assigned To', 'Location', 
                'Estimated Cost', 'Reported At', 'Acknowledged At', 'Resolved At',
                'Days Open', 'Description'
            ]);

            // Build query with filters and PM restrictions
            $query = SiteIssue::with(['project', 'task', 'reporter', 'assignedTo'])
                ->whereIn('project_id', $managedProjectIds);

            // Apply filters from request
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->filled('issue_type')) {
                $query->where('issue_type', $request->issue_type);
            }
            if ($request->filled('project_id') && in_array($request->project_id, $managedProjectIds)) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('date_from')) {
                $query->where('reported_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('reported_at', '<=', $request->date_to . ' 23:59:59');
            }

            // Export data
            $query->orderBy('reported_at', 'desc')->chunk(1000, function($issues) use ($file) {
                foreach ($issues as $issue) {
                    $daysOpen = $issue->resolved_at ? 
                        $issue->reported_at->diffInDays($issue->resolved_at) :
                        $issue->reported_at->diffInDays(now());

                    fputcsv($file, [
                        $issue->id,
                        $issue->issue_title,
                        ucfirst($issue->issue_type),
                        ucfirst($issue->priority),
                        ucfirst(str_replace('_', ' ', $issue->status)),
                        $issue->project->name,
                        $issue->task ? $issue->task->task_name : '',
                        $issue->reporter->first_name . ' ' . $issue->reporter->last_name,
                        $issue->reporter->email,
                        $issue->assignedTo ? $issue->assignedTo->first_name . ' ' . $issue->assignedTo->last_name : 'Unassigned',
                        $issue->location ?? '',
                        $issue->estimated_cost ? '₱' . number_format($issue->estimated_cost, 2) : '',
                        $issue->reported_at ? $issue->reported_at->format('M d, Y g:i A') : '',
                        $issue->acknowledged_at ? $issue->acknowledged_at->format('M d, Y g:i A') : '',
                        $issue->resolved_at ? $issue->resolved_at->format('M d, Y g:i A') : '',
                        $daysOpen,
                        strip_tags($issue->description)
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * ENHANCED: Get recent site issues for PM dashboard API
     */
    public function getPMRecentIssues()
    {
        $user = Auth::user();
        
        if ($user->role !== 'pm') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        $recentIssues = SiteIssue::with(['project', 'reporter', 'assignedTo'])
            ->whereIn('project_id', $managedProjectIds)
            ->orderBy('reported_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($issue) {
                return [
                    'id' => $issue->id,
                    'title' => $issue->issue_title,
                    'priority' => $issue->priority,
                    'status' => $issue->status,
                    'project_name' => $issue->project->name,
                    'reporter_name' => $issue->reporter->first_name . ' ' . $issue->reporter->last_name,
                    'reported_at' => $issue->reported_at->format('M d, Y g:i A'),
                    'status_color' => $issue->status_badge_color,
                    'priority_color' => $issue->priority_badge_color,
                    'is_critical' => $issue->priority === 'critical',
                    'is_unacknowledged' => !$issue->acknowledged_at,
                    'url' => route('pm.site-issues.show', $issue->id)
                ];
            });

        return response()->json($recentIssues);
    }
}