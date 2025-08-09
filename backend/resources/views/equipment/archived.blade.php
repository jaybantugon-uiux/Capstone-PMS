@extends('app')

@section('content')
<div class="container">
    <h1>Archived Equipment</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('equipment.index') }}" class="btn btn-secondary mb-3">Back to Active Equipment</a>

    @if($equipment->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Min Level</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipment as $item)
                    <tr>
                        <td>
                            <span class="badge bg-secondary me-2">ARCHIVED</span>
                            {{ $item->name }}
                        </td>
                        <td>{{ Str::limit($item->description ?: 'No description', 50) }}</td>
                        <td>{{ $item->stock }} units</td>
                        <td>{{ $item->min_stock_level }}</td>
                        <td>{{ $item->updated_at->format('M j, Y') }}</td>
                        <td>
                            <a href="{{ route('equipment.show', $item->id) }}" class="btn btn-sm btn-info">View</a>
                            <form action="{{ route('equipment.restore', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" 
                                        onclick="return confirm('Are you sure you want to restore this equipment?')">
                                    Restore
                                </button>
                            </form>
                            <a href="{{ route('equipment.logs', $item->id) }}" class="btn btn-sm btn-secondary">Logs</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-5">
            <h5 class="text-muted">No Archived Equipment</h5>
            <p class="text-muted">No equipment has been archived yet.</p>
            <a href="{{ route('equipment.index') }}" class="btn btn-primary">View Active Equipment</a>
        </div>
    @endif
</div>
@endsection