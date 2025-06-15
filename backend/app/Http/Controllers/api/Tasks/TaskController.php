<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

    // Method to create a task
    public function create(Request $request, Project $project)
    {
        // Check if user has permission to create tasks
        $user = Auth::user();
        if (!in_array($user->role, ['pm', 'admin'])) {
            abort(403, 'Unauthorized to create tasks.');
        }

        $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        // Verify the assigned user is a site coordinator
        $assignedUser = User::findOrFail($request->assigned_to);
        if ($assignedUser->role !== 'sc') {
            return redirect()->back()->withErrors(['assigned_to' => 'Tasks can only be assigned to Site Coordinators.']);
        }

        try {
            // Create the task
            $task = Task::create([
                'task_name' => $request->task_name,
                'description' => $request->description,
                'assigned_to' => $request->assigned_to,
                'project_id' => $project->id,
                'status' => 'pending',
            ]);

            // Notify the assigned Site Coordinator
            $assignedUser->notify(new TaskCreatedNotification($task));

            return redirect()->route('projects.show', $project->id)->with('success', 'Task created successfully and notification sent to Site Coordinator.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create task. Please try again.']);
        }
    }

    // Method to show a specific task
    public function show(Task $task)
    {
        $user = Auth::user();
        
        // Check permissions: admin, project creator, or assigned site coordinator can view
        if ($user->role !== 'admin' && 
            $task->project->created_by !== $user->id && 
            $task->assigned_to !== $user->id) {
            abort(403, 'Unauthorized to view this task.');
        }

        return view('tasks.show', compact('task'));
    }

    // Method to update task status (for site coordinators)
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // Only the assigned site coordinator can update status
        if ($task->assigned_to !== $user->id && $user->role !== 'admin') {
            abort(403, 'Unauthorized to update this task.');
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }
}