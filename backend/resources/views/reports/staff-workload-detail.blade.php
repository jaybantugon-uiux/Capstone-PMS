@extends('app')

@section('content')
<div class="container">
    <h1>Staff Workload Details</h1>
    <h2>{{ $staff->full_name }}</h2>
    <p>Workload Status: 
        <span class="badge bg-{{ $workloadStatus == 'available' ? 'success' : ($workloadStatus == 'busy' ? 'warning' : 'danger') }}">
            {{ ucfirst($workloadStatus) }}
        </span>
    </p>
    <p>Active Tasks: {{ $activeTasks }}</p>
    <p>Overdue Tasks: {{ $overdueTasks }}</p>

    <h3>Active Tasks</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Project</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Priority</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->task_name }}</td>
                    <td>{{ $task->project->name }}</td>
                    <td>{{ $task->formatted_due_date }}</td>
                    <td>{{ $task->formatted_status }}</td>
                    <td>{{ ucfirst($task->priority) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('reports.view-staff') }}" class="btn btn-secondary">Back to View Available Staff</a>
</div>
@endsection