@extends('app')

@section('content')
    <div class="container">
        <h1>SC Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- Tasks Assigned to Me -->
        <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-4">Tasks Assigned to Me</h2>
        @if($tasks->count() > 0)
            <div class="space-y-4">
                @foreach($tasks as $task)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <a href="{{ route('tasks.show', $task) }}" class="hover:text-blue-600">
                                {{ $task->task_name }}
                            </a>
                        </h3>
                        <p class="text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                            <span>Project: <a href="{{ route('projects.show', $task->project) }}" class="text-blue-600 hover:underline">{{ $task->project->name }}</a></span>
                            <span>Status: {{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No tasks assigned to you yet.</p>
        @endif
    </div>
@endsection