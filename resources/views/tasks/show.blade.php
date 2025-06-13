@extends('app')

@section('content')
    <div class="container">
        <h1>{{ $task->task_name }}</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <p><strong>Description:</strong> {{ $task->description ?? 'N/A' }}</p>
        <p><strong>Project:</strong> {{ $task->project->name }}</p>
        <p><strong>Assigned To:</strong> {{ $task->siteCoordinator->name }}</p>
        <p><strong>Status:</strong> <span class="badge badge-{{ $task->status_badge_color }}">{{ $task->formatted_status }}</span></p>
        <p><strong>Due Date:</strong> {{ $task->formatted_due_date ?? 'N/A' }}</p>
        <p><strong>Priority:</strong> <span class="badge badge-{{ $task->priority_badge_color }}">{{ ucfirst($task->priority) }}</span></p>
        
        @if(!$task->archived && (auth()->user()->role === 'admin' || auth()->user()->id === $task->created_by))
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary">Edit Task</a>
            <form action="{{ route('tasks.archive', $task) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Archive Task</button>
            </form>
        @elseif($task->archived && (auth()->user()->role === 'admin' || auth()->user()->id === $task->created_by))
            <form action="{{ route('tasks.restore', $task) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">Restore Task</button>
            </form>
        @endif
        
        @if(!$task->archived && (auth()->user()->role === 'admin' || (auth()->user()->role === 'pm' && auth()->user()->id === $task->created_by) || (auth()->user()->role === 'sc' && auth()->user()->id === $task->assigned_to)))
            <form method="POST" action="{{ route('tasks.update-status', $task) }}" class="mt-3">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="status">Update Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
        @endif
    </div>
@endsection