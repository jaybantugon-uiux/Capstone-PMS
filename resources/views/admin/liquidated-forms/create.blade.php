@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Liquidated Form</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Form Status Flow:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge badge-secondary">Pending</span> - Initial status when form is created</li>
                        <li><span class="badge badge-info">Under Review</span> - Form is being reviewed</li>
                        <li><span class="badge badge-warning">Flagged</span> - Form has issues that need attention</li>
                        <li><span class="badge badge-success">Completed</span> - Form has been finalized</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Important Notes:</h6>
                    <ul class="list-unstyled">
                        <li>• Form number will be automatically generated</li>
                        <li>• All required fields must be filled</li>
                        <li>• Period end date must be after or equal to period start date</li>
                        <li>• Total amount must be greater than zero</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Create Liquidated Form</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.liquidated-forms.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Form Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="project_id">Project</label>
                            <select class="form-control @error('project_id') is-invalid @enderror" 
                                    id="project_id" name="project_id">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prepared_by">Preparer <span class="text-danger">*</span></label>
                            <select class="form-control @error('prepared_by') is-invalid @enderror" 
                                    id="prepared_by" name="prepared_by" required>
                                <option value="">Select Preparer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('prepared_by') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prepared_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="liquidation_date">Liquidation Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('liquidation_date') is-invalid @enderror" 
                                   id="liquidation_date" name="liquidation_date" 
                                   value="{{ old('liquidation_date') }}" required>
                            @error('liquidation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period_covered_start">Period Start <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('period_covered_start') is-invalid @enderror" 
                                   id="period_covered_start" name="period_covered_start" 
                                   value="{{ old('period_covered_start') }}" required>
                            @error('period_covered_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period_covered_end">Period End <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('period_covered_end') is-invalid @enderror" 
                                   id="period_covered_end" name="period_covered_end" 
                                   value="{{ old('period_covered_end') }}" required>
                            @error('period_covered_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control @error('total_amount') is-invalid @enderror" 
                                   id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required>
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_receipts">Total Receipts</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_receipts') is-invalid @enderror" 
                                   id="total_receipts" name="total_receipts" value="{{ old('total_receipts') }}">
                            @error('total_receipts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Liquidated Form
                    </button>
                    <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-fill liquidation date with today's date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    if (!document.getElementById('liquidation_date').value) {
        document.getElementById('liquidation_date').value = today;
    }
});

// Client-side validation for date ranges
document.getElementById('period_covered_end').addEventListener('change', function() {
    const startDate = document.getElementById('period_covered_start').value;
    const endDate = this.value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('Period end date must be after or equal to period start date');
        this.value = '';
    }
});

document.getElementById('period_covered_start').addEventListener('change', function() {
    const startDate = this.value;
    const endDate = document.getElementById('period_covered_end').value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('Period start date must be before or equal to period end date');
        document.getElementById('period_covered_end').value = '';
    }
});

// Auto-calculate variance when amounts change
document.getElementById('total_amount').addEventListener('input', calculateVariance);
document.getElementById('total_receipts').addEventListener('input', calculateVariance);

function calculateVariance() {
    const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;
    const totalReceipts = parseFloat(document.getElementById('total_receipts').value) || 0;
    const variance = totalAmount - totalReceipts;
    
    // You can display the variance somewhere if needed
    console.log('Variance: ₱' + variance.toFixed(2));
}
</script>
@endpush
