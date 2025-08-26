@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Receipt</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.receipts.show', $receipt) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Receipt
            </a>
            <a href="{{ route('finance.receipts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list"></i> All Receipts
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Receipt Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.receipts.update', $receipt) }}" method="POST" id="editReceiptForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_date">Receipt Date <span class="text-danger">*</span></label>
                                    <input type="date" name="receipt_date" id="receipt_date" 
                                           class="form-control @error('receipt_date') is-invalid @enderror" 
                                           value="{{ old('receipt_date', $receipt->receipt_date->format('Y-m-d')) }}" required>
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
                                            <option value="{{ $value }}" {{ old('receipt_type', $receipt->receipt_type) == $value ? 'selected' : '' }}>
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
                                           value="{{ old('vendor_name', $receipt->vendor_name) }}" required>
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
                                           value="{{ old('receipt_number', $receipt->receipt_number) }}">
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
                                               value="{{ old('amount', $receipt->amount) }}" step="0.01" min="0.01" required>
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
                                               value="{{ old('tax_amount', $receipt->tax_amount) }}" step="0.01" min="0">
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
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $receipt->description) }}</textarea>
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
                                    <option value="{{ $financial_report->id }}" 
                                            {{ old('financial_report_id', $receipt->financial_report_id) == $financial_report->id ? 'selected' : '' }}>
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
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $receipt->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Receipt
                            </button>
                            <a href="{{ route('finance.receipts.show', $receipt) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current File Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current File</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        @if($receipt->is_image)
                            <img src="{{ Storage::disk('public')->url($receipt->file_path) }}" 
                                 class="img-fluid mb-2" style="max-height: 200px;" alt="Receipt">
                        @elseif($receipt->is_pdf)
                            <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                        @else
                            <i class="fas fa-file fa-3x text-gray-300 mb-3"></i>
                        @endif
                        <p class="text-muted">{{ $receipt->original_file_name }}</p>
                        <small class="text-muted">{{ $receipt->formatted_file_size }}</small>
                        <div class="mt-2">
                            <a href="{{ route('finance.receipts.download', $receipt) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Current Status:</strong><br>
                        <span class="badge badge-{{ $receipt->status_badge_color }} badge-lg">
                            {{ $receipt->formatted_status }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Uploaded By:</strong><br>
                        {{ $receipt->uploader->first_name }} {{ $receipt->uploader->last_name }}
                    </div>
                    <div class="mb-2">
                        <strong>Upload Date:</strong><br>
                        {{ $receipt->created_at->format('M d, Y g:i A') }}
                    </div>
                </div>
            </div>

            <!-- Linked Expenditure Card -->
            @if($receipt->dailyExpenditure)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Linked Expenditure</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Description:</strong><br>
                        {{ $receipt->dailyExpenditure->description }}
                    </div>
                    <div class="mb-2">
                        <strong>Amount:</strong><br>
                        <span class="text-success">{{ $receipt->dailyExpenditure->formatted_amount }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Date:</strong><br>
                        {{ $receipt->dailyExpenditure->formatted_expense_date }}
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $receipt->dailyExpenditure->status === 'submitted' ? 'success' : 'warning' }}">
                            {{ ucfirst($receipt->dailyExpenditure->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Edit Guidelines Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Only pending and active receipts can be edited
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            File cannot be changed after upload
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Amount and tax must be positive numbers
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Linking to expenditure helps with tracking
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Changes are logged for audit purposes
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#editReceiptForm').submit(function(e) {
        const amount = parseFloat($('#amount').val());
        const taxAmount = parseFloat($('#tax_amount').val()) || 0;

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

    // Confirm before leaving with unsaved changes
    let formChanged = false;
    $('#editReceiptForm input, #editReceiptForm textarea, #editReceiptForm select').on('change', function() {
        formChanged = true;
    });

    $('#editReceiptForm').on('submit', function() {
        formChanged = false;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>
@endpush
