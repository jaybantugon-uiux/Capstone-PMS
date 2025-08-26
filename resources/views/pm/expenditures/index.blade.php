@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>My Daily Expenditures</h1>
            <p class="text-muted">Manage and submit your project-related expenses</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.expenditures.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-1"></i>New Expenditure
            </a>
            <button type="button" class="btn btn-primary" onclick="bulkSubmit()">
                <i class="fas fa-check-double me-1"></i>Bulk Submit
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="exportExpenditures()">
                <i class="fas fa-download me-1"></i>Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4>{{ $stats['total'] ?? 0 }}</h4>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $stats['draft'] ?? 0 }}</h4>
                    <small>Draft</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $stats['submitted'] ?? 0 }}</h4>
                    <small>Submitted</small>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h4>₱{{ number_format($stats['total_amount'] ?? 0, 0) }}</h4>
                    <small>Total Amount</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pm.expenditures.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>

                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $category)
                                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date Range</label>
                        <select name="date_range" class="form-select" onchange="this.form.submit()">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ request('date_range') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="card mb-4" id="bulkActionsCard" style="display: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selectedCount">0</span> items selected
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" onclick="bulkSubmit()">
                        <i class="fas fa-paper-plane me-1"></i>Submit Selected
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                        <i class="fas fa-times me-1"></i>Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenditures Table -->
    <div class="card">
        <div class="card-body">
            @if($expenditures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Description</th>
                                <th>Project</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Receipts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenditures as $expenditure)
                                <tr>
                                    <td>
                                        @if($expenditure->status === 'draft')
                                            <input type="checkbox" class="expenditure-checkbox" value="{{ $expenditure->id }}" onchange="updateSelection()">
                                        @else
                                            <input type="checkbox" disabled title="Only draft expenditures can be selected">
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ Str::limit($expenditure->description, 50) }}</strong>
                                            @if($expenditure->vendor_supplier)
                                                <br><small class="text-muted">{{ $expenditure->vendor_supplier }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($expenditure->project)
                                            <span class="badge bg-primary">{{ $expenditure->project->name }}</span>
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($expenditure->category) }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">₱{{ number_format($expenditure->amount, 0) }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($expenditure->payment_method) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $expenditure->status_badge_color }}">
                                            {{ $expenditure->formatted_status }}
                                        </span>
                                        @if($expenditure->status === 'rejected' && $expenditure->rejection_reason)
                                            <br><small class="text-danger">{{ Str::limit($expenditure->rejection_reason, 30) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <small>{{ $expenditure->formatted_expense_date }}</small>
                                            <br><small class="text-muted">{{ $expenditure->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($expenditure->receipts_count > 0)
                                            <span class="badge bg-success">
                                                <i class="fas fa-receipt me-1"></i>{{ $expenditure->receipts_count }}
                                            </span>
                                        @else
                                            <span class="text-muted">No receipts</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pm.expenditures.show', $expenditure) }}" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expenditure->canBeEdited())
                                                <a href="{{ route('pm.expenditures.edit', $expenditure) }}" class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($expenditure->status === 'draft')
                                                <button class="btn btn-outline-success" onclick="quickSubmit({{ $expenditure->id }})" title="Submit">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            @endif
                                            @if($expenditure->canBeDeleted())
                                                <button class="btn btn-outline-danger" onclick="deleteExpenditure({{ $expenditure->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing {{ $expenditures->firstItem() }} to {{ $expenditures->lastItem() }} of {{ $expenditures->total() }} results
                    </div>
                    <div>
                        {{ $expenditures->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No expenditures found</h4>
                    <p class="text-muted">Start by creating your first daily expenditure</p>
                    <a href="{{ route('pm.expenditures.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Create Expenditure
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Submit Modal -->
<div class="modal fade" id="quickSubmitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Expenditure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this expenditure? Once submitted, it cannot be edited.</p>
                <div id="expenditureDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmSubmit">Submit Expenditure</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table th {
    background-color: #f8f9fa;
    border-top: none;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
let selectedExpenditures = [];

function updateSelection() {
    const checkboxes = document.querySelectorAll('.expenditure-checkbox:checked:not(:disabled)');
    selectedExpenditures = Array.from(checkboxes).map(cb => cb.value);
    
    const bulkCard = document.getElementById('bulkActionsCard');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedExpenditures.length > 0) {
        bulkCard.style.display = 'block';
        selectedCount.textContent = selectedExpenditures.length;
    } else {
        bulkCard.style.display = 'none';
    }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.expenditure-checkbox:not(:disabled)');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelection();
}

function clearSelection() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.expenditure-checkbox:not(:disabled)');
    
    selectAll.checked = false;
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    
    selectedExpenditures = [];
    updateSelection();
}

function quickSubmit(expenditureId) {
    // Fetch expenditure details for modal
    fetch(`/pm/expenditures/${expenditureId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const expenditure = data.expenditure;
                document.getElementById('expenditureDetails').innerHTML = `
                    <div class="alert alert-info">
                        <strong>${expenditure.description}</strong><br>
                        <small>Amount: ₱${parseInt(expenditure.amount).toLocaleString()} | Project: ${expenditure.project ? expenditure.project.name : 'No Project'}</small>
                    </div>
                `;
                
                document.getElementById('confirmSubmit').onclick = function() {
                    submitExpenditure(expenditureId);
                };
                
                const modal = new bootstrap.Modal(document.getElementById('quickSubmitModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading expenditure details');
        });
}

function submitExpenditure(expenditureId) {
    fetch(`/pm/expenditures/${expenditureId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error submitting expenditure: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting expenditure');
    });
}

function bulkSubmit() {
    if (selectedExpenditures.length === 0) {
        alert('Please select expenditures to submit');
        return;
    }
    
    if (confirm(`Are you sure you want to submit ${selectedExpenditures.length} expenditures?`)) {
        fetch('{{ route("pm.expenditures.bulk-submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                expenditure_ids: selectedExpenditures
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error during bulk submission');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error during bulk submission');
        });
    }
}

function bulkDelete() {
    if (selectedExpenditures.length === 0) {
        alert('Please select expenditures to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedExpenditures.length} expenditures? This action cannot be undone.`)) {
        fetch('{{ route("pm.expenditures.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                expenditure_ids: selectedExpenditures
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error during bulk deletion');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error during bulk deletion');
        });
    }
}

function deleteExpenditure(expenditureId) {
    if (confirm('Are you sure you want to delete this expenditure? This action cannot be undone.')) {
        fetch(`/pm/expenditures/${expenditureId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting expenditure: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting expenditure');
        });
    }
}

function exportExpenditures() {
    // Get current filters
    const status = document.querySelector('select[name="status"]').value;
    const projectId = document.querySelector('select[name="project_id"]').value;
    const category = document.querySelector('select[name="category"]').value;
    const dateRange = document.querySelector('select[name="date_range"]').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (projectId) params.append('project_id', projectId);
    if (category) params.append('category', category);
    if (dateRange) params.append('date_range', dateRange);
    
    // Show export options
    const format = confirm('Export as CSV? Click OK for CSV, Cancel for PDF') ? 'csv' : 'pdf';
    params.append('format', format);
    
    // Show loading message
    const loadingMsg = document.createElement('div');
    loadingMsg.innerHTML = '<div class="alert alert-info">Exporting data, please wait...</div>';
    loadingMsg.style.position = 'fixed';
    loadingMsg.style.top = '50%';
    loadingMsg.style.left = '50%';
    loadingMsg.style.transform = 'translate(-50%, -50%)';
    loadingMsg.style.zIndex = '9999';
    loadingMsg.style.backgroundColor = 'white';
    loadingMsg.style.padding = '20px';
    loadingMsg.style.border = '1px solid #ccc';
    loadingMsg.style.borderRadius = '5px';
    document.body.appendChild(loadingMsg);
    
    // Make the export request
    fetch(`{{ route('pm.expenditures.export') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Remove loading message
        document.body.removeChild(loadingMsg);
        
        if (data.success) {
            if (format === 'csv') {
                try {
                    // Download CSV
                    const csvContent = atob(data.data.content);
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = data.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    // Show success message
                    alert('CSV export completed successfully!');
                } catch (error) {
                    console.error('CSV export error:', error);
                    alert('Error processing CSV export: ' + error.message);
                }
            } else {
                // For PDF, redirect to the PDF export route
                const pdfUrl = `{{ route('pm.expenditures.export.pdf') }}?${params.toString()}`;
                window.open(pdfUrl, '_blank');
            }
        } else {
            alert('Error exporting expenditures: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Remove loading message
        if (document.body.contains(loadingMsg)) {
            document.body.removeChild(loadingMsg);
        }
        
        console.error('Export error:', error);
        alert('Error exporting expenditures: ' + error.message);
    });
}

// Auto-refresh stats every 30 seconds
setInterval(function() {
    fetch('{{ route("pm.expenditures.stats") }}')
        .then(response => response.json())
        .then(data => {
            // Update stats display
            Object.keys(data).forEach(function(key) {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    element.textContent = data[key];
                }
            });
        })
        .catch(error => console.log('Error fetching stats:', error));
}, 30000);
</script>
@endpush
@endsection
