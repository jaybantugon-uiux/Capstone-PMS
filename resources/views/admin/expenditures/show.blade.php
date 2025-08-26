@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Expenditure Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenditures.edit', $expenditure) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-danger btn-sm" onclick="deleteExpenditure({{ $expenditure->id }})">
                <i class="fas fa-trash"></i> Delete
            </button>
            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.expenditures.index') }}">Daily Expenditures</a></li>
            <li class="breadcrumb-item active" aria-current="page">View</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Expenditure Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Expenditure Information</h6>
                    <div>
                        <span class="badge badge-{{ $expenditure->status_badge_color }} badge-lg">
                            {{ $expenditure->formatted_status }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>#{{ $expenditure->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>{{ $expenditure->project ? $expenditure->project->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Submitter:</strong></td>
                                    <td>{{ $expenditure->submitter ? $expenditure->submitter->full_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Expense Date:</strong></td>
                                    <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><span class="badge badge-info">{{ $expenditure->category }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ $expenditure->payment_method }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td class="font-weight-bold text-success">₱{{ number_format($expenditure->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>{{ $expenditure->location ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor/Supplier:</strong></td>
                                    <td>{{ $expenditure->vendor_supplier ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reference Number:</strong></td>
                                    <td>{{ $expenditure->reference_number ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $expenditure->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $expenditure->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Description:</strong></h6>
                            <p class="text-muted">{{ $expenditure->description }}</p>
                        </div>
                    </div>

                    @if($expenditure->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Notes:</strong></h6>
                                <p class="text-muted">{{ $expenditure->notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($expenditure->status === 'rejected' && $expenditure->rejection_reason)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Rejection Reason:</strong></h6>
                                <p class="text-danger">{{ $expenditure->rejection_reason }}</p>
                            </div>
                        </div>
                    @endif

                    @if($expenditure->status === 'approved' && $expenditure->approval_notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Approval Notes:</strong></h6>
                                <p class="text-success">{{ $expenditure->approval_notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Receipts Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-receipt"></i> Associated Receipts
                    </h6>
                </div>
                <div class="card-body">
                    @if($expenditure->receipts && $expenditure->receipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Receipt Number</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenditure->receipts as $receipt)
                                        <tr>
                                            <td>{{ $receipt->receipt_number }}</td>
                                            <td>₱{{ number_format($receipt->amount, 2) }}</td>
                                            <td>{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $receipt->status === 'approved' ? 'success' : ($receipt->status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($receipt->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <p>No receipts associated with this expenditure</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($expenditure->status !== 'approved')
                            <button type="button" class="btn btn-success btn-sm" onclick="changeStatus('approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        @endif
                        @if($expenditure->status !== 'rejected')
                            <button type="button" class="btn btn-danger btn-sm" onclick="changeStatus('rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        @endif
                        @if($expenditure->status !== 'submitted')
                            <button type="button" class="btn btn-info btn-sm" onclick="changeStatus('submitted')">
                                <i class="fas fa-paper-plane"></i> Mark as Submitted
                            </button>
                        @endif
                        @if($expenditure->status !== 'draft')
                            <button type="button" class="btn btn-secondary btn-sm" onclick="changeStatus('draft')">
                                <i class="fas fa-edit"></i> Mark as Draft
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Project Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-project-diagram"></i> Project Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($expenditure->project)
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $expenditure->project->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $expenditure->project->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($expenditure->project->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td>{{ $expenditure->project->start_date ? $expenditure->project->start_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>End Date:</strong></td>
                                <td>{{ $expenditure->project->end_date ? $expenditure->project->end_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted text-center">No project associated</p>
                    @endif
                </div>
            </div>

            <!-- Submitter Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i> Submitter Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($expenditure->submitter)
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $expenditure->submitter->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $expenditure->submitter->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($expenditure->submitter->role) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge badge-{{ $expenditure->submitter->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($expenditure->submitter->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    @else
                        <p class="text-muted text-center">No submitter information</p>
                    @endif
                </div>
            </div>

            <!-- Audit Trail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Audit Trail
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Created</h6>
                                <p class="timeline-text">{{ $expenditure->created_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">by {{ $expenditure->submitter ? $expenditure->submitter->full_name : 'System' }}</small>
                            </div>
                        </div>
                        
                        @if($expenditure->submitted_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Submitted</h6>
                                    <p class="timeline-text">{{ $expenditure->submitted_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($expenditure->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Approved</h6>
                                    <p class="timeline-text">{{ $expenditure->approved_at->format('M d, Y H:i') }}</p>
                                    @if($expenditure->approvedBy)
                                        <small class="text-muted">by {{ $expenditure->approvedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Last Updated</h6>
                                <p class="timeline-text">{{ $expenditure->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="newStatus">New Status</label>
                    <select id="newStatus" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group" id="reasonGroup" style="display: none;">
                    <label for="statusReason">Reason</label>
                    <textarea id="statusReason" class="form-control" rows="3" 
                              placeholder="Please provide a reason for the status change..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmStatusChange()">Update Status</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let expenditureId = {{ $expenditure->id }};

document.addEventListener('DOMContentLoaded', function() {
    // Status change modal events
    document.getElementById('newStatus').addEventListener('change', function() {
        const reasonGroup = document.getElementById('reasonGroup');
        if (this.value === 'rejected') {
            reasonGroup.style.display = 'block';
        } else {
            reasonGroup.style.display = 'none';
        }
    });
});

function changeStatus(status) {
    document.getElementById('newStatus').value = status;
    if (status === 'rejected') {
        document.getElementById('reasonGroup').style.display = 'block';
    } else {
        document.getElementById('reasonGroup').style.display = 'none';
    }
    $('#statusChangeModal').modal('show');
}

function confirmStatusChange() {
    const newStatus = document.getElementById('newStatus').value;
    const reason = document.getElementById('statusReason').value;
    
    if (newStatus === 'rejected' && !reason.trim()) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    const formData = new FormData();
    formData.append('status', newStatus);
    if (reason) {
        formData.append('reason', reason);
    }
    
    fetch(`/admin/expenditures/${expenditureId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    })
    .finally(() => {
        $('#statusChangeModal').modal('hide');
    });
}

function deleteExpenditure(id) {
    if (confirm('Are you sure you want to delete this expenditure? This action cannot be undone.')) {
        fetch(`/admin/expenditures/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '{{ route("admin.expenditures.index") }}';
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the expenditure.');
        });
    }
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: bold;
}

.timeline-text {
    margin-bottom: 5px;
    color: #666;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e3e6f0;
}
</style>
@endpush
