@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Upload Receipt</h1>
        <a href="{{ route('finance.receipts.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Receipts
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Upload Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.receipts.store') }}" method="POST" enctype="multipart/form-data" id="receiptForm">
                        @csrf
                        
                        <!-- File Upload -->
                        <div class="form-group">
                            <label for="file">Receipt File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" 
                                   accept=".jpg,.jpeg,.png,.pdf" required>
                            <small class="form-text text-muted">
                                Supported formats: JPG, JPEG, PNG, PDF (Max 10MB)
                            </small>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_date">Receipt Date <span class="text-danger">*</span></label>
                                    <input type="date" name="receipt_date" id="receipt_date" 
                                           class="form-control @error('receipt_date') is-invalid @enderror" 
                                           value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                                    @error('receipt_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_type">Receipt Type <span class="text-danger">*</span></label>
                                    <select name="receipt_type" id="receipt_type" 
                                            class="form-control @error('receipt_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        @foreach($receipt_type_options as $value => $label)
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
                                    <label for="vendor_name">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="vendor_name" id="vendor_name" 
                                           class="form-control @error('vendor_name') is-invalid @enderror" 
                                           value="{{ old('vendor_name') }}" required>
                                    @error('vendor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_number">Receipt Number</label>
                                    <input type="text" name="receipt_number" id="receipt_number" 
                                           class="form-control @error('receipt_number') is-invalid @enderror" 
                                           value="{{ old('receipt_number') }}">
                                    @error('receipt_number')
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
                                        <input type="number" name="amount" id="amount" 
                                               class="form-control @error('amount') is-invalid @enderror" 
                                               value="{{ old('amount') }}" step="0.01" min="0.01" required>
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
                                        <input type="number" name="tax_amount" id="tax_amount" 
                                               class="form-control @error('tax_amount') is-invalid @enderror" 
                                               value="{{ old('tax_amount', 0) }}" step="0.01" min="0">
                                    </div>
                                    @error('tax_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>



                        <div class="form-group">
                            <label for="financial_report_id">Link to Financial Report (Optional)</label>
                            <select name="financial_report_id" id="financial_report_id" 
                                    class="form-control @error('financial_report_id') is-invalid @enderror">
                                <option value="">Select Financial Report</option>
                                @foreach($financial_reports as $financial_report)
                                    <option value="{{ $financial_report->id }}" {{ old('financial_report_id') == $financial_report->id ? 'selected' : '' }}>
                                        {{ $financial_report->title }} - {{ $financial_report->formatted_period }}
                                        ({{ ucfirst($financial_report->status) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('financial_report_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Receipt
                            </button>
                            <a href="{{ route('finance.receipts.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- File Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">File Preview</h6>
                </div>
                <div class="card-body">
                    <div id="filePreview" class="text-center">
                        <i class="fas fa-file-upload fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Select a file to preview</p>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Supported formats: JPG, JPEG, PNG, PDF
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Maximum file size: 10MB
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Ensure receipt is clearly readable
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Fill in all required fields accurately
                        </li>

                    </ul>
                </div>
            </div>

            <!-- Receipt Types Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Types</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <strong>Official Receipt:</strong> Standard business receipt<br>
                        <strong>Sales Invoice:</strong> Detailed sales document<br>
                        <strong>Delivery Receipt:</strong> Proof of delivery<br>
                        <strong>Payment Voucher:</strong> Payment authorization<br>
                        <strong>Cash Receipt:</strong> Cash transaction proof<br>
                        <strong>Bank Deposit Slip:</strong> Bank deposit confirmation<br>
                        <strong>Other:</strong> Miscellaneous documents
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // File preview functionality
    $('#file').change(function() {
        const file = this.files[0];
        const preview = $('#filePreview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.html(`
                        <img src="${e.target.result}" class="img-fluid mb-2" style="max-height: 200px;">
                        <p class="text-muted">${file.name}</p>
                        <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    `);
                } else {
                    preview.html(`
                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                        <p class="text-muted">${file.name}</p>
                        <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    `);
                }
            };
            reader.readAsDataURL(file);
        } else {
            preview.html(`
                <i class="fas fa-file-upload fa-3x text-gray-300 mb-3"></i>
                <p class="text-gray-500">Select a file to preview</p>
            `);
        }
    });

    // Form validation
    $('#receiptForm').submit(function(e) {
        const file = $('#file')[0].files[0];
        const amount = parseFloat($('#amount').val());
        const taxAmount = parseFloat($('#tax_amount').val()) || 0;

        // Check file size
        if (file && file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            e.preventDefault();
            return false;
        }

        // Check amount
        if (amount <= 0) {
            alert('Amount must be greater than 0');
            e.preventDefault();
            return false;
        }

        // Check tax amount
        if (taxAmount < 0) {
            alert('Tax amount cannot be negative');
            e.preventDefault();
            return false;
        }

        return true;
    });

    // Auto-calculate total
    $('#amount, #tax_amount').on('input', function() {
        const amount = parseFloat($('#amount').val()) || 0;
        const taxAmount = parseFloat($('#tax_amount').val()) || 0;
        const total = amount + taxAmount;
        
        // You can display the total somewhere if needed
        // $('#totalAmount').text('₱' + total.toFixed(2));
    });
});
</script>
@endpush
