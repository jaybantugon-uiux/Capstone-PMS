@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Request Clarification</h1>
        <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Receipt
        </a>
    </div>

    <!-- Receipt Details Card -->
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
                            <td><strong>Vendor:</strong></td>
                            <td>{{ $receipt->vendor_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td>₱{{ number_format($receipt->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Receipt Type:</strong></td>
                            <td><span class="badge badge-info">{{ $receipt->receipt_type }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge badge-{{ $receipt->status_badge_color }}">{{ $receipt->formatted_status }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Uploader:</strong></td>
                            <td>{{ $receipt->uploader->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Receipt Date:</strong></td>
                            <td>{{ $receipt->receipt_date ? $receipt->receipt_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $receipt->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($receipt->description)
            <div class="row mt-3">
                <div class="col-12">
                    <strong>Description:</strong>
                    <p class="text-muted">{{ $receipt->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Clarification Request Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Request Clarification</h6>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> This clarification request will be sent to the finance team. They will be notified via email and in-app notifications.
            </div>

            <form method="POST" action="{{ route('admin.receipts.request-clarification', $receipt) }}">
                @csrf
                
                <div class="form-group">
                    <label for="clarification_notes">Clarification Notes <span class="text-danger">*</span></label>
                    <textarea 
                        name="clarification_notes" 
                        id="clarification_notes" 
                        class="form-control @error('clarification_notes') is-invalid @enderror" 
                        rows="5" 
                        placeholder="Please specify what clarification is needed for this receipt..."
                        required
                    >{{ old('clarification_notes') }}</textarea>
                    @error('clarification_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Be specific about what information or documentation is needed from the finance team.
                    </small>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirm_request" required>
                        <label class="custom-control-label" for="confirm_request">
                            I confirm that I want to request clarification for this receipt. This will notify the finance team.
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-question-circle"></i> Request Clarification
                    </button>
                    <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const confirmCheckbox = document.getElementById('confirm_request');

    // Disable submit button until confirmation is checked
    submitBtn.disabled = !confirmCheckbox.checked;

    confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
    });

    // Form submission confirmation
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to request clarification for this receipt? This will notify the finance team.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
