@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0"><i class="fas fa-file-export me-2"></i>Equipment Status Report</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.equipment-monitoring.report-equipment-status', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>
                    <a href="{{ route('admin.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending_approval" {{ ($statusFilter ?? '') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="declined" {{ ($statusFilter ?? '') === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Usage Type</label>
                            <select name="usage_type" class="form-select">
                                <option value="">All</option>
                                <option value="personal" {{ ($typeFilter ?? '') === 'personal' ? 'selected' : '' }}>Personal</option>
                                <option value="project_site" {{ ($typeFilter ?? '') === 'project_site' ? 'selected' : '' }}>Project Site</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magnifying-glass me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('admin.equipment-monitoring.report-equipment-status') }}" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Equipment ({{ $equipment->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($equipment->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Equipment</th>
                                        <th>SC</th>
                                        <th>Usage</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Availability</th>
                                        <th>Quantity</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipment as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->equipment_name }}</strong>
                                                <div class="text-muted small">{{ Str::limit($item->equipment_description, 60) }}</div>
                                            </td>
                                            <td>{{ $item->user?->full_name }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $item->formatted_usage_type ?? ucfirst(str_replace('_',' ',$item->usage_type)) }}</span></td>
                                            <td>{{ $item->project?->name ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'pending_approval' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->availability_status === 'available' ? 'success' : ($item->availability_status === 'in_use' ? 'warning' : ($item->availability_status === 'maintenance' ? 'info' : 'danger')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->availability_status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <i class="fas fa-cubes fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No equipment matches your filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


