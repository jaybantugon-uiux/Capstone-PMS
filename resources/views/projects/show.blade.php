@extends('app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Project Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $project->name }}</h1>
                <p class="text-gray-600 mt-2">Created by: {{ $project->creator->first_name }} {{ $project->creator->last_name }}</p>
                <p class="text-sm text-gray-500">Created: {{ $project->created_at->format('M d, Y') }}</p>
            </div>
            
            @if(in_array(auth()->user()->role, ['pm', 'admin']))
            <div class="space-x-2">
                <a href="{{ route('projects.edit', $project) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Project
                </a>
                <a href="{{ route('projects.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Projects
                </a>
            </div>
            @endif
        </div>

        <!-- Project Description -->
        @if($project->description)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
            <p class="text-gray-700 bg-gray-50 p-4 rounded">{{ $project->description }}</p>
        </div>
        @endif

        <!-- Tasks Section -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Tasks</h3>
                
                @if(in_array(auth()->user()->role, ['pm', 'admin']))
                <button onclick="toggleTaskForm()" 
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    + Create New Task
                </button>
                @endif
            </div>

            <!-- Create Task Form (Hidden by default) -->
            @if(in_array(auth()->user()->role, ['pm', 'admin']))
            <div id="taskForm" class="hidden bg-gray-50 p-4 rounded-lg mb-6">
    <h4 class="text-lg font-semibold mb-4">Create New Task</h4>
    <form action="{{ route('tasks.create', $project) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="task_name" class="block text-sm font-medium text-gray-700">Task Name</label>
                <input type="text" name="task_name" id="task_name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       value="{{ old('task_name') }}">
                // @phpstan-ignore-next-line
                @error('task_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign to Site Coordinator</label>
                <select name="assigned_to" id="assigned_to" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Site Coordinator</option>
                    @foreach(App\Models\User::where('role', 'sc')->get() as $coordinator)
                        <option value="{{ $coordinator->id }}" {{ old('assigned_to') == $coordinator->id ? 'selected' : '' }}>
                            {{ $coordinator->first_name }} {{ $coordinator->last_name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3" required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
            // @phpstan-ignore-next-line
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mt-4 flex space-x-2">
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Create Task
            </button>
            <button type="button" onclick="toggleTaskForm()" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Cancel
            </button>
        </div>
    </form>
</div>

            <!-- Tasks List -->
            @if($project->tasks->count() > 0)
                <div class="space-y-4">
                    @foreach($project->tasks as $task)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-800">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-blue-600">
                                        {{ $task->task_name }}
                                    </a>
                                </h4>
                                <p class="text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    <span>Assigned to: {{ $task->siteCoordinator->first_name }} {{ $task->siteCoordinator->last_name }}</span>
                                    <span>Created: {{ $task->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($task->status === 'completed') bg-green-100 text-green-800
                                    @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>No tasks created for this project yet.</p>
                    @if(in_array(auth()->user()->role, ['pm', 'admin']))
                        <p class="mt-2">Click "Create New Task" to get started.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
