@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Receipts Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.receipts.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Upload Receipt
            </a>
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#bulkUploadModal">
                <i class="fas fa-upload"></i> Bulk Upload
            </button>

        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('finance.receipts.index') }}" id="filtersForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="receipt_type">Receipt Type</label>
                            <select name="receipt_type" id="receipt_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach($receiptTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('receipt_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="uploader_id">Uploader</label>
                            <select name="uploader_id" id="uploader_id" class="form-control">
                                <option value="">All Uploaders</option>
                                @foreach($uploaders as $uploader)
                                    <option value="{{ $uploader->id }}" {{ request('uploader_id') == $uploader->id ? 'selected' : '' }}>
                                        {{ $uploader->first_name }} {{ $uploader->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Vendor, description, receipt #...">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="amount_min">Min Amount</label>
                            <input type="number" name="amount_min" id="amount_min" class="form-control" 
                                   value="{{ request('amount_min') }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="amount_max">Max Amount</label>
                            <input type="number" name="amount_max" id="amount_max" class="form-control" 
                                   value="{{ request('amount_max') }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="vendor_name">Vendor</label>
                            <input type="text" name="vendor_name" id="vendor_name" class="form-control" 
                                   value="{{ request('vendor_name') }}" placeholder="Vendor name...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('finance.receipts.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Receipts ({{ $receipts->total() }})</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($receipts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Receipt</th>
                                <th>Vendor</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Uploader</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $receipt)
                            <tr>
                                <td>
                                    <input type="checkbox" class="receipt-checkbox" value="{{ $receipt->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            @if($receipt->is_image)
                                                <i class="fas fa-image text-primary"></i>
                                            @elseif($receipt->is_pdf)
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            @else
                                                <i class="fas fa-file text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">{{ Str::limit($receipt->original_file_name, 30) }}</div>
                                            <small class="text-muted">{{ $receipt->formatted_file_size }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-weight-bold">{{ Str::limit($receipt->vendor_name, 25) }}</div>
                                        @if($receipt->receipt_number)
                                            <small class="text-muted">#{{ $receipt->receipt_number }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-success">{{ $receipt->formatted_amount }}</div>
                                    @if($receipt->tax_amount > 0)
                                        <small class="text-muted">Tax: {{ $receipt->formatted_tax_amount }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $receiptTypeOptions[$receipt->receipt_type] ?? $receipt->receipt_type }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $receipt->status_badge_color }}">
                                        {{ $receipt->formatted_status }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div>{{ $receipt->uploader->first_name }} {{ $receipt->uploader->last_name }}</div>
                                        <small class="text-muted">{{ $receipt->uploader->role }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div>{{ $receipt->formatted_receipt_date }}</div>
                                        <small class="text-muted">{{ $receipt->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('finance.receipts.show', $receipt) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('finance.receipts.download', $receipt) }}" 
                                           class="btn btn-sm btn-outline-success" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($receipt->canBeEdited())
                                            <a href="{{ route('finance.receipts.edit', $receipt) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($receipt->canBeDeleted())
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-receipt-id="{{ $receipt->id }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $receipts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No receipts found</h5>
                    <p class="text-gray-400">Upload your first receipt to get started.</p>
                    <a href="{{ route('finance.receipts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload Receipt
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Upload Receipts</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('finance.receipts.bulk-upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_files">Select Files (Max 10)</label>
                        <input type="file" name="files[]" id="bulk_files" class="form-control" multiple 
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, PDF (Max 10MB each)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_receipt_date">Receipt Date</label>
                                <input type="date" name="receipt_date" id="bulk_receipt_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_vendor_name">Vendor Name</label>
                                <input type="text" name="vendor_name" id="bulk_vendor_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_receipt_type">Receipt Type</label>
                                <select name="receipt_type" id="bulk_receipt_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    @foreach($receiptTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_financial_report_id">Link to Financial Report (Optional)</label>
                                <select name="financial_report_id" id="bulk_financial_report_id" class="form-control">
                                    <option value="">Select Financial Report</option>
                                    @foreach($financial_reports as $financial_report)
                                        <option value="{{ $financial_report->id }}">
                                            {{ $financial_report->title }} - {{ $financial_report->formatted_period }}
                                            ({{ ucfirst($financial_report->status) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Empty column for layout balance -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Receipts</button>
                </div>
            </form>
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
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Receipt</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#selectAll').change(function() {
        $('.receipt-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkButtons();
    });

    $('.receipt-checkbox').change(function() {
        updateBulkButtons();
    });

    function updateBulkButtons() {
        const checkedCount = $('.receipt-checkbox:checked').length;
        $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
    }

    // Delete button
    $('.delete-btn').click(function() {
        const receiptId = $(this).data('receipt-id');
        $('#deleteForm').attr('action', `/finance/receipts/${receiptId}`);
        $('#deleteModal').modal('show');
    });

    // Bulk delete
    $('#bulkDeleteBtn').click(function() {
        const selectedIds = $('.receipt-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        if (confirm('Are you sure you want to delete the selected receipts? This action cannot be undone.')) {
            const form = $('<form method="POST" action="{{ route("finance.receipts.bulk-delete") }}">')
                .append($('<input type="hidden" name="_token" value="{{ csrf_token() }}">'))
                .append($('<input type="hidden" name="_method" value="DELETE">'));

            selectedIds.forEach(id => {
                form.append($(`<input type="hidden" name="receipt_ids[]" value="${id}">`));
            });

            $('body').append(form);
            form.submit();
        }
    });


});
</script>
@endpush
