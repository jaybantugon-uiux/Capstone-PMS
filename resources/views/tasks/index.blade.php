@extends('app')

@section('content')
    <div class="container">
        <h1>Tasks</h1>
        
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'pm')
            <a href="{{ route('tasks.create') }}" class="btn btn-primary mb-3">Create New Task</a>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if($tasks->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td><a href="{{ route('tasks.show', $task) }}">{{ $task->task_name }}</a></td>
                            <td>{{ $task->project->name }}</td>
                            <td>{{ $task->siteCoordinator->name }}</td>
                            <td><span class="badge badge-{{ $task->status_badge_color }}">{{ $task->formatted_status }}</span></td>
                            <td>{{ $task->formatted_due_date ?? 'N/A' }}</td>
                            <td>
                                @if(auth()->user()->role === 'admin' || $task->created_by === auth()->id())
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    @if(!$task->archived)
                                        <form action="{{ route('tasks.archive', $task) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Archive</button>
                                        </form>
                                    @else
                                        <form action="{{ route('tasks.restore', $task) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $tasks->links() }}
        @else
            <p>No tasks found.</p>
        @endif
    </div>
@endsection