<!-- Flag Modal -->
<div class="modal fade" id="flagModal" tabindex="-1" role="dialog" aria-labelledby="flagModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="flagModalLabel">
                    <i class="fas fa-flag"></i> Flag Liquidated Form
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="flagForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Flagging a form will mark it for review and notify administrators.
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_reason" class="font-weight-bold">
                            Reason for Flagging <span class="text-danger">*</span>
                        </label>
                        <textarea name="flag_reason" id="flag_reason" class="form-control" rows="4" required 
                                  placeholder="Please provide a detailed reason for flagging this form. Be specific about the issues or concerns..."></textarea>
                        <small class="form-text text-muted">This reason will be visible to administrators and may be used for audit purposes.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_priority" class="font-weight-bold">Priority Level</label>
                        <select name="flag_priority" id="flag_priority" class="form-control">
                            <option value="low">
                                <i class="fas fa-info-circle"></i> Low - Minor issues that need attention
                            </option>
                            <option value="medium" selected>
                                <i class="fas fa-exclamation-triangle"></i> Medium - Standard review required
                            </option>
                            <option value="high">
                                <i class="fas fa-exclamation-circle"></i> High - Urgent attention needed
                            </option>
                            <option value="critical">
                                <i class="fas fa-times-circle"></i> Critical - Immediate action required
                            </option>
                        </select>
                        <small class="form-text text-muted">Select the appropriate priority level based on the severity of the issue.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="flag_notes" class="font-weight-bold">Additional Notes (Optional)</label>
                        <textarea name="flag_notes" id="flag_notes" class="form-control" rows="2" 
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
