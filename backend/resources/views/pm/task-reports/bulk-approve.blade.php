@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Bulk Approve Task Reports</h1>
            <p class="text-muted">Efficiently approve multiple task reports with standardized feedback</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.task-reports.bulk-review') }}" class="btn btn-outline-info">
                <i class="fas fa-edit me-1"></i>Bulk Review
            </a>
            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Reports
            </a>
        </div>
    </div>

    <!-- Approval Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-check-circle me-2 text-success"></i>Approval Configuration
            </h5>
        </div>
        <div class="card-body">
            <form id="bulkApprovalForm">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Approval Type <span class="text-danger">*</span></label>
                        <select name="approval_type" class="form-select" required>
                            <option value="">Select Approval Type</option>
                            <option value="standard">Standard Approval</option>
                            <option value="conditional">Conditional Approval</option>
                            <option value="provisional">Provisional Approval</option>
                            <option value="final">Final Approval</option>
                        </select>
                        <small class="text-muted">Choose the type of approval to apply</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default Rating</label>
                        <select name="default_rating" class="form-select">
                            <option value="">No Default Rating</option>
                            <option value="5">★★★★★ Excellent (5/5)</option>
                            <option value="4">★★★★☆ Good (4/5)</option>
                            <option value="3">★★★☆☆ Average (3/5)</option>
                            <option value="2">★★☆☆☆ Poor (2/5)</option>
                            <option value="1">★☆☆☆☆ Very Poor (1/5)</option>
                        </select>
                        <small class="text-muted">Applied to reports without individual ratings</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority Filter</label>
                        <select id="priorityFilter" class="form-select">
                            <option value="all">All Reports</option>
                            <option value="high_quality">High Quality Only (4-5★)</option>
                            <option value="no_issues">No Issues Reported</option>
                            <option value="complete">100% Complete</option>
                            <option value="recent">Last 24 Hours</option>
                            <option value="overdue">Overdue for Review</option>
                        </select>
                        <small class="text-muted">Filter reports by criteria</small>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Approval Comments</label>
                        <textarea name="approval_comments" class="form-control" rows="4" 
                                  placeholder="Enter standard approval comments that will be applied to all selected reports..."></textarea>
                        <div class="mt-2">
                            <small class="text-muted">Quick Templates:</small>
                            <div class="btn-group-sm mt-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('excellent')">Excellent Work</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('good')">Good Progress</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('standard')">Meets Standards</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('continue')">Continue Work</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Additional Instructions</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="send_notifications" id="sendNotifications" checked>
                            <label class="form-check-label" for="sendNotifications">
                                Send notification emails to site coordinators
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="auto_close_tasks" id="autoCloseTasks">
                            <label class="form-check-label" for="autoCloseTasks">
                                Auto-close completed tasks (100% progress)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="create_follow_up" id="createFollowUp">
                            <label class="form-check-label" for="createFollowUp">
                                Create follow-up tasks for incomplete work
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="update_project_status" id="updateProjectStatus" checked>
                            <label class="form-check-label" for="updateProjectStatus">
                                Update project completion status
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Reports Selection -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Select Reports for Approval</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                                    <i class="fas fa-check-square me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectNoneBtn">
                                    <i class="fas fa-square me-1"></i>Select None
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="selectFilteredBtn">
                                    <i class="fas fa-filter me-1"></i>Select Filtered
                                </button>
                            </div>
                            <span class="badge bg-success fs-6" id="selectedCount">0 selected</span>
                            <span class="badge bg-info fs-6" id="totalReports">0 total</span>
                        </div>
                    </div>

                    <!-- Search and Filter Bar -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="searchReports" placeholder="Search reports, coordinators, or projects...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="projectFilter">
                                <option value="">All Projects</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="coordinatorFilter">
                                <option value="">All Coordinators</option>
                                @foreach($siteCoordinators ?? [] as $coordinator)
                                    <option value="{{ $coordinator->id }}">{{ $coordinator->first_name }} {{ $coordinator->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="dateFilter">
                                <option value="">All Dates</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                                <i class="fas fa-times me-1"></i>Clear
                            </button>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="reportsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                    </th>
                                    <th>Report Details</th>
                                    <th>Coordinator</th>
                                    <th>Project</th>
                                    <th>Progress</th>
                                    <th>Quality</th>
                                    <th>Issues</th>
                                    <th>Submitted</th>
                                    <th>Individual Rating</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                <!-- Reports will be loaded via AJAX -->
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Loading reports...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading pending reports for approval...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Approval Summary -->
                <div class="card bg-light mb-4" id="approvalSummary" style="display: none;">
                    <div class="card-body">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-info-circle me-2"></i>Approval Summary
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success mb-1" id="summaryCount">0</h4>
                                    <small class="text-muted">Reports to Approve</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info mb-1" id="summaryCoordinators">0</h4>
                                    <small class="text-muted">Coordinators Affected</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning mb-1" id="summaryProjects">0</h4>
                                    <small class="text-muted">Projects Affected</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary mb-1" id="summaryAvgProgress">0%</h4>
                                    <small class="text-muted">Avg Progress</small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Estimated processing time: <span id="estimatedTime">< 1 minute</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-info me-2" onclick="previewApproval()">
                            <i class="fas fa-eye me-1"></i>Preview Approval
                        </button>
                        <span class="text-muted">Select reports and configure approval settings above</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBulkApproval" disabled>
                            <i class="fas fa-check-circle me-1"></i>Approve Selected Reports
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approval Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Review the approval details before proceeding.</strong> This action will approve all selected reports with the configured settings.
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Approval Configuration</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Type:</strong></td><td id="previewApprovalType">-</td></tr>
                            <tr><td><strong>Default Rating:</strong></td><td id="previewDefaultRating">-</td></tr>
                            <tr><td><strong>Reports Count:</strong></td><td id="previewReportsCount">-</td></tr>
                            <tr><td><strong>Send Notifications:</strong></td><td id="previewNotifications">-</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Impact Summary</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Coordinators:</strong></td><td id="previewCoordinatorsList">-</td></tr>
                            <tr><td><strong>Projects:</strong></td><td id="previewProjectsList">-</td></tr>
                            <tr><td><strong>Avg Progress:</strong></td><td id="previewAvgProgress">-</td></tr>
                            <tr><td><strong>Issues Reports:</strong></td><td id="previewIssuesCount">-</td></tr>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Approval Comments</h6>
                    <div class="border rounded p-2 bg-light">
                        <small id="previewComments" class="text-muted">No comments specified</small>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Selected Reports Sample (First 10)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Coordinator</th>
                                    <th>Progress</th>
                                    <th>Individual Rating</th>
                                </tr>
                            </thead>
                            <tbody id="previewReportsList">
                                <!-- Sample reports will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="proceedWithApproval()">
                    <i class="fas fa-check-circle me-1"></i>Proceed with Approval
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Processing Approvals</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Progress</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted" id="progressStatus">Initializing approval process...</small>
                </div>
                <div class="mb-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <h6 class="text-success mb-1" id="approvedCount">0</h6>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-warning mb-1" id="processingCount">0</h6>
                            <small class="text-muted">Processing</small>
                        </div>
                        <div class="col-4">
                            <h6 class="text-danger mb-1" id="failedCount">0</h6>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please don't close this window while approvals are being processed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelProcess" disabled>Cancel Process</button>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approval Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Bulk approval completed!</strong> <span id="resultsSuccessCount">0</span> reports have been successfully approved.
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-success mb-1" id="resultsTotalApproved">0</h4>
                            <small class="text-muted">Successfully Approved</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-info mb-1" id="resultsNotificationsSent">0</h4>
                            <small class="text-muted">Notifications Sent</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-warning mb-1" id="resultsProjectsUpdated">0</h4>
                            <small class="text-muted">Projects Updated</small>
                        </div>
                    </div>
                </div>

                <div id="resultsFailures" class="mb-3" style="display: none;">
                    <h6 class="text-danger">Failed Approvals</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Coordinator</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="resultsFailuresList">
                                <!-- Failed items will be listed here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Next Steps</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Site coordinators have been notified</li>
                        <li><i class="fas fa-check text-success me-2"></i>Project statuses have been updated</li>
                        <li><i class="fas fa-check text-success me-2"></i>Task completion rates recalculated</li>
                        <li><i class="fas fa-info-circle text-info me-2"></i>Consider reviewing project timelines for completed tasks</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('pm.task-reports.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i>View All Reports
                </a>
                <a href="{{ route('pm.task-reports.analytics') }}" class="btn btn-outline-info">
                    <i class="fas fa-chart-line me-1"></i>View Analytics
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.table th {
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
}

.progress {
    height: 8px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
    font-weight: bold;
}

.table-hover tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.alert {
    border-radius: 0.5rem;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
}

.fs-6 {
    font-size: 1rem !important;
}

.input-group-text {
    background-color: #f8f9fa;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.border {
    border: 1px solid #dee2e6 !important;
}

.rounded {
    border-radius: 0.375rem !important;
}

.text-success {
    color: #198754 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-info {
    color: #0dcaf0 !important;
}

.text-primary {
    color: #0d6efd !important;
}

.text-muted {
    color: #6c757d !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let allReports = [];
    let selectedReports = [];
    let filteredReports = [];

    // Load reports on page load
    loadReports();

    // Event listeners
    document.getElementById('priorityFilter').addEventListener('change', applyFilters);
    document.getElementById('searchReports').addEventListener('input', applyFilters);
    document.getElementById('projectFilter').addEventListener('change', applyFilters);
    document.getElementById('coordinatorFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFilter').addEventListener('change', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearAllFilters);

    document.getElementById('selectAllBtn').addEventListener('click', selectAllReports);
    document.getElementById('selectNoneBtn').addEventListener('click', selectNoReports);
    document.getElementById('selectFilteredBtn').addEventListener('click', selectFilteredReports);
    document.getElementById('selectAllCheckbox').addEventListener('change', toggleAllSelection);

    document.getElementById('bulkApprovalForm').addEventListener('submit', handleFormSubmission);

    // Template insertion buttons
    window.insertTemplate = function(type) {
        const templates = {
            excellent: "Excellent work on this task report! Your attention to detail and thorough documentation is commendable. Keep up the outstanding performance.",
            good: "Good progress on this task. The work quality meets our standards and shows steady advancement toward project completion.",
            standard: "This report meets our project standards. Continue with the current approach and maintain quality consistency.",
            continue: "Report approved. Please continue with the next phase of work as outlined in the project timeline."
        };
        
        const textarea = document.querySelector('textarea[name="approval_comments"]');
        textarea.value = templates[type];
    };

    function loadReports() {
        const tbody = document.getElementById('reportsTableBody');
        
        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading pending reports...</p>
                </td>
            </tr>
        `;

        // Fetch pending reports
        fetch('{{ route("pm.task-reports.index") }}?status=pending&format=json')
            .then(response => response.json())
            .then(data => {
                allReports = data.reports || [];
                filteredReports = [...allReports];
                displayReports(filteredReports);
                updateCounts();
            })
            .catch(error => {
                console.error('Error loading reports:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Error loading reports. Please refresh the page.</p>
                        </td>
                    </tr>
                `;
            });
    }

    function displayReports(reports) {
        const tbody = document.getElementById('reportsTableBody');
        
        if (reports.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-clipboard-check fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No reports match the current filters.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = reports.map(report => `
            <tr class="${report.is_overdue ? 'table-warning' : ''} ${selectedReports.includes(report.id) ? 'table-success' : ''}" data-report-id="${report.id}">
                <td>
                    <input type="checkbox" class="form-check-input report-checkbox" 
                           value="${report.id}" ${selectedReports.includes(report.id) ? 'checked' : ''} 
                           onchange="updateSelection()">
                </td>
                <td>
                    <div>
                        <strong>${truncateText(report.report_title, 40)}</strong>
                        ${report.is_overdue ? '<span class="badge bg-danger ms-1">Overdue</span>' : ''}
                        ${report.has_issues ? '<span class="badge bg-warning ms-1"><i class="fas fa-exclamation-triangle"></i></span>' : ''}
                        ${report.photos_count > 0 ? `<span class="badge bg-info ms-1"><i class="fas fa-camera"></i> ${report.photos_count}</span>` : ''}
                    </div>
                    <small class="text-muted">${formatDate(report.report_date)} • Task: ${truncateText(report.task_name, 30)}</small>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                            ${report.coordinator_initials}
                        </div>
                        <div>
                            <small class="fw-medium">${report.coordinator_name}</small>
                            <br><small class="text-muted">Site Coordinator</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${truncateText(report.project_name, 25)}</strong>
                        <br><small class="text-muted">${report.project_status}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 60px; height: 8px;">
                            <div class="progress-bar bg-${getProgressColor(report.progress_percentage)}" 
                                 style="width: ${report.progress_percentage}%"></div>
                        </div>
                        <small class="fw-medium">${report.progress_percentage}%</small>
                    </div>
                    <small class="text-muted">${report.hours_worked}h worked</small>
                </td>
                <td>
                    <div class="text-center">
                        ${report.estimated_quality ? `<span class="badge bg-${getQualityColor(report.estimated_quality)}">${report.estimated_quality}</span>` : '<span class="text-muted">-</span>'}
                        <br><small class="text-muted">Auto-assessed</small>
                    </div>
                </td>
                <td>
                    <div class="text-center">
                        ${report.has_issues ? 
                            `<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> ${report.issues_count}</span>` : 
                            '<span class="badge bg-success"><i class="fas fa-check"></i> None</span>'
                        }
                    </div>
                </td>
                <td>
                    <div>
                        <small class="fw-medium">${report.submitted_ago}</small>
                        <br><small class="text-muted">${formatDateTime(report.created_at)}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <select class="form-select form-select-sm individual-rating" data-report-id="${report.id}">
                            <option value="">Use Default</option>
                            <option value="5">★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select>
                    </div>
                </td>
            </tr>
        `).join('');

        // Add event listeners to individual rating selects
        document.querySelectorAll('.individual-rating').forEach(select => {
            select.addEventListener('change', updateApprovalSummary);
        });
    }

    function applyFilters() {
        const priority = document.getElementById('priorityFilter').value;
        const search = document.getElementById('searchReports').value.toLowerCase();
        const project = document.getElementById('projectFilter').value;
        const coordinator = document.getElementById('coordinatorFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;

        filteredReports = allReports.filter(report => {
            // Priority filter
            if (priority && !matchesPriorityFilter(report, priority)) return false;
            
            // Search filter
            if (search && !matchesSearch(report, search)) return false;
            
            // Project filter
            if (project && report.project_id != project) return false;
            
            // Coordinator filter
            if (coordinator && report.user_id != coordinator) return false;
            
            // Date filter
            if (dateFilter && !matchesDateFilter(report, dateFilter)) return false;
            
            return true;
        });

        displayReports(filteredReports);
        updateCounts();
    }

    function matchesPriorityFilter(report, priority) {
        switch(priority) {
            case 'high_quality':
                return report.estimated_quality >= 4;
            case 'no_issues':
                return !report.has_issues;
            case 'complete':
                return report.progress_percentage >= 100;
            case 'recent':
                return new Date(report.created_at) > new Date(Date.now() - 24*60*60*1000);
            case 'overdue':
                return report.is_overdue;
            default:
                return true;
        }
    }

    function matchesSearch(report, search) {
        return report.report_title.toLowerCase().includes(search) ||
               report.coordinator_name.toLowerCase().includes(search) ||
               report.project_name.toLowerCase().includes(search) ||
               report.task_name.toLowerCase().includes(search);
    }

    function matchesDateFilter(report, dateFilter) {
        const reportDate = new Date(report.created_at);
        const now = new Date();
        
        switch(dateFilter) {
            case 'today':
                return reportDate.toDateString() === now.toDateString();
            case 'yesterday':
                const yesterday = new Date(now - 24*60*60*1000);
                return reportDate.toDateString() === yesterday.toDateString();
            case 'week':
                const weekAgo = new Date(now - 7*24*60*60*1000);
                return reportDate > weekAgo;
            case 'month':
                const monthAgo = new Date(now - 30*24*60*60*1000);
                return reportDate > monthAgo;
            default:
                return true;
        }
    }

    function clearAllFilters() {
        document.getElementById('priorityFilter').value = '';
        document.getElementById('searchReports').value = '';
        document.getElementById('projectFilter').value = '';
        document.getElementById('coordinatorFilter').value = '';
        document.getElementById('dateFilter').value = '';
        applyFilters();
    }

    function selectAllReports() {
        selectedReports = [...allReports.map(r => r.id)];
        updateDisplayAndSummary();
    }

    function selectNoReports() {
        selectedReports = [];
        updateDisplayAndSummary();
    }

    function selectFilteredReports() {
        selectedReports = [...filteredReports.map(r => r.id)];
        updateDisplayAndSummary();
    }

    function toggleAllSelection() {
        const isChecked = document.getElementById('selectAllCheckbox').checked;
        const visibleReportIds = filteredReports.map(r => r.id);
        
        if (isChecked) {
            // Add all visible reports to selection
            visibleReportIds.forEach(id => {
                if (!selectedReports.includes(id)) {
                    selectedReports.push(id);
                }
            });
        } else {
            // Remove all visible reports from selection
            selectedReports = selectedReports.filter(id => !visibleReportIds.includes(id));
        }
        
        updateDisplayAndSummary();
    }

    window.updateSelection = function() {
        const checkboxes = document.querySelectorAll('.report-checkbox');
        selectedReports = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value));
        
        updateDisplayAndSummary();
    };

    function updateDisplayAndSummary() {
        // Update checkboxes
        document.querySelectorAll('.report-checkbox').forEach(cb => {
            cb.checked = selectedReports.includes(parseInt(cb.value));
        });

        // Update row highlighting
        document.querySelectorAll('#reportsTable tbody tr').forEach(row => {
            const reportId = parseInt(row.dataset.reportId);
            if (selectedReports.includes(reportId)) {
                row.classList.add('table-success');
            } else {
                row.classList.remove('table-success');
            }
        });

        // Update master checkbox
        const allVisibleIds = filteredReports.map(r => r.id);
        const selectedVisibleIds = selectedReports.filter(id => allVisibleIds.includes(id));
        const masterCheckbox = document.getElementById('selectAllCheckbox');
        
        if (selectedVisibleIds.length === 0) {
            masterCheckbox.indeterminate = false;
            masterCheckbox.checked = false;
        } else if (selectedVisibleIds.length === allVisibleIds.length) {
            masterCheckbox.indeterminate = false;
            masterCheckbox.checked = true;
        } else {
            masterCheckbox.indeterminate = true;
        }

        updateCounts();
        updateApprovalSummary();
    }

    function updateCounts() {
        document.getElementById('selectedCount').textContent = `${selectedReports.length} selected`;
        document.getElementById('totalReports').textContent = `${filteredReports.length} total`;
        
        // Enable/disable submit button
        const submitBtn = document.getElementById('submitBulkApproval');
        submitBtn.disabled = selectedReports.length === 0;
    }

    function updateApprovalSummary() {
        const selectedReportData = allReports.filter(r => selectedReports.includes(r.id));
        
        if (selectedReportData.length === 0) {
            document.getElementById('approvalSummary').style.display = 'none';
            return;
        }

        document.getElementById('approvalSummary').style.display = 'block';
        
        // Calculate summary statistics
        const uniqueCoordinators = new Set(selectedReportData.map(r => r.user_id)).size;
        const uniqueProjects = new Set(selectedReportData.map(r => r.project_id)).size;
        const avgProgress = selectedReportData.reduce((sum, r) => sum + r.progress_percentage, 0) / selectedReportData.length;
        
        document.getElementById('summaryCount').textContent = selectedReportData.length;
        document.getElementById('summaryCoordinators').textContent = uniqueCoordinators;
        document.getElementById('summaryProjects').textContent = uniqueProjects;
        document.getElementById('summaryAvgProgress').textContent = `${avgProgress.toFixed(1)}%`;
        
        // Estimate processing time
        const estimatedMinutes = Math.ceil(selectedReportData.length / 10); // Assume 10 reports per minute
        document.getElementById('estimatedTime').textContent = 
            estimatedMinutes < 1 ? '< 1 minute' : `~${estimatedMinutes} minute${estimatedMinutes > 1 ? 's' : ''}`;
    }

    window.previewApproval = function() {
        if (selectedReports.length === 0) {
            alert('Please select at least one report to preview.');
            return;
        }

        const selectedData = allReports.filter(r => selectedReports.includes(r.id));
        const formData = new FormData(document.getElementById('bulkApprovalForm'));
        
        // Populate preview modal
        document.getElementById('previewApprovalType').textContent = 
            formData.get('approval_type') || 'Not specified';
        document.getElementById('previewDefaultRating').textContent = 
            formData.get('default_rating') ? `${formData.get('default_rating')} stars` : 'No default rating';
        document.getElementById('previewReportsCount').textContent = selectedData.length;
        document.getElementById('previewNotifications').textContent = 
            formData.get('send_notifications') ? 'Yes' : 'No';
        
        // Impact summary
        const coordinators = [...new Set(selectedData.map(r => r.coordinator_name))];
        const projects = [...new Set(selectedData.map(r => r.project_name))];
        
        document.getElementById('previewCoordinatorsList').textContent = 
            coordinators.length > 3 ? `${coordinators.slice(0, 3).join(', ')} +${coordinators.length - 3} more` : coordinators.join(', ');
        document.getElementById('previewProjectsList').textContent = 
            projects.length > 3 ? `${projects.slice(0, 3).join(', ')} +${projects.length - 3} more` : projects.join(', ');
        
        const avgProgress = selectedData.reduce((sum, r) => sum + r.progress_percentage, 0) / selectedData.length;
        document.getElementById('previewAvgProgress').textContent = `${avgProgress.toFixed(1)}%`;
        
        const issuesCount = selectedData.filter(r => r.has_issues).length;
        document.getElementById('previewIssuesCount').textContent = `${issuesCount} reports`;
        
        // Comments preview
        const comments = formData.get('approval_comments') || '';
        document.getElementById('previewComments').textContent = 
            comments || 'No comments specified';
        
        // Sample reports list
        const sampleReports = selectedData.slice(0, 10);
        const sampleHtml = sampleReports.map(report => {
            const individualRating = document.querySelector(`select[data-report-id="${report.id}"]`)?.value || '';
            return `
                <tr>
                    <td>${truncateText(report.report_title, 30)}</td>
                    <td>${report.coordinator_name}</td>
                    <td>${report.progress_percentage}%</td>
                    <td>${individualRating ? `${individualRating} stars` : 'Use default'}</td>
                </tr>
            `;
        }).join('');
        
        document.getElementById('previewReportsList').innerHTML = sampleHtml;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    };

    window.proceedWithApproval = function() {
        bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
        document.getElementById('bulkApprovalForm').dispatchEvent(new Event('submit'));
    };

    function handleFormSubmission(e) {
        e.preventDefault();
        
        if (selectedReports.length === 0) {
            alert('Please select at least one report to approve.');
            return;
        }

        const formData = new FormData(e.target);
        if (!formData.get('approval_type')) {
            alert('Please select an approval type.');
            return;
        }

        // Collect individual ratings
        const individualRatings = {};
        document.querySelectorAll('.individual-rating').forEach(select => {
            if (select.value && selectedReports.includes(parseInt(select.dataset.reportId))) {
                individualRatings[select.dataset.reportId] = select.value;
            }
        });

        // Prepare approval data
        const approvalData = {
            report_ids: selectedReports,
            approval_type: formData.get('approval_type'),
            default_rating: formData.get('default_rating'),
            approval_comments: formData.get('approval_comments'),
            send_notifications: formData.get('send_notifications') === 'on',
            auto_close_tasks: formData.get('auto_close_tasks') === 'on',
            create_follow_up: formData.get('create_follow_up') === 'on',
            update_project_status: formData.get('update_project_status') === 'on',
            individual_ratings: individualRatings
        };

        // Show progress modal
        const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
        progressModal.show();

        // Start approval process
        performBulkApproval(approvalData);
    }

    function performBulkApproval(approvalData) {
        let processed = 0;
        let approved = 0;
        let failed = 0;
        const total = approvalData.report_ids.length;

        const updateProgress = (status) => {
            processed++;
            if (status === 'approved') approved++;
            if (status === 'failed') failed++;

            const percentage = Math.round((processed / total) * 100);
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressPercent').textContent = percentage + '%';
            document.getElementById('approvedCount').textContent = approved;
            document.getElementById('processingCount').textContent = total - processed;
            document.getElementById('failedCount').textContent = failed;
            document.getElementById('progressStatus').textContent = 
                `Processing report ${processed} of ${total}...`;
        };

        // Simulate batch processing (replace with actual API calls)
        const processInBatches = async () => {
            const batchSize = 5;
            const batches = [];
            
            for (let i = 0; i < approvalData.report_ids.length; i += batchSize) {
                batches.push(approvalData.report_ids.slice(i, i + batchSize));
            }

            for (const batch of batches) {
                try {
                    const response = await fetch('{{ route("pm.task-reports.bulk-approve") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            ...approvalData,
                            report_ids: batch
                        })
                    });

                    const result = await response.json();
                    
                    batch.forEach(reportId => {
                        updateProgress(result.success ? 'approved' : 'failed');
                    });
                    
                    // Small delay between batches
                    await new Promise(resolve => setTimeout(resolve, 500));
                    
                } catch (error) {
                    console.error('Batch processing error:', error);
                    batch.forEach(reportId => {
                        updateProgress('failed');
                    });
                }
            }

            // Show results
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('progressModal')).hide();
                showResults(approved, failed, total);
            }, 1000);
        };

        processInBatches();
    }

    function showResults(approved, failed, total) {
        document.getElementById('resultsSuccessCount').textContent = approved;
        document.getElementById('resultsTotalApproved').textContent = approved;
        document.getElementById('resultsNotificationsSent').textContent = approved; // Assume notifications sent for approved
        document.getElementById('resultsProjectsUpdated').textContent = 
            new Set(allReports.filter(r => selectedReports.includes(r.id)).map(r => r.project_id)).size;

        if (failed > 0) {
            document.getElementById('resultsFailures').style.display = 'block';
            // You would populate the failures list here with actual failure data
        }

        const resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
        resultsModal.show();
    }

    // Utility functions
    function truncateText(text, length) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function getProgressColor(percentage) {
        if (percentage >= 100) return 'success';
        if (percentage >= 75) return 'info';
        if (percentage >= 50) return 'warning';
        return 'danger';
    }

    function getQualityColor(quality) {
        if (quality >= 4) return 'success';
        if (quality >= 3) return 'warning';
        return 'danger';
    }
});
</script>
@endpush
@endsection