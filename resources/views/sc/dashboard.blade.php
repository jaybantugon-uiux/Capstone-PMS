@extends('app')

@section('content')
    <div class="container">
        <h1>Site Coordinator Dashboard</h1>
        
        <div class="mb-3">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-info">
                Notifications <span class="badge badge-light">{{ auth()->user()->unreadNotifications()->count() }}</span>
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Tasks</h5>
                        <p class="card-text">{{ $totalTasks }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Tasks</h5>
                        <p class="card-text">{{ $pendingTasks }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">In Progress Tasks</h5>
                        <p class="card-text">{{ $inProgressTasks }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Completed Tasks</h5>
                        <p class="card-text">{{ $completedTasks }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <h2 class="mt-4">Overdue Tasks</h2>
        @php
            $overdueTasks = App\Models\Task::where('assigned_to', auth()->id())
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->where('archived', false)
                ->take(5)
                ->get();
        @endphp
        @if($overdueTasks->count() > 0)
            <ul class="list-group">
                @foreach($overdueTasks as $task)
                    <li class="list-group-item">
                        <a href="{{ route('tasks.show', $task) }}">{{ $task->task_name }}</a> - Due: {{ $task->formatted_due_date }}
                    </li>
                @endforeach
            </ul>
        @else
            <p>No overdue tasks.</p>
        @endif
        
        <h2 class="mt-4">Projects</h2>
        @if($projects->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Tasks Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                            <td>{{ $project->tasks_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No projects found.</p>
        @endif
        
        <h2 class="mt-4">Recent Tasks</h2>
        @if($tasks->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td><a href="{{ route('tasks.show', $task) }}">{{ $task->task_name }}</a></td>
                            <td>{{ $task->project->name }}</td>
                            <td><span class="badge badge-{{ $task->status_badge_color }}">{{ $task->formatted_status }}</span></td>
                            <td>{{ $task->formatted_due_date ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $tasks->links() }}
        @else
            <p>No tasks assigned.</p>
        @endif
    </div>
@endsection