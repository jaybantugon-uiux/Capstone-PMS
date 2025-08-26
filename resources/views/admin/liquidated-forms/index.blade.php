@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Liquidated Forms Administration</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.liquidated-forms.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Form
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
                    <h4>{{ $liquidatedForms->total() }}</h4>
                    <small>Total Forms</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <h4>{{ $liquidatedForms->where('status', 'pending')->count() }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $liquidatedForms->where('status', 'under_review')->count() }}</h4>
                    <small>Under Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $liquidatedForms->where('status', 'flagged')->count() }}</h4>
                    <small>Flagged</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $liquidatedForms->where('status', 'completed')->count() }}</h4>
                    <small>Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>₱{{ number_format($liquidatedForms->sum('total_amount'), 2) }}</h4>
                    <small>Total Amount</small>
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
            <form method="GET" action="{{ route('admin.liquidated-forms.index') }}">
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
                            <label for="project_id">Project</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="preparer_id">Preparer</label>
                            <select name="preparer_id" id="preparer_id" class="form-control">
                                <option value="">All Preparers</option>
                                @foreach($preparers as $preparer)
                                    <option value="{{ $preparer->id }}" {{ request('preparer_id') == $preparer->id ? 'selected' : '' }}>
                                        {{ $preparer->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="Form #, title, description...">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
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
                                <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary">
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
            <h6 class="m-0 font-weight-bold text-primary">Liquidated Forms</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('flag')">
                    <i class="fas fa-flag"></i> Flag Selected
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="bulkAction('request-revision')">
                    <i class="fas fa-edit"></i> Request Revision
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
                            <th>Form Number</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Preparer</th>
                            <th>Status</th>

                            <th>Liquidation Date</th>
                            <th>Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liquidatedForms as $form)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_forms[]" value="{{ $form->id }}" class="form-checkbox">
                            </td>
                            <td>
                                <strong>{{ $form->form_number }}</strong>
                            </td>
                            <td>{{ $form->title }}</td>
                            <td>
                                @if($form->project)
                                    <span class="badge badge-info">{{ $form->project->name }}</span>
                                @else
                                    <span class="text-muted">No Project</span>
                                @endif
                            </td>
                            <td>
                                @if($form->preparer)
                                    {{ $form->preparer->full_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $form->status_badge_color }}">
                                    {{ $form->formatted_status }}
                                </span>
                            </td>

                            <td>{{ $form->liquidation_date ? $form->liquidation_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $form->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.liquidated-forms.show', $form) }}" 
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.liquidated-forms.edit', $form) }}" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($form->canRequestRevision())
                                        <a href="{{ route('admin.liquidated-forms.request-revision.form', $form) }}" 
                                           class="btn btn-secondary btn-sm" title="Request Revision">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteForm({{ $form->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No liquidated forms found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $liquidatedForms->firstItem() ?? 0 }} to {{ $liquidatedForms->lastItem() ?? 0 }} 
                    of {{ $liquidatedForms->total() }} entries
                </div>
                <div>
                    {{ $liquidatedForms->appends(request()->query())->links() }}
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
                Are you sure you want to delete this liquidated form? This action cannot be undone.
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
                <form id="bulkActionForm" method="POST" action="{{ route('admin.liquidated-forms.bulk-action') }}">
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
    const checkboxes = document.querySelectorAll('input[name="selected_forms[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function deleteForm(formId) {
    if (confirm('Are you sure you want to delete this liquidated form?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/liquidated-forms/${formId}`;
        
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
    const selectedForms = document.querySelectorAll('input[name="selected_forms[]"]:checked');
    
    if (selectedForms.length === 0) {
        alert('Please select at least one liquidated form');
        return;
    }
    
    const form = document.getElementById('bulkActionForm');
    const actionField = document.getElementById('bulkActionType');
    const message = document.getElementById('bulkActionMessage');
    const fields = document.getElementById('bulkActionFields');
    
    actionField.value = action;
    
    // Clear previous fields
    fields.innerHTML = '';
    
    // Add selected forms
    selectedForms.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_forms[]';
        input.value = checkbox.value;
        fields.appendChild(input);
    });
    
    // Set message based on action
    switch(action) {
        case 'delete':
            message.textContent = `Are you sure you want to delete ${selectedForms.length} selected liquidated form(s)? This action cannot be undone.`;
            break;
        case 'flag':
            message.textContent = `Are you sure you want to flag ${selectedForms.length} selected liquidated form(s)?`;
            break;
        case 'request-revision':
            message.textContent = `Are you sure you want to request revision for ${selectedForms.length} selected liquidated form(s)? Note: This action can be performed on both pending and flagged forms.`;
            // Add notes field for revision request
            const notesField = document.createElement('div');
            notesField.className = 'form-group';
            notesField.innerHTML = `
                <label for="revision_notes">Revision Reason:</label>
                <textarea class="form-control" name="notes" id="revision_notes" rows="3" 
                          placeholder="Please provide a reason for the revision request..."></textarea>
            `;
            fields.appendChild(notesField);
            break;
    }
    
    $('#bulkActionModal').modal('show');
}

function exportSelected() {
    const selectedForms = document.querySelectorAll('input[name="selected_forms[]"]:checked');
    
    if (selectedForms.length === 0) {
        alert('Please select at least one liquidated form to export');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.liquidated-forms.bulk-action") }}';
    
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
    
    selectedForms.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_forms[]';
        input.value = checkbox.value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
