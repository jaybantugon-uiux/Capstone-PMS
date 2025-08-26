@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Receipts Administration</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.receipts.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Receipt
            </a>
            <button type="button" class="btn btn-success btn-sm" onclick="exportSelected()">
                <i class="fas fa-download"></i> Export Selected
            </button>
        </div>
    </div>

                   <!-- Statistics Cards -->
      <div class="row mb-4">
          <div class="col-md-2">
              <div class="card bg-primary text-white">
                  <div class="card-body text-center">
                      <h4>{{ $statistics['total'] }}</h4>
                      <small>Total Receipts</small>
                  </div>
              </div>
          </div>
          <div class="col-md-2">
              <div class="card bg-success text-white">
                  <div class="card-body text-center">
                      <h4>{{ $statistics['active'] }}</h4>
                      <small>Active</small>
                  </div>
              </div>
          </div>
          <div class="col-md-2">
              <div class="card bg-warning text-white">
                  <div class="card-body text-center">
                      <h4>{{ $statistics['pending'] }}</h4>
                      <small>Pending</small>
                  </div>
              </div>
          </div>
          <div class="col-md-2">
              <div class="card bg-danger text-white">
                  <div class="card-body text-center">
                      <h4>{{ $statistics['archived'] }}</h4>
                      <small>Archived</small>
                  </div>
              </div>
          </div>
          <div class="col-md-2">
              <div class="card bg-info text-white">
                  <div class="card-body text-center">
                      <h4>₱{{ number_format($statistics['total_amount'], 2) }}</h4>
                      <small>Total Amount</small>
                  </div>
              </div>
          </div>
          <div class="col-md-2">
              <div class="card bg-secondary text-white">
                  <div class="card-body text-center">
                      <h4>{{ $statistics['clarification_needed'] }}</h4>
                      <small>Clarification Needed</small>
                  </div>
              </div>
          </div>
      </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.receipts.index') }}">
                <div class="row">
                    <div class="col-md-2">
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
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="uploader_id">Uploader</label>
                            <select name="uploader_id" id="uploader_id" class="form-control">
                                <option value="">All Uploaders</option>
                                @foreach($uploaders as $uploader)
                                    <option value="{{ $uploader->id }}" {{ request('uploader_id') == $uploader->id ? 'selected' : '' }}>
                                        {{ $uploader->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Vendor, description, receipt #...">
                        </div>
                    </div>
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
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="amount_min">Amount Min</label>
                            <input type="number" name="amount_min" id="amount_min" class="form-control" 
                                   value="{{ request('amount_min') }}" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="amount_max">Amount Max</label>
                            <input type="number" name="amount_max" id="amount_max" class="form-control" 
                                   value="{{ request('amount_max') }}" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="per_page">Per Page</label>
                            <select name="per_page" id="per_page" class="form-control">
                                <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Receipts</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('update_status')">
                    <i class="fas fa-edit"></i> Update Status
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="bulkAction('request_clarification')">
                    <i class="fas fa-question-circle"></i> Request Clarification
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Receipt #</th>
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Clarification</th>
                            <th>Uploader</th>
                            <th>Date</th>
                            <th>Created</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_receipts[]" value="{{ $receipt->id }}" class="form-checkbox">
                            </td>
                            <td>
                                <strong>{{ $receipt->receipt_number ?? 'N/A' }}</strong>
                            </td>
                            <td>{{ $receipt->vendor_name }}</td>
                            <td>
                                <strong>₱{{ number_format($receipt->amount, 2) }}</strong>
                                @if($receipt->tax_amount > 0)
                                    <br><small class="text-muted">Tax: ₱{{ number_format($receipt->tax_amount, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $receipt->receipt_type }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $receipt->status_badge_color }}">
                                    {{ $receipt->formatted_status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $receipt->clarification_status_badge_color }}">
                                    {{ $receipt->formatted_clarification_status }}
                                </span>
                            </td>
                            <td>
                                @if($receipt->uploader)
                                    {{ $receipt->uploader->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $receipt->receipt_date ? $receipt->receipt_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $receipt->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.receipts.show', $receipt) }}" 
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.receipts.edit', $receipt) }}" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($receipt->canRequestClarification())
                                        <a href="{{ route('admin.receipts.request-clarification.form', $receipt) }}" 
                                           class="btn btn-info btn-sm" title="Request Clarification">
                                            <i class="fas fa-question-circle"></i>
                                        </a>
                                    @endif
                                    @if($receipt->clarification_status === 'requested')
                                        <a href="{{ route('admin.receipts.resolve-clarification.form', $receipt) }}" 
                                           class="btn btn-success btn-sm" title="Resolve Clarification">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                    @if($receipt->file_path)
                                        <a href="{{ Storage::url($receipt->file_path) }}" 
                                           class="btn btn-success btn-sm" title="Download" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteReceipt({{ $receipt->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No receipts found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $receipts->firstItem() ?? 0 }} to {{ $receipts->lastItem() ?? 0 }} 
                    of {{ $receipts->total() }} entries
                </div>
                <div>
                    {{ $receipts->appends(request()->query())->links() }}
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
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Bulk Action</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="bulkActionForm" method="POST" action="{{ route('admin.receipts.bulk-action') }}">
                    @csrf
                    <input type="hidden" name="action" id="bulkActionType">
                    <div id="bulkActionFields"></div>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="selected_receipts[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function deleteReceipt(receiptId) {
    if (confirm('Are you sure you want to delete this receipt?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/receipts/${receiptId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkAction(action) {
    const selectedReceipts = document.querySelectorAll('input[name="selected_receipts[]"]:checked');
    
    if (selectedReceipts.length === 0) {
        alert('Please select at least one receipt');
        return;
    }
    
    const form = document.getElementById('bulkActionForm');
    const actionField = document.getElementById('bulkActionType');
    const message = document.getElementById('bulkActionMessage');
    const fields = document.getElementById('bulkActionFields');
    
    actionField.value = action;
    
    // Clear previous fields
    fields.innerHTML = '';
    
    // Add selected receipts
    selectedReceipts.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'receipt_ids[]';
        input.value = checkbox.value;
        fields.appendChild(input);
    });
    
    // Set message based on action
    switch(action) {
        case 'delete':
            message.textContent = `Are you sure you want to delete ${selectedReceipts.length} selected receipt(s)? This action cannot be undone.`;
            break;
        case 'update_status':
            message.textContent = `Are you sure you want to update the status of ${selectedReceipts.length} selected receipt(s)?`;
            // Add status field
            const statusField = document.createElement('div');
            statusField.className = 'form-group';
            statusField.innerHTML = `
                <label for="status">New Status:</label>
                <select class="form-control" name="status" id="status" required>
                    <option value="">Select Status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            `;
            fields.appendChild(statusField);
            break;
        case 'request_clarification':
            message.textContent = `Are you sure you want to request clarification for ${selectedReceipts.length} selected receipt(s)?`;
            // Add notes field
            const notesField = document.createElement('div');
            notesField.className = 'form-group';
            notesField.innerHTML = `
                <label for="notes">Clarification Notes:</label>
                <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Please specify what clarification is needed..." required></textarea>
            `;
            fields.appendChild(notesField);
            break;
    }
    
    $('#bulkActionModal').modal('show');
}

function exportSelected() {
    const selectedReceipts = document.querySelectorAll('input[name="selected_receipts[]"]:checked');
    
    if (selectedReceipts.length === 0) {
        alert('Please select at least one receipt to export');
        return;
    }
    
    // Create form for export
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.receipts.bulk-action") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const actionField = document.createElement('input');
    actionField.type = 'hidden';
    actionField.name = 'action';
    actionField.value = 'export';
    
    form.appendChild(csrfToken);
    form.appendChild(actionField);
    
    selectedReceipts.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'receipt_ids[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
