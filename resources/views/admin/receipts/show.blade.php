@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Receipt Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('admin.receipts.edit', $receipt) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit Receipt
            </a>
            @if($receipt->file_path)
                <a href="{{ Storage::url($receipt->file_path) }}" class="btn btn-success btn-sm" target="_blank">
                    <i class="fas fa-download"></i> Download
                </a>
            @endif
            <button type="button" class="btn btn-danger btn-sm" onclick="deleteReceipt()">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Receipt Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Receipt Number:</strong></td>
                                    <td>{{ $receipt->receipt_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor Name:</strong></td>
                                    <td>{{ $receipt->vendor_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><span class="text-success font-weight-bold">₱{{ number_format($receipt->amount, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tax Amount:</strong></td>
                                    <td>{{ $receipt->tax_amount > 0 ? '₱' . number_format($receipt->tax_amount, 2) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Amount:</strong></td>
                                    <td><span class="text-primary font-weight-bold">₱{{ number_format($receipt->amount + $receipt->tax_amount, 2) }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Receipt Type:</strong></td>
                                    <td><span class="badge badge-info">{{ $receipt->receipt_type }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge badge-{{ $receipt->status_badge_color }}">{{ $receipt->formatted_status }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Receipt Date:</strong></td>
                                    <td>{{ $receipt->receipt_date ? $receipt->receipt_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded By:</strong></td>
                                    <td>{{ $receipt->uploader ? $receipt->uploader->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded On:</strong></td>
                                    <td>{{ $receipt->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($receipt->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Description:</strong></h6>
                                <p class="text-muted">{{ $receipt->description }}</p>
                            </div>
                        </div>
                    @endif

                    @if($receipt->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Notes:</strong></h6>
                                <p class="text-muted">{{ $receipt->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Clarification Information -->
            @if($receipt->clarification_status !== 'none')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Clarification Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><span class="badge badge-{{ $receipt->clarification_status_badge_color }}">{{ $receipt->formatted_clarification_status }}</span></td>
                                    </tr>
                                    @if($receipt->clarificationRequester)
                                        <tr>
                                            <td><strong>Requested By:</strong></td>
                                            <td>{{ $receipt->clarificationRequester->name }}</td>
                                        </tr>
                                    @endif
                                    @if($receipt->clarification_requested_at)
                                        <tr>
                                            <td><strong>Requested At:</strong></td>
                                            <td>{{ $receipt->clarification_requested_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        
                        @if($receipt->clarification_notes)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6><strong>Clarification Notes:</strong></h6>
                                    <div class="alert alert-warning">
                                        {{ $receipt->clarification_notes }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- File Information -->
            @if($receipt->file_path)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">File Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Original Filename:</strong></td>
                                        <td>{{ $receipt->original_file_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>File Size:</strong></td>
                                        <td>{{ $receipt->file_size ? number_format($receipt->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>File Type:</strong></td>
                                        <td>{{ $receipt->file_type ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    @if(in_array(strtolower(pathinfo($receipt->original_file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ Storage::url($receipt->file_path) }}" 
                                             alt="Receipt Image" 
                                             class="img-fluid rounded" 
                                             style="max-height: 200px;">
                                    @else
                                        <div class="bg-light p-4 rounded">
                                            <i class="fas fa-file fa-3x text-muted"></i>
                                            <p class="mt-2 text-muted">{{ $receipt->original_file_name }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Linked Liquidated Form -->
            @if($receipt->liquidatedForm)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Linked Liquidated Form</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Form Number:</strong></td>
                                <td>{{ $receipt->liquidatedForm->form_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Title:</strong></td>
                                <td>{{ $receipt->liquidatedForm->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-{{ $receipt->liquidatedForm->status_badge_color }}">{{ $receipt->liquidatedForm->formatted_status }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td>₱{{ number_format($receipt->liquidatedForm->total_amount, 2) }}</td>
                            </tr>
                        </table>
                        <a href="{{ route('admin.liquidated-forms.show', $receipt->liquidatedForm) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View Form
                        </a>
                    </div>
                </div>
            @endif

            <!-- Linked Financial Report -->
            @if($receipt->financialReport)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Linked Financial Report</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Report Number:</strong></td>
                                <td>{{ $receipt->financialReport->report_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Title:</strong></td>
                                <td>{{ $receipt->financialReport->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge badge-{{ $receipt->financialReport->status_badge_color }}">{{ $receipt->financialReport->formatted_status }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td>₱{{ number_format($receipt->financialReport->total_amount, 2) }}</td>
                            </tr>
                        </table>
                        <a href="{{ route('admin.financial-reports.show', $receipt->financialReport) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.receipts.edit', $receipt) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Receipt
                        </a>
                        @if($receipt->canRequestClarification())
                            <a href="{{ route('admin.receipts.request-clarification.form', $receipt) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-question-circle"></i> Request Clarification
                            </a>
                        @endif
                        @if($receipt->clarification_status === 'requested')
                            <a href="{{ route('admin.receipts.resolve-clarification.form', $receipt) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Resolve Clarification
                            </a>
                        @endif
                        @if($receipt->file_path)
                            <a href="{{ Storage::url($receipt->file_path) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        @endif
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteReceipt()">
                            <i class="fas fa-trash"></i> Delete Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Created</h6>
                                <p class="timeline-text">{{ $receipt->created_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">by {{ $receipt->uploader ? $receipt->uploader->name : 'System' }}</small>
                            </div>
                        </div>
                        @if($receipt->updated_at != $receipt->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Last Updated</h6>
                                    <p class="timeline-text">{{ $receipt->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this receipt? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.receipts.destroy', $receipt) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteReceipt() {
    $('#deleteModal').modal('show');
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
    background: #f8f9fc;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #4e73df;
}

.timeline-text {
    margin: 0;
    color: #5a5c69;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -29px;
    top: 12px;
    width: 2px;
    height: calc(100% + 8px);
    background: #e3e6f0;
}
</style>
@endpush
