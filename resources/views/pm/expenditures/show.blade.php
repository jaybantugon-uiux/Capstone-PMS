@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Expenditure Details</h1>
            <p class="text-muted">View expenditure information and status</p>
        </div>
        <div class="d-flex gap-2">
            @if($expenditure->canBeEdited())
                <a href="{{ route('pm.expenditures.edit', $expenditure) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
            @if($expenditure->status === 'draft')
                <button class="btn btn-success" onclick="submitExpenditure()">
                    <i class="fas fa-paper-plane me-1"></i>Submit
                </button>
            @endif
            <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Expenditure Information</h5>
                    <span class="badge bg-{{ $expenditure->status_badge_color }} fs-6">
                        {{ $expenditure->formatted_status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Description</label>
                                <p class="mb-0">{{ $expenditure->description }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Amount</label>
                                <p class="mb-0 fs-4 text-success fw-bold">₱{{ number_format($expenditure->amount, 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Project</label>
                                <p class="mb-0">
                                    @if($expenditure->project)
                                        <span class="badge bg-primary">{{ $expenditure->project->name }}</span>
                                    @else
                                        <span class="text-muted">No Project Assigned</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Category</label>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ ucfirst($expenditure->category) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Payment Method</label>
                                <p class="mb-0">{{ ucfirst($expenditure->payment_method) }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Expense Date</label>
                                <p class="mb-0">{{ $expenditure->formatted_expense_date }}</p>
                            </div>
                        </div>
                    </div>

                    @if($expenditure->vendor_supplier)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Vendor/Supplier</label>
                                    <p class="mb-0">{{ $expenditure->vendor_supplier }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Reference Number</label>
                                    <p class="mb-0">{{ $expenditure->reference_number ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($expenditure->location)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Location</label>
                                    <p class="mb-0">{{ $expenditure->location }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($expenditure->notes)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Additional Notes</label>
                            <p class="mb-0">{{ $expenditure->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Receipts Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Receipts</h5>
                    <span class="badge bg-secondary">{{ $expenditure->receipts_count }} receipt(s)</span>
                </div>
                <div class="card-body">
                    @if($expenditure->receipts->count() > 0)
                        <div class="row">
                            @foreach($expenditure->receipts as $receipt)
                                <div class="col-md-4 mb-3">
                                    <div class="card border">
                                        <div class="card-body p-2">
                                            @if($receipt->is_image)
                                                <img src="{{ Storage::url($receipt->file_path) }}" 
                                                     class="img-fluid rounded" 
                                                     alt="Receipt"
                                                     style="max-height: 150px; object-fit: cover;">
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                    <p class="mb-0 mt-2 small">PDF Receipt</p>
                                                </div>
                                            @endif
                                            <div class="mt-2">
                                                <small class="text-muted d-block">{{ $receipt->vendor_name }}</small>
                                                <small class="text-success fw-bold">${{ number_format($receipt->amount, 2) }}</small>
                                                <div class="mt-1">
                                                    <a href="{{ route('pm.receipts.download', $receipt) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="{{ route('pm.receipts.show', $receipt) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No receipts uploaded for this expenditure</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status History Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Created</h6>
                                <p class="text-muted mb-0">{{ $expenditure->created_at->format('M d, Y g:i A') }}</p>
                                <small class="text-muted">by {{ $expenditure->submitter->name }}</small>
                            </div>
                        </div>

                        @if($expenditure->status !== 'draft')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Submitted</h6>
                                    <p class="text-muted mb-0">{{ $expenditure->updated_at->format('M d, Y g:i A') }}</p>
                                    <small class="text-muted">by {{ $expenditure->submitter->name }}</small>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Current Status</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $expenditure->status_badge_color }} fs-6">
                                {{ $expenditure->formatted_status }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Days Since Creation</label>
                        <p class="mb-0">{{ $expenditure->days_since_submission }} days</p>
                    </div>

                    @if($expenditure->status === 'submitted')
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Processing Time</label>
                            <p class="mb-0">
                                @if($expenditure->is_overdue)
                                    <span class="text-danger">Overdue</span>
                                @else
                                    <span class="text-warning">In Progress</span>
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($expenditure->is_liquidated)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Liquidation Status</label>
                            <p class="mb-0">
                                <span class="badge bg-success">Liquidated</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($expenditure->canBeEdited())
                            <a href="{{ route('pm.expenditures.edit', $expenditure) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit Expenditure
                            </a>
                        @endif

                        @if($expenditure->status === 'draft')
                            <button class="btn btn-success" onclick="submitExpenditure()">
                                <i class="fas fa-paper-plane me-1"></i>Submit for Review
                            </button>
                        @endif

                        @if($expenditure->canBeDeleted())
                            <button class="btn btn-danger" onclick="deleteExpenditure()">
                                <i class="fas fa-trash me-1"></i>Delete Expenditure
                            </button>
                        @endif

                        <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Related Information</h6>
                </div>
                <div class="card-body">
                    @if($expenditure->project)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Project Details</label>
                            <p class="mb-1"><strong>{{ $expenditure->project->name }}</strong></p>
                            <small class="text-muted">{{ $expenditure->project->description }}</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Submitter</label>
                        <p class="mb-0">{{ $expenditure->submitter->name }}</p>
                        <small class="text-muted">{{ $expenditure->submitter->email }}</small>
                    </div>

                    @if($expenditure->approver)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Approver</label>
                            <p class="mb-0">{{ $expenditure->approver->name }}</p>
                            <small class="text-muted">{{ $expenditure->approver->email }}</small>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Form Number</label>
                        <p class="mb-0">{{ $expenditure->form_number }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Expenditure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit this expenditure? Once submitted, it cannot be edited.</p>
                <div class="alert alert-info">
                    <strong>{{ $expenditure->description }}</strong><br>
                    <small>Amount: ${{ number_format($expenditure->amount, 2) }} | Project: {{ $expenditure->project ? $expenditure->project->name : 'No Project' }}</small>
                </div>
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
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.card {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.badge {
    font-size: 0.875em;
}
</style>
@endpush

@push('scripts')
<script>
function submitExpenditure() {
    const modal = new bootstrap.Modal(document.getElementById('submitModal'));
    modal.show();
}

document.getElementById('confirmSubmit').addEventListener('click', function() {
    fetch('{{ route("pm.expenditures.submit", $expenditure) }}', {
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
});

function deleteExpenditure() {
    if (confirm('Are you sure you want to delete this expenditure? This action cannot be undone.')) {
        fetch('{{ route("pm.expenditures.destroy", $expenditure) }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("pm.expenditures.index") }}';
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
</script>
@endpush
@endsection
