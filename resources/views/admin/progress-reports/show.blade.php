@extends('app')

@section('title', 'Progress Report - ' . $progressReport->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.progress-reports.index') }}">Progress Reports</a>
                    </li>
                    <li class="breadcrumb-item active">{{ Str::limit($progressReport->title, 30) }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">{{ $progressReport->title }}</h1>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->role === 'admin' || $progressReport->created_by === auth()->id())
                <a href="{{ route('admin.progress-reports.edit', $progressReport) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <button type="button" class="btn btn-danger" onclick="deleteReport()">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
            @endif
            <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Report Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>Report Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $progressReport->status_color }} ms-2">
                                {{ $progressReport->formatted_status }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Views:</strong>
                            <span class="badge bg-light text-dark ms-2">{{ $progressReport->view_count }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Created:</strong>
                            <span class="ms-2">{{ $progressReport->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="col-md-6">
                            @if($progressReport->sent_at)
                                <strong>Sent:</strong>
                                <span class="ms-2">{{ $progressReport->sent_at->format('M d, Y g:i A') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($progressReport->first_viewed_at)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong>First Viewed:</strong>
                                <span class="ms-2">{{ $progressReport->first_viewed_at->format('M d, Y g:i A') }}</span>
                                <small class="text-muted">({{ $progressReport->first_viewed_at->diffForHumans() }})</small>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <!-- Report Description -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold">Description:</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($progressReport->description)) !!}
                        </div>
                    </div>

                    <!-- Attachment -->
                    @if($progressReport->hasAttachment())
                        <div class="mb-3">
                            <h6 class="font-weight-bold">Attachment:</h6>
                            <div class="d-flex align-items-center">
                                <i class="{{ $progressReport->attachment_icon }} me-2"></i>
                                <div>
                                    <strong>{{ $progressReport->original_filename }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $progressReport->formatted_file_size }}</small>
                                </div>
                                <div class="ms-auto">
                                    <a href="{{ route('admin.progress-reports.download-attachment', $progressReport) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Client Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Client Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle mx-auto mb-2">
                            {{ strtoupper(substr($progressReport->client->first_name, 0, 1) . substr($progressReport->client->last_name, 0, 1)) }}
                        </div>
                        <h6 class="mb-1">{{ $progressReport->client->first_name }} {{ $progressReport->client->last_name }}</h6>
                        <p class="text-muted mb-0">{{ $progressReport->client->email }}</p>
                    </div>
                    
                    @if($progressReport->project)
                        <hr>
                        <div>
                            <strong>Project:</strong>
                            <br>
                            <a href="{{ route('projects.show', $progressReport->project) }}" class="text-decoration-none">
                                {{ $progressReport->project->name }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Creator Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-tie me-2"></i>Created By
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar-circle mx-auto mb-2 bg-{{ $progressReport->creator_role_badge_color }}">
                            {{ strtoupper(substr($progressReport->creator->first_name, 0, 1) . substr($progressReport->creator->last_name, 0, 1)) }}
                        </div>
                        <h6 class="mb-1">{{ $progressReport->creator->first_name }} {{ $progressReport->creator->last_name }}</h6>
                        <span class="badge bg-{{ $progressReport->creator_role_badge_color }}">
                            {{ $progressReport->formatted_creator_role }}
                        </span>
                        <p class="text-muted mt-2 mb-0">{{ $progressReport->creator->email }}</p>
                    </div>
                </div>
            </div>

            <!-- View Statistics -->
            @if($progressReport->view_count > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line me-2"></i>View Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-right">
                                    <h4 class="text-primary">{{ $viewStats['total_views'] }}</h4>
                                    <small class="text-muted">Total Views</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ $viewStats['recent_views'] }}</h4>
                                <small class="text-muted">Last 7 Days</small>
                            </div>
                        </div>
                        
                        @if($viewStats['latest_view'])
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    Last viewed {{ $viewStats['latest_view']->viewed_at->diffForHumans() }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($progressReport->status === 'draft')
                            <form method="POST" action="{{ route('admin.progress-reports.update', $progressReport) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="sent">
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-paper-plane me-1"></i>Send to Client
                                </button>
                            </form>
                        @endif
                        
                        @if($progressReport->status !== 'archived')
                            <form method="POST" action="{{ route('admin.progress-reports.update', $progressReport) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="archived">
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="fas fa-archive me-1"></i>Archive Report
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('admin.progress-reports.create') }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-plus me-1"></i>Create New Report
                        </a>
                        
                        <a href="{{ route('client.reports.show', $progressReport) }}" 
                           class="btn btn-outline-info btn-sm w-100" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>View as Client
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this progress report?</p>
                <p><strong>{{ $progressReport->title }}</strong></p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.progress-reports.destroy', $progressReport) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #495057;
}

.bg-primary.avatar-circle {
    background-color: #4e73df !important;
    color: white;
}

.bg-danger.avatar-circle {
    background-color: #e74a3b !important;
    color: white;
}

.border-right {
    border-right: 1px solid #e3e6f0 !important;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.badge {
    font-size: 0.8em;
}
</style>
@endpush

@push('scripts')
<script>
function deleteReport() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-refresh view statistics every 30 seconds (optional)
setInterval(function() {
    // You can implement AJAX refresh here if needed
    console.log('Checking for updated view statistics...');
}, 30000);
</script>
@endpush