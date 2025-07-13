
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Export Task Reports</h1>
            <p class="text-muted">Generate and download task report data in various formats</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Export Options -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Export Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form id="exportForm">
                        @csrf
                        <!-- Format Selection -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Export Format</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="csv">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-csv fa-3x text-success mb-2"></i>
                                            <h6>CSV</h6>
                                            <small class="text-muted">Comma-separated values for Excel/Sheets</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="csv" id="formatCsv" checked>
                                                <label class="form-check-label" for="formatCsv">Select CSV</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="excel">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                            <h6>Excel</h6>
                                            <small class="text-muted">Native Excel format with formatting</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="excel" id="formatExcel">
                                                <label class="form-check-label" for="formatExcel">Select Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="pdf">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <h6>PDF</h6>
                                            <small class="text-muted">Formatted report for printing/sharing</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="pdf" id="formatPdf">
                                                <label class="form-check-label" for="formatPdf">Select PDF</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Date Range</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="{{ now()->subDays(30)->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quick Ranges</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(7)">Last 7 Days</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(30)">Last 30 Days</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(90)">Last 3 Months</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(365)">Last Year</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Filters</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Review Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="needs_revision">Needs Revision</option>
                                        <option value="reviewed">Reviewed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Project</label>
                                    <select name="project_id" class="form-select">
                                        <option value="">All Projects</option>
                                        @foreach($projects ?? [] as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Site Coordinator</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">All Coordinators</option>
                                        @foreach($siteCoordinators ?? [] as $coordinator)
                                            <option value="{{ $coordinator->id }}">{{ $coordinator->first_name }} {{ $coordinator->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating_filter" class="form-select">
                                        <option value="">All Ratings</option>
                                        <option value="5">5 Stars</option>
                                        <option value="4">4+ Stars</option>
                                        <option value="3">3+ Stars</option>
                                        <option value="unrated">Unrated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Selection -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Include Fields</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="basic_info" id="fieldBasicInfo" checked>
                                        <label class="form-check-label" for="fieldBasicInfo">
                                            <strong>Basic Information</strong>
                                            <br><small class="text-muted">Report title, date, coordinator, task</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="work_details" id="fieldWorkDetails" checked>
                                        <label class="form-check-label" for="fieldWorkDetails">
                                            <strong>Work Details</strong>
                                            <br><small class="text-muted">Description, progress, hours worked</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="review_info" id="fieldReviewInfo" checked>
                                        <label class="form-check-label" for="fieldReviewInfo">
                                            <strong>Review Information</strong>
                                            <br><small class="text-muted">Status, comments, rating, reviewer</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="materials" id="fieldMaterials">
                                        <label class="form-check-label" for="fieldMaterials"></label>
                                        
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Export Task Reports</h1>
            <p class="text-muted">Generate and download task report data in various formats</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.task-reports.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Export Options -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Export Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form id="exportForm">
                        @csrf
                        <!-- Format Selection -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Export Format</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="csv">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-csv fa-3x text-success mb-2"></i>
                                            <h6>CSV</h6>
                                            <small class="text-muted">Comma-separated values for Excel/Sheets</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="csv" id="formatCsv" checked>
                                                <label class="form-check-label" for="formatCsv">Select CSV</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="excel">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                            <h6>Excel</h6>
                                            <small class="text-muted">Native Excel format with formatting</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="excel" id="formatExcel">
                                                <label class="form-check-label" for="formatExcel">Select Excel</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border format-option" data-format="pdf">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                            <h6>PDF</h6>
                                            <small class="text-muted">Formatted report for printing/sharing</small>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="format" value="pdf" id="formatPdf">
                                                <label class="form-check-label" for="formatPdf">Select PDF</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Date Range</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="date_from" class="form-control" 
                                           value="{{ now()->subDays(30)->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="date_to" class="form-control" 
                                           value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quick Ranges</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(7)">Last 7 Days</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(30)">Last 30 Days</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(90)">Last 3 Months</button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange(365)">Last Year</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Filters</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Review Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="needs_revision">Needs Revision</option>
                                        <option value="reviewed">Reviewed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Project</label>
                                    <select name="project_id" class="form-select">
                                        <option value="">All Projects</option>
                                        @foreach($projects ?? [] as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Site Coordinator</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">All Coordinators</option>
                                        @foreach($siteCoordinators ?? [] as $coordinator)
                                            <option value="{{ $coordinator->id }}">{{ $coordinator->first_name }} {{ $coordinator->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating_filter" class="form-select">
                                        <option value="">All Ratings</option>
                                        <option value="5">5 Stars</option>
                                        <option value="4">4+ Stars</option>
                                        <option value="3">3+ Stars</option>
                                        <option value="unrated">Unrated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Selection -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Include Fields</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="basic_info" id="fieldBasicInfo" checked>
                                        <label class="form-check-label" for="fieldBasicInfo">
                                            <strong>Basic Information</strong>
                                            <br><small class="text-muted">Report title, date, coordinator, task</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="work_details" id="fieldWorkDetails" checked>
                                        <label class="form-check-label" for="fieldWorkDetails">
                                            <strong>Work Details</strong>
                                            <br><small class="text-muted">Description, progress, hours worked</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="review_info" id="fieldReviewInfo" checked>
                                        <label class="form-check-label" for="fieldReviewInfo">
                                            <strong>Review Information</strong>
                                            <br><small class="text-muted">Status, comments, rating, reviewer</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="materials" id="fieldMaterials">
                                        <label class="form-check-label" for="fieldMaterials">
                                         <strong>Materials & Equipment</strong>
                                            <br><small class="text-muted">Materials used, equipment used</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="issues" id="fieldIssues" checked>
                                        <label class="form-check-label" for="fieldIssues">
                                            <strong>Issues & Solutions</strong>
                                            <br><small class="text-muted">Issues encountered, next steps</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="photos" id="fieldPhotos">
                                        <label class="form-check-label" for="fieldPhotos">
                                            <strong>Photo Information</strong>
                                            <br><small class="text-muted">Photo count, file paths</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="weather" id="fieldWeather">
                                        <label class="form-check-label" for="fieldWeather">
                                            <strong>Weather Conditions</strong>
                                            <br><small class="text-muted">Weather during work</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="additional_notes" id="fieldNotes">
                                        <label class="form-check-label" for="fieldNotes">
                                            <strong>Additional Notes</strong>
                                            <br><small class="text-muted">Extra comments and observations</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Options -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Advanced Options</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_photos_zip" id="includePhotosZip">
                                        <label class="form-check-label" for="includePhotosZip">
                                            Include photos as ZIP file
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="group_by_project" id="groupByProject" checked>
                                        <label class="form-check-label" for="groupByProject">
                                            Group reports by project
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="include_summary" id="includeSummary" checked>
                                        <label class="form-check-label" for="includeSummary">
                                            Include summary statistics
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label">Sort Order</label>
                                        <select name="sort_order" class="form-select">
                                            <option value="newest_first" selected>Newest First</option>
                                            <option value="oldest_first">Oldest First</option>
                                            <option value="project_name">By Project Name</option>
                                            <option value="coordinator_name">By Coordinator Name</option>
                                            <option value="rating_desc">By Rating (High to Low)</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">File Name</label>
                                        <input type="text" name="filename" class="form-control" 
                                               placeholder="Custom filename (optional)"
                                               value="task_reports_{{ now()->format('Y_m_d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted" id="estimatedSize">Estimated file size: <strong>~</strong></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="previewExport()">
                                    <i class="fas fa-eye me-1"></i>Preview
                                </button>
                                <button type="submit" class="btn btn-primary" id="generateBtn">
                                    <i class="fas fa-download me-1"></i>Generate Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Export History & Templates -->
        <div class="col-lg-4">
            <!-- Recent Exports -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Recent Exports</h6>
                </div>
                <div class="card-body">
                    <div id="recentExports">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <small class="fw-medium">task_reports_2024_01_15.csv</small>
                                <br><small class="text-muted">Jan 15, 2024 • 247 KB</small>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <small class="fw-medium">monthly_summary_dec.xlsx</small>
                                <br><small class="text-muted">Jan 02, 2024 • 1.2 MB</small>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <small class="fw-medium">project_alpha_reports.pdf</small>
                                <br><small class="text-muted">Dec 28, 2023 • 892 KB</small>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-secondary btn-sm">View All Exports</a>
                    </div>
                </div>
            </div>

            <!-- Export Templates -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Export Templates</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadTemplate('weekly')">
                            <i class="fas fa-calendar-week me-1"></i>Weekly Summary
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadTemplate('monthly')">
                            <i class="fas fa-calendar-alt me-1"></i>Monthly Report
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadTemplate('project')">
                            <i class="fas fa-project-diagram me-1"></i>Project Specific
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadTemplate('performance')">
                            <i class="fas fa-chart-line me-1"></i>Performance Review
                        </button>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="saveAsTemplate()">
                            <i class="fas fa-save me-1"></i>Save Current as Template
                        </button>
                    </div>
                </div>
            </div>

            <!-- Export Statistics -->
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Export Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Reports Available:</span>
                            <strong id="totalReports">{{ $totalReports ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>With Current Filters:</span>
                            <strong id="filteredReports">{{ $totalReports ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Exports This Month:</span>
                            <strong>7</strong>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span>Total Data Size:</span>
                            <strong>~4.2 MB</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Progress Modal -->
<div class="modal fade" id="exportProgressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generating Export</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Progress</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted" id="progressStatus">Initializing export...</small>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Large exports may take several minutes to complete. Please don't close this window.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelExport">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Export Summary</h6>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Format:</small> <strong id="previewFormat">CSV</strong><br>
                            <small class="text-muted">Date Range:</small> <strong id="previewDateRange">Last 30 days</strong><br>
                            <small class="text-muted">Records:</small> <strong id="previewRecords">0</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Fields:</small> <strong id="previewFields">7</strong><br>
                            <small class="text-muted">File Size:</small> <strong id="previewSize">~247 KB</strong><br>
                            <small class="text-muted">Estimated Time:</strong> <strong>< 1 minute</strong>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>Sample Data</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr id="previewHeaders">
                                    <!-- Headers will be populated by JavaScript -->
                                </tr>
                            </thead>
                            <tbody id="previewData">
                                <!-- Sample data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="proceedWithExport()">
                    <i class="fas fa-download me-1"></i>Generate Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Export Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Template Name</label>
                    <input type="text" class="form-control" id="templateName" placeholder="Enter template name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description (Optional)</label>
                    <textarea class="form-control" id="templateDescription" rows="2" 
                              placeholder="Describe when to use this template"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">
                    <i class="fas fa-save me-1"></i>Save Template
                </button>
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

.format-option {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.format-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.format-option.selected {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.progress {
    height: 8px;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.form-check-input {
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
}

.alert {
    border-radius: 0.5rem;
}

.table-sm th,
.table-sm td {
    padding: 0.25rem 0.5rem;
}

.d-grid {
    gap: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize export form
    initializeExportForm();
    
    // Update estimated file size when options change
    updateEstimatedSize();
    
    // Load initial data
    updateFilteredCount();
});

function initializeExportForm() {
    // Format selection handlers
    document.querySelectorAll('.format-option').forEach(option => {
        option.addEventListener('click', function() {
            const format = this.dataset.format;
            const radio = document.getElementById(`format${format.charAt(0).toUpperCase() + format.slice(1)}`);
            radio.checked = true;
            
            // Update selected styling
            document.querySelectorAll('.format-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            updateEstimatedSize();
        });
    });

    // Form submission handler
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        startExport();
    });

    // Update filtered count when filters change
    ['status', 'project_id', 'user_id', 'rating_filter', 'date_from', 'date_to'].forEach(field => {
        const element = document.querySelector(`[name="${field}"]`);
        if (element) {
            element.addEventListener('change', updateFilteredCount);
        }
    });

    // Update estimated size when fields change
    document.querySelectorAll('input[name="fields[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateEstimatedSize);
    });
}

function setDateRange(days) {
    const toDate = new Date();
    const fromDate = new Date();
    fromDate.setDate(toDate.getDate() - days);
    
    document.querySelector('[name="date_from"]').value = fromDate.toISOString().split('T')[0];
    document.querySelector('[name="date_to"]').value = toDate.toISOString().split('T')[0];
    
    updateFilteredCount();
}

function updateFilteredCount() {
    const formData = new FormData(document.getElementById('exportForm'));
    
    fetch('{{ route("pm.task-reports.export.count") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('filteredReports').textContent = data.count || 0;
        updateEstimatedSize();
    })
    .catch(error => {
        console.error('Error getting filtered count:', error);
    });
}

function updateEstimatedSize() {
    const format = document.querySelector('input[name="format"]:checked').value;
    const fieldsCount = document.querySelectorAll('input[name="fields[]"]:checked').length;
    const recordsCount = parseInt(document.getElementById('filteredReports').textContent) || 0;
    
    let baseSize = recordsCount * fieldsCount * 50; // rough estimate in bytes
    
    // Adjust for format
    switch (format) {
        case 'excel':
            baseSize *= 1.5;
            break;
        case 'pdf':
            baseSize *= 3;
            break;
    }
    
    // Add photos if included
    if (document.getElementById('includePhotosZip').checked) {
        baseSize += recordsCount * 500000; // assume ~500KB per report with photos
    }
    
    const sizeText = formatFileSize(baseSize);
    document.getElementById('estimatedSize').innerHTML = `Estimated file size: <strong>${sizeText}</strong>`;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function previewExport() {
    const formData = new FormData(document.getElementById('exportForm'));
    formData.append('preview', 'true');
    
    fetch('{{ route("pm.task-reports.export.preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        populatePreviewModal(data);
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error generating preview:', error);
        alert('Error generating preview');
    });
}

function populatePreviewModal(data) {
    document.getElementById('previewFormat').textContent = data.format.toUpperCase();
    document.getElementById('previewDateRange').textContent = data.date_range;
    document.getElementById('previewRecords').textContent = data.record_count;
    document.getElementById('previewFields').textContent = data.field_count;
    document.getElementById('previewSize').textContent = data.estimated_size;
    
    // Populate headers
    const headersRow = document.getElementById('previewHeaders');
    headersRow.innerHTML = data.headers.map(header => `<th>${header}</th>`).join('');
    
    // Populate sample data
    const dataBody = document.getElementById('previewData');
    dataBody.innerHTML = data.sample_rows.map(row => 
        `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`
    ).join('');
}

function proceedWithExport() {
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();
    startExport();
}

function startExport() {
    const modal = new bootstrap.Modal(document.getElementById('exportProgressModal'));
    modal.show();
    
    const formData = new FormData(document.getElementById('exportForm'));
    
    // Start export process
    fetch('{{ route("pm.task-reports.export") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        } else {
            throw new Error('Export failed');
        }
    })
    .then(blob => {
        // Download the file
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = getFileName();
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Close modal and show success
        modal.hide();
        showAlert('success', 'Export completed successfully!');
        
        // Add to recent exports list
        addToRecentExports(getFileName());
    })
    .catch(error => {
        console.error('Export error:', error);
        modal.hide();
        showAlert('danger', 'Export failed. Please try again.');
    });
    
    // Simulate progress (replace with actual progress tracking if available)
    simulateProgress();
}

function simulateProgress() {
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressStatus = document.getElementById('progressStatus');
    
    const statuses = [
        'Initializing export...',
        'Fetching report data...',
        'Processing filters...',
        'Formatting data...',
        'Generating file...',
        'Finalizing export...'
    ];
    
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.round(progress) + '%';
        
        const statusIndex = Math.floor(progress / 20);
        if (statuses[statusIndex]) {
            progressStatus.textContent = statuses[statusIndex];
        }
        
        if (progress >= 100) {
            clearInterval(interval);
        }
    }, 500);
}

function getFileName() {
    const customName = document.querySelector('[name="filename"]').value;
    const format = document.querySelector('input[name="format"]:checked').value;
    const extension = format === 'excel' ? 'xlsx' : format;
    
    return customName ? `${customName}.${extension}` : `export.${extension}`;
}

function loadTemplate(templateType) {
    const templates = {
        weekly: {
            date_from: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            date_to: new Date().toISOString().split('T')[0],
            fields: ['basic_info', 'work_details', 'review_info'],
            format: 'excel',
            group_by_project: true,
            include_summary: true
        },
        monthly: {
            date_from: new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1).toISOString().split('T')[0],
            date_to: new Date().toISOString().split('T')[0],
            fields: ['basic_info', 'work_details', 'review_info', 'issues'],
            format: 'pdf',
            group_by_project: true,
            include_summary: true
        },
        project: {
            fields: ['basic_info', 'work_details', 'materials', 'photos'],
            format: 'csv',
            group_by_project: true
        },
        performance: {
            fields: ['basic_info', 'review_info'],
            format: 'excel',
            sort_order: 'rating_desc'
        }
    };
    
    const template = templates[templateType];
    if (template) {
        // Apply template settings to form
        Object.keys(template).forEach(key => {
            if (key === 'fields') {
                // Clear all field checkboxes first
                document.querySelectorAll('input[name="fields[]"]').forEach(cb => cb.checked = false);
                // Check template fields
                template.fields.forEach(field => {
                    const checkbox = document.querySelector(`input[value="${field}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            } else {
                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = template[key];
                    } else {
                        element.value = template[key];
                    }
                }
            }
        });
        
        updateEstimatedSize();
        updateFilteredCount();
        showAlert('info', `${templateType.charAt(0).toUpperCase() + templateType.slice(1)} template loaded successfully!`);
    }
}

function saveAsTemplate() {
    const modal = new bootstrap.Modal(document.getElementById('saveTemplateModal'));
    modal.show();
}

function saveTemplate() {
    const name = document.getElementById('templateName').value;
    const description = document.getElementById('templateDescription').value;
    
    if (!name) {
        alert('Please enter a template name');
        return;
    }
    
    const formData = new FormData(document.getElementById('exportForm'));
    formData.append('template_name', name);
    formData.append('template_description', description);
    
    fetch('{{ route("pm.task-reports.export.save-template") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('saveTemplateModal')).hide();
            showAlert('success', 'Template saved successfully!');
            // Clear modal inputs
            document.getElementById('templateName').value = '';
            document.getElementById('templateDescription').value = '';
        } else {
            alert('Error saving template: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving template:', error);
        alert('Error saving template');
    });
}

function addToRecentExports(filename) {
    // Add to recent exports list in UI
    const recentExports = document.getElementById('recentExports');
    const newExport = document.createElement('div');
    newExport.className = 'd-flex align-items-center justify-content-between mb-2';
    newExport.innerHTML = `
        <div>
            <small class="fw-medium">${filename}</small>
            <br><small class="text-muted">Just now • Processing...</small>
        </div>
        <button class="btn btn-outline-primary btn-sm" disabled>
            <i class="fas fa-spinner fa-spin"></i>
        </button>
    `;
    recentExports.insertBefore(newExport, recentExports.firstChild);
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'danger' ? 'exclamation-circle' : 'info-circle')} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush
@endsection