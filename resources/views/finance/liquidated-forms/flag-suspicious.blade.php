@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-exclamation-triangle text-warning"></i> Flag Suspicious Activities
            </h1>
            <p class="text-muted">Review and flag liquidated forms for investigation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Suspicious Activity Indicators -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Status Flow:</strong> Pending forms can be flagged for investigation. When unflagged, forms return to pending status.
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                High Variance Forms
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $highVarianceCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Missing Receipts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $missingReceiptsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Unusual Patterns
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $unusualPatternsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Already Flagged
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $flaggedCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspicious Forms List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Liquidated Forms (Flagged & Pending)
            </h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-danger btn-sm" id="bulkFlagBtn" disabled>
                    <i class="fas fa-flag"></i> Flag Selected
                </button>
                <button type="button" class="btn btn-success btn-sm" id="bulkUnflagBtn" disabled>
                    <i class="fas fa-flag-checkered"></i> Unflag Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($suspiciousForms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="suspiciousFormsTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Form #</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Preparer</th>
                                <th>Total Amount</th>
                                <th>Variance</th>
                                <th>Receipts Coverage</th>
                                <th>Risk Level</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suspiciousForms as $form)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input form-checkbox" value="{{ $form->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('finance.liquidated-forms.show', $form) }}" class="font-weight-bold text-primary">
                                        {{ $form->form_number }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($form->title, 30) }}</td>
                                <td>{{ $form->project->name ?? 'N/A' }}</td>
                                <td>{{ $form->preparer->first_name ?? 'N/A' }} {{ $form->preparer->last_name ?? '' }}</td>
                                <td class="text-right font-weight-bold">{{ $form->formatted_total_amount }}</td>
                                <td class="text-right">
                                    <span class="text-{{ $form->is_variance_positive ? 'success' : 'danger' }} font-weight-bold">
                                        {{ $form->formatted_variance_amount }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $form->receipts_coverage >= 80 ? 'success' : ($form->receipts_coverage >= 50 ? 'warning' : 'danger') }}">
                                        {{ number_format($form->receipts_coverage, 1) }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $riskLevel = 'low';
                                        $riskColor = 'success';
                                        if ($form->variance_percentage > 20 || $form->receipts_coverage < 50) {
                                            $riskLevel = 'high';
                                            $riskColor = 'danger';
                                        } elseif ($form->variance_percentage > 10 || $form->receipts_coverage < 80) {
                                            $riskLevel = 'medium';
                                            $riskColor = 'warning';
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $riskColor }}">{{ ucfirst($riskLevel) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $form->status_badge_color }}">
                                        {{ ucfirst($form->status) }}
                                    </span>
                                    <br><small class="text-muted">ID: {{ $form->id }}</small>
                                    <br><small class="text-muted">Can Flag: {{ $form->canBeFlagged() ? 'Yes' : 'No' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('finance.liquidated-forms.show', $form) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($form->canBeFlagged())
                                        <button type="button" class="btn btn-sm btn-danger flag-btn" 
                                                data-form-id="{{ $form->id }}" 
                                                data-form-number="{{ $form->form_number }}"
                                                title="Flag for Review"
                                                onclick="handleFlagClick({{ $form->id }}, '{{ $form->form_number }}')">
                                            <i class="fas fa-flag"></i>
                                        </button>
                                        @elseif($form->status === 'flagged')
                                        <button type="button" class="btn btn-sm btn-success unflag-btn" 
                                                data-form-id="{{ $form->id }}" 
                                                data-form-number="{{ $form->form_number }}"
                                                title="Remove Flag"
                                                onclick="handleUnflagClick({{ $form->id }}, '{{ $form->form_number }}')">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                        @else
                                        <span class="badge badge-secondary">{{ ucfirst($form->status) }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Flagged or Pending Forms Found</h5>
                    <p class="text-muted">There are currently no liquidated forms with flagged or pending status.</p>
                    <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Forms
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Flag Modal -->
@include('finance.liquidated-forms.partials.flag-modal')

@push('scripts')
<script>
// Wait for jQuery to be available
function waitForJQuery(callback) {
    if (typeof jQuery !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForJQuery(callback);
        }, 100);
    }
}

// Global variables
let currentFormId = null;
let currentFormNumber = null;

// Global functions for flag and unflag operations
function handleFlagClick(formId, formNumber) {
    console.log('=== FLAG BUTTON CLICKED ===');
    console.log('Form ID:', formId);
    console.log('Form Number:', formNumber);
    
    // Wait for jQuery to be available
    waitForJQuery(function() {
        try {
            // Store current form info
            currentFormId = formId;
            currentFormNumber = formNumber;
            
            // Show immediate feedback
            alert('Flag button clicked for form ' + formNumber + ' (ID: ' + formId + ')');
            
            // Check if modal exists
            if (jQuery('#flagModal').length === 0) {
                alert('ERROR: Flag modal not found!');
                return;
            }
            
            // Check if form exists
            if (jQuery('#flagForm').length === 0) {
                alert('ERROR: Flag form not found!');
                return;
            }
            
            // Set form ID for AJAX submission
            jQuery('#flagForm').data('form-id', formId);
            
            // Update modal title
            jQuery('#flagModalLabel').html('<i class="fas fa-flag"></i> Flag Form #' + formNumber);
            
            // Show modal
            jQuery('#flagModal').modal('show');
            
            console.log('Modal should be visible now');
        } catch (error) {
            console.error('Error in handleFlagClick:', error);
            alert('Error: ' + error.message);
        }
    });
}

function handleUnflagClick(formId, formNumber) {
    console.log('Unflag button clicked for form:', formNumber, 'ID:', formId);
    
    if (confirm('Are you sure you want to remove the flag from Form #' + formNumber + '?')) {
        // Show loading state
        const button = event.target.closest('.unflag-btn');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Make AJAX call to unflag the form
        waitForJQuery(function() {
            jQuery.ajax({
                url: '{{ route("finance.liquidated-forms.unflag", ":id") }}'.replace(':id', formId),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('Unflag successful:', response);
                    // Show success message
                    alert('Form #' + formNumber + ' has been unflagged successfully!');
                    // Refresh the page to show updated status
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Unflag failed:', error);
                    // Restore button state
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    // Show error message
                    alert('Failed to unflag form #' + formNumber + '. Please try again.');
                }
            });
        });
    }
}

// Wait for jQuery and document to be ready
waitForJQuery(function() {
    jQuery(document).ready(function() {
        console.log('Suspicious activities page loaded');
        
        // Initialize DataTable
        var table = jQuery('#suspiciousFormsTable').DataTable({
            order: [[8, 'desc'], [6, 'desc']], // Sort by risk level, then variance
            pageLength: 25,
            responsive: {
                details: {
                    display: jQuery.fn.dataTable.Responsive.display.modal({
                        header: function(row) {
                            var data = row.data();
                            return 'Details for ' + data[1]; // Form number
                        }
                    }),
                    renderer: jQuery.fn.dataTable.Responsive.renderer.tableAll()
                }
            },
            columnDefs: [
                { orderable: false, targets: [0, 10] }, // Disable sorting for checkbox and actions columns
                { responsivePriority: 1, targets: 10 }, // High priority for actions column
                { className: 'text-center', targets: [0, 7, 8, 9, 10] }, // Center align certain columns
                { className: 'text-right', targets: [5, 6] } // Right align amount columns
            ],
            language: {
                search: "Search forms:",
                lengthMenu: "Show _MENU_ forms per page",
                info: "Showing _START_ to _END_ of _TOTAL_ forms",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Select all checkbox functionality
        jQuery('#selectAll').change(function() {
            var isChecked = jQuery(this).is(':checked');
            jQuery('.form-checkbox').prop('checked', isChecked);
            updateBulkButtons();
        });

        // Individual checkbox functionality
        jQuery(document).on('change', '.form-checkbox', function() {
            updateBulkButtons();
            
            // Update select all checkbox
            var totalCheckboxes = jQuery('.form-checkbox').length;
            var checkedCheckboxes = jQuery('.form-checkbox:checked').length;
            
            if (checkedCheckboxes === 0) {
                jQuery('#selectAll').prop('indeterminate', false).prop('checked', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                jQuery('#selectAll').prop('indeterminate', false).prop('checked', true);
            } else {
                jQuery('#selectAll').prop('indeterminate', true);
            }
        });

        // Update bulk action buttons
        function updateBulkButtons() {
            var selectedIds = jQuery('.form-checkbox:checked').map(function() {
                return jQuery(this).val();
            }).get();
            
            jQuery('#bulkFlagBtn').prop('disabled', selectedIds.length === 0);
            jQuery('#bulkUnflagBtn').prop('disabled', selectedIds.length === 0);
        }

        // Initialize bulk buttons state
        updateBulkButtons();
        
        // Bulk flag functionality
        jQuery('#bulkFlagBtn').click(function() {
            var selectedIds = jQuery('.form-checkbox:checked').map(function() {
                return jQuery(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                alert('Please select at least one form to flag.');
                return;
            }
            
            if (confirm('Are you sure you want to flag ' + selectedIds.length + ' selected form(s)?')) {
                // Show loading state
                jQuery(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Flagging...');
                
                jQuery.ajax({
                    url: '{{ route("finance.liquidated-forms.bulk-flag") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        liquidated_form_ids: selectedIds,
                        flag_reason: 'Bulk flag from suspicious activities page',
                        flag_priority: 'medium'
                    },
                    success: function(response) {
                        alert('Successfully flagged ' + selectedIds.length + ' form(s)!');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to flag forms. Please try again.');
                        jQuery('#bulkFlagBtn').prop('disabled', false).html('<i class="fas fa-flag"></i> Flag Selected');
                    }
                });
            }
        });
        
        // Bulk unflag functionality
        jQuery('#bulkUnflagBtn').click(function() {
            var selectedIds = jQuery('.form-checkbox:checked').map(function() {
                return jQuery(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                alert('Please select at least one form to unflag.');
                return;
            }
            
            if (confirm('Are you sure you want to unflag ' + selectedIds.length + ' selected form(s)?')) {
                // Show loading state
                jQuery(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Unflagging...');
                
                jQuery.ajax({
                    url: '{{ route("finance.liquidated-forms.bulk-unflag") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        liquidated_form_ids: selectedIds
                    },
                    success: function(response) {
                        alert('Successfully unflagged ' + selectedIds.length + ' form(s)!');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to unflag forms. Please try again.');
                        jQuery('#bulkUnflagBtn').prop('disabled', false).html('<i class="fas fa-flag-checkered"></i> Unflag Selected');
                    }
                });
            }
        });
    });
});
</script>
@endpush

<style>
/* Ensure buttons are clickable */
.flag-btn, .unflag-btn {
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 1000 !important;
    position: relative !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.btn-group .btn {
    position: relative !important;
    z-index: 1000 !important;
    pointer-events: auto !important;
}

#suspiciousFormsTable .btn {
    pointer-events: auto !important;
    z-index: 1000 !important;
}

/* Ensure DataTable doesn't interfere with button clicks */
.dataTables_wrapper .btn {
    pointer-events: auto !important;
    z-index: 1000 !important;
}

/* Override any DataTable responsive hiding */
.dataTables_wrapper .btn-group {
    pointer-events: auto !important;
    z-index: 1000 !important;
}

/* Make sure buttons are visible and clickable */
.flag-btn:hover, .unflag-btn:hover {
    opacity: 0.8 !important;
    transform: scale(1.05) !important;
    transition: all 0.2s ease !important;
}

/* Ensure button text and icons are visible */
.flag-btn i, .unflag-btn i {
    pointer-events: none !important;
}

/* Override any Bootstrap or DataTable styles that might hide buttons */
.table .btn {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure proper button sizing */
.btn-sm {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.5 !important;
    border-radius: 0.2rem !important;
}

/* Force button visibility in DataTable */
.dataTables_wrapper .table .btn {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}

/* Ensure button group works properly */
.btn-group > .btn {
    position: relative !important;
    z-index: 1000 !important;
    pointer-events: auto !important;
}

/* Override any responsive hiding */
@media (max-width: 768px) {
    .flag-btn, .unflag-btn {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
}
</style>
@endsection
