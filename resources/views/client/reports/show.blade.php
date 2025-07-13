
@extends('app')

@section('title', $progressReport->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('client.reports.index') }}">Progress Reports</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($progressReport->title, 50) }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-alt me-2"></i>{{ $progressReport->title }}
                    </h1>
                </div>
                <div class="d-flex gap-2">
                    @if($progressReport->hasAttachment())
                        <a href="{{ route('client.reports.download-attachment', $progressReport->id) }}" 
                           class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Download Attachment
                        </a>
                    @endif
                    <a href="{{ route('client.reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Reports
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Report Content -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-text me-2"></i>Report Details
                                </h5>
                                <span class="badge bg-{{ $progressReport->status_color }} bg-opacity-75">
                                    {{ $progressReport->formatted_status }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Report Metadata -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-md me-3">
                                            <div class="avatar-title bg-{{ $progressReport->creator_role_badge_color }} rounded-circle">
                                                {{ substr($progressReport->creator->first_name, 0, 1) }}{{ substr($progressReport->creator->last_name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $progressReport->creator->first_name }} {{ $progressReport->creator->last_name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>{{ $progressReport->creator->email }}
                                            </small>
                                            <br>
                                            <span class="badge bg-{{ $progressReport->creator_role_badge_color }}">
                                                {{ $progressReport->formatted_creator_role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Date Received</small>
                                            <strong>{{ $progressReport->created_at->format('M d, Y') }}</strong>
                                            <br><small class="text-muted">{{ $progressReport->created_at->format('g:i A') }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Project</small>
                                            @if($progressReport->project)
                                                <span class="badge bg-info">{{ $progressReport->project->name }}</span>
                                            @else
                                                <span class="text-muted">General Report</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Report Description -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-align-left me-2"></i>Report Content
                                </h6>
                                <div class="report-content">
                                    {!! nl2br(e($progressReport->description)) !!}
                                </div>
                            </div>

                            <!-- Attachment Section -->
                            @if($progressReport->hasAttachment())
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-paperclip me-2"></i>Attachment
                                    </h6>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="attachment-icon">
                                                        <i class="{{ $progressReport->attachment_icon }} fa-2x"></i>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <h6 class="mb-1">{{ $progressReport->original_filename }}</h6>
                                                    <p class="mb-1 text-muted">
                                                        <i class="fas fa-weight me-1"></i>{{ $progressReport->formatted_file_size }}
                                                        @if($progressReport->mime_type)
                                                            <span class="ms-2">
                                                                <i class="fas fa-info-circle me-1"></i>{{ strtoupper($progressReport->file_extension) }}
                                                            </span>
                                                        @endif
                                                    </p>
                                                    <small class="text-muted">
                                                        Click the download button to view or save this file
                                                    </small>
                                                </div>
                                                <div class="col-auto">
                                                    <a href="{{ route('client.reports.download-attachment', $progressReport->id) }}" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Image Preview for Images -->
                                            @if($progressReport->isImage())
                                                <div class="mt-3">
                                                    <img src="{{ $progressReport->attachment_url }}" 
                                                         alt="{{ $progressReport->original_filename }}" 
                                                         class="img-fluid rounded border"
                                                         style="max-height: 300px; cursor: pointer;"
                                                         onclick="openImageModal(this.src)">
                                                    <small class="d-block text-muted mt-1">
                                                        <i class="fas fa-search-plus me-1"></i>Click to view full size
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- View History (if multiple views) -->
                            @if($progressReport->view_count > 1)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-eye me-2"></i>View History
                                    </h6>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You have viewed this report <strong>{{ $progressReport->view_count }}</strong> times.
                                        @if($progressReport->first_viewed_at)
                                            First viewed on {{ $progressReport->first_viewed_at->format('M d, Y \a\t g:i A') }}.
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Report Information Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Report Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-5 text-muted">Status:</div>
                                <div class="col-7">
                                    <span class="badge bg-{{ $progressReport->status_color }}">
                                        {{ $progressReport->formatted_status }}
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 text-muted">Date Sent:</div>
                                <div class="col-7">{{ $progressReport->sent_at ? $progressReport->sent_at->format('M d, Y') : 'N/A' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-5 text-muted">Views:</div>
                                <div class="col-7">{{ $progressReport->view_count }}</div>
                            </div>
                            @if($progressReport->first_viewed_at)
                                <div class="row mb-3">
                                    <div class="col-5 text-muted">First Viewed:</div>
                                    <div class="col-7">{{ $progressReport->first_viewed_at->format('M d, Y') }}</div>
                                </div>
                            @endif
                            @if($progressReport->hasAttachment())
                                <div class="row mb-3">
                                    <div class="col-5 text-muted">Attachment:</div>
                                    <div class="col-7">
                                        <i class="fas fa-check text-success"></i> 
                                        {{ $progressReport->formatted_file_size }}
                                    </div>
                                </div>
                            @endif
                            @if($progressReport->project)
                                <div class="row mb-3">
                                    <div class="col-5 text-muted">Project:</div>
                                    <div class="col-7">
                                        <a href="{{ route('client.projects.show', $progressReport->project->id) }}" 
                                           class="text-decoration-none">
                                            {{ $progressReport->project->name }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($progressReport->hasAttachment())
                                    <a href="{{ route('client.reports.download-attachment', $progressReport->id) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-download me-2"></i>Download Attachment
                                    </a>
                                @endif
                                
                                <button type="button" class="btn btn-outline-primary" onclick="printReport()">
                                    <i class="fas fa-print me-2"></i>Print Report
                                </button>
                                
                                <button type="button" class="btn btn-outline-secondary" onclick="shareReport()">
                                    <i class="fas fa-share me-2"></i>Share Link
                                </button>
                                
                                <a href="{{ route('client.reports.index') }}" class="btn btn-outline-dark">
                                    <i class="fas fa-list me-2"></i>All Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Need Help Card -->
                    <div class="card shadow border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-question-circle me-2"></i>Need Help?
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="card-text mb-3">
                                If you have questions about this progress report, feel free to contact us.
                            </p>
                            <div class="d-grid gap-2">
                                <a href="mailto:{{ $progressReport->creator->email }}?subject=Question about: {{ $progressReport->title }}" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-envelope me-2"></i>Email {{ $progressReport->formatted_creator_role }}
                                </a>
                                <a href="tel:+1234567890" class="btn btn-outline-info">
                                    <i class="fas fa-phone me-2"></i>Call Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">{{ $progressReport->original_filename }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid">
            </div>
            <div class="modal-footer">
                <a href="" id="modalDownloadBtn" class="btn btn-primary" download>
                    <i class="fas fa-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.avatar-md {
    width: 3rem;
    height: 3rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 600;
    width: 100%;
    height: 100%;
}

.report-content {
    line-height: 1.8;
    font-size: 1.1rem;
    color: #495057;
}

.attachment-icon {
    text-align: center;
    padding: 1rem;
}

@media print {
    .btn, .card-header, nav, .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        max-width: 100% !important;
    }
}

.cursor-pointer {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
function openImageModal(imageSrc) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const downloadBtn = document.getElementById('modalDownloadBtn');
    
    modalImage.src = imageSrc;
    downloadBtn.href = '{{ route("client.reports.download-attachment", $progressReport->id) }}';
    modal.show();
}

function printReport() {
    window.print();
}

function shareReport() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $progressReport->title }}',
            text: 'Progress Report: {{ $progressReport->title }}',
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Report link copied to clipboard!');
        }).catch(() => {
            prompt('Copy this link:', window.location.href);
        });
    }
}

// Mark as read when page loads (if not already viewed)
document.addEventListener('DOMContentLoaded', function() {
    // Track scroll to measure engagement
    let maxScroll = 0;
    window.addEventListener('scroll', function() {
        const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
        maxScroll = Math.max(maxScroll, scrollPercent);
    });
    
    // Send engagement data when user leaves the page
    window.addEventListener('beforeunload', function() {
        if (maxScroll > 50) { // User scrolled more than 50%
            fetch('{{ route("client.reports.show", $progressReport->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    engagement: {
                        max_scroll: maxScroll,
                        time_spent: Date.now() - pageLoadTime
                    }
                }),
                keepalive: true
            });
        }
    });
    
    const pageLoadTime = Date.now();
});
</script>
@endpush
@endsection