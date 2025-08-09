{{-- resources/views/admin/site-issues/index.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-exclamation-triangle me-2"></i>Site Issues Management
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['total'] }}</h4>
                                    <p class="card-text">Total Issues</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['open'] }}</h4>
                                    <p class="card-text">Open Issues</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-folder-open fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['critical'] }}</h4>
                                    <p class="card-text">Critical Issues</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-fire fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ $stats['unacknowledged'] }}</h4>
                                    <p class="card-text">Unacknowledged</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bell fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="">All Priorities</option>
                                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="issue_type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="safety" {{ request('issue_type') === 'safety' ? 'selected' : '' }}>Safety</option>
                                    <option value="equipment" {{ request('issue_type') === 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    <option value="environmental" {{ request('issue_type') === 'environmental' ? 'selected' : '' }}>Environmental</option>
                                    <option value="personnel" {{ request('issue_type') === 'personnel' ? 'selected' : '' }}>Personnel</option>
                                    <option value="quality" {{ request('issue_type') === 'quality' ? 'selected' : '' }}>Quality</option>
                                    <option value="timeline" {{ request('issue_type') === 'timeline' ? 'selected' : '' }}>Timeline</option>
                                    <option value="other" {{ request('issue_type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select form-select-sm">
                                    <option value="">All Assignees</option>
                                    <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                                    @foreach($assignableUsers as $user)
                                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="Search issues..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="{{ route('admin.site-issues.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Issues List -->
            <div class="card">
                <div class="card-body">
                    @if($siteIssues->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Issue</th>
                                        <th>Priority</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Project</th>
                                        <th>Reporter</th>
                                        <th>Assigned To</th>
                                        <th>Reported</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siteIssues as $issue)
                                        <tr class="{{ $issue->needs_attention ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="fw-bold">{{ $issue->issue_title }}</div>
                                                @if($issue->location)
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $issue->location }}
                                                    </small>
                                                @endif
                                                @if($issue->needs_attention)
                                                    <br><span class="badge bg-warning text-dark">Needs Attention</span>
                                                @endif
                                                @if($issue->estimated_cost)
                                                    <br><small class="text-muted">Est. Cost: {{ $issue->formatted_estimated_cost }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->priority_badge_color }}">
                                                    {{ $issue->formatted_priority }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->issue_type_badge_color }}">
                                                    {{ $issue->formatted_issue_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->status_badge_color }}">
                                                    {{ $issue->formatted_status }}
                                                </span>
                                                @if(!$issue->acknowledged_at && $issue->status === 'open')
                                                    <br><small class="text-muted">Unacknowledged</small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $issue->project) }}" class="text-decoration-none">
                                                    {{ $issue->project->name }}
                                                </a>
                                                @if($issue->task)
                                                    <br><small class="text-muted">Task: {{ Str::limit($issue->task->task_name, 20) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $issue->reporter->full_name }}
                                                <br><small class="text-muted">{{ $issue->reporter->role }}</small>
                                            </td>
                                            <td>
                                                @if($issue->assignedTo)
                                                    {{ $issue->assignedTo->full_name }}
                                                    <br><small class="text-muted">{{ $issue->assignedTo->role }}</small>
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                    @if($issue->status === 'open')
                                                        <br>
                                                        <form method="POST" action="{{ route('admin.site-issues.assign', $issue) }}" class="d-inline">
                                                            @csrf
                                                            <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()">
                                                                <option value="">Assign to...</option>
                                                                @foreach($assignableUsers as $user)
                                                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                {{ $issue->formatted_reported_at }}
                                                <br><small class="text-muted">{{ $issue->age }}</small>
                                                @if($issue->resolved_at)
                                                    <br><small class="text-success">Resolved: {{ $issue->formatted_resolved_at }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.site-issues.show', $issue) }}" 
                                                       class="btn btn-outline-primary" title="View/Manage">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.site-issues.edit', $issue) }}" 
                                                       class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(!$issue->acknowledged_at && $issue->status === 'open')
                                                        <form method="POST" action="{{ route('admin.site-issues.acknowledge', $issue) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-info btn-sm" title="Acknowledge">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $siteIssues->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No site issues found matching your criteria.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-warning {
    --bs-table-accent-bg: var(--bs-warning-bg-subtle);
}
</style>
@endpush