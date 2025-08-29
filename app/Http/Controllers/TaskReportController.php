<?php

namespace App\Http\Controllers;

use App\Models\TaskReport;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskReportSubmitted;
use App\Notifications\TaskReportReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskReportController extends Controller
{
    /**
     * Display a listing of task reports for site coordinators
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role === 'sc') {
            // Site coordinator sees only their reports
            $query = TaskReport::where('user_id', $user->id)->with(['task.project']);
        } else {
            // Admin/PM sees all reports
            $query = TaskReport::with(['task.project', 'user', 'reviewer']);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('review_status', $request->status);
        }

        if ($request->filled('task_status')) {
            $query->where('task_status', $request->task_status);
        }

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('report_title', 'like', '%' . $request->search . '%')
                  ->orWhere('work_description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('task', function($taskQuery) use ($request) {
                      $taskQuery->where('task_name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get summary statistics
        $baseQuery = TaskReport::query();
        if ($user->role === 'sc') {
            $baseQuery->where('user_id', $user->id);
        }
        
        $stats = [
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->where('review_status', 'pending')->count(),
            'approved' => $baseQuery->where('review_status', 'approved')->count(),
            'needs_revision' => $baseQuery->where('review_status', 'needs_revision')->count(),
        ];

        return view('task-reports.index', compact('reports', 'stats'));
    }

    /**
     * Show the form for creating a new task report
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Only site coordinators can create reports
        if ($user->role !== 'sc') {
            abort(403, 'Only site coordinators can create task reports.');
        }
        
        // Get tasks assigned to the current site coordinator
        $tasksQuery = Task::where('assigned_to', $user->id)
                         ->where('archived', false)
                         ->with('project');

        // If task_id is provided, filter for that specific task
        if ($request->filled('task_id')) {
            $tasksQuery->where('id', $request->task_id);
        }

        $tasks = $tasksQuery->get();
        $selectedTaskId = $request->task_id;

        return view('task-reports.create', compact('tasks', 'selectedTaskId'));
    }

    /**
     * Store a newly created task report
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'report_title' => 'required|string|max:255',
            'report_date' => 'required|date|before_or_equal:today',
            'task_status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
            'work_description' => 'required|string|min:10',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'issues_encountered' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'materials_used' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'weather_conditions' => 'nullable|in:sunny,cloudy,rainy,stormy,windy',
            'additional_notes' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if user is assigned to the task
        $task = Task::findOrFail($request->task_id);
        if ($task->assigned_to !== auth()->id()) {
            return back()->withErrors(['task_id' => 'You can only create reports for tasks assigned to you.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle photo uploads with better path handling
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    
                    // Store in task-reports directory
                    $path = $photo->storeAs('task-reports', $filename, 'public');
                    
                    // Verify the file was stored
                    if (Storage::disk('public')->exists($path)) {
                        $photoPaths[] = $path;
                        Log::info('Photo stored successfully: ' . $path);
                    } else {
                        Log::error('Failed to store photo: ' . $path);
                    }
                }
            }

            // Create the task report
            $taskReport = TaskReport::create([
                'task_id' => $request->task_id,
                'user_id' => auth()->id(),
                'report_title' => $request->report_title,
                'report_date' => $request->report_date,
                'task_status' => $request->task_status,
                'work_description' => $request->work_description,
                'progress_percentage' => $request->progress_percentage,
                'hours_worked' => $request->hours_worked,
                'issues_encountered' => $request->issues_encountered,
                'next_steps' => $request->next_steps,
                'materials_used' => $request->materials_used,
                'equipment_used' => $request->equipment_used,
                'weather_conditions' => $request->weather_conditions,
                'additional_notes' => $request->additional_notes,
                'photos' => $photoPaths,
            ]);

            // Update task status and progress if provided
            $task->update([
                'status' => $request->task_status,
                'progress_percentage' => $request->progress_percentage,
                'updated_at' => now(),
            ]);

            // UPDATED: Send notifications to admins AND project managers (exclude clients)
            $notifiableUsers = User::where(function($query) use ($task) {
                $query->where('role', 'admin')
                      ->orWhere(function($q) use ($task) {
                          $q->where('role', 'pm')
                            ->where(function($pmQuery) use ($task) {
                                // PM who created the project
                                $pmQuery->where('id', $task->project->created_by)
                                        // OR PM who has created tasks in this project  
                                        ->orWhereHas('createdTasks', function($taskQuery) use ($task) {
                                            $taskQuery->where('project_id', $task->project_id);
                                        })
                                        // OR PM who has been assigned tasks in this project
                                        ->orWhereHas('tasks', function($taskQuery) use ($task) {
                                            $taskQuery->where('project_id', $task->project_id);
                                        });
                            });
                      });
            })->where('status', 'active')->where('role', '!=', 'client')->get();

            // Also include all PMs if no specific PM relationship found
            if ($notifiableUsers->where('role', 'pm')->isEmpty()) {
                $allPMs = User::where('role', 'pm')->where('status', 'active')->get();
                $notifiableUsers = $notifiableUsers->merge($allPMs);
            }

            foreach ($notifiableUsers as $notifiableUser) {
                try {
                    $notifiableUser->notify(new TaskReportSubmitted($taskReport));
                    Log::info('Task report notification sent', [
                        'user_id' => $notifiableUser->id,
                        'user_role' => $notifiableUser->role,
                        'task_report_id' => $taskReport->id,
                        'task_id' => $task->id,
                        'project_id' => $task->project_id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send task report notification', [
                        'user_id' => $notifiableUser->id,
                        'user_role' => $notifiableUser->role,
                        'task_report_id' => $taskReport->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sc.task-reports.show', $taskReport)
                           ->with('success', 'Task report submitted successfully! Notifications have been sent to administrators and project managers.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded photos if transaction failed
            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            Log::error('Task report creation failed', [
                'user_id' => auth()->id(),
                'task_id' => $request->task_id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create task report. Please try again.']);
        }
    }

    /**
     * Display the specified task report
     */
    public function show(TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Check authorization
        if ($user->role === 'sc' && $taskReport->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this task report.');
        }

        $taskReport->load(['task.project', 'user', 'reviewer']);
        
        return view('task-reports.show', compact('taskReport'));
    }

    /**
     * Show the form for editing the specified task report
     */
    public function edit(TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Check if user can edit this report
        if (!$taskReport->canBeEditedBy($user)) {
            return redirect()->route('sc.task-reports.show', $taskReport)
                           ->with('error', 'This report cannot be edited. Reports can only be edited if they are pending review or need revision.');
        }

        $tasks = Task::where('assigned_to', $taskReport->user_id)
                    ->where('archived', false)
                    ->with('project')
                    ->get();

        return view('task-reports.edit', compact('taskReport', 'tasks'));
    }

    /**
     * Update the specified task report
     */
    public function update(Request $request, TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Check if user can edit this report
        if (!$taskReport->canBeEditedBy($user)) {
            return redirect()->route('sc.task-reports.show', $taskReport)
                           ->with('error', 'This report cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'report_title' => 'required|string|max:255',
            'report_date' => 'required|date|before_or_equal:today',
            'task_status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
            'work_description' => 'required|string|min:10',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'issues_encountered' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'materials_used' => 'nullable|string',
            'equipment_used' => 'nullable|string',
            'weather_conditions' => 'nullable|in:sunny,cloudy,rainy,stormy,windy',
            'additional_notes' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_photos' => 'nullable|array',
            'remove_photos.*' => 'string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle photo removals
            $currentPhotos = $taskReport->photos ?? [];
            if ($request->filled('remove_photos')) {
                foreach ($request->remove_photos as $photoToRemove) {
                    if (in_array($photoToRemove, $currentPhotos)) {
                        Storage::disk('public')->delete($photoToRemove);
                        $currentPhotos = array_diff($currentPhotos, [$photoToRemove]);
                    }
                }
            }

            // Handle new photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('task-reports', $filename, 'public');
                    $currentPhotos[] = $path;
                }
            }

            // Update the task report
            $taskReport->update([
                'task_id' => $request->task_id,
                'report_title' => $request->report_title,
                'report_date' => $request->report_date,
                'task_status' => $request->task_status,
                'work_description' => $request->work_description,
                'progress_percentage' => $request->progress_percentage,
                'hours_worked' => $request->hours_worked,
                'issues_encountered' => $request->issues_encountered,
                'next_steps' => $request->next_steps,
                'materials_used' => $request->materials_used,
                'equipment_used' => $request->equipment_used,
                'weather_conditions' => $request->weather_conditions,
                'additional_notes' => $request->additional_notes,
                'photos' => array_values($currentPhotos),
                'review_status' => 'pending', // Reset to pending when edited
            ]);

            // Update related task
            $task = Task::find($request->task_id);
            $task->update([
                'status' => $request->task_status,
                'progress_percentage' => $request->progress_percentage,
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('sc.task-reports.show', $taskReport)
                           ->with('success', 'Task report updated successfully! The review status has been reset to pending.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Task report update failed', [
                'task_report_id' => $taskReport->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update task report. Please try again.']);
        }
    }

    /**
     * UPDATED: Admin/PM listing of all task reports with PM project filtering
     */
    public function adminIndex(Request $request)
    {
        $user = auth()->user();
        
        // Ensure user has admin or pm role
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = TaskReport::with(['task.project', 'user', 'reviewer']);

        // NEW: For PMs, filter to only show reports from their projects
        if ($user->role === 'pm') {
            $query->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('review_status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('task', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('report_title', 'like', '%' . $request->search . '%')
                  ->orWhere('work_description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('task', function($taskQuery) use ($request) {
                      $taskQuery->where('task_name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('first_name', 'like', '%' . $request->search . '%')
                               ->orWhere('last_name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // NEW: Get PM-specific or admin statistics
        $statsQuery = TaskReport::query();
        if ($user->role === 'pm') {
            $statsQuery->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }
        
        $stats = [
            'total' => $statsQuery->count(),
            'pending' => $statsQuery->where('review_status', 'pending')->count(),
            'approved' => $statsQuery->where('review_status', 'approved')->count(),
            'needs_revision' => $statsQuery->where('review_status', 'needs_revision')->count(),
            'overdue_reviews' => $statsQuery->where('review_status', 'pending')
                                           ->where('created_at', '<', now()->subDays(2))
                                           ->count(),
        ];

        // Get site coordinators for filter (limited to PM's projects if applicable)
        $siteCoordinatorsQuery = User::where('role', 'sc')->where('status', 'active');
        if ($user->role === 'pm') {
            $siteCoordinatorsQuery->whereHas('tasks', function($q) use ($user) {
                $q->whereHas('project', function($projectQuery) use ($user) {
                    $projectQuery->where('created_by', $user->id);
                });
            });
        }
        $siteCoordinators = $siteCoordinatorsQuery->orderBy('first_name')->get();

        // NEW: Get projects for filter (PM-specific if applicable)
        $projectsQuery = \App\Models\Project::query();
        if ($user->role === 'pm') {
            $projectsQuery->where('created_by', $user->id)
                         ->orWhereHas('tasks', function($q) use ($user) {
                             $q->where('created_by', $user->id);
                         });
        }
        $projects = $projectsQuery->orderBy('name')->get();

        return view('admin.task-reports.index', compact('reports', 'stats', 'siteCoordinators', 'projects'));
    }

    /**
     * Admin/PM view of specific task report with PM authorization
     */
    public function adminShow(TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Ensure user has admin or pm role
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized access.');
        }

        // NEW: For PMs, check if they have access to this report's project
        if ($user->role === 'pm') {
            $hasAccess = $taskReport->task->project->created_by === $user->id ||
                        $taskReport->task->project->tasks()->where('created_by', $user->id)->exists();
            
            if (!$hasAccess) {
                abort(403, 'You can only view task reports from your projects.');
            }
        }

        $taskReport->load(['task.project', 'user', 'reviewer']);
        return view('admin.task-reports.show', compact('taskReport'));
    }

    /**
     * Update review status of task report (available to both Admin and PM)
     */
    public function updateReview(Request $request, TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Ensure user has admin or pm role
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized access.');
        }

        // NEW: For PMs, check if they have access to this report's project
        if ($user->role === 'pm') {
            $hasAccess = $taskReport->task->project->created_by === $user->id ||
                        $taskReport->task->project->tasks()->where('created_by', $user->id)->exists();
            
            if (!$hasAccess) {
                abort(403, 'You can only review task reports from your projects.');
            }
        }

        $validator = Validator::make($request->all(), [
            'review_status' => 'required|in:reviewed,needs_revision,approved',
            'admin_comments' => 'nullable|string|max:1000',
            'admin_rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            $taskReport->update([
                'review_status' => $request->review_status,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_comments' => $request->admin_comments,
                'admin_rating' => $request->admin_rating,
            ]);

            // Send notification to the site coordinator (exclude clients)
            if ($taskReport->user && $taskReport->user->role !== 'client') {
                try {
                    $taskReport->user->notify(new TaskReportReviewed($taskReport));
                    Log::info('Task report review notification sent', [
                        'reviewer_id' => $user->id,
                        'reviewer_role' => $user->role,
                        'task_report_id' => $taskReport->id,
                        'review_status' => $request->review_status
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send task report review notification', [
                        'task_report_id' => $taskReport->id,
                        'user_id' => $taskReport->user_id,
                        'reviewer_id' => $user->id,
                        'reviewer_role' => $user->role,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $statusMessage = match($request->review_status) {
                'approved' => 'approved',
                'needs_revision' => 'marked as needing revision',
                'reviewed' => 'reviewed',
            };

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => "Task report has been {$statusMessage} successfully!"
                ]);
            }

            return redirect()->route('admin.task-reports.show', $taskReport)
                           ->with('success', "Task report has been {$statusMessage} successfully!");

        } catch (\Exception $e) {
            Log::error('Task report review failed', [
                'task_report_id' => $taskReport->id,
                'reviewer_id' => auth()->id(),
                'reviewer_role' => $user->role,
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to update review status. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to update review status. Please try again.');
        }
    }

    /**
     * NEW: PM-specific dashboard method
     */
    public function pmDashboard()
    {
        $user = auth()->user();
        
        if ($user->role !== 'pm') {
            abort(403, 'Unauthorized access.');
        }
        
        // Get PM's project-related task reports
        $pmReports = TaskReport::whereHas('task.project', function($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('tasks', function($taskQuery) use ($user) {
                  $taskQuery->where('created_by', $user->id);
              });
        });
        
        $stats = [
            'total_reports' => $pmReports->count(),
            'pending_review' => $pmReports->where('review_status', 'pending')->count(),
            'approved_reports' => $pmReports->where('review_status', 'approved')->count(),
            'needs_revision' => $pmReports->where('review_status', 'needs_revision')->count(),
            'overdue_reviews' => $pmReports->where('review_status', 'pending')
                                          ->where('created_at', '<', now()->subDays(2))
                                          ->count(),
        ];

        $recentReports = $pmReports->with(['task.project', 'user'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get();

        return compact('stats', 'recentReports');
    }

    /**
     * Delete a task report (updated for PM access)
     */
    public function destroy(TaskReport $taskReport)
    {
        $user = auth()->user();
        
        // Allow deletion by the creator if not yet reviewed, admin, or PM with project access
        $canDelete = false;
        
        if ($user->role === 'admin') {
            $canDelete = true;
        } elseif ($user->role === 'pm') {
            // PM can delete if they have access to the project
            $canDelete = $taskReport->task->project->created_by === $user->id ||
                        $taskReport->task->project->tasks()->where('created_by', $user->id)->exists();
        } elseif ($taskReport->user_id === $user->id && in_array($taskReport->review_status, ['pending', 'needs_revision'])) {
            $canDelete = true;
        }
        
        if (!$canDelete) {
            return redirect()->route('sc.task-reports.index')
                           ->with('error', 'You cannot delete this task report.');
        }

        try {
            // Delete associated photos
            if ($taskReport->photos) {
                foreach ($taskReport->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            $taskReport->delete();

            $redirectRoute = match($user->role) {
                'admin', 'pm' => 'admin.task-reports.index',
                default => 'sc.task-reports.index'
            };

            return redirect()->route($redirectRoute)
                           ->with('success', 'Task report deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Task report deletion failed', [
                'task_report_id' => $taskReport->id,
                'user_id' => auth()->id(),
                'user_role' => $user->role,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete task report. Please try again.');
        }
    }

    /**
     * Dashboard statistics for site coordinators
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if ($user->role !== 'sc') {
            return [
                'stats' => [],
                'recentReports' => collect(),
                'tasksNeedingReports' => collect()
            ];
        }
        
        $stats = [
            'total_reports' => TaskReport::where('user_id', $user->id)->count(),
            'pending_review' => TaskReport::where('user_id', $user->id)->where('review_status', 'pending')->count(),
            'approved_reports' => TaskReport::where('user_id', $user->id)->where('review_status', 'approved')->count(),
            'needs_revision' => TaskReport::where('user_id', $user->id)->where('review_status', 'needs_revision')->count(),
            'average_rating' => round(TaskReport::where('user_id', $user->id)->whereNotNull('admin_rating')->avg('admin_rating') ?? 0, 1),
            'total_hours' => TaskReport::where('user_id', $user->id)->sum('hours_worked') ?? 0,
        ];

        $recentReports = TaskReport::where('user_id', $user->id)
                                  ->with(['task.project'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get();

        $tasksNeedingReports = Task::where('assigned_to', $user->id)
                                  ->where('status', 'in_progress')
                                  ->where('archived', false)
                                  ->whereDoesntHave('taskReports', function($query) {
                                      $query->where('report_date', '>=', now()->subDays(7));
                                  })
                                  ->with('project')
                                  ->orderBy('due_date', 'asc')
                                  ->limit(10)
                                  ->get();

        return compact('stats', 'recentReports', 'tasksNeedingReports');
    }

    /**
     * Export task reports to CSV (updated for PM filtering)
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        
        // Ensure user has admin or pm role
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = TaskReport::with(['task.project', 'user', 'reviewer']);

        // NEW: For PMs, filter to only their project reports
        if ($user->role === 'pm') {
            $query->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }

        // Apply filters if provided
        if ($request->filled('status')) {
            $query->where('review_status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('task', function($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        $filename = 'task-reports-' . $user->role . '-' . date('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID', 'Report Title', 'Task', 'Project', 'Submitted By', 'Report Date',
                'Task Status', 'Progress %', 'Hours Worked', 'Weather', 'Review Status',
                'Reviewed By', 'Reviewed At', 'Admin Rating', 'Has Issues', 'Has Photos', 'Created At'
            ]);

            // Add data rows
            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->id,
                    $report->report_title,
                    $report->task->task_name,
                    $report->task->project->name,
                    $report->user->full_name,
                    $report->report_date->format('Y-m-d'),
                    $report->formatted_task_status,
                    $report->progress_percentage,
                    $report->hours_worked ?? 0,
                    $report->weather_conditions ?? 'N/A',
                    $report->formatted_review_status,
                    $report->reviewer ? $report->reviewer->full_name : 'N/A',
                    $report->reviewed_at ? $report->reviewed_at->format('Y-m-d H:i:s') : 'N/A',
                    $report->admin_rating ?? 'N/A',
                    $report->issues_encountered ? 'Yes' : 'No',
                    $report->photos && count($report->photos) > 0 ? 'Yes' : 'No',
                    $report->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get task reports for a specific task (updated for PM access)
     */
    public function taskReports(Task $task)
    {
        $user = auth()->user();
        
        // Check permissions
        if ($user->role === 'sc' && $task->assigned_to !== $user->id) {
            abort(403, 'You can only view reports for tasks assigned to you.');
        }
        
        if ($user->role === 'pm') {
            // PM can only view reports for tasks in their projects
            $hasAccess = $task->project->created_by === $user->id ||
                        $task->project->tasks()->where('created_by', $user->id)->exists();
            
            if (!$hasAccess) {
                abort(403, 'You can only view reports for tasks in your projects.');
            }
        }
        
        if (!in_array($user->role, ['admin', 'pm', 'sc'])) {
            abort(403, 'Unauthorized access.');
        }
        
        $reports = $task->taskReports()
                       ->with(['user', 'reviewer'])
                       ->orderBy('report_date', 'desc')
                       ->paginate(10);
        
        return view('tasks.reports', compact('task', 'reports'));
    }

    /**
     * Create report from task page
     */
    public function createFromTask(Task $task)
    {
        $user = auth()->user();
        
        // Only assigned site coordinator can create reports
        if ($user->role !== 'sc' || $task->assigned_to !== $user->id) {
            abort(403, 'Only the assigned site coordinator can create reports for this task.');
        }
        
        return redirect()->route('sc.task-reports.create', ['task_id' => $task->id]);
    }

    /**
     * Get notifications for task reports
     */
    public function notifications(Request $request)
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
                             ->where('type', 'like', '%TaskReport%')
                             ->orderBy('created_at', 'desc')
                             ->paginate(20);
        
        return view('notifications.task-reports', compact('notifications'));
    }

    /**
     * Mark task report notification as read
     */
    public function markTaskReportNotificationRead($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get task report statistics API endpoint (updated for PM access)
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->role === 'sc') {
            $stats = [
                'total' => TaskReport::where('user_id', $user->id)->count(),
                'pending' => TaskReport::where('user_id', $user->id)->where('review_status', 'pending')->count(),
                'approved' => TaskReport::where('user_id', $user->id)->where('review_status', 'approved')->count(),
                'needs_revision' => TaskReport::where('user_id', $user->id)->where('review_status', 'needs_revision')->count(),
                'average_rating' => round(TaskReport::where('user_id', $user->id)->whereNotNull('admin_rating')->avg('admin_rating') ?? 0, 1),
            ];
        } elseif ($user->role === 'pm') {
            // NEW: PM-specific stats for their projects
            $pmQuery = TaskReport::whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
            
            $stats = [
                'total' => $pmQuery->count(),
                'pending' => $pmQuery->where('review_status', 'pending')->count(),
                'approved' => $pmQuery->where('review_status', 'approved')->count(),
                'needs_revision' => $pmQuery->where('review_status', 'needs_revision')->count(),
                'overdue_reviews' => $pmQuery->where('review_status', 'pending')
                                           ->where('created_at', '<', now()->subDays(2))
                                           ->count(),
            ];
        } else {
            // Admin stats
            $stats = [
                'total' => TaskReport::count(),
                'pending' => TaskReport::where('review_status', 'pending')->count(),
                'approved' => TaskReport::where('review_status', 'approved')->count(),
                'needs_revision' => TaskReport::where('review_status', 'needs_revision')->count(),
                'overdue_reviews' => TaskReport::where('review_status', 'pending')
                                               ->where('created_at', '<', now()->subDays(2))
                                               ->count(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * Bulk approve reports (admin and PM)
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Ensure user has admin or pm role
        if (!in_array($user->role, ['admin', 'pm'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:task_reports,id',
            'admin_comments' => 'nullable|string|max:1000',
            'admin_rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $query = TaskReport::whereIn('id', $request->report_ids)
                              ->where('review_status', 'pending');

            // NEW: For PMs, filter to only their project reports
            if ($user->role === 'pm') {
                $query->whereHas('task.project', function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('tasks', function($taskQuery) use ($user) {
                          $taskQuery->where('created_by', $user->id);
                      });
                });
            }

            $reports = $query->get();

            if ($reports->isEmpty()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No pending reports found to approve or you do not have permission to approve these reports.'
                ], 400);
            }

            foreach ($reports as $report) {
                $report->update([
                    'review_status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'admin_comments' => $request->admin_comments,
                    'admin_rating' => $request->admin_rating,
                ]);

                // Send notification to the site coordinator (exclude clients)
                if ($report->user && $report->user->role !== 'client') {
                    try {
                        $report->user->notify(new TaskReportReviewed($report));
                    } catch (\Exception $e) {
                        Log::error('Failed to send bulk approval notification', [
                            'task_report_id' => $report->id,
                            'user_id' => $report->user_id,
                            'reviewer_id' => $user->id,
                            'reviewer_role' => $user->role,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => count($reports) . ' reports approved successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Bulk approve failed', [
                'report_ids' => $request->report_ids,
                'reviewer_id' => $user->id,
                'reviewer_role' => $user->role,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Failed to approve reports. Please try again.'
            ], 500);
        }
    }

    /**
     * Get recent reports for dashboard widget (updated for PM access)
     */
    public function getRecentReports(Request $request): JsonResponse
    {
        $user = auth()->user();
        $limit = $request->get('limit', 5);

        if ($user->role === 'sc') {
            $reports = TaskReport::where('user_id', $user->id)
                                ->with(['task.project'])
                                ->orderBy('created_at', 'desc')
                                ->limit($limit)
                                ->get();
        } elseif ($user->role === 'pm') {
            // NEW: PM gets reports from their projects
            $reports = TaskReport::whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            })
            ->with(['task.project', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        } else {
            $reports = TaskReport::with(['task.project', 'user'])
                                ->orderBy('created_at', 'desc')
                                ->limit($limit)
                                ->get();
        }

        return response()->json($reports);
    }

    /**
     * Get overdue reports (admin/pm only) - updated for PM filtering
     */
    public function getOverdueReports(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $days = $request->get('days', 2); // Default 2 days overdue

        $query = TaskReport::with(['task.project', 'user'])
                          ->where('review_status', 'pending')
                          ->where('created_at', '<', now()->subDays($days));

        // NEW: For PMs, filter to only their project reports
        if ($user->role === 'pm') {
            $query->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }

        $reports = $query->orderBy('created_at', 'asc')->get();

        return response()->json($reports);
    }

    /**
     * Search reports with advanced filters (updated for PM access)
     */
    public function search(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = TaskReport::with(['task.project', 'user', 'reviewer']);

        // Role-based filtering
        if ($user->role === 'sc') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'pm') {
            // NEW: PM filtering
            $query->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }

        // Search filters
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('report_title', 'like', "%{$searchTerm}%")
                  ->orWhere('work_description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('task', function($taskQuery) use ($searchTerm) {
                      $taskQuery->where('task_name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('task.project', function($projectQuery) use ($searchTerm) {
                      $projectQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('review_status', $request->status);
        }

        if ($request->filled('task_status')) {
            $query->where('task_status', $request->task_status);
        }

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        if ($request->filled('has_issues')) {
            if ($request->has_issues == 'true') {
                $query->whereNotNull('issues_encountered');
            } else {
                $query->whereNull('issues_encountered');
            }
        }

        if ($request->filled('progress_min')) {
            $query->where('progress_percentage', '>=', $request->progress_min);
        }

        if ($request->filled('progress_max')) {
            $query->where('progress_percentage', '<=', $request->progress_max);
        }

        $reports = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json($reports);
    }

    /**
     * Get summary report for admin/PM dashboard (updated for PM filtering)
     */
    public function getSummaryReport(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = TaskReport::whereBetween('report_date', [$dateFrom, $dateTo]);

        // NEW: For PMs, filter to only their project reports
        if ($user->role === 'pm') {
            $query->whereHas('task.project', function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('tasks', function($taskQuery) use ($user) {
                      $taskQuery->where('created_by', $user->id);
                  });
            });
        }

        $summary = [
            'total_reports' => $query->count(),
            'reports_by_status' => $query->selectRaw('review_status, COUNT(*) as count')
                                        ->groupBy('review_status')
                                        ->pluck('count', 'review_status'),
            'reports_by_user' => $query->with('user:id,first_name,last_name')
                                      ->selectRaw('user_id, COUNT(*) as count')
                                      ->groupBy('user_id')
                                      ->get()
                                      ->map(function($item) {
                                          return [
                                              'user_name' => $item->user->full_name,
                                              'count' => $item->count
                                          ];
                                      }),
            'average_progress' => round($query->avg('progress_percentage'), 1),
            'total_hours' => $query->sum('hours_worked'),
            'average_rating' => round($query->whereNotNull('admin_rating')->avg('admin_rating'), 1),
        ];

        return response()->json($summary);
    }

    /**
     * Quick status update for mobile/ajax (updated for PM access)
     */
    public function quickStatusUpdate(Request $request, TaskReport $taskReport): JsonResponse
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // NEW: For PMs, check project access
        if ($user->role === 'pm') {
            $hasAccess = $taskReport->task->project->created_by === $user->id ||
                        $taskReport->task->project->tasks()->where('created_by', $user->id)->exists();
            
            if (!$hasAccess) {
                return response()->json(['error' => 'You can only review reports from your projects'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:reviewed,needs_revision,approved',
            'comments' => 'nullable|string|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $taskReport->update([
                'review_status' => $request->status,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_comments' => $request->comments,
                'admin_rating' => $request->rating,
            ]);

            // Send notification (exclude clients)
            if ($taskReport->user && $taskReport->user->role !== 'client') {
                $taskReport->user->notify(new TaskReportReviewed($taskReport));
            }

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
                'report' => $taskReport->fresh(['user', 'reviewer'])
            ]);

        } catch (\Exception $e) {
            Log::error('Quick status update failed', [
                'task_report_id' => $taskReport->id,
                'reviewer_id' => $user->id,
                'reviewer_role' => $user->role,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Validate report data before submission
     */
    public function validateReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'report_title' => 'required|string|max:255',
            'report_date' => 'required|date|before_or_equal:today',
            'task_status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
            'work_description' => 'required|string|min:10',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Check if user is assigned to the task
        $task = Task::find($request->task_id);
        if ($task && $task->assigned_to !== auth()->id()) {
            return response()->json([
                'valid' => false,
                'errors' => ['task_id' => ['You can only create reports for tasks assigned to you.']]
            ]);
        }

        return response()->json(['valid' => true]);
    }
}