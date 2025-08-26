@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Create New Expenditure</h1>
            <p class="text-muted">Submit a new daily expenditure for your project</p>
        </div>
        <div>
            <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Expenditures
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Expenditure Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pm.expenditures.store') }}" id="expenditureForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Describe the expense..." required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Project *</label>
                                    <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (₱) *</label>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           min="1" placeholder="₱0" value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category *</label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $key => $category)
                                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Method</option>
                                        @foreach($paymentMethods as $key => $method)
                                            <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expense_date" class="form-label">Expense Date *</label>
                                    <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                           value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vendor_supplier" class="form-label">Vendor/Supplier</label>
                                    <input type="text" name="vendor_supplier" id="vendor_supplier" class="form-control @error('vendor_supplier') is-invalid @enderror" 
                                           placeholder="Vendor or supplier name" value="{{ old('vendor_supplier') }}">
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
                                           placeholder="Invoice, receipt, or reference number" value="{{ old('reference_number') }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                           placeholder="Where the expense occurred" value="{{ old('location') }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                      rows="3" placeholder="Any additional information or context...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="receipt_files" class="form-label">Receipt Files</label>
                            <input type="file" name="receipt_files[]" id="receipt_files" class="form-control @error('receipt_files') is-invalid @enderror" 
                                   multiple accept="image/*,.pdf">
                            <div class="form-text">You can upload multiple receipt files (images or PDFs). Maximum 5 files, 5MB each.</div>
                            @error('receipt_files')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="save_draft" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>Save as Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i>Submit Expenditure
                            </button>
                            <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Help Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-1"></i>Help & Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Required Fields</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Description</li>
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Project</li>
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Amount</li>
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Category</li>
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Payment Method</li>
                            <li><i class="fas fa-asterisk text-danger me-1"></i>Expense Date</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-info">Categories</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Materials:</strong> Construction materials, supplies</li>
                            <li><strong>Equipment:</strong> Tools, machinery, rentals</li>
                            <li><strong>Labor:</strong> Subcontractor payments, wages</li>
                            <li><strong>Transportation:</strong> Fuel, vehicle expenses</li>
                            <li><strong>Utilities:</strong> Electricity, water, internet</li>
                            <li><strong>Other:</strong> Miscellaneous expenses</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-success">Tips</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-lightbulb text-warning me-1"></i>Be specific in your description</li>
                            <li><i class="fas fa-lightbulb text-warning me-1"></i>Upload receipts when possible</li>
                            <li><i class="fas fa-lightbulb text-warning me-1"></i>Save as draft if you need to add more details later</li>
                            <li><i class="fas fa-lightbulb text-warning me-1"></i>Double-check amounts and dates</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent Expenditures -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-1"></i>Recent Expenditures
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($recentExpenditures) && $recentExpenditures->count() > 0)
                        @foreach($recentExpenditures->take(3) as $expenditure)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div class="flex-grow-1">
                                        <small class="text-muted">{{ Str::limit($expenditure->description, 30) }}</small>
                                        <br>
                                                                                 <small class="text-success">₱{{ number_format($expenditure->amount, 0) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $expenditure->status_badge_color }} small">
                                            {{ $expenditure->formatted_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-2">
                            <a href="{{ route('pm.expenditures.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    @else
                        <p class="text-muted small text-center">No recent expenditures</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.form-label {
    font-weight: 500;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('expenditureForm');
    const amountField = document.getElementById('amount');
    const descriptionField = document.getElementById('description');
    const projectField = document.getElementById('project_id');

    // Amount field formatting for whole numbers
    amountField.addEventListener('input', function() {
        let value = this.value;
        if (value && !isNaN(value)) {
            this.value = parseInt(value);
        }
    });

    // Character counter for description
    descriptionField.addEventListener('input', function() {
        const maxLength = 500;
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;
        
        // Update character counter if it exists
        let counter = document.getElementById('description-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.id = 'description-counter';
            counter.className = 'form-text';
            descriptionField.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (remaining < 50) {
            counter.className = 'form-text text-warning';
        } else if (remaining < 0) {
            counter.className = 'form-text text-danger';
        } else {
            counter.className = 'form-text text-muted';
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const requiredFields = ['description', 'project_id', 'amount', 'category', 'payment_method', 'expense_date'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validate amount
        const amount = parseInt(amountField.value);
        if (isNaN(amount) || amount <= 0) {
            amountField.classList.add('is-invalid');
            isValid = false;
        }

        // Validate date
        const expenseDate = new Date(document.getElementById('expense_date').value);
        const today = new Date();
        if (expenseDate > today) {
            document.getElementById('expense_date').classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            showNotification('Please fill in all required fields correctly', 'error');
        }
    });

    // File upload validation
    const fileInput = document.getElementById('receipt_files');
    fileInput.addEventListener('change', function() {
        const files = this.files;
        const maxFiles = 5;
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (files.length > maxFiles) {
            alert(`You can only upload up to ${maxFiles} files`);
            this.value = '';
            return;
        }

        for (let file of files) {
            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large. Maximum size is 5MB`);
                this.value = '';
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!validTypes.includes(file.type)) {
                alert(`File "${file.name}" is not a valid file type. Please upload images or PDFs only`);
                this.value = '';
                return;
            }
        }
    });

    // Show notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Category-specific fields
    const categoryField = document.getElementById('category');
    categoryField.addEventListener('change', function() {
        const category = this.value;
        
        // Show/hide vendor field based on category
        const vendorField = document.getElementById('vendor_supplier');
        if (['materials', 'equipment', 'labor'].includes(category)) {
            vendorField.required = true;
            vendorField.parentNode.querySelector('.form-label').innerHTML = 'Vendor/Supplier *';
        } else {
            vendorField.required = false;
            vendorField.parentNode.querySelector('.form-label').innerHTML = 'Vendor/Supplier';
        }
    });

    // Project change handler
    projectField.addEventListener('change', function() {
        const projectId = this.value;
        if (projectId) {
            // You could fetch project-specific information here
            console.log('Project selected:', projectId);
        }
    });
});
</script>
@endpush
@endsection
