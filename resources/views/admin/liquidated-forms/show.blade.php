@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Liquidated Form Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.liquidated-forms.edit', $liquidatedForm) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit Form
            </a>
            <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Form Information -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Form Number:</strong></td>
                            <td>{{ $liquidatedForm->form_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Title:</strong></td>
                            <td>{{ $liquidatedForm->title }}</td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>{{ $liquidatedForm->description ?: 'No description provided' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-{{ $liquidatedForm->status_badge_color }}">
                                    {{ $liquidatedForm->formatted_status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Project:</strong></td>
                            <td>
                                @if($liquidatedForm->project)
                                    <a href="{{ route('projects.show', $liquidatedForm->project) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-project-diagram"></i> {{ $liquidatedForm->project->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No Project</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Preparer:</strong></td>
                            <td>
                                @if($liquidatedForm->preparer)
                                    {{ $liquidatedForm->preparer->full_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Reviewer:</strong></td>
                            <td>
                                @if($liquidatedForm->reviewer)
                                    {{ $liquidatedForm->reviewer->full_name }}
                                @else
                                    <span class="text-muted">Not reviewed yet</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Liquidation Date:</strong></td>
                            <td>{{ $liquidatedForm->liquidation_date ? $liquidatedForm->liquidation_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Period Covered:</strong></td>
                            <td>{{ $liquidatedForm->formatted_period }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $liquidatedForm->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <!-- Financial Report Information -->
    @if($liquidatedForm->financialReport)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Linked Financial Report</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Report Title:</strong><br>
                {{ $liquidatedForm->financialReport->title }}
            </div>
            <div class="mb-3">
                <strong>Period:</strong><br>
                {{ $liquidatedForm->financialReport->formatted_period }}
            </div>
            <div class="mb-3">
                <strong>Status:</strong><br>
                <span class="badge badge-{{ $liquidatedForm->financialReport->status_badge_color }}">
                    {{ $liquidatedForm->financialReport->formatted_status }}
                </span>
            </div>
            <a href="{{ route('admin.financial-reports.show', $liquidatedForm->financialReport) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> View Full Report
            </a>
        </div>
    </div>
    @endif

    <!-- Linked Receipts from Financial Report -->
    @if($liquidatedForm->financialReport && $liquidatedForm->financialReport->directReceipts->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Receipts from Financial Report</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($liquidatedForm->financialReport->directReceipts as $receipt)
                <div class="col-md-6 mb-3">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">{{ Str::limit($receipt->original_file_name, 25) }}</h6>
                                    <p class="card-text text-muted mb-1">
                                        {{ $receipt->vendor_name }} - {{ $receipt->formatted_amount }}
                                    </p>
                                    <small class="text-muted">
                                        {{ $receipt->formatted_receipt_date }}
                                    </small>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('admin.receipts.download', $receipt) }}"
                                       class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="{{ route('admin.receipts.show', $receipt) }}"
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Related Expenditures -->
    @if($liquidatedForm->expenditures->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Related Expenditures ({{ $liquidatedForm->expenditures->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Submitter</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liquidatedForm->expenditures as $expenditure)
                        <tr>
                            <td>{{ $expenditure->description }}</td>
                            <td>{{ $expenditure->category }}</td>
                            <td>₱{{ number_format($expenditure->amount, 2) }}</td>
                            <td>{{ $expenditure->submitter->full_name }}</td>
                            <td>
                                <span class="badge badge-{{ $expenditure->status_badge_color }}">
                                    {{ $expenditure->formatted_status }}
                                </span>
                            </td>
                            <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($liquidatedForm->notes)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
        </div>
        <div class="card-body">
            <p>{{ $liquidatedForm->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Flag Information -->
    @if($liquidatedForm->status === 'flagged')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">
                <i class="fas fa-flag"></i> Flagged Form
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Flagged By:</strong></td>
                            <td>{{ $liquidatedForm->flaggedBy->full_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Flagged Date:</strong></td>
                            <td>{{ $liquidatedForm->flagged_at ? $liquidatedForm->flagged_at->format('M d, Y g:i A') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Priority:</strong></td>
                            <td>
                                <span class="badge badge-{{ $liquidatedForm->flag_priority_color }}">
                                    {{ ucfirst($liquidatedForm->flag_priority ?? 'medium') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Flag Reason:</strong></td>
                            <td>{{ $liquidatedForm->flag_reason ?: 'No reason provided' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Days Flagged:</strong></td>
                            <td>{{ $liquidatedForm->days_since_flagged }} days</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @if($liquidatedForm->is_overdue)
                                    <span class="badge badge-danger">Overdue</span>
                                @else
                                    <span class="badge badge-warning">Active</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif



    <!-- Actions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.liquidated-forms.edit', $liquidatedForm) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Form
                </a>
                
                @if($liquidatedForm->status === 'pending' || $liquidatedForm->status === 'flagged')
                <a href="{{ route('admin.liquidated-forms.request-revision.form', $liquidatedForm) }}" class="btn btn-info">
                    <i class="fas fa-redo"></i> Request Revision
                </a>
                @endif
                
                @if($liquidatedForm->status === 'flagged')
                <button type="button" class="btn btn-success unflag-btn" data-form-id="{{ $liquidatedForm->id }}">
                    <i class="fas fa-flag-checkered"></i> Unflag Form
                </button>
                @endif
                
                <button type="button" class="btn btn-danger" onclick="deleteForm({{ $liquidatedForm->id }})">
                    <i class="fas fa-trash"></i> Delete Form
                </button>
                <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
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
                Are you sure you want to delete this liquidated form? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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
$(document).ready(function() {
    // Unflag button
    $('.unflag-btn').click(function() {
        const formId = $(this).data('form-id');
        if (confirm('Are you sure you want to unflag this form?')) {
            $.post(`/admin/liquidated-forms/${formId}/admin-unflag`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Unflag request failed:', xhr.responseText);
                alert('An error occurred while unflagging the form. Please try again.');
            });
        }
    });
});

function deleteForm(formId) {
    if (confirm('Are you sure you want to delete this liquidated form?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/liquidated-forms/${formId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
