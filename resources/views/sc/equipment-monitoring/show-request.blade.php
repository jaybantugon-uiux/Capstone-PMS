@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-truck-loading me-2"></i>Equipment Request Details
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Requests
                            </a>
                            @if($equipmentRequest->status === 'pending')
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-1"></i> Cancel Request
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Alert -->
                    <div class="alert alert-{{ $equipmentRequest->status === 'approved' ? 'success' : ($equipmentRequest->status === 'declined' ? 'danger' : 'warning') }} mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-{{ $equipmentRequest->status === 'approved' ? 'check-circle' : ($equipmentRequest->status === 'declined' ? 'times-circle' : 'clock') }} me-2"></i>
                            <div>
                                <strong>Status: {{ ucfirst($equipmentRequest->status) }}</strong>
                                @if($equipmentRequest->status === 'approved')
                                    <br><small>Your equipment request has been approved!</small>
                                @elseif($equipmentRequest->status === 'declined')
                                    <br><small>Your equipment request has been declined.</small>
                                @else
                                    <br><small>Your equipment request is pending approval.</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-cogs me-2"></i>Equipment Information
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Equipment Name:</strong></td>
                                    <td>{{ $equipmentRequest->equipment_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $equipmentRequest->equipment_description }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td><span class="badge bg-primary">{{ $equipmentRequest->quantity }} units</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Usage Type:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentRequest->usage_type === 'personal' ? 'info' : 'primary' }}">
                                            {{ $equipmentRequest->formatted_usage_type }}
                                        </span>
                                    </td>
                                </tr>
                                @if($equipmentRequest->project)
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>
                                        <a href="{{ route('projects.show', $equipmentRequest->project) }}" class="text-decoration-none">
                                            {{ $equipmentRequest->project->name }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Request Details
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Urgency Level:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentRequest->urgency_badge_color }}">
                                            {{ $equipmentRequest->formatted_urgency }}
                                        </span>
                                        @if($equipmentRequest->is_urgent)
                                            <span class="badge bg-danger ms-1">Urgent</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $equipmentRequest->status_badge_color }}">
                                            {{ $equipmentRequest->formatted_status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Requested Date:</strong></td>
                                    <td>{{ $equipmentRequest->created_at->format('M d, Y \a\t h:i A') }}</td>
                                </tr>
                                @if($equipmentRequest->updated_at != $equipmentRequest->created_at)
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $equipmentRequest->updated_at->format('M d, Y \a\t h:i A') }}</td>
                                </tr>
                                @endif
                                @if($equipmentRequest->estimated_cost)
                                <tr>
                                    <td><strong>Estimated Cost:</strong></td>
                                    <td>${{ number_format($equipmentRequest->estimated_cost, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Justification -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-comment me-2"></i>Justification
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $equipmentRequest->justification }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    @if($equipmentRequest->additional_notes)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-sticky-note me-2"></i>Additional Notes
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0">{{ $equipmentRequest->additional_notes }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Approval Information -->
                    @if($equipmentRequest->status !== 'pending')
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user-check me-2"></i>Approval Information
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Reviewed By:</strong> 
                                        {{ $equipmentRequest->approvedBy->first_name }} {{ $equipmentRequest->approvedBy->last_name }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Review Date:</strong> 
                                        {{ $equipmentRequest->updated_at->format('M d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                                @if($equipmentRequest->admin_notes)
                                <div class="mt-3">
                                    <strong>Admin Notes:</strong>
                                    <p class="mb-0 mt-1">{{ $equipmentRequest->admin_notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Monitored Equipment Information -->
                    @if($equipmentRequest->monitoredEquipment)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-cogs me-2"></i>Equipment Assignment
                        </h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Equipment ID:</strong> 
                                        {{ $equipmentRequest->monitoredEquipment->id }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($equipmentRequest->monitoredEquipment->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Availability:</strong> 
                                        <span class="badge bg-{{ $equipmentRequest->monitoredEquipment->availability_status === 'available' ? 'success' : ($equipmentRequest->monitoredEquipment->availability_status === 'in_use' ? 'warning' : 'danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $equipmentRequest->monitoredEquipment->availability_status)) }}
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Assigned Date:</strong> 
                                        {{ $equipmentRequest->monitoredEquipment->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                @if($equipmentRequest->monitoredEquipment->next_maintenance_date)
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <strong>Next Maintenance:</strong> 
                                        {{ $equipmentRequest->monitoredEquipment->next_maintenance_date->format('M d, Y') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('sc.equipment-monitoring.requests') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Requests
                        </a>
                        @if($equipmentRequest->monitoredEquipment)
                            <a href="{{ route('sc.equipment-monitoring.show-equipment', $equipmentRequest->monitoredEquipment) }}" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> View Equipment Details
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
@if($equipmentRequest->status === 'pending')
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Equipment Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this equipment request?</p>
                <div class="alert alert-warning">
                    <strong>{{ $equipmentRequest->equipment_name }}</strong>
                </div>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('sc.equipment-monitoring.requests') }}/{{ $equipmentRequest->id }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Cancel Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
.table-borderless td {
    padding: 0.5rem 0;
    border: none;
}
</style>
@endpush 