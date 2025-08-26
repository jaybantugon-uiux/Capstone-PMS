@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Receipt Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.receipts.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Receipts
            </a>
            @if($receipt->canBeEdited())
                <a href="{{ route('finance.receipts.edit', $receipt) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
            <a href="{{ route('finance.receipts.download', $receipt) }}" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Receipt Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Information</h6>
                    <span class="badge badge-{{ $receipt->status_badge_color }} badge-lg">
                        {{ $receipt->formatted_status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $receipt->vendor_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Receipt Number:</strong></td>
                                    <td>{{ $receipt->receipt_number ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Receipt Date:</strong></td>
                                    <td>{{ $receipt->formatted_receipt_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Receipt Type:</strong></td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ \App\Models\Receipt::getReceiptTypeOptions()[$receipt->receipt_type] ?? $receipt->receipt_type }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td class="font-weight-bold text-success">{{ $receipt->formatted_amount }}</td>
                                </tr>
                                @if($receipt->tax_amount > 0)
                                <tr>
                                    <td><strong>Tax Amount:</strong></td>
                                    <td>{{ $receipt->formatted_tax_amount }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td class="font-weight-bold text-primary">{{ $receipt->formatted_total_amount_with_tax }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Uploaded By:</strong></td>
                                    <td>{{ $receipt->uploader->first_name }} {{ $receipt->uploader->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Upload Date:</strong></td>
                                    <td>{{ $receipt->created_at->format('M d, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>File Size:</strong></td>
                                    <td>{{ $receipt->formatted_file_size }}</td>
                                </tr>
                                <tr>
                                    <td><strong>File Type:</strong></td>
                                    <td>{{ strtoupper($receipt->file_extension) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($receipt->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Description:</strong></h6>
                            <p class="text-muted">{{ $receipt->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($receipt->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Notes:</strong></h6>
                            <p class="text-muted">{{ $receipt->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- File Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Preview</h6>
                </div>
                <div class="card-body">
                    @if($receipt->is_image)
                        <div class="text-center">
                            <img src="{{ Storage::disk('public')->url($receipt->file_path) }}" 
                                 class="img-fluid" style="max-height: 500px;" alt="Receipt">
                        </div>
                    @elseif($receipt->is_pdf)
                        <div class="text-center">
                            <iframe src="{{ Storage::disk('public')->url($receipt->file_path) }}" 
                                    width="100%" height="500px" frameborder="0"></iframe>
                        </div>
                    @else
                        <div class="text-center">
                            <i class="fas fa-file fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">File preview not available</p>
                            <a href="{{ route('finance.receipts.download', $receipt) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    @if($receipt->canBeEdited())
                        <a href="{{ route('finance.receipts.edit', $receipt) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Receipt
                        </a>
                    @endif

                    <a href="{{ route('finance.receipts.download', $receipt) }}" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-download"></i> Download File
                    </a>

                    @if($receipt->canBeDeleted())
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Receipt
                        </button>
                    @endif
                </div>
            </div>



            <!-- Linked Financial Report Card -->
            @if($receipt->financialReport)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Linked Financial Report</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Title:</strong><br>
                        {{ $receipt->financialReport->title }}
                    </div>
                    <div class="mb-2">
                        <strong>Period:</strong><br>
                        {{ $receipt->financialReport->formatted_period }}
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $receipt->financialReport->status_badge_color }}">
                            {{ $receipt->financialReport->formatted_status }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Total Expenditures:</strong><br>
                        <span class="text-success">{{ $receipt->financialReport->formatted_total_expenditures }}</span>
                    </div>
                    <a href="{{ route('finance.financial-reports.show', $receipt->financialReport) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> View Report
                    </a>
                </div>
            </div>
            @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Link to Financial Report</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">This receipt is not linked to any financial report.</p>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#linkReportModal">
                        <i class="fas fa-link"></i> Link to Financial Report
                    </button>
                </div>
            </div>
            @endif

            <!-- Liquidated Form Card -->
            @if($receipt->liquidatedForm)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Liquidated Form</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Form Number:</strong><br>
                        {{ $receipt->liquidatedForm->form_number }}
                    </div>
                    <div class="mb-2">
                        <strong>Title:</strong><br>
                        {{ $receipt->liquidatedForm->title }}
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $receipt->liquidatedForm->status === 'approved' ? 'success' : 'warning' }}">
                            {{ ucfirst($receipt->liquidatedForm->status) }}
                        </span>
                    </div>
                    <a href="{{ route('finance.liquidated-forms.show', $receipt->liquidatedForm) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> View Form
                    </a>
                </div>
            </div>
            @endif

            <!-- File Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">File Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Original Name:</strong><br>
                        <small class="text-muted">{{ $receipt->original_file_name }}</small>
                    </div>
                    <div class="mb-2">
                        <strong>File Size:</strong><br>
                        {{ $receipt->formatted_file_size }}
                    </div>
                    <div class="mb-2">
                        <strong>File Type:</strong><br>
                        {{ strtoupper($receipt->file_extension) }}
                    </div>
                    <div class="mb-2">
                        <strong>Uploaded:</strong><br>
                        {{ $receipt->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Receipt</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this receipt? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('finance.receipts.destroy', $receipt) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Receipt</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Link to Financial Report Modal -->
<div class="modal fade" id="linkReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Link to Financial Report</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('finance.receipts.match-financial-report', $receipt) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="financial_report_id">Select Financial Report</label>
                        <select name="financial_report_id" id="financial_report_id" class="form-control" required>
                            <option value="">Select a financial report...</option>
                            @foreach(\App\Models\FinancialReport::whereIn('status', ['generated', 'approved', 'liquidated'])->get() as $financial_report)
                                <option value="{{ $financial_report->id }}">
                                    {{ $financial_report->title }} - {{ $financial_report->formatted_period }}
                                    ({{ ucfirst($financial_report->status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Link Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
