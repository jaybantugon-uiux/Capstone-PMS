@extends('app')

@section('content')
<div class="container">
    <h1>Equipment Details: {{ $equipment->name }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Equipment Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $equipment->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $equipment->description ?: 'No description provided' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Current Stock:</strong></td>
                            <td>
                                <span class="h5">{{ $equipment->stock }}</span> units
                                @if($equipment->stock <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($equipment->isLowStock())
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Min Stock Level:</strong></td>
                            <td>{{ $equipment->min_stock_level }} units</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @if($equipment->archived)
                                    <span class="badge bg-secondary">Archived</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total Restocked:</strong></td>
                            <td>{{ $equipment->total_restocked }} units</td>
                        </tr>
                        <tr>
                            <td><strong>Total Used:</strong></td>
                            <td>{{ $equipment->total_used }} units</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $equipment->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $equipment->updated_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recent Stock Transactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>Recent Stock Transactions</h5>
                    <a href="{{ route('equipment.logs', $equipment->id) }}" class="btn btn-sm btn-outline-primary">
                        View All Logs
                    </a>
                </div>
                <div class="card-body">
                    @if($equipment->stockLogs->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Change</th>
                                    <th>Type</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipment->stockLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                        <td>
                                            @if($log->user)
                                                {{ $log->user->first_name }} {{ $log->user->last_name }}
                                                <small class="text-muted">({{ $log->user->username }})</small>
                                            @else
                                                <em class="text-muted">Unknown User</em>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->change > 0 ? 'success' : 'warning' }}">
                                                {{ $log->change > 0 ? '+' : '' }}{{ $log->change }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->change > 0)
                                                Restock
                                            @else
                                                Usage
                                            @endif
                                        </td>
                                        <td>{{ $log->note ?: 'No note provided' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No stock transactions recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Quick Actions -->
            @if(!$equipment->archived)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="{{ route('equipment.restock.form', $equipment->id) }}" class="btn btn-success">
                            Restock Equipment
                        </a>
                        <a href="{{ route('equipment.use.form', $equipment->id) }}" class="btn btn-warning">
                            Use Equipment
                        </a>
                        <a href="{{ route('equipment.edit', $equipment->id) }}" class="btn btn-primary">
                            Edit Details
                        </a>
                        <a href="{{ route('equipment.logs', $equipment->id) }}" class="btn btn-info">
                            View All Logs
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#archiveModal">
                            Archive Equipment
                        </button>
                    </div>
                </div>
            @else
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Equipment Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('equipment.restore', $equipment->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to restore this equipment?')">
                                Restore Equipment
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Stock Status Alert -->
            @if($equipment->isLowStock() && !$equipment->archived)
                <div class="alert alert-warning">
                    <strong>Low Stock Alert!</strong><br>
                    Stock is below minimum level ({{ $equipment->min_stock_level }})
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('equipment.index') }}" class="btn btn-secondary mt-3">Back to Equipment List</a>
</div>

<!-- Archive Confirmation Modal -->
@if(!$equipment->archived)
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveModalLabel">Archive Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive this equipment?</p>
                <p class="text-muted">Archived equipment cannot be restocked or used until restored.</p>
                <div class="alert alert-warning">
                    <strong>{{ $equipment->name }}</strong><br>
                    Current Stock: <strong>{{ $equipment->stock }} units</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('equipment.archive', $equipment->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        Archive Equipment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
