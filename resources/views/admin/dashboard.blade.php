@extends('app')

@section('content')
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>     
        <!-- Project Management Section -->
        <div class="mt-4">
            <h2>Project Management</h2>
            <p>Total Projects: {{ App\Models\Project::count() }}</p>
            <p>Active Projects: {{ App\Models\Project::where('archived', false)->count() }}</p>
            <p>Archived Projects: {{ App\Models\Project::where('archived', true)->count() }}</p>
            
            <div class="mt-2">
                <a href="{{ route('projects.index') }}" class="btn btn-primary">View All Projects</a>
            </div>
        </div>
        
        <!-- Recent Projects Section -->
        <div class="mt-4">
            <h2>Recent Projects</h2>
            @php
                $recentProjects = App\Models\Project::latest()->take(5)->get();
            @endphp
            
            @if($recentProjects->count() > 0)
                <ul>
                    @foreach($recentProjects as $project)
                        <li>
                            <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No projects found.</p>
            @endif
        </div>
    </div>
@endsection