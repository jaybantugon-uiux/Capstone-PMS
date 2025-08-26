@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create Liquidated Form</h1>
            <p class="text-muted">Create a new liquidated form manually</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <form action="{{ route('finance.liquidated-forms.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Form Details -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Form Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="form_number">Form Number <span class="text-danger">*</span></label>
                                    <input type="text" name="form_number" id="form_number" class="form-control @error('form_number') is-invalid @enderror" 
                                           value="{{ old('form_number') }}" required>
                                    @error('form_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id">Project</label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror">
                                        <option value="">Select Project</option>
                                        @foreach($projects ?? [] as $project)
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="liquidation_date">Liquidation Date <span class="text-danger">*</span></label>
                                    <input type="date" name="liquidation_date" id="liquidation_date" class="form-control @error('liquidation_date') is-invalid @enderror" 
                                           value="{{ old('liquidation_date', date('Y-m-d')) }}" required>
                                    @error('liquidation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_covered_start">Period Covered Start <span class="text-danger">*</span></label>
                                    <input type="date" name="period_covered_start" id="period_covered_start" class="form-control @error('period_covered_start') is-invalid @enderror" 
                                           value="{{ old('period_covered_start') }}" required>
                                    @error('period_covered_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_covered_end">Period Covered End <span class="text-danger">*</span></label>
                                    <input type="date" name="period_covered_end" id="period_covered_end" class="form-control @error('period_covered_end') is-invalid @enderror" 
                                           value="{{ old('period_covered_end') }}" required>
                                    @error('period_covered_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_amount">Total Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control @error('total_amount') is-invalid @enderror" 
                                               value="{{ old('total_amount') }}" step="0.01" min="0.01" required>
                                    </div>
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_receipts">Total Receipts</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" name="total_receipts" id="total_receipts" class="form-control @error('total_receipts') is-invalid @enderror" 
                                               value="{{ old('total_receipts', 0) }}" step="0.01" min="0">
                                    </div>
                                    @error('total_receipts')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="variance_amount">Variance Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" name="variance_amount" id="variance_amount" class="form-control @error('variance_amount') is-invalid @enderror" 
                                               value="{{ old('variance_amount', 0) }}" step="0.01" readonly>
                                    </div>
                                    @error('variance_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Help Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Ensure all amounts are accurate and verified
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Period covered should match the actual expenses
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Include all relevant receipts and documentation
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Review all information before submission
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Liquidated Form
                            </button>
                            <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate variance automatically
    function calculateVariance() {
        const totalAmount = parseFloat($('#total_amount').val()) || 0;
        const totalReceipts = parseFloat($('#total_receipts').val()) || 0;
        const variance = totalAmount - totalReceipts;
        $('#variance_amount').val(variance.toFixed(2));
    }

    $('#total_amount, #total_receipts').on('input', calculateVariance);
    
    // Initialize variance calculation
    calculateVariance();
});
</script>
@endpush

@endsection
