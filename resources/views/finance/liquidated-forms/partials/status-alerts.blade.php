@if($liquidatedForm->status === 'flagged')
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-flag"></i>
    <strong>Flagged:</strong> This form has been flagged for review.
    @if($liquidatedForm->flag_reason)
        <br><strong>Reason:</strong> {{ $liquidatedForm->flag_reason }}
    @endif
    @if($liquidatedForm->flagged_at)
        <br><small>Flagged on {{ $liquidatedForm->flagged_at->format('M d, Y g:i A') }} by {{ $liquidatedForm->flaggedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->flaggedBy->last_name ?? '' }}</small>
    @endif
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if($liquidatedForm->status === 'clarification_requested')
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-question-circle"></i>
    <strong>Clarification Requested:</strong> Additional information is needed for this form.
    @if($liquidatedForm->clarification_notes)
        <br><strong>Notes:</strong> {{ $liquidatedForm->clarification_notes }}
    @endif
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if($liquidatedForm->status === 'revision_requested')
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-edit"></i>
    <strong>Revision Requested:</strong> This form requires revision before approval.
    @if($liquidatedForm->revision_notes)
        <br><strong>Notes:</strong> {{ $liquidatedForm->revision_notes }}
    @endif
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if($liquidatedForm->status === 'under_review')
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-search"></i>
    <strong>Under Review:</strong> This form is currently being reviewed by administrators.
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if($liquidatedForm->status === 'approved')
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i>
    <strong>Approved:</strong> This form has been approved.
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if($liquidatedForm->status === 'rejected')
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-times-circle"></i>
    <strong>Rejected:</strong> This form has been rejected.
    @if($liquidatedForm->rejection_reason)
        <br><strong>Reason:</strong> {{ $liquidatedForm->rejection_reason }}
    @endif
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif
