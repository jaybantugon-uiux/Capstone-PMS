@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Daily Expenditure</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenditures.show', $expenditure) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.expenditures.index') }}">Daily Expenditures</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expenditure Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expenditures.update', $expenditure) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
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
                                <div class="form-group">
                                    <label for="submitted_by" class="form-label">Submitter <span class="text-danger">*</span></label>
                                    <select name="submitted_by" id="submitted_by" class="form-control @error('submitted_by') is-invalid @enderror" required>
                                        <option value="">Select Submitter</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('submitted_by', $expenditure->submitted_by) == $user->id ? 'selected' : '' }}>
                                                {{ $user->full_name }} ({{ $user->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('submitted_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date" id="expense_date" class="form-control @error('expense_date') is-invalid @enderror" 
                                           value="{{ old('expense_date', $expenditure->expense_date->format('Y-m-d')) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($category_options as $value => $label)
                                            <option value="{{ $value }}" {{ old('category', $expenditure->category) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" 
                                      placeholder="Provide a detailed description of the expense..." required>{{ old('description', $expenditure->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" min="0.01" placeholder="0.00" value="{{ old('amount', $expenditure->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Payment Method</option>
                                        @foreach($payment_method_options as $value => $label)
                                            <option value="{{ $value }}" {{ old('payment_method', $expenditure->payment_method) == $value ? 'selected' : '' }}>
                                                {{ $label }}
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
                                <div class="form-group">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" 
                                           placeholder="Where the expense occurred" value="{{ old('location', $expenditure->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vendor_supplier" class="form-label">Vendor/Supplier</label>
                                    <input type="text" name="vendor_supplier" id="vendor_supplier" class="form-control @error('vendor_supplier') is-invalid @enderror" 
                                           placeholder="Vendor or supplier name" value="{{ old('vendor_supplier', $expenditure->vendor_supplier) }}">
                                    @error('vendor_supplier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                           placeholder="Receipt, invoice, or reference number" value="{{ old('reference_number', $expenditure->reference_number) }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        @foreach($status_options as $value => $label)
                                            <option value="{{ $value }}" {{ old('status', $expenditure->status) == $value ? 'selected' : '' }}>
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

                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                                      placeholder="Additional notes or comments...">{{ old('notes', $expenditure->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> As an administrator, you can edit any expenditure and modify all fields including the submitter and status. Changes will be logged for audit purposes.
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Expenditure
                            </button>
                            <a href="{{ route('admin.expenditures.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Current Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>#{{ $expenditure->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-{{ $expenditure->status_badge_color }}">
                                    {{ $expenditure->formatted_status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Project:</strong></td>
                            <td>{{ $expenditure->project ? $expenditure->project->name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Submitter:</strong></td>
                                                            <td>{{ $expenditure->submitter ? $expenditure->submitter->full_name : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td class="font-weight-bold">₱{{ number_format($expenditure->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $expenditure->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>{{ $expenditure->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Help & Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Required Fields</h6>
                    <ul class="small text-muted">
                        <li>Project - Select the project this expense belongs to</li>
                        <li>Submitter - Choose the user who submitted this expense</li>
                        <li>Expense Date - When the expense occurred</li>
                        <li>Category - Type of expense</li>
                        <li>Description - Detailed description of the expense</li>
                        <li>Amount - Total cost in Philippine Peso</li>
                        <li>Payment Method - How the expense was paid</li>
                        <li>Status - Current status of the expenditure</li>
                    </ul>

                    <h6>Optional Fields</h6>
                    <ul class="small text-muted">
                        <li>Location - Where the expense occurred</li>
                        <li>Vendor/Supplier - Who provided the service/goods</li>
                        <li>Reference Number - Receipt or invoice number</li>
                        <li>Notes - Additional comments or context</li>
                    </ul>

                    <hr>

                    <h6>Status Options</h6>
                    <ul class="small text-muted">
                        <li><strong>Draft:</strong> Work in progress, not submitted</li>
                        <li><strong>Submitted:</strong> Submitted for review</li>
                        <li><strong>Approved:</strong> Approved by finance/admin</li>
                        <li><strong>Rejected:</strong> Rejected with reason</li>
                    </ul>

                    <hr>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Changing the status may trigger notifications and affect the approval workflow.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
