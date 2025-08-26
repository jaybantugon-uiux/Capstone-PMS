@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Remove Flag from Liquidated Form</h1>
            <p class="text-muted">Form #{{ $liquidatedForm->form_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Form
            </a>
        </div>
    </div>

    <!-- Confirmation Alert -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Confirmation Required:</strong> You are about to remove the flag from this form. This action will change the form status back to pending.
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Unflag Confirmation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-flag-checkered"></i> Remove Flag from Form #{{ $liquidatedForm->form_number }}
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Current Flag Information -->
                    @if($liquidatedForm->flag_reason)
                    <div class="alert alert-warning">
                        <h6><strong>Current Flag Information:</strong></h6>
                        <p><strong>Reason:</strong> {{ $liquidatedForm->flag_reason }}</p>
                        @if($liquidatedForm->flag_priority)
                        <p><strong>Priority:</strong> {{ ucfirst($liquidatedForm->flag_priority) }}</p>
                        @endif
                        @if($liquidatedForm->flagged_at)
                        <p><strong>Flagged On:</strong> {{ $liquidatedForm->flagged_at->format('M d, Y g:i A') }}</p>
                        @endif
                        @if($liquidatedForm->flaggedBy)
                        <p><strong>Flagged By:</strong> {{ $liquidatedForm->flaggedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->flaggedBy->last_name ?? '' }}</p>
                        @endif
                    </div>
                    @endif

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Removing the flag will:
                        <ul class="mb-0 mt-2">
                            <li>Change the form status from "flagged" to "pending"</li>
                            <li>Clear all flag-related information</li>
                            <li>Allow the form to be processed normally</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('finance.liquidated-forms.unflag', $liquidatedForm) }}" id="unflagForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="unflag_reason" class="font-weight-bold">Reason for Removing Flag (Optional)</label>
                            <textarea name="unflag_reason" id="unflag_reason" class="form-control" 
                                      rows="3" 
                                      placeholder="Please provide a reason for removing the flag (optional)..."></textarea>
                            <small class="form-text text-muted">This will be logged for audit purposes.</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="unflagBtn">
                                <i class="fas fa-flag-checkered"></i> Remove Flag
                            </button>
                            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Form Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Form Number:</strong><br>
                        {{ $liquidatedForm->form_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Title:</strong><br>
                        {{ $liquidatedForm->title }}
                    </div>
                    <div class="mb-3">
                        <strong>Current Status:</strong><br>
                        <span class="badge badge-danger">
                            {{ ucfirst($liquidatedForm->status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Prepared By:</strong><br>
                        {{ $liquidatedForm->preparer->first_name ?? 'N/A' }} {{ $liquidatedForm->preparer->last_name ?? '' }}
                    </div>
                    <div class="mb-3">
                        <strong>Liquidation Date:</strong><br>
                        {{ $liquidatedForm->liquidation_date->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add confirmation dialog
    $('#unflagBtn').click(function(e) {
        if (!confirm('Are you sure you want to remove the flag from this form? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
