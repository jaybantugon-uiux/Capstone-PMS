<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to view reports.');
        }

        return view('reports.index');
    }

    public function projectReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to view project reports.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $query = Project::with(['creator', 'tasks' => function($q) {
            $q->where('archived', false);
        }])->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($user->role === 'pm') {
            $query->where('created_by', $user->id);
        }

        $projects = $query->get();

        $totalProjects = $projects->count();
        $completedProjects = $projects->filter(function($project) {
            return $project->status === 'Completed';
        })->count();
        $inProgressProjects = $projects->filter(function($project) {
            return $project->status === 'In Progress';
        })->count();
        $overdueProjects = $projects->filter(function($project) {
            return $project->is_overdue;
        })->count();

        $avgCompletionRate = $projects->avg('completion_percentage');

        return view('reports.project-report', compact(
            'projects', 
            'dateFrom', 
            'dateTo', 
            'totalProjects', 
            'completedProjects', 
            'inProgressProjects', 
            'overdueProjects',
            'avgCompletionRate'
        ));
    }

    public function taskReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to view task reports.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $assignedTo = $request->get('assigned_to', 'all');

        $query = Task::with(['creator', 'siteCoordinator', 'project'])
            ->where('archived', false)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($user->role === 'pm') {
            $query->where('created_by', $user->id);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($assignedTo !== 'all') {
            $query->where('assigned_to', $assignedTo);
        }

        $tasks = $query->get();

        $siteCoordinators = User::where('role', 'sc')->where('status', 'active')->get();

        $totalTasks = $tasks->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->filter(function($task) {
            return $task->is_overdue;
        })->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        return view('reports.task-report', compact(
            'tasks', 
            'dateFrom', 
            'dateTo', 
            'status', 
            'assignedTo',
            'siteCoordinators',
            'totalTasks', 
            'pendingTasks', 
            'inProgressTasks', 
            'completedTasks', 
            'overdueTasks',
            'completionRate'
        ));
    }

    public function performanceReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to view performance reports.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $siteCoordinators = User::where('role', 'sc')
            ->where('status', 'active')
            ->withCount([
                'assignedTasks as total_tasks' => function($query) use ($dateFrom, $dateTo) {
                    $query->where('archived', false)
                          ->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'assignedTasks as completed_tasks' => function($query) use ($dateFrom, $dateTo) {
                    $query->where('archived', false)
                          ->where('status', 'completed')
                          ->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'assignedTasks as pending_tasks' => function($query) use ($dateFrom, $dateTo) {
                    $query->where('archived', false)
                          ->where('status', 'pending')
                          ->whereBetween('created_at', [$dateFrom, $dateTo]);
                },
                'assignedTasks as overdue_tasks' => function($query) use ($dateFrom, $dateTo) {
                    $query->where('archived', false)
                          ->where('due_date', '<', Carbon::now())
                          ->where('status', '!=', 'completed')
                          ->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            ])->get();

        $performanceData = $siteCoordinators->map(function($sc) {
            $completionRate = $sc->total_tasks > 0 ? round(($sc->completed_tasks / $sc->total_tasks) * 100, 2) : 0;
            $overdueRate = $sc->total_tasks > 0 ? round(($sc->overdue_tasks / $sc->total_tasks) * 100, 2) : 0;
            
            return [
                'name' => $sc->name,
                'email' => $sc->email,
                'total_tasks' => $sc->total_tasks,
                'completed_tasks' => $sc->completed_tasks,
                'pending_tasks' => $sc->pending_tasks,
                'overdue_tasks' => $sc->overdue_tasks,
                'completion_rate' => $completionRate,
                'overdue_rate' => $overdueRate,
                'performance_score' => max(0, $completionRate - $overdueRate)
            ];
        });

        $performanceData = $performanceData->sortByDesc('performance_score');

        return view('reports.performance-report', compact(
            'performanceData', 
            'dateFrom', 
            'dateTo'
        ));
    }

    public function exportProjectReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to export project reports.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        $query = Project::with(['creator', 'tasks' => function($q) {
            $q->where('archived', false);
        }])->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($user->role === 'pm') {
            $query->where('created_by', $user->id);
        }

        $projects = $query->get();

        $filename = 'project_report_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function() use ($projects) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'Project Name',
                'Description',
                'Creator',
                'Start Date',
                'End Date',
                'Status',
                'Completion %',
                'Total Tasks',
                'Completed Tasks',
                'Pending Tasks',
                'In Progress Tasks',
                'Created At'
            ]);

            foreach ($projects as $project) {
                fputcsv($handle, [
                    $project->name,
                    $project->description,
                    $project->creator->name,
                    $project->formatted_start_date,
                    $project->formatted_end_date,
                    $project->status,
                    $project->completion_percentage . '%',
                    $project->tasks->count(),
                    $project->tasks->where('status', 'completed')->count(),
                    $project->tasks->where('status', 'pending')->count(),
                    $project->tasks->where('status', 'in_progress')->count(),
                    $project->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportTaskReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to export task reports.');
        }

        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $assignedTo = $request->get('assigned_to', 'all');

        $query = Task::with(['creator', 'siteCoordinator', 'project'])
            ->where('archived', false)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($user->role === 'pm') {
            $query->where('created_by', $user->id);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($assignedTo !== 'all') {
            $query->where('assigned_to', $assignedTo);
        }

        $tasks = $query->get();

        $filename = 'task_report_' . $dateFrom . '_to_' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function() use ($tasks) {
            $handle = fopen('php://output', 'w');
            
            fputcsv($handle, [
                'Task Name',
                'Description',
                'Project',
                'Created By',
                'Assigned To',
                'Status',
                'Priority',
                'Due Date',
                'Is Overdue',
                'Created At',
                'Updated At'
            ]);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->task_name,
                    $task->description,
                    $task->project->name,
                    $task->creator->name,
                    $task->siteCoordinator->name,
                    $task->formatted_status,
                    ucfirst($task->priority),
                    $task->formatted_due_date,
                    $task->is_overdue ? 'Yes' : 'No',
                    $task->created_at->format('Y-m-d H:i:s'),
                    $task->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function generateReport(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to generate reports.');
        }

        $reportType = $request->get('report_type', 'project');
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        switch ($reportType) {
            case 'project':
                return $this->projectReport($request);
            case 'task':
                return $this->taskReport($request);
            case 'performance':
                return $this->performanceReport($request);
            default:
                return redirect()->route('reports.index')->with('error', 'Invalid report type selected.');
        }
    }

    public function viewAvailableStaff(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $staff = User::where('role', 'sc')
                ->where('status', 'active')
                ->withCount([
                    'assignedTasks as active_tasks' => function($query) {
                        $query->where('archived', false)
                              ->where('status', '!=', 'completed');
                    },
                    'assignedTasks as overdue_tasks' => function($query) {
                        $query->where('archived', false)
                              ->where('due_date', '<', now())
                              ->where('status', '!=', 'completed');
                    }
                ])->get();
        } elseif ($user->role === 'pm') {
            $staff = User::where('role', 'sc')
                ->where('status', 'active')
                ->whereHas('assignedTasks', function($query) use ($user) {
                    $query->whereIn('project_id', function($subQuery) use ($user) {
                        $subQuery->select('id')
                                 ->from('projects')
                                 ->where('created_by', $user->id);
                    });
                })
                ->withCount([
                    'assignedTasks as active_tasks' => function($query) use ($user) {
                        $query->where('archived', false)
                              ->where('status', '!=', 'completed')
                              ->whereIn('project_id', function($subQuery) use ($user) {
                                  $subQuery->select('id')
                                           ->from('projects')
                                           ->where('created_by', $user->id);
                              });
                    },
                    'assignedTasks as overdue_tasks' => function($query) use ($user) {
                        $query->where('archived', false)
                              ->where('due_date', '<', now())
                              ->where('status', '!=', 'completed')
                              ->whereIn('project_id', function($subQuery) use ($user) {
                                  $subQuery->select('id')
                                           ->from('projects')
                                           ->where('created_by', $user->id);
                              });
                    }
                ])->get();
        } else {
            abort(403, 'Unauthorized');
        }

        $staff = $staff->map(function($user) {
            $user->workload_status = $this->getWorkloadStatus($user->active_tasks);
            return $user;
        });

        $staff = $staff->sortBy('workload_status');

        return view('reports.view-staff', compact('staff'));
    }

    public function staffWorkloadDetail($userId)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized');
        }

        $staff = User::where('role', 'sc')
            ->where('id', $userId)
            ->where('status', 'active')
            ->firstOrFail();

        $query = Task::where('assigned_to', $staff->id)
            ->where('archived', false)
            ->where('status', '!=', 'completed')
            ->with(['project']);

        if ($user->role === 'pm') {
            $query->whereIn('project_id', function($subQuery) use ($user) {
                $subQuery->select('id')
                         ->from('projects')
                         ->where('created_by', $user->id);
            });
        }

        $tasks = $query->get();

        $activeTasks = $tasks->count();
        $overdueTasks = $tasks->where('due_date', '<', now())->count();

        $workloadStatus = $this->getWorkloadStatus($activeTasks);

        return view('reports.staff-workload-detail', compact('staff', 'tasks', 'activeTasks', 'overdueTasks', 'workloadStatus'));
    }

    private function getWorkloadStatus($activeTasks)
    {
        if ($activeTasks <= 2) {
            return 'available';
        } elseif ($activeTasks <= 5) {
            return 'busy';
        } else {
            return 'overloaded';
        }
    }
}