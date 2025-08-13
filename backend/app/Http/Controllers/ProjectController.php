<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectCreatedNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Admin and PM can see projects they created or all projects
        if (in_array($user->role, ['admin', 'pm'])) {
            if ($user->role === 'admin') {
                $projects = Project::where('archived', false)->with('creator')->latest()->paginate(10);
            } else {
                $projects = Project::where('created_by', $user->id)
                    ->where('archived', false)->with('creator')->latest()->paginate(10);
            }
        } elseif ($user->role === 'sc') {
            // Site coordinators can see projects they have tasks for
            $projects = Project::whereHas('tasks', function($query) use ($user) {
                $query->where('assigned_to', $user->id)
                      ->where('archived', false);
            })->where('archived', false)->with('creator')->latest()->paginate(10);
        } else {
            abort(403, 'Unauthorized access.');
        }
        
        return view('projects.index', compact('projects'));
    }

    /**
     * Show projects for site coordinators only - Enhanced version
     */
    public function scIndex()
    {
        $user = auth()->user();
        
        Log::info('SC accessing their projects', [
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);
        
        // Get projects where user has assigned tasks
        $projects = Project::whereHas('tasks', function ($query) use ($user) {
            $query->where('assigned_to', $user->id)
                  ->where('archived', false);
        })
        ->with(['tasks' => function ($query) use ($user) {
            $query->where('assigned_to', $user->id)
                  ->where('archived', false)
                  ->with('creator');
        }, 'creator'])
        ->where('archived', false)
        ->paginate(10);
        
        // Add task counts and progress for each project
        $projects->getCollection()->transform(function ($project) use ($user) {
            $userTasks = $project->tasks;
            $project->user_tasks_count = $userTasks->count();
            $project->user_completed_tasks = $userTasks->where('status', 'completed')->count();
            $project->user_progress_percentage = $userTasks->count() > 0 
                ? round(($project->user_completed_tasks / $userTasks->count()) * 100) 
                : 0;
            
            return $project;
        });
        
        Log::info('SC projects loaded', [
            'user_id' => $user->id,
            'projects_count' => $projects->total(),
            'project_ids' => $projects->pluck('id')->toArray()
        ]);
        
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        // Only admin and pm can create projects
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to create projects.');
        }
        
        return view('projects.create');
    }

    public function store(Request $request)
    {
        // Only admin and pm can create projects
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to create projects.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        Log::info('Creating new project', [
            'creator_id' => Auth::id(),
            'creator_role' => Auth::user()->role,
            'creator_name' => Auth::user()->full_name,
            'project_name' => $request->name
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => Auth::id(),
            'archived' => false,
        ]);

        $project->load('creator');
        
        Log::info('Project created successfully, preparing notifications', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'creator_id' => Auth::id(),
            'creator_name' => Auth::user()->full_name
        ]);
        
        // ENHANCED: Notify all active site coordinators about the new project
        $siteCoordinators = User::where('role', 'sc')
            ->where('status', 'active')
            ->get();
        
        Log::info('Found site coordinators for project notification', [
            'project_id' => $project->id,
            'coordinators_count' => $siteCoordinators->count(),
            'coordinator_emails' => $siteCoordinators->pluck('email')->toArray()
        ]);
        
        $notificationResults = [];
        
        if ($siteCoordinators->count() > 0) {
            try {
                foreach ($siteCoordinators as $coordinator) {
                    Log::info('Sending project notification to coordinator', [
                        'project_id' => $project->id,
                        'coordinator_id' => $coordinator->id,
                        'coordinator_name' => $coordinator->full_name,
                        'coordinator_email' => $coordinator->email
                    ]);
                    
                    $coordinator->notify(new ProjectCreatedNotification($project));
                    
                    $notificationResults[] = [
                        'coordinator_id' => $coordinator->id,
                        'coordinator_email' => $coordinator->email,
                        'status' => 'sent'
                    ];
                }
                
                Log::info('All project notifications sent successfully', [
                    'project_id' => $project->id,
                    'notifications_sent' => count($notificationResults),
                    'results' => $notificationResults
                ]);
                
                $message = "Project '{$project->name}' created successfully. {$siteCoordinators->count()} site coordinator(s) have been notified via email.";
                
            } catch (\Exception $e) {
                Log::error('Failed to send project notifications', [
                    'project_id' => $project->id,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                    'attempted_notifications' => count($notificationResults)
                ]);
                
                $message = "Project '{$project->name}' created successfully, but there was an issue sending email notifications to site coordinators.";
            }
        } else {
            Log::warning('No active site coordinators found for project notification', [
                'project_id' => $project->id
            ]);
            
            $message = "Project '{$project->name}' created successfully. No active site coordinators found to notify.";
        }

        return redirect()->route('projects.index')->with('success', $message);
    }

    public function show(Project $project)
    {
        $user = auth()->user();
        
        Log::info('Project access attempt', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_email' => $user->email,
            'project_id' => $project->id,
            'project_name' => $project->name,
            'project_creator' => $project->created_by
        ]);
        
        // Role-based access control
        if ($user->role === 'sc') {
            // Site coordinator: can view projects where they have assigned tasks
            $userTasksInProject = $project->tasks()
                ->where('assigned_to', $user->id)
                ->where('archived', false)
                ->with('creator')
                ->get();
            
            if ($userTasksInProject->isEmpty()) {
                Log::warning('SC attempted to access project without assigned tasks', [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'user_email' => $user->email
                ]);
                
                return redirect()->route('sc.projects.index')
                    ->with('error', 'You do not have any tasks assigned in this project.');
            }
            
            // For SCs, only show their tasks
            $tasks = $userTasksInProject;
            
            Log::info('SC granted access to project', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'assigned_tasks_count' => $tasks->count()
            ]);
            
        } elseif ($user->role === 'pm') {
            // Project manager: can view projects they created or projects with tasks they created
            $canAccess = ($project->created_by === $user->id) || 
                        $project->tasks()->where('created_by', $user->id)->exists();
            
            if (!$canAccess) {
                Log::warning('PM attempted to access unauthorized project', [
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'project_creator' => $project->created_by
                ]);
                
                return redirect()->route('projects.index')
                    ->with('error', 'You can only view projects you created or projects with tasks you manage.');
            }
            
            // For PMs, show all tasks in the project
            $tasks = $project->tasks()->where('archived', false)->with('siteCoordinator')->get();
            
            Log::info('PM granted access to project', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'access_reason' => $project->created_by === $user->id ? 'project_creator' : 'task_creator',
                'total_tasks_count' => $tasks->count()
            ]);
            
        } else { // Admin
            // Admin: can view all projects and all tasks
            $tasks = $project->tasks()->where('archived', false)->with('siteCoordinator')->get();
            
            Log::info('Admin granted access to project', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'total_tasks_count' => $tasks->count()
            ]);
        }
        
        $project->load(['creator', 'tasks.siteCoordinator']);
        
        // Add additional project statistics for display
        $project->total_tasks = $tasks->count();
        $project->completed_tasks = $tasks->where('status', 'completed')->count();
        $project->in_progress_tasks = $tasks->where('status', 'in_progress')->count();
        $project->pending_tasks = $tasks->where('status', 'pending')->count();
        
        if ($project->total_tasks > 0) {
            $project->completion_percentage = round(($project->completed_tasks / $project->total_tasks) * 100);
        } else {
            $project->completion_percentage = 0;
        }
        
        return view('projects.show', compact('project', 'tasks'))
            ->with('userRole', $user->role);
    }

    public function edit(Project $project)
    {
        // Only admin and project creator can edit
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to edit projects.');
        }
        
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to edit this project.');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        // Only admin and project creator can update
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to update projects.');
        }
        
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to update this project.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        Log::info('Project updated', [
            'project_id' => $project->id,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function archive(Project $project)
    {
        // Only admin and project creator can archive
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to archive projects.');
        }
        
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to archive this project.');
        }
        
        $project->update(['archived' => true]);
        
        // Archive all associated tasks
        $archivedTasksCount = $project->tasks()->where('archived', false)->count();
        $project->tasks()->update(['archived' => true]);
        
        Log::info('Project archived with tasks', [
            'project_id' => $project->id,
            'archived_by' => Auth::id(),
            'archived_tasks_count' => $archivedTasksCount
        ]);
        
        $message = "Project '{$project->name}' archived successfully.";
        if ($archivedTasksCount > 0) {
            $message .= " {$archivedTasksCount} associated task(s) were also archived.";
        }
        
        return redirect()->route('projects.index')->with('success', $message);
    }

    public function restore(Project $project)
    {
        // Only admin and project creator can restore
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to restore projects.');
        }
        
        if (Auth::user()->role !== 'admin' && $project->created_by !== Auth::id()) {
            abort(403, 'Unauthorized to restore this project.');
        }
        
        $project->update(['archived' => false]);
        
        Log::info('Project restored', [
            'project_id' => $project->id,
            'restored_by' => Auth::id()
        ]);
        
        return redirect()->route('projects.archived')->with('success', 'Project restored successfully. Note: Associated tasks remain archived and must be restored individually.');
    }

    public function apiStore(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'created_by' => Auth::id(),
            'archived' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully.',
            'project' => $project,
        ]);
    }

    public function apiIndex()
    {
        $projects = \App\Models\Project::all();
        return response()->json([
            'success' => true,
            'projects' => $projects,
        ]);
    }

    public function apiActive()
    {
        try {
            $user = Auth::user();

            // Optional: Filter based on role if needed
            if ($user->role === 'pm') {
                $projects = Project::where('created_by', $user->id)
                    ->where('archived', false)
                    ->with(['creator', 'siteCoordinator', 'tasks'])
                    ->get();
            } else {
                $projects = Project::where('archived', false)
                    ->with(['creator', 'siteCoordinator', 'tasks'])
                    ->get();
            }

            return response()->json([
                'success' => true,
                'projects' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Active projects fetch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Server error while fetching active projects.'
            ], 500);
        }
    }


    public function archived()
    {
        $user = Auth::user();
        
        // Only admin and pm can see archived projects
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to view archived projects.');
        }
        
        if ($user->role === 'admin') {
            $projects = Project::where('archived', true)->with('creator')->latest()->paginate(10);
        } else {
            $projects = Project::where('created_by', $user->id)
                ->where('archived', true)->with('creator')->latest()->paginate(10);
        }
        
        return view('projects.archived', compact('projects'));
    }

    public function apiArchive(Project $project)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($project->archived) {
            return response()->json([
                'status' => 'info',
                'message' => 'Project is already archived.'
            ]);
        }

        $project->update(['archived' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Project archived successfully.',
            'project' => $project
        ]);
    }


    public function apiArchived()
    {
        try {
            $projects = Project::where('archived', true)->get();

            return response()->json([
                'success' => true,
                'projects' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Archived fetch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Server error while fetching archived projects.'
            ], 500);
        }
    }


    public function apiUnarchive(Project $project)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$project->archived) {
            return response()->json([
                'status' => 'info',
                'message' => 'Project is already active.'
            ]);
        }

        $project->update(['archived' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Project unarchived successfully.',
            'project' => $project
        ]);
    }
}