@extends('app')

@section('content')
<div class="container">
    <h1>Task Report</h1>
    
    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.task') }}">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date_from">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date_to">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" class="form-control">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="assigned_to">Assigned To</label>
                    <select name="assigned_to" class="form-control">
                        <option value="all" {{ $assignedTo == 'all' ? 'selected' : '' }}>All</option>
                        @foreach($siteCoordinators as $sc)
                        <option value="{{ $sc->id }}" {{ $assignedTo == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
            </div>
        </div>
    </form>
    
    <!-- Summary Statistics -->
    <div class="card mt-4">
        <div class="card-body">
            <h5>Summary</h5>
            <p>Total Tasks: {{ $totalTasks }}</p>
            <p>Pending Tasks: {{ $pendingTasks }}</p>
            <p>In Progress Tasks: {{ $inProgressTasks }}</p>
            <p>Completed Tasks: {{ $completedTasks }}</p>
            <p>Overdue Tasks: {{ $overdueTasks }}</p>
            <p>Completion Rate: {{ $completionRate }}%</p>
        </div>
    </div>
    
    <!-- Tasks Table -->
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Description</th>
                <th>Project</th>
                <th>Created By</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Due Date</th>
                <th>Is Overdue</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>{{ $task->task_name }}</td>
                <td>{{ $task->description }}</td>
                <td>{{ $task->project->name }}</td>
                <td>{{ $task->creator->name }}</td>
                <td>{{ $task->siteCoordinator->name }}</td>
                <td>{{ $task->formatted_status }}</td>
                <td>{{ ucfirst($task->priority) }}</td>
                <td>{{ $task->formatted_due_date }}</td>
                <td>{{ $task->is_overdue ? 'Yes' : 'No' }}</td>
                <td>{{ $task->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $task->updated_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11">No tasks found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Export Button -->
    <a href="{{ route('reports.task.export', request()->query()) }}" class="btn btn-success">Export to CSV</a>
</div>
@endsection