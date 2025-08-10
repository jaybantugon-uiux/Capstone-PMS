<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Notifications\TaskCreatedNotification;
use App\Notifications\TaskStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $activeTasks = Task::where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->paginate(10);

            $archivedTasks = Task::where('archived', true)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } elseif ($user->role === 'pm') {
            $activeTasks = Task::where('created_by', $user->id)
                ->where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->paginate(10);

            $archivedTasks = Task::where('created_by', $user->id)
                ->where('archived', true)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } elseif ($user->role === 'sc') {
            $activeTasks = Task::where('assigned_to', $user->id)
                ->where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->paginate(10);

            $archivedTasks = Task::where('assigned_to', $user->id)
                ->where('archived', true)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } else {
            abort(403, 'Unauthorized access.');
        }

        return response()->json([
            'success' => true,
            'active_tasks' => $activeTasks,
            'archived_tasks' => $archivedTasks,
        ]);
    }


    public function create(Request $request)
    {
        // Only admin and pm can create tasks
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to create tasks.');
        }

        $projects = Project::where('archived', false)->get();
        // Fixed: Remove archived filter from users table since it doesn't exist there
        $siteCoordinators = User::where('role', 'sc')
            ->where('status', 'active') // Use status instead of archived
            ->get();
        $selectedProject = null;

        if ($request->has('project_id')) {
            $selectedProject = Project::find($request->project_id);
        }

        return view('tasks.create', compact('projects', 'siteCoordinators', 'selectedProject'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        if ($user->role !== 'sc') {
            abort(403, 'Unauthorized access to dashboard.');
        }

        $tasks = Task::where('assigned_to', $user->id)
                     ->where('archived', false)
                     ->with('project')
                     ->latest()
                     ->paginate(10);

        $projects = Project::whereHas('tasks', function($query) use ($user) {
            $query->where('assigned_to', $user->id)->where('archived', false);
        })->withCount(['tasks' => function($query) use ($user) {
            $query->where('assigned_to', $user->id)->where('archived', false);
        }])->get();

        $totalTasks = Task::where('assigned_to', $user->id)->where('archived', false)->count();
        $pendingTasks = Task::where('assigned_to', $user->id)->where('archived', false)->where('status', 'pending')->count();
        $inProgressTasks = Task::where('assigned_to', $user->id)->where('archived', false)->where('status', 'in_progress')->count();
        $completedTasks = Task::where('assigned_to', $user->id)->where('archived', false)->where('status', 'completed')->count();

        return view('sc.dashboard', compact('tasks', 'projects', 'totalTasks', 'pendingTasks', 'inProgressTasks', 'completedTasks'));
    }

    public function store(Request $request)
    {
        // Only admin and pm can create tasks
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to create tasks.');
        }

        $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date|after:today',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        // Fixed: Verify the assigned user is a site coordinator and is active
        $assignedUser = User::findOrFail($request->assigned_to);
        if ($assignedUser->role !== 'sc' || $assignedUser->status !== 'active') {
            return redirect()->back()->withErrors(['assigned_to' => 'Tasks can only be assigned to active site coordinators.']);
        }

        // Verify the project is not archived
        $project = Project::findOrFail($request->project_id);
        if ($project->archived) {
            return redirect()->back()->withErrors(['project_id' => 'Cannot create tasks for archived projects.']);
        }

        $task = Task::create([
            'task_name' => $request->task_name,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'project_id' => $request->project_id,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'archived' => false,
        ]);

        // Load relationships and send notification
        $task->load(['project', 'siteCoordinator']);
        $siteCoordinator = $task->siteCoordinator;
        
        if ($siteCoordinator) {
            try {
                $siteCoordinator->notify(new TaskCreatedNotification($task));
            } catch (\Exception $e) {
                // Log the error but don't fail the task creation
                Log::error('Failed to send task notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('tasks.show', $task->id)->with('success', 'Task created successfully and site coordinator has been notified.');
    }

    public function show(Task $task)
    {
        $user = Auth::user();
        
        // Check if user can view this task
        if (!in_array($user->role, ['admin', 'pm', 'sc'])) {
            abort(403, 'Unauthorized to view this task.');
        }
        
        // PM can only see tasks they created
        if ($user->role === 'pm' && $task->created_by !== $user->id) {
            abort(403, 'You can only view tasks you created.');
        }
        
        // SC can only see tasks assigned to them
        if ($user->role === 'sc' && $task->assigned_to !== $user->id) {
            abort(403, 'You can only view tasks assigned to you.');
        }

        // Mark task notification as read if it's a site coordinator viewing the task
        if ($user->role === 'sc' && $task->assigned_to === $user->id) {
            $notification = $user->unreadNotifications
                ->where('type', TaskCreatedNotification::class)
                ->whereJsonContains('data->task_id', $task->id)
                ->first();
                
            if ($notification) {
                $notification->markAsRead();
            }
        }

        // Load relationships
        $task->load(['creator', 'siteCoordinator', 'project']);

        return view('tasks.show', compact('task'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // Only site coordinators can update status of tasks assigned to them
        if ($user->role !== 'sc' || $task->assigned_to !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to update task status.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        // Notify the task creator about status change if status actually changed
        if ($task->creator && $oldStatus !== $request->status) {
            try {
                $task->creator->notify(new TaskStatusUpdatedNotification($task, $oldStatus, $request->status, $user));
            } catch (\Exception $e) {
                Log::error('Failed to send status update notification: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true, 
            'message' => 'Status updated successfully',
            'new_status' => $request->status,
            'formatted_status' => ucfirst(str_replace('_', ' ', $request->status))
        ]);
    }

    public function archive(Task $task)
    {
        $user = Auth::user();
        
        // Only admin and task creator can archive
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to archive tasks.');
        }
        
        // PM can only archive their own tasks
        if ($user->role === 'pm' && $task->created_by !== $user->id) {
            abort(403, 'You can only archive tasks you created.');
        }
        
        $task->update(['archived' => true]);
        return redirect()->back()->with('success', 'Task archived successfully.');
    }

    public function restore(Task $task)
    {
        $user = Auth::user();
        
        // Only admin and task creator can restore
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to restore tasks.');
        }
        
        // PM can only restore their own tasks
        if ($user->role === 'pm' && $task->created_by !== $user->id) {
            abort(403, 'You can only restore tasks you created.');
        }
        
        $task->update(['archived' => false]);
        return redirect()->back()->with('success', 'Task restored successfully.');
    }

    public function edit(Task $task)
    {
        $user = Auth::user();
        
        // Only admin and task creator can edit
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to edit tasks.');
        }
        
        // PM can only edit their own tasks
        if ($user->role === 'pm' && $task->created_by !== $user->id) {
            abort(403, 'You can only edit tasks you created.');
        }

        $projects = Project::where('archived', false)->get();
        // Fixed: Remove archived filter from users table since it doesn't exist there
        $siteCoordinators = User::where('role', 'sc')
            ->where('status', 'active') // Use status instead of archived
            ->get();
        
        return view('tasks.edit', compact('task', 'projects', 'siteCoordinators'));
    }

    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        
        // Only admin and task creator can update
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'Unauthorized to update tasks.');
        }
        
        // PM can only update their own tasks
        if ($user->role === 'pm' && $task->created_by !== $user->id) {
            abort(403, 'You can only update tasks you created.');
        }

        $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        // Fixed: Verify the assigned user is a site coordinator and is active
        $assignedUser = User::findOrFail($request->assigned_to);
        if ($assignedUser->role !== 'sc' || $assignedUser->status !== 'active') {
            return redirect()->back()->withErrors(['assigned_to' => 'Tasks can only be assigned to active site coordinators.']);
        }

        // Verify the project is not archived
        $project = Project::findOrFail($request->project_id);
        if ($project->archived) {
            return redirect()->back()->withErrors(['project_id' => 'Cannot assign tasks to archived projects.']);
        }

        $oldAssignedTo = $task->assigned_to;
        $task->update([
            'task_name' => $request->task_name,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'project_id' => $request->project_id,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        // If assigned to different site coordinator, send notification
        if ($oldAssignedTo != $request->assigned_to) {
            $task->load(['project', 'siteCoordinator']);
            $siteCoordinator = $task->siteCoordinator;
            if ($siteCoordinator) {
                try {
                    $siteCoordinator->notify(new TaskCreatedNotification($task));
                } catch (\Exception $e) {
                    Log::error('Failed to send task reassignment notification: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('tasks.show', $task->id)->with('success', 'Task updated successfully.');
    }

    public function active()
    {
        $tasks = Task::where('archived', false)->with(['project', 'siteCoordinator'])->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }
    
    public function archived()
    {
        $user = Auth::user();

        // Only admin and pm can see archived tasks
        if (!in_array($user->role, ['admin', 'pm'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view archived tasks.'
            ], 403);
        }

        // Fetch archived tasks based on role
        $query = Task::where('archived', true)
            ->with(['creator', 'siteCoordinator', 'project'])
            ->latest();

        if ($user->role !== 'admin') {
            $query->where('created_by', $user->id);
        }

        $tasks = $query->get(); // Remove pagination for frontend use

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function unarchive($id)
    {
        $user = Auth::user();
        $task = Task::findOrFail($id);

        // Only admin or the creator can unarchive
        if ($user->role !== 'admin' && $task->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to unarchive this task.'
            ], 403);
        }

        $task->archived = false;
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Task unarchived successfully.',
            'task' => $task,
        ]);
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markNotificationAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function deleteNotification($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
        }
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }
    public function calendar(Request $request)
{
    // Get the year and month from the request, default to current month
    $year = $request->input('year', Carbon::now()->year);
    $month = $request->input('month', Carbon::now()->month);

    // Create a Carbon instance for the first day of the month
    $date = Carbon::create($year, $month, 1);
    $startOfMonth = $date->copy()->startOfMonth();
    $endOfMonth = $date->copy()->endOfMonth();

    // Fetch tasks due within the month, excluding archived tasks
    $tasks = Task::whereBetween('due_date', [$startOfMonth, $endOfMonth])
        ->where('archived', false)
        ->get()
        ->groupBy(function ($task) {
            return $task->due_date->day;
        });

    // Build the calendar data
    $calendar = [];
    $currentDate = $startOfMonth->copy();
    while ($currentDate->lte($endOfMonth)) {
        $day = $currentDate->day;
        $calendar[$day] = $tasks->get($day, []);
        $currentDate->addDay();
    }

    // Calculate navigation dates
    $prevMonth = $date->copy()->subMonth();
    $nextMonth = $date->copy()->addMonth();

    // Get the first day of the week (0 = Sunday, 1 = Monday, etc.)
    $firstDayOfWeek = $startOfMonth->dayOfWeek;

    // Get today's date for highlighting
    $today = Carbon::now();

    // Return the view with all necessary data
    return view('tasks.calendar', compact(
        'calendar',
        'year',
        'month',
        'firstDayOfWeek',
        'prevMonth',
        'nextMonth',
        'date',
        'today'
    ));
}

    private function getStatusColor($status)
    {
        switch ($status) {
            case 'completed':
                return '#28a745';
            case 'in_progress':
                return '#007bff';
            case 'pending':
                return '#ffc107';
            case 'on_hold':
                return '#fd7e14';
            case 'cancelled':
                return '#dc3545';
            default:
                return '#6c757d';
        }
    }

    // API Methods
    public function apiIndex()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $tasks = Task::where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } elseif ($user->role === 'pm') {
            $tasks = Task::where('created_by', $user->id)
                ->where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } elseif ($user->role === 'sc') {
            $tasks = Task::where('assigned_to', $user->id)
                ->where('archived', false)
                ->with(['creator', 'siteCoordinator', 'project'])
                ->latest()
                ->get();
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function apiStore(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:Pending,In Progress,Completed,On Hold,Cancelled',
            'due_date' => 'nullable|date',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $task = Task::create([
            'task_name' => $validated['task_name'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'],
            'project_id' => $validated['project_id'],
            'status' => strtolower(str_replace(' ', '_', $validated['status'])),
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => $validated['created_by'] ?? Auth::id(),
            'archived' => false,
        ]);

        // Load relationships for response
        $task->load(['creator', 'siteCoordinator', 'project']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully.',
            'task' => $task,
        ]);
    }

    public function apiUpdate(Request $request, Task $task)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:Pending,In Progress,Completed,On Hold,Cancelled',
            'due_date' => 'nullable|date',
        ]);

        $task->update([
            'task_name' => $validated['task_name'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'],
            'project_id' => $validated['project_id'],
            'status' => strtolower(str_replace(' ', '_', $validated['status'])),
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Load relationships for response
        $task->load(['creator', 'siteCoordinator', 'project']);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully.',
            'task' => $task,
        ]);
    }

    public function apiArchive(Task $task)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update(['archived' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task archived successfully.',
        ]);
    }

    public function apiUnarchive(Task $task)
    {
        if (!in_array(Auth::user()->role, ['admin', 'pm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$task->archived) {
            return response()->json(['status' => 'info', 'message' => 'Task is already active.']);
        }

        $task->update(['archived' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task unarchived successfully.',
            'task' => $task
        ]);
    }
}