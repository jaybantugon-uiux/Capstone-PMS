<!-- Enhanced Flag Modal for Suspicious Activities -->
<div class="modal fade" id="enhancedFlagModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-flag"></i> Flag Suspicious Activity
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="enhancedFlagForm">
                <div class="modal-body">
                    <!-- Form Information -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Form Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Form #:</strong> <span id="flagFormNumber"></span><br>
                                <strong>Title:</strong> <span id="flagFormTitle"></span><br>
                                <strong>Project:</strong> <span id="flagFormProject"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Amount:</strong> <span id="flagFormAmount"></span><br>
                                <strong>Variance:</strong> <span id="flagFormVariance"></span><br>
                                <strong>Receipts Coverage:</strong> <span id="flagFormCoverage"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Flag Category -->
                    <div class="form-group">
                        <label for="flag_category">Flag Category <span class="text-danger">*</span></label>
                        <select name="flag_category" id="flag_category" class="form-control" required>
                            <option value="">Select a category...</option>
                            <option value="high_variance">High Variance (>10% of total amount)</option>
                            <option value="missing_receipts">Missing or Insufficient Receipts</option>
                            <option value="negative_variance">Negative Variance (Over-expenditure)</option>
                            <option value="unusual_patterns">Unusual Spending Patterns</option>
                            <option value="documentation_issues">Documentation Issues</option>
                            <option value="compliance_concerns">Compliance Concerns</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Priority Level -->
                    <div class="form-group">
                        <label for="enhanced_flag_priority">Priority Level <span class="text-danger">*</span></label>
                        <select name="flag_priority" id="enhanced_flag_priority" class="form-control" required>
                            <option value="low">Low - Minor issues that need attention</option>
                            <option value="medium" selected>Medium - Standard review required</option>
                            <option value="high">High - Urgent review needed</option>
                            <option value="critical">Critical - Immediate action required</option>
                        </select>
                    </div>

                    <!-- Detailed Reason -->
                    <div class="form-group">
                        <label for="enhanced_flag_reason">Detailed Reason for Flagging <span class="text-danger">*</span></label>
                        <textarea name="flag_reason" id="enhanced_flag_reason" class="form-control" rows="4" required 
                                  placeholder="Please provide a detailed explanation of why this form is being flagged..."></textarea>
                        <small class="form-text text-muted">
                            Include specific details about the suspicious activity, amounts involved, and any patterns observed.
                        </small>
                    </div>

                    <!-- Additional Evidence -->
                    <div class="form-group">
                        <label for="flag_evidence">Additional Evidence or Notes</label>
                        <textarea name="flag_evidence" id="flag_evidence" class="form-control" rows="3" 
                                  placeholder="Any additional evidence, supporting documents, or notes..."></textarea>
                    </div>

                    <!-- Recommended Actions -->
                    <div class="form-group">
                        <label for="flag_recommended_actions">Recommended Actions</label>
                        <select name="flag_recommended_actions" id="flag_recommended_actions" class="form-control">
                            <option value="">Select recommended action...</option>
                            <option value="request_clarification">Request Clarification from Preparer</option>
                            <option value="additional_documentation">Request Additional Documentation</option>
                            <option value="review_by_admin">Review by Administration</option>
                            <option value="audit_investigation">Audit Investigation Required</option>
                            <option value="corrective_action">Corrective Action Plan</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Follow-up Required -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="flag_followup_required" name="flag_followup_required" value="1">
                            <label class="custom-control-label" for="flag_followup_required">
                                Follow-up Required
                            </label>
                        </div>
                    </div>

                    <!-- Follow-up Date -->
                    <div class="form-group" id="followupDateGroup" style="display: none;">
                        <label for="flag_followup_date">Follow-up Date</label>
                        <input type="date" name="flag_followup_date" id="flag_followup_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-flag"></i> Flag for Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show/hide follow-up date based on checkbox
    $('#flag_followup_required').change(function() {
        if ($(this).is(':checked')) {
            $('#followupDateGroup').show();
        } else {
            $('#followupDateGroup').hide();
        }
    });

    // Enhanced flag form submission
    $('#enhancedFlagForm').submit(function(e) {
        e.preventDefault();
        
        const formId = $(this).data('form-id');
        const category = $('#flag_category').val();
        const priority = $('#enhanced_flag_priority').val();
        const reason = $('#enhanced_flag_reason').val();
        const evidence = $('#flag_evidence').val();
        const recommendedActions = $('#flag_recommended_actions').val();
        const followupRequired = $('#flag_followup_required').is(':checked');
        const followupDate = $('#flag_followup_date').val();

        if (!category || !reason.trim()) {
            alert('Please fill in all required fields.');
            return;
        }

        // Combine reason with additional information
        let fullReason = `Category: ${category}\n\nReason: ${reason}`;
        if (evidence) {
            fullReason += `\n\nEvidence: ${evidence}`;
        }
        if (recommendedActions) {
            fullReason += `\n\nRecommended Action: ${recommendedActions}`;
        }
        if (followupRequired && followupDate) {
            fullReason += `\n\nFollow-up Required: ${followupDate}`;
        }

        $.post(`/finance/liquidated-forms/${formId}/flag`, {
            _token: '{{ csrf_token() }}',
            flag_reason: fullReason,
            flag_priority: priority
        })
        .done(function(response) {
            if (response.success) {
                $('#enhancedFlagModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Unknown error occurred'));
            }
        })
        .fail(function() {
            alert('An error occurred while flagging the form.');
        });
    });

    // Clear form when modal is hidden
    $('#enhancedFlagModal').on('hidden.bs.modal', function() {
        $('#flag_category').val('');
        $('#enhanced_flag_priority').val('medium');
        $('#enhanced_flag_reason').val('');
        $('#flag_evidence').val('');
        $('#flag_recommended_actions').val('');
        $('#flag_followup_required').prop('checked', false);
        $('#flag_followup_date').val('');
        $('#followupDateGroup').hide();
    });
});
</script>
