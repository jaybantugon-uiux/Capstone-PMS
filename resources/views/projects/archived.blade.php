@extends('app')

@section('content')
<div class="container">
    <h1>Archived Projects</h1>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="mb-3">
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">Back to Active Projects</a>
    </div>
    
    @if($projects->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr>
                        <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                        <td>{{ $project->description }}</td>
                        <td>{{ $project->start_date }}</td>
                        <td>{{ $project->end_date ?? 'N/A' }}</td>
                        <td>{{ $project->creator->full_name }}</td>
                        <td>
                            <form action="{{ route('projects.restore', $project) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this project?')">Restore</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $projects->links() }}
    @else
        <p>No archived projects found.</p>
    @endif
</div>
@endsection