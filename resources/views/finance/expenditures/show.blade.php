@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Expenditure Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.expenditures.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>

        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- General Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">ID:</td>
                                    <td>{{ $expenditure->id }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Project:</td>
                                    <td>
                                        @if($expenditure->project)
                                            <span class="font-weight-bold text-primary">{{ $expenditure->project->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Submitter:</td>
                                    <td>
                                        @if($expenditure->submitter)
                                            <span class="font-weight-bold">{{ $expenditure->submitter->name }}</span>
                                            <br><small class="text-muted">{{ $expenditure->submitter->role }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        <span class="badge badge-{{ $expenditure->status_color }} badge-lg">
                                            {{ $expenditure->formatted_status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Expense Date:</td>
                                    <td>{{ $expenditure->formatted_expense_date }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Category:</td>
                                    <td>
                                        <span class="badge badge-info">{{ $expenditure->formatted_category }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Payment Method:</td>
                                    <td>{{ $expenditure->formatted_payment_method }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Reference Number:</td>
                                    <td>{{ $expenditure->reference_number ?: 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description and Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Description & Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="font-weight-bold">Description:</h6>
                            <p class="text-justify">{{ $expenditure->description }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold">Amount:</h6>
                            <h3 class="text-success font-weight-bold">{{ $expenditure->formatted_amount }}</h3>
                        </div>
                    </div>
                    
                    @if($expenditure->location || $expenditure->vendor_supplier)
                        <hr>
                        <div class="row">
                            @if($expenditure->location)
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold">Location:</h6>
                                    <p>{{ $expenditure->location }}</p>
                                </div>
                            @endif
                            @if($expenditure->vendor_supplier)
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold">Vendor/Supplier:</h6>
                                    <p>{{ $expenditure->vendor_supplier }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($expenditure->notes)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="font-weight-bold">Notes:</h6>
                                <p class="text-justify">{{ $expenditure->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Receipts Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Attached Receipts</h6>
                    <span class="badge badge-info badge-lg">{{ $expenditure->receipts_count }} Receipt(s)</span>
                </div>
                <div class="card-body">
                    @if($expenditure->receipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenditure->receipts as $receipt)
                                    <tr>
                                        <td>{{ $receipt->id }}</td>
                                        <td>
                                            <span class="font-weight-bold text-success">
                                                ₱{{ number_format($receipt->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $receipt->status === 'verified' ? 'success' : 'warning' }}">
                                                {{ ucfirst($receipt->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $receipt->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            @if($receipt->file_path)
                                                <a href="{{ route('finance.receipts.download', $receipt) }}" 
                                                   class="btn btn-sm btn-info" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Receipts Summary -->
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total Receipts</h6>
                                        <h4 class="text-primary">{{ $expenditure->formatted_amount }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Receipts Coverage</h6>
                                        <h4 class="text-success">{{ number_format($expenditure->receipts_coverage, 1) }}%</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Status</h6>
                                        <h4 class="text-{{ $expenditure->receipts_coverage >= 100 ? 'success' : 'warning' }}">
                                            {{ $expenditure->receipts_coverage >= 100 ? 'Complete' : 'Incomplete' }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No receipts attached to this expenditure.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Liquidated Forms Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Liquidated Forms</h6>
                    <span class="badge badge-info badge-lg">{{ $expenditure->liquidated_forms_count }} Form(s)</span>
                </div>
                <div class="card-body">
                    @if($expenditure->liquidatedForms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Form #</th>
                                        <th>Title</th>
                                        <th>Amount Allocated</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenditure->liquidatedForms as $liquidatedForm)
                                    <tr>
                                        <td>{{ $liquidatedForm->form_number }}</td>
                                        <td>{{ $liquidatedForm->title }}</td>
                                        <td>
                                            <span class="font-weight-bold text-success">
                                                ₱{{ number_format($liquidatedForm->pivot->amount_allocated, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $liquidatedForm->status_color }}">
                                                {{ $liquidatedForm->formatted_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" 
                                               class="btn btn-sm btn-info" title="View Form">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">This expenditure has not been included in any liquidated forms yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Current Status:</label>
                        <div class="mt-2">
                            <span class="badge badge-{{ $expenditure->status_color }} badge-lg">
                                {{ $expenditure->formatted_status }}
                            </span>
                        </div>
                    </div>

                    @if($expenditure->submitted_at)
                        <div class="mb-3">
                            <label class="font-weight-bold">Submitted:</label>
                            <div class="mt-2">
                                <p class="mb-1">{{ $expenditure->formatted_submitted_date }}</p>
                                <small class="text-muted">{{ $expenditure->submitted_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="font-weight-bold">Created:</label>
                        <div class="mt-2">
                            <p class="mb-1">{{ $expenditure->created_at->format('M d, Y H:i') }}</p>
                            <small class="text-muted">{{ $expenditure->created_at->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if($expenditure->updated_at != $expenditure->created_at)
                        <div class="mb-3">
                            <label class="font-weight-bold">Last Updated:</label>
                            <div class="mt-2">
                                <p class="mb-1">{{ $expenditure->updated_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $expenditure->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.expenditures.index') }}" 
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> View All Expenditures
                        </a>
                    </div>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Finance users can view all expenditures. 
                        No approval process is required for submitted expenditures.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
