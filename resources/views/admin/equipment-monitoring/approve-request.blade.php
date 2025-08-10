@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-check-circle me-2 text-success"></i>Approve Equipment Request
                        </h1>
                        <p class="text-muted mb-0">Review and approve equipment request</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.equipment-monitoring.requests') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Requests
                        </a>
                    </div>
                </div>

                <!-- Request Details Card -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Request Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Equipment Name:</strong><br>
                                        <span class="text-primary">{{ $equipmentRequest->equipment_name }}</span></p>
                                        
                                        <p><strong>Site Coordinator:</strong><br>
                                        {{ $equipmentRequest->user?->full_name }}</p>
                                        
                                        <p><strong>Usage Type:</strong><br>
                                        <span class="badge bg-info">{{ $equipmentRequest->formatted_usage_type }}</span></p>
                                        
                                        <p><strong>Quantity:</strong><br>
                                        {{ $equipmentRequest->quantity }} units</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Project:</strong><br>
                                        {{ $equipmentRequest->project?->name ?? 'Personal Use' }}</p>
                                        
                                        <p><strong>Urgency Level:</strong><br>
                                        <span class="badge bg-{{ $equipmentRequest->urgency_badge_color }}">
                                            {{ $equipmentRequest->formatted_urgency }}
                                        </span></p>
                                        
                                        <p><strong>Estimated Cost:</strong><br>
                                        {{ $equipmentRequest->estimated_cost ? '₱' . number_format($equipmentRequest->estimated_cost, 2) : 'Not specified' }}</p>
                                        
                                        <p><strong>Requested:</strong><br>
                                        {{ $equipmentRequest->created_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <p><strong>Description:</strong></p>
                                        <div class="bg-light p-3 rounded">
                                            {{ $equipmentRequest->equipment_description }}
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <p><strong>Justification:</strong></p>
                                        <div class="bg-light p-3 rounded">
                                            {{ $equipmentRequest->justification }}
                                        </div>
                                    </div>
                                </div>
                                
                                @if($equipmentRequest->additional_notes)
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <p><strong>Additional Notes:</strong></p>
                                        <div class="bg-light p-3 rounded">
                                            {{ $equipmentRequest->additional_notes }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Approval Form -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-check-circle me-2"></i>Approve Request
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Success/Error Messages -->
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        @foreach($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('admin.equipment-monitoring.approve-request', $equipmentRequest) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="admin_notes" class="form-label">Admin Notes <span class="text-muted">(Optional)</span></label>
                                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4" 
                                            placeholder="Add any notes about this approval...">{{ old('admin_notes') }}</textarea>
                                        <div class="form-text">These notes will be visible to the site coordinator.</div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Approval will:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Create monitored equipment entry</li>
                                            <li>Set equipment status to "Active"</li>
                                            <li>Send notification to site coordinator</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-check me-2"></i>Approve Request
                                        </button>
                                        <a href="{{ route('admin.equipment-monitoring.show-request', $equipmentRequest) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye me-2"></i>View Details Only
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2"></i>Quick Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.equipment-monitoring.show-request', $equipmentRequest) }}" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times me-1"></i>Decline Instead
                                    </a>
                                    <a href="{{ route('admin.equipment-monitoring.requests') }}?status=pending" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-list me-1"></i>All Pending Requests
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endpush