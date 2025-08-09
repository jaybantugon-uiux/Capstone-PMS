@extends('app')

@section('content')
    <div class="container">
        <h1>{{ $project->name }}</h1>
        <p><strong>Description:</strong> {{ $project->description ?? 'N/A' }}</p>
        <p><strong>Start Date:</strong> {{ $project->formatted_start_date }}</p>
        <p><strong>End Date:</strong> {{ $project->formatted_end_date ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ $project->status }}</p>
        <p><strong>Created by:</strong> {{ $project->creator->name }}</p>
        
        @if(auth()->user()->role === 'admin' || auth()->user()->id === $project->created_by)
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">Edit Project</a>
            @if(!$project->archived)
                <form action="{{ route('projects.archive', $project) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Archive Project</button>
                </form>
            @else
                <form action="{{ route('projects.restore', $project) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">Restore Project</button>
                </form>
            @endif
        @endif
        
        @if(!$project->archived && (auth()->user()->role === 'admin' || auth()->user()->role === 'pm'))
            <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary mt-2">Create New Task</a>
        @endif
        
        <h2>Tasks</h2>
        @if($project->tasks->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->tasks as $task)
                        <tr>
                            <td><a href="{{ route('tasks.show', $task) }}">{{ $task->task_name }}</a></td>
                            <td>{{ $task->siteCoordinator->name }}</td>
                            <td>{{ $task->formatted_status }}</td>
                            <td>{{ $task->formatted_due_date ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No tasks found for this project.</p>
        @endif
    </div>
@endsection