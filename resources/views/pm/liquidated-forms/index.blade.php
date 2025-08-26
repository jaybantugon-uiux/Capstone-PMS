@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Liquidated Forms</h1>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form id="filtersForm" method="GET" action="{{ route('pm.liquidated-forms.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="flagged" {{ request('status') == 'flagged' ? 'selected' : '' }}>Flagged</option>
                                <option value="clarification_requested" {{ request('status') == 'clarification_requested' ? 'selected' : '' }}>Clarification Requested</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="project_id">Project</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">All Projects</option>
                                @foreach($projects ?? [] as $project)
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
                                @foreach($preparers ?? [] as $preparer)
                                    <option value="{{ $preparer->id }}" {{ request('preparer_id') == $preparer->id ? 'selected' : '' }}>
                                        {{ $preparer->first_name }} {{ $preparer->last_name }}
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
                            <label for="period_from">Period From</label>
                            <input type="date" name="period_from" id="period_from" class="form-control" 
                                   value="{{ request('period_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period_to">Period To</label>
                            <input type="date" name="period_to" id="period_to" class="form-control" 
                                   value="{{ request('period_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liquidated Forms List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Liquidated Forms</h6>
            <div class="d-flex gap-2">
                <span class="badge badge-info">{{ $liquidatedForms->total() }} total forms</span>
            </div>
        </div>
        <div class="card-body">
            @if($liquidatedForms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="liquidatedFormsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Form #</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Preparer</th>
                                <th>Liquidation Date</th>
                                <th>Period Covered</th>

                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($liquidatedForms as $form)
                            <tr>
                                <td>
                                    <strong>{{ $form->form_number }}</strong>
                                </td>
                                <td>{{ $form->title }}</td>
                                <td>{{ $form->project->name ?? 'N/A' }}</td>
                                <td>{{ $form->preparer->first_name ?? 'N/A' }} {{ $form->preparer->last_name ?? '' }}</td>
                                <td>{{ $form->liquidation_date->format('M d, Y') }}</td>
                                <td>
                                    {{ $form->period_covered_start->format('M d, Y') }} - 
                                    {{ $form->period_covered_end->format('M d, Y') }}
                                </td>

                                <td>
                                    @switch($form->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @case('under_review')
                                            <span class="badge badge-info">Under Review</span>
                                            @break
                                        @case('flagged')
                                            <span class="badge badge-danger">Flagged</span>
                                            @break
                                        @case('clarification_requested')
                                            <span class="badge badge-warning">Clarification Requested</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($form->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('pm.liquidated-forms.show', $form) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $liquidatedForms->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No liquidated forms found</h5>
                    <p class="text-muted">No liquidated forms match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#liquidatedFormsTable').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "responsive": true,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No liquidated forms found"
        }
    });

    // Auto-submit form when filters change
    $('#status, #project_id, #preparer_id').change(function() {
        $('#filtersForm').submit();
    });
});
</script>
@endpush
@endsection
