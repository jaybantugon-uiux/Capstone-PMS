@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Resolve Clarification</h1>
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
        </div>
    </div>

    <!-- Clarification Request Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Clarification Request Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Requested By:</strong></td>
                            <td>{{ $receipt->clarificationRequester->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Requested At:</strong></td>
                            <td>{{ $receipt->clarification_requested_at ? $receipt->clarification_requested_at->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge badge-{{ $receipt->clarification_status_badge_color }}">{{ $receipt->formatted_clarification_status }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <strong>Clarification Notes:</strong>
                    <div class="alert alert-warning">
                        {{ $receipt->clarification_notes }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolve Clarification Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Resolve Clarification</h6>
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

            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Note:</strong> Resolving this clarification will mark it as completed and update the receipt's clarification status.
            </div>

            <form method="POST" action="{{ route('admin.receipts.resolve-clarification', $receipt) }}">
                @csrf
                
                <div class="form-group">
                    <label for="resolution_notes">Resolution Notes</label>
                    <textarea 
                        name="resolution_notes" 
                        id="resolution_notes" 
                        class="form-control @error('resolution_notes') is-invalid @enderror" 
                        rows="4" 
                        placeholder="Optional: Add notes about how the clarification was resolved..."
                    >{{ old('resolution_notes') }}</textarea>
                    @error('resolution_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        You can add notes about how the clarification was addressed or what actions were taken.
                    </small>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirm_resolution" required>
                        <label class="custom-control-label" for="confirm_resolution">
                            I confirm that the clarification has been resolved and this receipt can proceed.
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Resolve Clarification
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
    const confirmCheckbox = document.getElementById('confirm_resolution');

    // Disable submit button until confirmation is checked
    submitBtn.disabled = !confirmCheckbox.checked;

    confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
    });

    // Form submission confirmation
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to resolve this clarification? This will mark the clarification as completed.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
