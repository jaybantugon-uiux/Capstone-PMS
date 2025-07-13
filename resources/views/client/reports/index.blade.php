{{-- resources/views/client/reports/index.blade.php --}}
@extends('app')

@section('title', 'My Progress Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-chart-line me-2"></i>My Progress Reports
                    </h1>
                    <p class="text-muted mb-0">View all progress reports shared with you</p>
                </div>
                <div class="d-flex gap-2">
                    @if($stats['unread_reports'] > 0)
                        <form action="{{ route('client.reports.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-check-double me-1"></i>Mark All as Read
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('client.reports.export') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Reports
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_reports'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Unread Reports
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unread_reports'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Recent Reports (7 days)
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['recent_reports'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        With Attachments
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['reports_with_attachments'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-paperclip fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filter Reports
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('client.reports.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Unread</option>
                                <option value="viewed" {{ request('status') == 'viewed' ? 'selected' : '' }}>Read</option>
                                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Search reports..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('client.reports.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports List -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Progress Reports
                        @if($reports->total() > 0)
                            <span class="badge bg-secondary ms-2">{{ $reports->total() }} total</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if($reports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Report</th>
                                        <th>Project</th>
                                        <th>From Admin</th>
                                        <th>Date Received</th>
                                        <th>Status</th>
                                        <th>Attachment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr class="{{ $report->status === 'sent' ? 'table-warning' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($report->status === 'sent')
                                                        <span class="badge bg-warning me-2">NEW</span>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <a href="{{ route('client.reports.show', $report->id) }}" 
                                                               class="text-decoration-none">
                                                                {{ $report->title }}
                                                            </a>
                                                        </h6>
                                                        <small class="text-muted">
                                                            {{ Str::limit($report->description, 80) }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($report->project)
                                                    <span class="badge bg-info">{{ $report->project->name }}</span>
                                                @else
                                                    <span class="text-muted">General</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-primary rounded-circle">
                                                            {{ substr($report->admin->first_name, 0, 1) }}{{ substr($report->admin->last_name, 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $report->admin->first_name }} {{ $report->admin->last_name }}</div>
                                                        <small class="text-muted">{{ $report->admin->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $report->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $report->created_at->format('g:i A') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $report->status_color }}">
                                                    {{ $report->formatted_status }}
                                                </span>
                                                @if($report->view_count > 0)
                                                    <br><small class="text-muted">Viewed {{ $report->view_count }}x</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($report->hasAttachment())
                                                    <a href="{{ route('client.reports.download-attachment', $report->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Download {{ $report->original_filename }}">
                                                        <i class="{{ $report->attachment_icon }}"></i>
                                                        <small class="d-block">{{ $report->formatted_file_size }}</small>
                                                    </a>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-minus"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('client.reports.show', $report->id) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} reports
                            </div>
                            {{ $reports->appends(request()->query())->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Progress Reports Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'project_id', 'search']))
                                    No reports match your current filters. Try adjusting your search criteria.
                                @else
                                    You haven't received any progress reports yet. They will appear here when administrators share them with you.
                                @endif
                            </p>
                            @if(request()->hasAny(['status', 'project_id', 'search']))
                                <a href="{{ route('client.reports.index') }}" class="btn btn-primary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    width: 100%;
    height: 100%;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.badge.bg-warning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit filter form on select change
    const statusSelect = document.getElementById('status');
    const projectSelect = document.getElementById('project_id');
    
    statusSelect.addEventListener('change', function() {
        this.form.submit();
    });
    
    projectSelect.addEventListener('change', function() {
        this.form.submit();
    });
    
    // Highlight new reports
    const newReports = document.querySelectorAll('.table-warning');
    newReports.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 193, 7, 0.2)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
        });
    });
});
</script>
@endpush
@endsection