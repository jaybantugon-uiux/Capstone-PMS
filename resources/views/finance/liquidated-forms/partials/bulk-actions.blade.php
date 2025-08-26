<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Bulk Actions</h6>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-danger btn-sm" id="bulkFlagBtn" disabled>
                <i class="fas fa-flag"></i> Flag Selected
            </button>
            <button type="button" class="btn btn-info btn-sm" id="bulkPrintBtn" disabled>
                <i class="fas fa-print"></i> Print Selected
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">
                        Select All Forms
                    </label>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <span class="text-muted">
                    <span id="selectedCount">0</span> of <span id="totalCount">{{ $liquidatedForms->total() ?? 0 }}</span> forms selected
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Flag Modal -->
<div class="modal fade" id="bulkFlagModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Flag Selected Forms</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkFlagForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_flag_reason">Reason for Flagging <span class="text-danger">*</span></label>
                        <textarea name="flag_reason" id="bulk_flag_reason" class="form-control" rows="3" required 
                                  placeholder="Please provide a detailed reason for flagging these forms..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="bulk_flag_priority">Priority Level</label>
                        <select name="flag_priority" id="bulk_flag_priority" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This action will flag <span id="bulkFlagCount">0</span> selected form(s).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-flag"></i> Flag Forms
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
        const totalCount = $('.form-checkbox').length;
        
        $('#selectedCount').text(checkedCount);
        $('#bulkFlagCount').text(checkedCount);
        
        $('#bulkFlagBtn').prop('disabled', checkedCount === 0);
        $('#bulkPrintBtn').prop('disabled', checkedCount === 0);
        
        // Update select all checkbox
        if (checkedCount === 0) {
            $('#selectAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCount === totalCount) {
            $('#selectAll').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAll').prop('indeterminate', true);
        }
    }

    // Bulk flag button
    $('#bulkFlagBtn').click(function() {
        const checkedForms = $('.form-checkbox:checked');
        if (checkedForms.length === 0) {
            alert('Please select at least one form to flag.');
            return;
        }
        $('#bulkFlagModal').modal('show');
    });

    // Bulk flag form submission
    $('#bulkFlagForm').submit(function(e) {
        e.preventDefault();
        const reason = $('#bulk_flag_reason').val();
        const priority = $('#bulk_flag_priority').val();
        const formIds = $('.form-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (!reason.trim()) {
            alert('Please provide a reason for flagging.');
            return;
        }

        $.post('/finance/liquidated-forms/bulk-flag', {
            _token: '{{ csrf_token() }}',
            form_ids: formIds,
            flag_reason: reason,
            flag_priority: priority
        })
        .done(function(response) {
            if (response.success) {
                $('#bulkFlagModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function() {
            alert('An error occurred while flagging the forms.');
        });
    });

    // Bulk print button
    $('#bulkPrintBtn').click(function() {
        const checkedForms = $('.form-checkbox:checked');
        if (checkedForms.length === 0) {
            alert('Please select at least one form to print.');
            return;
        }

        const formIds = checkedForms.map(function() {
            return $(this).val();
        }).get();

        // Open print windows for each form
        formIds.forEach(function(formId) {
            window.open(`/finance/liquidated-forms/${formId}/print`, '_blank');
        });
    });

    // Clear form when modal is hidden
    $('#bulkFlagModal').on('hidden.bs.modal', function() {
        $('#bulk_flag_reason').val('');
        $('#bulk_flag_priority').val('medium');
    });
});
</script>
@endpush
