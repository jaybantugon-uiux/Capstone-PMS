@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Daily Expenditure</h1>
        <div class="d-flex gap-2">
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
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Create Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expenditure Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expenditures.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="submitted_by" class="form-label">Submitter <span class="text-danger">*</span></label>
                                    <select name="submitted_by" id="submitted_by" class="form-control @error('submitted_by') is-invalid @enderror" required>
                                        <option value="">Select Submitter</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('submitted_by') == $user->id ? 'selected' : '' }}>
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
                                           value="{{ old('expense_date', date('Y-m-d')) }}" required>
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
                                            <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>
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
                                      placeholder="Provide a detailed description of the expense..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           step="0.01" min="0.01" placeholder="0.00" value="{{ old('amount') }}" required>
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
                                            <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
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
                                           placeholder="Where the expense occurred" value="{{ old('location') }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
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
                                <div class="form-group">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" 
                                           placeholder="Receipt, invoice, or reference number" value="{{ old('reference_number') }}">
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

                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                                      placeholder="Additional notes or comments...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> As an administrator, you can create expenditures on behalf of any user and set any status. This is useful for system corrections or administrative entries.
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Expenditure
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
                </div>
            </div>

            <!-- Recent Expenditures Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Expenditures
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $recentExpenditures = \App\Models\DailyExpenditure::with(['project', 'submitter'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($recentExpenditures->count() > 0)
                        @foreach($recentExpenditures as $expenditure)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ Str::limit($expenditure->description, 50) }}</h6>
                                        <small class="text-muted">
                                            {{ $expenditure->project ? $expenditure->project->name : 'N/A' }} • 
                                            {{ $expenditure->expense_date->format('M d') }}
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-weight-bold">₱{{ number_format($expenditure->amount, 2) }}</span>
                                        <br>
                                        <span class="badge badge-{{ $expenditure->status_badge_color }} badge-sm">
                                            {{ $expenditure->formatted_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No recent expenditures</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
