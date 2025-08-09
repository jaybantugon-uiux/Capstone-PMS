@extends('app')

@section('content')
<div class="container">
    <h1>Archived Tasks</h1>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="mb-3">
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to Active Tasks</a>
    </div>
    
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
                        <td>{{ $task->siteCoordinator->first_name }} {{ $task->siteCoordinator->last_name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                        <td>{{ $task->due_date ?? 'N/A' }}</td>
                        <td>
                            @if(auth()->user()->role === 'admin' || $task->created_by === auth()->id())
                                <form action="{{ route('tasks.restore', $task) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this task?')">Restore</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $tasks->links() }}
    @else
        <p>No archived tasks found.</p>
    @endif
</div>
@endsection