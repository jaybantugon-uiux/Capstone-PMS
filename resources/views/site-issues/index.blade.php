
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">My Site Issues</h1>
                <a href="{{ route('sc.site-issues.create') }}" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i> Report New Issue
                </a>
            </div>

            <!-- Alert for Critical Issues -->
            @if($siteIssues->where('priority', 'critical')->where('status', '!=', 'resolved')->count() > 0)
                <div class="alert alert-danger" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Critical Issues Requiring Attention
                    </h5>
                    <p class="mb-0">
                        You have {{ $siteIssues->where('priority', 'critical')->where('status', '!=', 'resolved')->count() }} 
                        critical issue(s) that need immediate attention.
                    </p>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('sc.site-issues.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">All Priorities</option>
                                @foreach($priorityOptions as $priority)
                                    <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>
                                        {{ ucfirst($priority) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="issue_type" class="form-label">Type</label>
                            <select name="issue_type" class="form-select">
                                <option value="">All Types</option>
                                @foreach($typeOptions as $type)
                                    <option value="{{ $type }}" {{ request('issue_type') === $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search issues..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Issues List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Site Issues ({{ $siteIssues->total() }} total)</h5>
                </div>
                <div class="card-body">
                    @if($siteIssues->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Issue</th>
                                        <th>Type</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Project</th>
                                        <th>Reported</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siteIssues as $issue)
                                        <tr class="{{ $issue->priority === 'critical' && $issue->status !== 'resolved' ? 'table-danger' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div>
                                                        <a href="{{ route('sc.site-issues.show', $issue) }}" class="text-decoration-none fw-bold">
                                                            {{ $issue->issue_title }}
                                                        </a>
                                                        @if($issue->location)
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $issue->location }}
                                                            </small>
                                                        @endif
                                                        @if($issue->estimated_cost)
                                                            <br><small class="text-success">
                                                                <i class="fas fa-peso-sign me-1"></i>{{ $issue->formatted_estimated_cost }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->issue_type_badge_color }}">
                                                    {{ $issue->formatted_issue_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->priority_badge_color }}">
                                                    {{ $issue->formatted_priority }}
                                                </span>
                                                @if($issue->is_overdue_for_acknowledgment || $issue->is_overdue_for_resolution)
                                                    <br><small class="text-danger">
                                                        <i class="fas fa-clock me-1"></i>Overdue
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $issue->status_badge_color }}">
                                                    {{ $issue->formatted_status }}
                                                </span>
                                                @if($issue->assignedTo)
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>{{ $issue->assignedTo->first_name }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $issue->project) }}" class="text-decoration-none">
                                                    {{ $issue->project->name }}
                                                </a>
                                                @if($issue->task)
                                                    <br><small class="text-muted">{{ $issue->task->task_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $issue->formatted_reported_at }}</small>
                                                <br><small class="text-muted">{{ $issue->age }}</small>
                                            </td>
                                            <td>
                                                @if($issue->acknowledged_at)
                                                    <small class="text-success">
                                                        <i class="fas fa-check me-1"></i>{{ $issue->formatted_acknowledged_at }}
                                                    </small>
                                                @elseif($issue->updated_at != $issue->created_at)
                                                    <small>{{ $issue->updated_at->format('M d, Y g:i A') }}</small>
                                                @else
                                                    <small class="text-muted">No updates</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('sc.site-issues.show', $issue) }}" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!in_array($issue->status, ['resolved', 'closed']))
                                                        <a href="{{ route('sc.site-issues.edit', $issue) }}" 
                                                           class="btn btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $siteIssues->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Site Issues Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'priority', 'issue_type', 'project_id', 'search']))
                                    No issues match your current filters.
                                    <br><a href="{{ route('sc.site-issues.index') }}" class="text-decoration-none">Clear filters</a>
                                @else
                                    You haven't reported any site issues yet.
                                @endif
                            </p>
                            <a href="{{ route('sc.site-issues.create') }}" class="btn btn-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i> Report Your First Issue
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            @if($siteIssues->count() > 0)
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h4>{{ $siteIssues->total() }}</h4>
                            <p class="mb-0">Total Issues</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $siteIssues->where('status', 'open')->count() }}</h4>
                            <p class="mb-0">Open Issues</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4>{{ $siteIssues->where('priority', 'critical')->where('status', '!=', 'resolved')->count() }}</h4>
                            <p class="mb-0">Critical Issues</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ $siteIssues->where('status', 'resolved')->count() }}</h4>
                            <p class="mb-0">Resolved Issues</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-danger {
    --bs-table-accent-bg: var(--bs-danger-bg-subtle);
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
.badge {
    font-size: 0.8em;
}
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    .btn-group-sm {
        display: flex;
        flex-direction: column;
    }
    .btn-group-sm .btn {
        margin-bottom: 2px;
    }
}
</style>
@endpush