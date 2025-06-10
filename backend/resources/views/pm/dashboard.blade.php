@extends('app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Project Manager Dashboard</h1>
            
            {{-- DEBUG SECTION - Remove this after fixing --}}
            <div class="alert alert-info mb-4">
                <h4>🔍 Debug Information:</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>User ID:</strong> {{ auth()->id() }}</p>
                        <p><strong>User Role:</strong> {{ auth()->user()->role }}</p>
                        <p><strong>User Email:</strong> {{ auth()->user()->email }}</p>
                        <p><strong>Full Name:</strong> {{ auth()->user()->full_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Is Authenticated:</strong> {{ auth()->check() ? 'Yes' : 'No' }}</p>
                        <p><strong>Is Verified:</strong> {{ auth()->user()->hasVerifiedEmail() ? 'Yes' : 'No' }}</p>
                        <p><strong>Account Status:</strong> {{ auth()->user()->status }}</p>
                        <p><strong>Can Create Projects:</strong> {{ in_array(auth()->user()->role, ['pm', 'admin']) ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            </div>

            {{-- ACTUAL DASHBOARD CONTENT --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-header">My Projects</div>
                        <div class="card-body">
                            <h4 class="card-title">{{ auth()->user()->projects()->count() }}</h4>
                            <p class="card-text">Total projects you've created</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-header">Active Projects</div>
                        <div class="card-body">
                            <h4 class="card-title">{{ auth()->user()->projects()->count() }}</h4>
                            <p class="card-text">Currently active projects</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-lg">
                                            <i class="fas fa-plus"></i> Create New Project
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        <a href="{{ route('projects.index') }}" class="btn btn-outline-primary btn-lg">
                                            <i class="fas fa-list"></i> View All Projects
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        <button class="btn btn-outline-secondary btn-lg" disabled>
                                            <i class="fas fa-chart-bar"></i> Project Reports
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Projects --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Projects</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $recentProjects = auth()->user()->projects()->latest()->take(5)->get();
                            @endphp
                            
                            @if($recentProjects->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentProjects as $project)
                                                <tr>
                                                    <td>{{ $project->name }}</td>
                                                    <td>{{ Str::limit($project->description, 50) }}</td>
                                                    <td>{{ $project->created_at->diffForHumans() }}</td>
                                                    <td>
                                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No projects yet</h5>
                                    <p class="text-muted">Create your first project to get started!</p>
                                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Project
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection