{{-- resources/views/client/dashboard.blade.php --}}
{{-- UPDATED: Fixed layout arrangement and structure --}}
@extends('app')

@section('title', 'Client Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt me-2"></i>Welcome back, {{ auth()->user()->first_name }}!
            </h1>
            <p class="mb-0 text-muted">Here's your project overview and latest reports</p>
        </div>
        <div>
            <span class="badge bg-primary">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Progress Reports -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Progress Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reportStats['total_reports'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unread Reports -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                New Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $reportStats['unread_reports'] }}
                                @if($reportStats['unread_reports'] > 0)
                                    <span class="badge bg-warning text-dark ms-1">NEW</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Public Photos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Project Photos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalPhotos = 0;
                                    foreach($projects as $project) {
                                        $totalPhotos += $project->client_visible_photos_count ?? 0;
                                    }
                                @endphp
                                {{ $totalPhotos }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Projects -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Projects
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $projects->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Progress Reports -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-chart-line me-2"></i>Recent Progress Reports
                    </h6>
                    <a href="{{ route('client.reports.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>View All Reports
                    </a>
                </div>
                <div class="card-body">
                    @if($recentReports->count() > 0)
                        @foreach($recentReports as $report)
                            <div class="d-flex align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="me-3">
                                    @if($report->status === 'sent')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>NEW
                                        </span>
                                    @else
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="{{ route('client.reports.show', $report->id) }}" 
                                           class="text-decoration-none {{ $report->status === 'sent' ? 'fw-bold' : '' }}">
                                            {{ Str::limit($report->title, 45) }}
                                        </a>
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="fas fa-user me-1"></i>
                                        From {{ $report->creator->first_name }} {{ $report->creator->last_name }}
                                        <span class="badge bg-{{ $report->creator->role === 'admin' ? 'danger' : 'primary' }} ms-1">
                                            {{ $report->formatted_creator_role }}
                                        </span>
                                        @if($report->project)
                                            <br><i class="fas fa-project-diagram me-1"></i>{{ $report->project->name }}
                                        @endif
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $report->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    @if($report->hasAttachment())
                                        <div class="text-success mb-1">
                                            <i class="fas fa-paperclip" title="Has attachment"></i>
                                        </div>
                                    @endif
                                    @if($report->view_count > 0)
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i>{{ $report->view_count }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        @if($reportStats['unread_reports'] > 0)
                            <div class="text-center pt-3">
                                <a href="{{ route('client.reports.index', ['status' => 'sent']) }}" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-envelope-open me-1"></i>
                                    View {{ $reportStats['unread_reports'] }} New Report{{ $reportStats['unread_reports'] > 1 ? 's' : '' }}
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No progress reports yet</h6>
                            <p class="text-muted small">Progress reports from your project team will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('client.reports.index') }}" 
                               class="btn btn-outline-primary w-100 position-relative">
                                <i class="fas fa-file-alt mb-1 d-block"></i>
                                <small>View Reports</small>
                                @if($reportStats['unread_reports'] > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                        {{ $reportStats['unread_reports'] }}
                                    </span>
                                @endif
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('photos.featured') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-images mb-1 d-block"></i>
                                <small>Project Photos</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('photos.search') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-search mb-1 d-block"></i>
                                <small>Search Photos</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('account.edit') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-user-cog mb-1 d-block"></i>
                                <small>Settings</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>Client Access
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-check-circle me-2"></i>What you can access:
                        </h6>
                        <ul class="list-unstyled ms-3 mb-0">
                            <li><i class="fas fa-file-alt text-primary me-2"></i>Progress reports from your team</li>
                            <li><i class="fas fa-images text-success me-2"></i>Public project photos</li>
                            <li><i class="fas fa-eye text-info me-2"></i>Project status and information</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Note:</strong> You will only receive progress report notifications. All other project updates and task notifications are managed internally by your project team.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
}

.card {
    border: none;
    border-radius: 0.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.badge {
    font-size: 0.75rem;
}

.list-unstyled li {
    padding: 0.25rem 0;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.text-decoration-none:hover {
    text-decoration: underline !important;
}

/* Animation for statistics cards */
.border-left-primary,
.border-left-success,
.border-left-warning,
.border-left-info {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

.border-left-primary {
    animation-delay: 0.1s;
}

.border-left-success {
    animation-delay: 0.2s;
}

.border-left-warning {
    animation-delay: 0.3s;
}

.border-left-info {
    animation-delay: 0.4s;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pulse animation for new reports */
.badge.bg-warning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

/* Enhanced progress bar styling */
.progress-bar {
    transition: width 0.6s ease;
}

/* Hover effects for project cards */
.card-body .d-flex:hover {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    transition: background-color 0.2s ease;
}

/* Quick actions responsive adjustments */
@media (max-width: 768px) {
    .col-6 {
        margin-bottom: 1rem;
    }
    
    .btn {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced statistics card animation
    const statCards = document.querySelectorAll('.border-left-primary, .border-left-success, .border-left-warning, .border-left-info');
    
    // Initialize cards as hidden
    statCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
    });
    
    // Animate cards with stagger effect
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, (index + 1) * 150);
    });
    
    // Auto-refresh unread report count every 2 minutes
    setInterval(function() {
        fetch('{{ route("client.reports.index") }}?ajax=1')
            .then(response => response.json())
            .then(data => {
                if (data.unread_count !== undefined) {
                    const unreadElements = document.querySelectorAll('[data-unread-count]');
                    unreadElements.forEach(element => {
                        element.textContent = data.unread_count;
                        if (data.unread_count > 0) {
                            element.parentElement.querySelector('.badge')?.classList.add('bg-warning');
                        }
                    });
                }
            })
            .catch(error => console.log('Error fetching report updates:', error));
    }, 120000); // 2 minutes
    
    // Add click tracking for quick actions
    document.querySelectorAll('.btn[href]').forEach(button => {
        button.addEventListener('click', function(e) {
            const action = this.textContent.trim();
            console.log('Client dashboard action:', action);
            
            // Add visual feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // Enhanced hover effects for project cards
    document.querySelectorAll('.card-body .d-flex').forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
            this.style.borderRadius = '0.375rem';
            this.style.padding = '0.75rem';
            this.style.margin = '-0.25rem';
            this.style.transition = 'all 0.2s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.borderRadius = '';
            this.style.padding = '';
            this.style.margin = '';
        });
    });
    
    // Progress bar animation
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
    
    // Handle notification preferences link (if exists)
    const notificationLink = document.querySelector('a[href*="notification"]');
    if (notificationLink) {
        notificationLink.addEventListener('click', function(e) {
            // Could add a modal or tooltip explaining client notification limitations
            console.log('Client notification preferences accessed');
        });
    }
    
    // Add tooltips to explain client access
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Function to show success message when viewing new reports
function markReportAsViewed(reportId) {
    fetch(`/client/reports/${reportId}/mark-viewed`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI to reflect the report has been viewed
            const reportElement = document.querySelector(`[data-report-id="${reportId}"]`);
            if (reportElement) {
                reportElement.classList.remove('fw-bold');
                const newBadge = reportElement.querySelector('.badge.bg-warning');
                if (newBadge) {
                    newBadge.remove();
                }
            }
        }
    })
    .catch(error => console.error('Error marking report as viewed:', error));
}
</script>
@endpush
@endsection