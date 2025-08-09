@extends('app')

@section('content')
    <div class="container">
        <h1>Projects</h1>
        
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'pm')
            <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">Create New Project</a>
        @endif
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if($projects->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                            <td>{{ $project->formatted_start_date }}</td>
                            <td>{{ $project->formatted_end_date ?? 'N/A' }}</td>
                            <td>{{ $project->status }}</td>
                            <td>
                                @if(auth()->user()->role === 'admin' || $project->created_by === auth()->id())
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    @if(!$project->archived)
                                        <form action="{{ route('projects.archive', $project) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Archive</button>
                                        </form>
                                    @else
                                        <form action="{{ route('projects.restore', $project) }}" method="POST" style="display:inline;">
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
            {{ $projects->links() }}
        @else
            <p>No projects found.</p>
        @endif
    </div>
@endsection