@extends('app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Task Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $task->task_name }}</h1>
                <p class="text-gray-600 mt-2">
                    Project: <a href="{{ route('projects.show', $task->project) }}" class="text-blue-600 hover:underline">{{ $task->project->name }}</a>
                </p>
                <p class="text-sm text-gray-500">Created: {{ $task->created_at->format('M d, Y H:i') }}</p>
            </div>
            
            <div class="text-right">
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    @if($task->status === 'completed') bg-green-100 text-green-800
                    @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
                
                <div class="mt-2 space-x-2">
                    <a href="{{ route('projects.show', $task->project) }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Back to Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Task Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Task Description</h3>
                <div class="bg-gray-50 p-4 rounded">
                    <p class="text-gray-700">{{ $task->description }}</p>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Assignment Details</h3>
                <div class="bg-gray-50 p-4 rounded space-y-2">
                    <p><strong>Assigned to:</strong> {{ $task->siteCoordinator->first_name }} {{ $task->siteCoordinator->last_name }}</p>
                    <p><strong>Email:</strong> {{ $task->siteCoordinator->email }}</p>
                    <p><strong>Role:</strong> Site Coordinator</p>
                    <p><strong>Status:</strong> 
                        <span class="font-semibold
                            @if($task->status === 'completed') text-green-600
                            @elseif($task->status === 'in_progress') text-yellow-600
                            @else text-gray-600
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Status Update Form (for Site Coordinators and Admins) -->
        @if(auth()->user()->id === $task->assigned_to || auth()->user()->role === 'admin')
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Update Task Status</h3>
            <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="flex items-center space-x-4">
                @csrf
                @method('PATCH')
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" required
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="pt-6">
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Task Timeline -->
        <div class="border-t pt-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Task Timeline</h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium">Task Created</p>
                        <p class="text-xs text-gray-500">{{ $task->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                
                @if($task->updated_at != $task->created_at)
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium">Last Updated</p>
                        <p class="text-xs text-gray-500">{{ $task->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
                
                @if($task->status === 'completed')
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium">Task Completed</p>
                        <p class="text-xs text-gray-500">{{ $task->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection