<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProjectCreatedNotification;

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
            $projects = Project::whereIn('id', function($query) use ($user) {
                $query->select('project_id')
                      ->from('tasks')
                      ->where('assigned_to', $user->id)
                      ->where('archived', false);
            })->where('archived', false)->with('creator')->latest()->paginate(10);
        } else {
            abort(403, 'Unauthorized access.');
        }
        
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

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => Auth::id(),
            'archived' => false,
        ]);

        $project->load('creator');
        
        // Notify all site coordinators about the new project
        $siteCoordinators = User::where('role', 'sc')->get();
        if ($siteCoordinators->count() > 0) {
            Notification::send($siteCoordinators, new ProjectCreatedNotification($project));
            $message = "Project created successfully. {$siteCoordinators->count()} site coordinator(s) notified.";
        } else {
            $message = "Project created successfully. No site coordinators found.";
        }

        return redirect()->route('projects.index')->with('success', $message);
    }

    public function show(Project $project)
    {
        $user = Auth::user();
        
        // Check if user can view this project
        if (!in_array($user->role, ['admin', 'pm', 'sc'])) {
            abort(403, 'Unauthorized to view this project.');
        }
        
        // PM can only see their own projects unless they are admin
        if ($user->role === 'pm' && $project->created_by !== $user->id) {
            abort(403, 'Unauthorized to view this project.');
        }
        
        // SC can only see projects they have tasks for
        if ($user->role === 'sc') {
            $hasTasksInProject = $project->tasks()->where('assigned_to', $user->id)->exists();
            if (!$hasTasksInProject) {
                abort(403, 'Unauthorized to view this project.');
            }
        }

        // Mark project notification as read if it's a site coordinator viewing the project
        if ($user->role === 'sc') {
            $notification = $user->unreadNotifications()
                ->where('type', ProjectCreatedNotification::class)
                ->where(function($query) use ($project) {
                    $query->where('data->project_id', $project->id)
                          ->orWhere('data->project_id', (string)$project->id);
                })
                ->first();
                
            if ($notification) {
                $notification->markAsRead();
            }
        }

        $project->load(['creator', 'tasks' => function($query) {
            $query->where('archived', false)->with('siteCoordinator');
        }]);

        return view('projects.show', compact('project'));
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
        
        $message = "Project archived successfully.";
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
        
        return redirect()->route('projects.archived')->with('success', 'Project restored successfully. Note: Associated tasks remain archived and must be restored individually.');
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
}