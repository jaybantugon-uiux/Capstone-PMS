@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0"><i class="fas fa-cubes me-2"></i>Monitored Equipment</h1>
                <a href="{{ route('admin.equipment-monitoring.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending_approval" {{ ($statusFilter ?? '') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="declined" {{ ($statusFilter ?? '') === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Usage Type</label>
                            <select name="usage_type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="personal" {{ ($typeFilter ?? '') === 'personal' ? 'selected' : '' }}>Personal</option>
                                <option value="project_site" {{ ($typeFilter ?? '') === 'project_site' ? 'selected' : '' }}>Project Site</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Site Coordinator</label>
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ ($userFilter ?? '') == $u->id ? 'selected' : '' }}>{{ $u->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment Table -->
            <div class="card">
                <div class="card-body p-0">
                    @if($equipment->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Equipment</th>
                                        <th>SC</th>
                                        <th>Usage</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Availability</th>
                                        <th>Quantity</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($equipment as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->equipment_name }}</strong>
                                                <div class="text-muted small">{{ Str::limit($item->equipment_description, 60) }}</div>
                                            </td>
                                            <td>{{ $item->user?->full_name }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $item->formatted_usage_type ?? ucfirst(str_replace('_',' ',$item->usage_type)) }}</span></td>
                                            <td>{{ $item->project?->name ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'pending_approval' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->availability_status === 'available' ? 'success' : ($item->availability_status === 'in_use' ? 'warning' : ($item->availability_status === 'maintenance' ? 'info' : 'danger')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $item->availability_status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->created_at->format('M d, Y') }}</td>
                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.equipment-monitoring.show-equipment', $item) }}" class="btn btn-sm btn-outline-primary" title="View Equipment">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($item->status === 'pending_approval' && $item->equipmentRequest && $item->equipmentRequest->status === 'pending')
                                                        <a href="{{ route('admin.equipment-monitoring.show-request', $item->equipmentRequest) }}" class="btn btn-sm btn-outline-secondary" title="View Request">
                                                            <i class="fas fa-clipboard-list"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('admin.equipment-monitoring.approve-request', $item->equipmentRequest) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="admin_notes" value="Approved via equipment list">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Decline Request"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#declineRequestModal"
                                                            data-request-url="{{ route('admin.equipment-monitoring.decline-request', $item->equipmentRequest) }}"
                                                            data-equipment-name="{{ $item->equipment_name }}"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center p-3 border-top">
                            {{ $equipment->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <i class="fas fa-cubes fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No monitored equipment found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineRequestModal" tabindex="-1" aria-labelledby="declineRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="declineRequestModalLabel">
            <i class="fas fa-times-circle me-2"></i>Decline Equipment Request
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="declineRequestForm" method="POST" action="#">
        @csrf
        <div class="modal-body">
            <div class="mb-2 small text-muted" id="declineEquipmentInfo"></div>
            <div class="mb-3">
                <label for="decline_reason" class="form-label">Decline Reason <span class="text-danger">*</span></label>
                <textarea name="decline_reason" id="decline_reason" class="form-control" rows="4" required placeholder="Provide a clear reason..."></textarea>
            </div>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Declining will notify the Site Coordinator and set this monitored equipment to "Declined".
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times me-1"></i>Decline Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var declineModal = document.getElementById('declineRequestModal');
    declineModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var requestUrl = button.getAttribute('data-request-url');
        var equipmentName = button.getAttribute('data-equipment-name');

        var form = document.getElementById('declineRequestForm');
        form.setAttribute('action', requestUrl);

        var info = document.getElementById('declineEquipmentInfo');
        info.textContent = 'Equipment: ' + (equipmentName || '');

        // Clear previous input
        document.getElementById('decline_reason').value = '';
    });
});
</script>
@endpush
@endsection


