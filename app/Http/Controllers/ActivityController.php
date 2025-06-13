<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activities = [];

        // Get recent projects (last 10)
        $recentProjects = Project::latest()->take(10)->get();
        foreach ($recentProjects as $project) {
            $activities[] = [
                'type' => 'project',
                'action' => 'created',
                'title' => $project->name,
                'description' => 'Project created',
                'date' => $project->created_at,
                'user' => $project->user->full_name ?? 'System',
                'link' => route('projects.show', $project->id)
            ];
        }

        // Get recent tasks (last 10)
        $recentTasks = Task::latest()->take(10)->get();
        foreach ($recentTasks as $task) {
            $activities[] = [
                'type' => 'task',
                'action' => 'created',
                'title' => $task->name,
                'description' => 'Task created',
                'date' => $task->created_at,
                'user' => $task->assignedUser->full_name ?? 'Unassigned',
                'link' => route('tasks.show', $task->id)
            ];
        }

        // Sort activities by date (newest first)
        usort($activities, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        // Take only the most recent 20 activities
        $activities = array_slice($activities, 0, 20);

        return view('activity.index', compact('activities'));
    }
}