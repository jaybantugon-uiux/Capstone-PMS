@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Receipt</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Receipt
            </a>
            <a href="{{ route('admin.receipts.index') }}" class="btn btn-info btn-sm">
                <i class="fas fa-list"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Receipt Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.receipts.update', $receipt) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_number">Receipt Number</label>
                                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                           id="receipt_number" name="receipt_number" 
                                           value="{{ old('receipt_number', $receipt->receipt_number) }}" 
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
                                           value="{{ old('receipt_date', $receipt->receipt_date ? $receipt->receipt_date->format('Y-m-d') : '') }}" 
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
                                           value="{{ old('vendor_name', $receipt->vendor_name) }}" 
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
                                    <label for="amount">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" 
                                               value="{{ old('amount', $receipt->amount) }}" 
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
                                               value="{{ old('tax_amount', $receipt->tax_amount) }}" 
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
                                            <option value="{{ $value }}" {{ old('status', $receipt->status) == $value ? 'selected' : '' }}>
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
                                            <option value="{{ $user->id }}" {{ old('uploaded_by', $receipt->uploaded_by) == $user->id ? 'selected' : '' }}>
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
                                      placeholder="Enter description">{{ old('description', $receipt->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Enter notes">{{ old('notes', $receipt->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current File Information -->
            @if($receipt->file_path)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Current File</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if(in_array(strtolower(pathinfo($receipt->original_file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                <img src="{{ Storage::url($receipt->file_path) }}" 
                                     alt="Receipt Image" 
                                     class="img-fluid rounded" 
                                     style="max-height: 200px;">
                            @else
                                <div class="bg-light p-4 rounded">
                                    <i class="fas fa-file fa-3x text-muted"></i>
                                    <p class="mt-2 text-muted">{{ $receipt->original_file_name }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Filename:</strong></td>
                                <td>{{ $receipt->original_file_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Size:</strong></td>
                                <td>{{ $receipt->file_size ? number_format($receipt->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td>{{ $receipt->file_type ?? 'N/A' }}</td>
                            </tr>
                        </table>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ Storage::url($receipt->file_path) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="fas fa-download"></i> Download Current File
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Receipt
                        </a>
                        <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteReceipt()">
                            <i class="fas fa-trash"></i> Delete Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Receipt Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Receipt #:</strong></td>
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
                            <td><strong>Tax:</strong></td>
                            <td>{{ $receipt->tax_amount > 0 ? '₱' . number_format($receipt->tax_amount, 2) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total:</strong></td>
                            <td><strong>₱{{ number_format($receipt->amount + $receipt->tax_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge badge-{{ $receipt->status_badge_color }}">{{ $receipt->formatted_status }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this receipt? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('admin.receipts.destroy', $receipt) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteReceipt() {
    $('#deleteModal').modal('show');
}

// Auto-calculate total amount
document.getElementById('amount').addEventListener('input', calculateTotal);
document.getElementById('tax_amount').addEventListener('input', calculateTotal);

function calculateTotal() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const total = amount + taxAmount;
    
    // You can display the total somewhere if needed
    console.log('Total:', total);
}
</script>
@endpush
