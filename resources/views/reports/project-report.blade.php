@extends('app')

@section('content')
<div class="container">
    <h1>Project Report</h1>
    
    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.project') }}">
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
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
            </div>
        </div>
    </form>
    
    <!-- Summary Statistics -->
    <div class="card mt-4">
        <div class="card-body">
            <h5>Summary</h5>
            <p>Total Projects: {{ $totalProjects }}</p>
            <p>Completed Projects: {{ $completedProjects }}</p>
            <p>In Progress Projects: {{ $inProgressProjects }}</p>
            <p>Overdue Projects: {{ $overdueProjects }}</p>
            <p>Average Completion Rate: {{ number_format($avgCompletionRate, 2) }}%</p>
        </div>
    </div>
    
    <!-- Projects Table -->
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Description</th>
                <th>Creator</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Completion %</th>
                <th>Total Tasks</th>
                <th>Completed Tasks</th>
                <th>Pending Tasks</th>
                <th>In Progress Tasks</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
            <tr>
                <td>{{ $project->name }}</td>
                <td>{{ $project->description }}</td>
                <td>{{ $project->creator->name }}</td>
                <td>{{ $project->formatted_start_date }}</td>
                <td>{{ $project->formatted_end_date }}</td>
                <td>{{ $project->status }}</td>
                <td>{{ $project->completion_percentage }}%</td>
                <td>{{ $project->tasks->count() }}</td>
                <td>{{ $project->tasks->where('status', 'completed')->count() }}</td>
                <td>{{ $project->tasks->where('status', 'pending')->count() }}</td>
                <td>{{ $project->tasks->where('status', 'in_progress')->count() }}</td>
                <td>{{ $project->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12">No projects found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Export Button -->
    <a href="{{ route('reports.project.export', request()->query()) }}" class="btn btn-success">Export to CSV</a>
</div>
@endsection