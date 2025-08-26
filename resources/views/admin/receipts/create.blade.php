@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Receipt</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.receipts.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_number">Receipt Number</label>
                                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                           id="receipt_number" name="receipt_number" 
                                           value="{{ old('receipt_number') }}" 
                                           placeholder="Enter receipt number">
                                    @error('receipt_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_date">Receipt Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('receipt_date') is-invalid @enderror" 
                                           id="receipt_date" name="receipt_date" 
                                           value="{{ old('receipt_date', date('Y-m-d')) }}" 
                                           required>
                                    @error('receipt_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_name">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vendor_name') is-invalid @enderror" 
                                           id="vendor_name" name="vendor_name" 
                                           value="{{ old('vendor_name') }}" 
                                           placeholder="Enter vendor name" required>
                                    @error('vendor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_type">Receipt Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('receipt_type') is-invalid @enderror" 
                                            id="receipt_type" name="receipt_type" required>
                                        <option value="">Select Receipt Type</option>
                                        @foreach($receiptTypeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('receipt_type') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('receipt_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" 
                                               value="{{ old('amount') }}" 
                                               step="0.01" min="0.01" placeholder="0.00" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_amount">Tax Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control @error('tax_amount') is-invalid @enderror" 
                                               id="tax_amount" name="tax_amount" 
                                               value="{{ old('tax_amount') }}" 
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    @error('tax_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('status', 'pending') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="uploaded_by">Uploaded By</label>
                                    <select class="form-control @error('uploaded_by') is-invalid @enderror" 
                                            id="uploaded_by" name="uploaded_by">
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('uploaded_by', auth()->user()->id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('uploaded_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Enter description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Enter notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="receipt_file">Receipt File</label>
                            <input type="file" class="form-control-file @error('receipt_file') is-invalid @enderror" 
                                   id="receipt_file" name="receipt_file" 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx">
                            <small class="form-text text-muted">
                                Accepted formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX. Max size: 10MB.
                            </small>
                            @error('receipt_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Back to List
                        </a>

                    </div>
                </div>
            </div>

            <!-- Receipt Guidelines -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Receipt Number:</strong> Optional, but recommended for tracking
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Vendor Name:</strong> Required for proper categorization
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Amount:</strong> Must be greater than zero
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>Tax Amount:</strong> Optional, separate from main amount
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <strong>File Upload:</strong> Supported formats: JPG, PNG, PDF, DOC
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Receipt Summary Preview -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Summary</h6>
                </div>
                <div class="card-body">
                    <div id="receiptSummary">
                        <p class="text-muted">Fill in the form to see a preview of the receipt summary.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-calculate total amount
document.getElementById('amount').addEventListener('input', updateSummary);
document.getElementById('tax_amount').addEventListener('input', updateSummary);
document.getElementById('vendor_name').addEventListener('input', updateSummary);
document.getElementById('receipt_type').addEventListener('change', updateSummary);

function updateSummary() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const total = amount + taxAmount;
    const vendorName = document.getElementById('vendor_name').value || 'N/A';
    const receiptType = document.getElementById('receipt_type');
    const receiptTypeText = receiptType.options[receiptType.selectedIndex]?.text || 'N/A';
    
    const summary = document.getElementById('receiptSummary');
    
    if (vendorName !== 'N/A' || amount > 0) {
        summary.innerHTML = `
            <table class="table table-sm">
                <tr>
                    <td><strong>Vendor:</strong></td>
                    <td>${vendorName}</td>
                </tr>
                <tr>
                    <td><strong>Type:</strong></td>
                    <td>${receiptTypeText}</td>
                </tr>
                <tr>
                    <td><strong>Amount:</strong></td>
                    <td>₱${amount.toFixed(2)}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td>${taxAmount > 0 ? '₱' + taxAmount.toFixed(2) : 'N/A'}</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td><strong>₱${total.toFixed(2)}</strong></td>
                </tr>
            </table>
        `;
    } else {
        summary.innerHTML = '<p class="text-muted">Fill in the form to see a preview of the receipt summary.</p>';
    }
}

// File size validation
document.getElementById('receipt_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (file && file.size > maxSize) {
        alert('File size must be less than 10MB');
        this.value = '';
    }
});
</script>
@endpush
