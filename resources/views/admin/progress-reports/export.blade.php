@extends('app')

@section('title', 'Export Progress Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.progress-reports.index') }}">Progress Reports</a>
                            </li>
                            <li class="breadcrumb-item active">Export</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-download me-2"></i>Export Progress Reports
                    </h1>
                    <p class="text-muted mb-0">Generate and download progress report data</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ auth()->user()->role === 'admin' ? 'danger' : 'primary' }}">
                        {{ auth()->user()->role === 'admin' ? 'Administrator' : 'Project Manager' }}
                    </span>
                    <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Reports
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Export Configuration -->
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>Export Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="exportForm" method="GET" action="{{ route('admin.progress-reports.export') }}">
                                <!-- Export Format -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-file-alt me-1"></i>Export Format
                                        </label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="format" id="formatCSV" value="csv" checked>
                                            <label class="form-check-label" for="formatCSV">
                                                <i class="fas fa-file-csv me-1 text-success"></i>CSV (Comma Separated Values)
                                                <br><small class="text-muted">Recommended for Excel and data analysis</small>
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="radio" name="format" id="formatExcel" value="excel">
                                            <label class="form-check-label" for="formatExcel">
                                                <i class="fas fa-file-excel me-1 text-success"></i>Excel (.xlsx)
                                                <br><small class="text-muted">Formatted spreadsheet with multiple sheets</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-database me-1"></i>Data Scope
                                        </label>
                                        @if(auth()->user()->role === 'admin')
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="scope" id="scopeAll" value="all" checked>
                                                <label class="form-check-label" for="scopeAll">
                                                    <i class="fas fa-globe me-1 text-primary"></i>All Reports (Admin Access)
                                                </label>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="scope" id="scopeMine" value="mine">
                                                <label class="form-check-label" for="scopeMine">
                                                    <i class="fas fa-user me-1 text-info"></i>My Reports Only
                                                </label>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                As a Project Manager, you can only export reports you created.
                                            </div>
                                            <input type="hidden" name="scope" value="mine">
                                        @endif
                                    </div>
                                </div>

                                <!-- Filters -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="fw-bold">
                                            <i class="fas fa-filter me-1"></i>Filters (Optional)
                                        </h6>
                                        <p class="text-muted small">Apply filters to export specific reports</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status</label>
                                        <select name="status" id="status" class="form-select">
                                            <option value="">All Status</option>
                                            <option value="draft">Draft</option>
                                            <option value="sent">Sent</option>
                                            <option value="viewed">Viewed</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="client_id" class="form-label">Client</label>
                                        <select name="client_id" id="client_id" class="form-select">
                                            <option value="">All Clients</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}">
                                                    {{ $client->first_name }} {{ $client->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="project_id" class="form-label">Project</label>
                                        <select name="project_id" id="project_id" class="form-select">
                                            <option value="">All Projects</option>
                                            @foreach($projects as $project)
                                                @if(auth()->user()->role === 'admin' || auth()->user()->canManageProject($project->id))
                                                    <option value="{{ $project->id }}">
                                                        {{ $project->name }}
                                                        @if(auth()->user()->role === 'pm')
                                                            <small>(You manage this)</small>
                                                        @endif
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(auth()->user()->role === 'admin')
                                        <div class="col-md-6">
                                            <label for="creator_role" class="form-label">Created By Role</label>
                                            <select name="creator_role" id="creator_role" class="form-select">
                                                <option value="">All Roles</option>
                                                <option value="admin">Administrators</option>
                                                <option value="pm">Project Managers</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="date_from" class="form-label">Date From</label>
                                        <input type="date" name="date_from" id="date_from" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_to" class="form-label">Date To</label>
                                        <input type="date" name="date_to" id="date_to" class="form-control">
                                    </div>
                                </div>

                                <!-- Export Options -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="fw-bold">
                                            <i class="fas fa-cogs me-1"></i>Export Options
                                        </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_descriptions" id="includeDescriptions" checked>
                                            <label class="form-check-label" for="includeDescriptions">
                                                Include Report Descriptions
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="include_attachments_info" id="includeAttachments" checked>
                                            <label class="form-check-label" for="includeAttachments">
                                                Include Attachment Information
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="include_view_stats" id="includeViewStats" checked>
                                            <label class="form-check-label" for="includeViewStats">
                                                Include View Statistics
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="include_client_details" id="includeClientDetails" checked>
                                            <label class="form-check-label" for="includeClientDetails">
                                                Include Client Contact Details
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="include_project_details" id="includeProjectDetails" checked>
                                            <label class="form-check-label" for="includeProjectDetails">
                                                Include Project Details
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="include_creator_info" id="includeCreatorInfo" checked>
                                            <label class="form-check-label" for="includeCreatorInfo">
                                                Include Creator Information
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Export Actions -->
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-download me-2"></i>Generate & Download Export
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="previewExport()">
                                        <i class="fas fa-eye me-2"></i>Preview Data
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="fas fa-undo me-2"></i>Reset Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Export Summary & Help -->
                <div class="col-lg-4">
                    <!-- Export Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Export Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="exportSummary">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">Loading export summary...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Export Options -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Exports
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.progress-reports.export', ['quick' => 'all']) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>All Reports (Current Month)
                                </a>
                                <a href="{{ route('admin.progress-reports.export', ['quick' => 'sent']) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>Sent Reports Only
                                </a>
                                <a href="{{ route('admin.progress-reports.export', ['quick' => 'viewed']) }}" 
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-eye me-1"></i>Viewed Reports Only
                                </a>
                                @if(auth()->user()->role === 'pm')
                                    <a href="{{ route('pm.progress-reports.export') }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-user me-1"></i>My Reports Only
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Export Help -->
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-question-circle me-2"></i>Export Help
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="helpAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#csvHelp">
                                            CSV Format
                                        </button>
                                    </h2>
                                    <div id="csvHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <small>
                                                CSV files can be opened in Excel, Google Sheets, or any spreadsheet application. 
                                                Best for data analysis and importing into other systems.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterHelp">
                                            Using Filters
                                        </button>
                                    </h2>
                                    <div id="filterHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <small>
                                                Apply filters to export specific reports. Leave filters empty to export all available reports.
                                                Date ranges help limit exports to specific time periods.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#permissionHelp">
                                            Access Permissions
                                        </button>
                                    </h2>
                                    <div id="permissionHelp" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                                        <div class="accordion-body">
                                            <small>
                                                @if(auth()->user()->role === 'admin')
                                                    As an Administrator, you can export all progress reports or limit to your own reports.
                                                @else
                                                    As a Project Manager, you can only export progress reports you created.
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                        <p class="text-muted">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="proceedWithExport()">
                    <i class="fas fa-download me-1"></i>Proceed with Export
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-check-label {
        cursor: pointer;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e7f3ff;
        color: #0d6efd;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .export-summary-item {
        border-bottom: 1px solid #e9ecef;
        padding: 8px 0;
    }
    
    .export-summary-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadExportSummary();
    
    // Update summary when filters change
    document.querySelectorAll('#exportForm select, #exportForm input[type="date"]').forEach(element => {
        element.addEventListener('change', loadExportSummary);
    });

    // Form submission handling
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Export...';
        
        // Re-enable button after 5 seconds (in case of issues)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-download me-2"></i>Generate & Download Export';
        }, 5000);
    });
});

function loadExportSummary() {
    const formData = new FormData(document.getElementById('exportForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`{{ route('admin.progress-reports.api.stats') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateExportSummary(data);
        })
        .catch(error => {
            console.error('Error loading export summary:', error);
            document.getElementById('exportSummary').innerHTML = 
                '<div class="alert alert-warning">Unable to load export summary</div>';
        });
}

function updateExportSummary(data) {
    const summaryHtml = `
        <div class="export-summary-item d-flex justify-content-between">
            <span>Total Reports:</span>
            <strong>${data.total_reports || 0}</strong>
        </div>
        <div class="export-summary-item d-flex justify-content-between">
            <span>Recent Reports:</span>
            <strong class="text-info">${data.recent_reports || 0}</strong>
        </div>
        <div class="export-summary-item d-flex justify-content-between">
            <span>Sent Reports:</span>
            <strong class="text-warning">${data.sent_reports || 0}</strong>
        </div>
        <div class="export-summary-item d-flex justify-content-between">
            <span>Viewed Reports:</span>
            <strong class="text-success">${data.viewed_reports || 0}</strong>
        </div>
        <div class="export-summary-item d-flex justify-content-between">
            <span>With Attachments:</span>
            <strong class="text-primary">${data.reports_with_attachments || 0}</strong>
        </div>
        <div class="mt-3 text-center">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Based on current filters
            </small>
        </div>
    `;
    
    document.getElementById('exportSummary').innerHTML = summaryHtml;
}

function previewExport() {
    const formData = new FormData(document.getElementById('exportForm'));
    formData.append('preview', '1');
    const params = new URLSearchParams(formData);
    
    fetch(`{{ route('admin.progress-reports.export') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            showPreviewModal(data);
        })
        .catch(error => {
            console.error('Error loading preview:', error);
            alert('Unable to load preview. Please try again.');
        });
}

function showPreviewModal(data) {
    let previewHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
    
    // Headers
    if (data.headers) {
        previewHtml += '<thead class="table-dark"><tr>';
        data.headers.forEach(header => {
            previewHtml += `<th>${header}</th>`;
        });
        previewHtml += '</tr></thead>';
    }
    
    // Sample data (first 10 rows)
    if (data.sample_data) {
        previewHtml += '<tbody>';
        data.sample_data.slice(0, 10).forEach(row => {
            previewHtml += '<tr>';
            row.forEach(cell => {
                previewHtml += `<td>${cell || ''}</td>`;
            });
            previewHtml += '</tr>';
        });
        previewHtml += '</tbody>';
    }
    
    previewHtml += '</table></div>';
    
    if (data.total_rows > 10) {
        previewHtml += `<div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            Showing first 10 rows of ${data.total_rows} total rows.
        </div>`;
    }
    
    document.getElementById('previewContent').innerHTML = previewHtml;
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

function proceedWithExport() {
    // Close modal and submit form
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
    document.getElementById('exportForm').submit();
}

function resetForm() {
    document.getElementById('exportForm').reset();
    // Reset to default values
    document.getElementById('formatCSV').checked = true;
    @if(auth()->user()->role === 'admin')
        document.getElementById('scopeAll').checked = true;
    @endif
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
    loadExportSummary();
}
</script>
@endpush
@endsection