@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Liquidated Forms</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.suspicious-activities') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-exclamation-triangle"></i> Flag Suspicious Activities
            </a>
            <a href="{{ route('finance.liquidated-forms.export.csv') }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm" method="GET" action="{{ route('finance.liquidated-forms.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                
                                <option value="flagged" {{ request('status') == 'flagged' ? 'selected' : '' }}>Flagged</option>
                                <option value="clarification_requested" {{ request('status') == 'clarification_requested' ? 'selected' : '' }}>Clarification Requested</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="project_id">Project</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">All Projects</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="preparer_id">Preparer</label>
                            <select name="preparer_id" id="preparer_id" class="form-control">
                                <option value="">All Preparers</option>
                                @foreach($preparers ?? [] as $preparer)
                                    <option value="{{ $preparer->id }}" {{ request('preparer_id') == $preparer->id ? 'selected' : '' }}>
                                        {{ $preparer->first_name }} {{ $preparer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Form #, title, description...">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period_from">Period From</label>
                            <input type="date" name="period_from" id="period_from" class="form-control" 
                                   value="{{ request('period_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period_to">Period To</label>
                            <input type="date" name="period_to" id="period_to" class="form-control" 
                                   value="{{ request('period_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liquidated Forms Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Liquidated Forms</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm" id="bulkFlagBtn" disabled>
                    <i class="fas fa-flag"></i> Flag Selected
                </button>
                <button type="button" class="btn btn-info btn-sm" id="bulkPrintBtn" disabled>
                    <i class="fas fa-print"></i> Print Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(isset($liquidatedForms) && $liquidatedForms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Form #</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Preparer</th>

                                <th>Status</th>
                                <th>Liquidation Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidatedForms as $form)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-checkbox" value="{{ $form->id }}">
                                </td>
                                <td>{{ $form->form_number }}</td>
                                <td>{{ Str::limit($form->title, 50) }}</td>
                                <td>{{ $form->project->name ?? 'N/A' }}</td>
                                <td>{{ $form->preparer->first_name ?? 'N/A' }} {{ $form->preparer->last_name ?? '' }}</td>

                                <td>
                                    @if($form->status)
                                        <span class="badge badge-{{ $form->status_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $form->status)) }}
                                        </span>
                                        @if($form->flagged_at)
                                            <i class="fas fa-flag text-danger ml-1" title="Flagged on {{ $form->flagged_at->format('M d, Y') }}"></i>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">No Status</span>
                                    @endif
                                </td>
                                <td>{{ $form->liquidation_date->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('finance.liquidated-forms.show', $form) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($form->status === 'pending')
                                        <a href="{{ route('finance.liquidated-forms.edit', $form) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('finance.liquidated-forms.print', $form) }}" 
                                           class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($form->status === 'flagged')
                                        <button type="button" class="btn btn-sm btn-success unflag-btn" 
                                                data-form-id="{{ $form->id }}" title="Unflag">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger flag-btn" 
                                                data-form-id="{{ $form->id }}" title="Flag (Status: {{ $form->status }})">
                                            <i class="fas fa-flag"></i>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $liquidatedForms->firstItem() }} to {{ $liquidatedForms->lastItem() }} 
                        of {{ $liquidatedForms->total() }} entries
                    </div>
                    <div>
                        {{ $liquidatedForms->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No liquidated forms found matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Flag Modal -->
<div class="modal fade" id="flagModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Flag Liquidated Form
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="flagForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Flagging a form will mark it for review and notify administrators.
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_reason" class="font-weight-bold">
                            Reason for Flagging <span class="text-danger">*</span>
                        </label>
                        <textarea name="flag_reason" id="flag_reason" class="form-control" 
                                  rows="4" required 
                                  placeholder="Please provide a detailed reason for flagging this form. Be specific about the issues or concerns..."></textarea>
                        <small class="form-text text-muted">This reason will be visible to administrators and may be used for audit purposes.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_priority" class="font-weight-bold">Priority Level</label>
                        <select name="flag_priority" id="flag_priority" class="form-control">
                            <option value="low">Low - Minor issues that need attention</option>
                            <option value="medium" selected>Medium - Standard review required</option>
                            <option value="high">High - Urgent attention needed</option>
                            <option value="critical">Critical - Immediate action required</option>
                        </select>
                        <small class="form-text text-muted">Select the appropriate priority level based on the severity of the issue.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_notes" class="font-weight-bold">Additional Notes (Optional)</label>
                        <textarea name="flag_notes" id="flag_notes" class="form-control" 
                                  rows="2" 
                                  placeholder="Any additional notes or recommendations..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-flag"></i> Flag Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAll').change(function() {
        $('.form-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkButtons();
    });

    $('.form-checkbox').change(function() {
        updateBulkButtons();
    });

    function updateBulkButtons() {
        const checkedCount = $('.form-checkbox:checked').length;
        $('#bulkFlagBtn, #bulkPrintBtn').prop('disabled', checkedCount === 0);
    }

    // Individual flag button
    $('.flag-btn').click(function() {
        const formId = $(this).data('form-id');
        const button = $(this);
        
        // Check if form can be flagged
        if (button.attr('title') && button.attr('title').includes('Status:')) {
            const status = button.attr('title').match(/Status: (.+)\)/)[1];
            if (status !== 'pending') {
                alert('This form cannot be flagged. Current status: ' + status);
                return;
            }
        }
        
        console.log('Flag button clicked for form ID:', formId);
        $('#flagForm').data('form-id', formId);
        $('#flagModal').modal('show');
    });

    // Unflag button
    $('.unflag-btn').click(function() {
        const formId = $(this).data('form-id');
        const button = $(this);
        
        if (confirm('Are you sure you want to unflag this form? This will change the status back to pending.')) {
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.post(`/finance/liquidated-forms/${formId}/unflag`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                    button.prop('disabled', false).html('<i class="fas fa-flag-checkered"></i>');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Unflag request failed:', xhr.responseText);
                let errorMessage = 'An error occurred while unflagging the form.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to unflag this form.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Form not found.';
                }
                
                alert(errorMessage);
                button.prop('disabled', false).html('<i class="fas fa-flag-checkered"></i>');
            });
        }
    });

    // Flag form submission
    $('#flagForm').submit(function(e) {
        e.preventDefault();
        const formId = $(this).data('form-id');
        const reason = $('#flag_reason').val();
        const priority = $('#flag_priority').val();
        const notes = $('#flag_notes').val();
        const submitBtn = $(this).find('button[type="submit"]');

        if (!reason.trim()) {
            alert('Please provide a reason for flagging.');
            return;
        }

        console.log('Submitting flag request for form ID:', formId, 'with reason:', reason);

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Flagging...');

        $.post(`/finance/liquidated-forms/${formId}/flag`, {
            _token: '{{ csrf_token() }}',
            flag_reason: reason,
            flag_priority: priority,
            flag_notes: notes
        })
        .done(function(response) {
            console.log('Flag response:', response);
            if (response.success) {
                $('#flagModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Unknown error occurred'));
                submitBtn.prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Form');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Flag request failed:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            
            let errorMessage = 'An error occurred while flagging the form.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 403) {
                errorMessage = 'You do not have permission to flag this form.';
            } else if (xhr.status === 404) {
                errorMessage = 'Form not found.';
            } else if (xhr.status === 422) {
                errorMessage = 'Validation error. Please check your input.';
            }
            
            alert(errorMessage);
            submitBtn.prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Form');
        });
    });

    // Bulk flag
    $('#bulkFlagBtn').click(function() {
        const selectedIds = $('.form-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select forms to flag.');
            return;
        }

        const reason = prompt('Enter reason for flagging:');
        if (!reason) return;

        const priority = prompt('Enter priority level (low/medium/high/critical):', 'medium');
        if (!priority || !['low', 'medium', 'high', 'critical'].includes(priority.toLowerCase())) {
            alert('Invalid priority level. Using medium priority.');
            priority = 'medium';
        }

        const button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Flagging...');

        $.post('/finance/liquidated-forms/bulk-flag', {
            _token: '{{ csrf_token() }}',
            liquidated_form_ids: selectedIds,
            flag_reason: reason,
            flag_priority: priority
        })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Unknown error occurred'));
                button.prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Selected');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Bulk flag request failed:', xhr.responseText);
            let errorMessage = 'An error occurred while flagging the forms.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            alert(errorMessage);
            button.prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Selected');
        });
    });

    // Bulk print
    $('#bulkPrintBtn').click(function() {
        const selectedIds = $('.form-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select forms to print.');
            return;
        }

        // Open print windows for each selected form
        selectedIds.forEach(function(formId) {
            window.open(`/finance/liquidated-forms/${formId}/print`, '_blank');
        });
    });

    // Clear modal form when modal is hidden
    $('#flagModal').on('hidden.bs.modal', function() {
        $('#flagForm')[0].reset();
        $('#flagForm').find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Form');
    });
});
</script>
@endpush
@endsection
