<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function pmDashboard()
    {
        $user = Auth::user();
        
        // Get PM's projects and tasks data
        $myProjects = Project::where('created_by', $user->id)->where('archived', false)->count();
        $activeProjects = Project::where('created_by', $user->id)->where('archived', false)->count();
        
        // Get tasks assigned by this PM (tasks in projects created by this PM)
        $tasksAssigned = Task::whereIn('project_id', function($query) use ($user) {
            $query->select('id')
                  ->from('projects')
                  ->where('created_by', $user->id);
        })->where('archived', false)->count();
        
        // Get upcoming deadlines (tasks due within 7 days in PM's projects)
        $upcomingDeadlines = Task::whereIn('project_id', function($query) use ($user) {
            $query->select('id')
                  ->from('projects')
                  ->where('created_by', $user->id);
        })
        ->where('archived', false)
        ->where('status', '!=', 'completed')
        ->where('due_date', '>=', now())
        ->where('due_date', '<=', now()->addDays(7))
        ->count();
        
        // Get project progress for PM's projects
        $projectProgress = Project::where('created_by', $user->id)
            ->where('archived', false)
            ->with(['tasks' => function($query) {
                $query->where('archived', false);
            }])
            ->get()
            ->map(function($project) {
                $totalTasks = $project->tasks->count();
                $completedTasks = $project->tasks->where('status', 'completed')->count();
                
                // Add computed properties
                $project->total_tasks = $totalTasks;
                $project->completed_tasks = $completedTasks;
                $project->completion_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                $project->formatted_due_date = $project->end_date ? $project->end_date->format('M d, Y') : 'No due date';
                $project->team_members_count = $project->tasks->pluck('assigned_to')->unique()->count();
                $project->priority = 'Medium'; // You can adjust this based on your logic
                $project->priority_badge_color = 'warning'; // You can adjust this based on priority
                $project->is_behind_schedule = $project->end_date && $project->end_date->isPast() && $project->completion_percentage < 100;
                $project->progress_color = $project->completion_percentage >= 75 ? 'success' : ($project->completion_percentage >= 50 ? 'warning' : 'danger');
                
                return $project;
            });
        
        // Get team performance (users assigned to tasks in PM's projects)
        $teamPerformance = User::whereIn('id', function($query) use ($user) {
            $query->select('assigned_to')
                ->from('tasks')
                ->whereIn('project_id', function($subQuery) use ($user) {
                    $subQuery->select('id')
                             ->from('projects')
                             ->where('created_by', $user->id);
                })
                ->where('archived', false);
        })
        ->get()
        ->map(function($member) use ($user) {
            $totalTasks = Task::whereIn('project_id', function($query) use ($user) {
                $query->select('id')
                      ->from('projects')
                      ->where('created_by', $user->id);
            })
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->count();
            
            $completedTasks = Task::whereIn('project_id', function($query) use ($user) {
                $query->select('id')
                      ->from('projects')
                      ->where('created_by', $user->id);
            })
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->where('status', 'completed')
            ->count();
            
            $activeTasks = Task::whereIn('project_id', function($query) use ($user) {
                $query->select('id')
                      ->from('projects')
                      ->where('created_by', $user->id);
            })
            ->where('assigned_to', $member->id)
            ->where('archived', false)
            ->where('status', '!=', 'completed')
            ->count();
            
            $member->completion_rate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            $member->active_tasks = $activeTasks;
            
            return $member;
        });
        
        // Get upcoming tasks (tasks due soon in PM's projects)
        $upcomingTasks = Task::whereIn('project_id', function($query) use ($user) {
            $query->select('id')
                  ->from('projects')
                  ->where('created_by', $user->id);
        })
        ->where('archived', false)
        ->where('status', '!=', 'completed')
        ->where('due_date', '>=', now())
        ->orderBy('due_date', 'asc')
        ->with(['project', 'siteCoordinator'])
        ->take(10)
        ->get()
        ->map(function($task) {
            $task->formatted_due_date = $task->due_date ? $task->due_date->format('M d, Y') : 'No due date';
            $task->is_overdue = $task->due_date && $task->due_date->isPast();
            $task->priority = 'Medium'; // Adjust based on your logic
            $task->priority_badge_color = 'warning'; // Adjust based on priority
            $task->assignee = $task->siteCoordinator; // Assuming tasks are assigned to site coordinators
            return $task;
        });
        
        // Get recent activity (you might want to create an Activity model for this)
        // For now, we'll create a simple collection
        $recentActivity = collect([
            (object)[
                'description' => 'New project created',
                'icon' => 'plus-circle',
                'color' => 'success',
                'user' => $user,
                'created_at' => now()->subHours(2)
            ],
            (object)[
                'description' => 'Task completed',
                'icon' => 'check-circle',
                'color' => 'primary',
                'user' => $user,
                'created_at' => now()->subHours(5)
            ]
        ]);
        
        return view('pm.dashboard', compact(
            'myProjects',
            'activeProjects', 
            'tasksAssigned',
            'upcomingDeadlines',
            'projectProgress',
            'teamPerformance',
            'upcomingTasks',
            'recentActivity'
        ));
    }
}