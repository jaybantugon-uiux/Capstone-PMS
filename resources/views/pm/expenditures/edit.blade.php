@extends('app')

@section('title', 'Edit Daily Expenditure')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Daily Expenditure</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('pm.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pm.expenditures.index') }}">Daily Expenditures</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Expenditure Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pm.expenditures.update', $expenditure) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Project *</label>
                                    <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id', $expenditure->project_id) == $project->id ? 'selected' : '' }}>
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
                                <div class="mb-3">
                                    <label for="expense_date" class="form-label">Expense Date *</label>
                                    <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                           value="{{ old('expense_date', $expenditure->expense_date) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category *</label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $key => $category)
                                            <option value="{{ $key }}" {{ old('category', $expenditure->category) == $key ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentMethods as $key => $method)
                                            <option value="{{ $key }}" {{ old('payment_method', $expenditure->payment_method) == $key ? 'selected' : '' }}>
                                                {{ $method }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (₱) *</label>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           min="1" placeholder="₱0" value="{{ old('amount', $expenditure->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                           placeholder="Enter location" value="{{ old('location', $expenditure->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="vendor_supplier" class="form-label">Vendor/Supplier</label>
                                    <input type="text" name="vendor_supplier" id="vendor_supplier" class="form-control @error('vendor_supplier') is-invalid @enderror" 
                                           placeholder="Enter vendor/supplier" value="{{ old('vendor_supplier', $expenditure->vendor_supplier) }}">
                                    @error('vendor_supplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                           placeholder="Enter reference number" value="{{ old('reference_number', $expenditure->reference_number) }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Enter detailed description of the expense" required>{{ old('description', $expenditure->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Additional notes (optional)">{{ old('notes', $expenditure->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="receipt_files" class="form-label">Receipt Files</label>
                            <input type="file" name="receipt_files[]" id="receipt_files" class="form-control @error('receipt_files.*') is-invalid @enderror" 
                                   multiple accept=".jpg,.jpeg,.png,.pdf">
                            <small class="form-text text-muted">You can select multiple files. Supported formats: JPG, JPEG, PNG, PDF (max 10MB each)</small>
                            @error('receipt_files.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Receipts -->
                        @if($expenditure->receipts->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Receipts</label>
                            <div class="row">
                                @foreach($expenditure->receipts as $receipt)
                                <div class="col-md-4 mb-2">
                                    <div class="card border">
                                        <div class="card-body p-2">
                                            <small class="text-muted">{{ $receipt->original_name }}</small>
                                            <br>
                                            <small class="text-success">{{ number_format($receipt->file_size / 1024, 1) }} KB</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pm.expenditures.show', $expenditure) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <div>
                                <button type="submit" name="action" value="save" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="action" value="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-{{ $expenditure->status === 'draft' ? 'secondary' : 'success' }} fs-6">
                            {{ $expenditure->formatted_status }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Submitted</small>
                        <p class="mb-0">{{ $expenditure->submitted_at ? $expenditure->submitted_at->format('M d, Y g:i A') : 'Not submitted' }}</p>
                    </div>
                    

                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pm.expenditures.show', $expenditure) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                        <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Expenditures -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Expenditures</h5>
                </div>
                <div class="card-body">
                    @php
                        $recentExpenditures = \App\Models\DailyExpenditure::where('submitted_by', auth()->id())
                            ->where('id', '!=', $expenditure->id)
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @if($recentExpenditures->count() > 0)
                        @foreach($recentExpenditures as $recentExpenditure)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="text-muted">{{ $recentExpenditure->expense_date->format('M d') }}</small>
                                <br>
                                <small class="fw-medium">{{ Str::limit($recentExpenditure->description, 30) }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-success">₱{{ number_format($recentExpenditure->amount, 0) }}</small>
                                <br>
                                <span class="badge bg-{{ $recentExpenditure->status === 'draft' ? 'secondary' : 'success' }} fs-10">
                                    {{ $recentExpenditure->formatted_status }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">No other expenditures found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Amount field formatting for whole numbers
    const amountField = document.getElementById('amount');
    if (amountField) {
        amountField.addEventListener('input', function() {
            let value = this.value;
            if (value && !isNaN(value)) {
                this.value = parseInt(value);
            }
        });
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
});
</script>
@endpush
@endsection
