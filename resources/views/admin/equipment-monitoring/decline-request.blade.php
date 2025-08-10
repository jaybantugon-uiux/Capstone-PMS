@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-times-circle me-2 text-danger"></i>Decline Equipment Request
                        </h1>
                        <p class="text-muted mb-0">Review and decline equipment request with reason</p>
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
                        <!-- Decline Form -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-danger text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-times-circle me-2"></i>Decline Request
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

                                <form action="{{ route('admin.equipment-monitoring.decline-request', $equipmentRequest) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="decline_reason" class="form-label">
                                            Decline Reason <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('decline_reason') is-invalid @enderror" 
                                            id="decline_reason" name="decline_reason" rows="4" required
                                            placeholder="Please provide a clear reason for declining this request...">{{ old('decline_reason') }}</textarea>
                                        @error('decline_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">This reason will be sent to the site coordinator via notification.</div>
                                    </div>
                                    
                                    <!-- Common Decline Reasons -->
                                    <div class="mb-3">
                                        <label class="form-label">Common Reasons <span class="text-muted">(Click to use)</span></label>
                                        <div class="d-grid gap-1">
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start reason-btn" 
                                                data-reason="Budget constraints - insufficient funds allocated for this equipment.">
                                                <i class="fas fa-dollar-sign me-2"></i>Budget Constraints
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start reason-btn" 
                                                data-reason="Equipment not necessary for project requirements or duplicate functionality exists.">
                                                <i class="fas fa-ban me-2"></i>Not Necessary
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start reason-btn" 
                                                data-reason="Insufficient justification provided for equipment purchase.">
                                                <i class="fas fa-question-circle me-2"></i>Insufficient Justification
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start reason-btn" 
                                                data-reason="Alternative equipment or solution available that better meets requirements.">
                                                <i class="fas fa-exchange-alt me-2"></i>Alternative Available
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm text-start reason-btn" 
                                                data-reason="Request requires additional information or documentation before approval.">
                                                <i class="fas fa-file-alt me-2"></i>Need More Info
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Declining will:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Mark request as declined</li>
                                            <li>Set equipment status to "Declined"</li>
                                            <li>Send notification with reason to site coordinator</li>
                                            <li>Cannot be undone (new request needed)</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-lg">
                                            <i class="fas fa-times me-2"></i>Decline Request
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
                                    <a href="{{ route('admin.equipment-monitoring.show-request', $equipmentRequest) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Approve Instead
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

    // Handle common reason buttons
    document.querySelectorAll('.reason-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const reason = this.getAttribute('data-reason');
            const textarea = document.getElementById('decline_reason');
            
            // If textarea is empty, set the reason directly
            if (textarea.value.trim() === '') {
                textarea.value = reason;
            } else {
                // If textarea has content, append the reason
                textarea.value += '\n\n' + reason;
            }
            
            // Focus the textarea
            textarea.focus();
            
            // Visual feedback
            this.classList.add('btn-secondary');
            this.classList.remove('btn-outline-secondary');
            setTimeout(() => {
                this.classList.remove('btn-secondary');
                this.classList.add('btn-outline-secondary');
            }, 200);
        });
    });
</script>
@endpush