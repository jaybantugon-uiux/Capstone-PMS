@extends('app')

@section('content')
<div class="container">
    <h1>Equipment Inventory</h1>
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <a href="{{ route('equipment.create') }}" class="btn btn-primary mb-3">Add New Equipment</a>
    <a href="{{ route('equipment.bulk-restock.form') }}" class="btn btn-secondary mb-3">Bulk Restock</a>
    <a href="{{ route('equipment.low-stock') }}" class="btn btn-warning mb-3">View Low Stock</a>
    <a href="{{ route('equipment.archived') }}" class="btn btn-outline-secondary mb-3">View Archived</a>

    @if($equipment->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Stock Status</th>
                    <th>Current Stock</th>
                    <th>Min Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipment as $item)
                <tr class="{{ $item->stock <= 0 ? 'table-danger' : ($item->isLowStock() ? 'table-warning' : '') }}">
                    <td>
                        @if($item->stock <= 0)
                            <span class="badge bg-danger me-1">OUT</span>
                        @elseif($item->isLowStock())
                            <span class="badge bg-warning me-1">LOW</span>
                        @endif
                        {{ $item->name }}
                    </td>
                    <td>{{ Str::limit($item->description ?: 'No description', 50) }}</td>
                    <td>
                        @if($item->stock <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($item->isLowStock())
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">In Stock</span>
                        @endif
                    </td>
                    <td><strong>{{ $item->stock }}</strong> units</td>
                    <td>{{ $item->min_stock_level }}</td>
                    <td>
                        <a href="{{ route('equipment.show', $item->id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('equipment.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        <a href="{{ route('equipment.restock.form', $item->id) }}" class="btn btn-sm btn-success">Restock</a>
                        <a href="{{ route('equipment.use.form', $item->id) }}" class="btn btn-sm btn-warning">Use</a>
                        <button type="button" class="btn btn-sm btn-danger" 
                                data-bs-toggle="modal" data-bs-target="#archiveModal"
                                data-equipment-id="{{ $item->id }}"
                                data-equipment-name="{{ $item->name }}"
                                data-equipment-stock="{{ $item->stock }}">
                            Archive
                        </button>
                        <a href="{{ route('equipment.logs', $item->id) }}" class="btn btn-sm btn-secondary">Logs</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-5">
            <h5 class="text-muted">No Equipment Found</h5>
            <p class="text-muted">Start by adding your first equipment item to the inventory.</p>
            <a href="{{ route('equipment.create') }}" class="btn btn-primary">Add First Equipment</a>
        </div>
    @endif
</div>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveModalLabel">Archive Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive this equipment?</p>
                <p class="text-muted">Archived equipment cannot be restocked or used until restored.</p>
                <div class="alert alert-warning">
                    <strong id="equipmentName"></strong><br>
                    Current Stock: <strong id="equipmentStock"></strong> units
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="archiveForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Archive Equipment</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle archive modal
    var archiveModal = document.getElementById('archiveModal');
    if (archiveModal) {
        archiveModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var equipmentId = button.getAttribute('data-equipment-id');
            var equipmentName = button.getAttribute('data-equipment-name');
            var equipmentStock = button.getAttribute('data-equipment-stock');
            
            var modal = this;
            modal.querySelector('#equipmentName').textContent = equipmentName;
            modal.querySelector('#equipmentStock').textContent = equipmentStock;
            modal.querySelector('#archiveForm').setAttribute('action', '/equipment/' + equipmentId + '/archive');
        });
    }
});
</script>
@endpush