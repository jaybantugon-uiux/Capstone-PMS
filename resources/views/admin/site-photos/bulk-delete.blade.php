{{-- Admin Site Photos Bulk Delete - views/admin/site-photos/bulk-delete.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 text-danger">
                        <i class="fas fa-trash me-2"></i>Bulk Delete Photos
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bulk Delete</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>

            @if($photos->count() > 0)
                <!-- Warning Alert -->
                <div class="alert alert-danger mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1">Warning: Permanent Deletion</h5>
                            <p class="mb-0">
                                You are about to permanently delete <strong>{{ $photos->count() }} photo(s)</strong>. 
                                This action cannot be undone and will remove all photo files and data from the system.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Selected Photos for Deletion -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-trash me-2"></i>Photos Selected for Deletion ({{ $photos->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $hasIssues = false; @endphp
                        
                        <div class="row">
                            @foreach($photos as $photo)
                                @php 
                                    $deletionCheck = $photo->canBeSafelyDeleted();
                                    if (!$deletionCheck['can_delete']) $hasIssues = true;
                                @endphp
                                
                                <div class="col-md-3 mb-3">
                                    <div class="card {{ !$deletionCheck['can_delete'] ? 'border-warning' : '' }}">
                                        <img src="{{ $photo->thumbnail_url }}" 
                                             class="card-img-top" 
                                             style="height: 120px; object-fit: cover;"
                                             alt="{{ $photo->title }}">
                                        <div class="card-body p-2">
                                            <h6 class="card-title small mb-1" title="{{ $photo->title }}">
                                                {{ Str::limit($photo->title, 20) }}
                                            </h6>
                                            <p class="card-text small text-muted mb-1">
                                                {{ Str::limit($photo->project->name, 15) }}
                                            </p>
                                            <div class="mb-1">
                                                <span class="badge bg-{{ $photo->submission_status_badge_color }} small">
                                                    {{ $photo->formatted_submission_status }}
                                                </span>
                                                @if($photo->is_featured)
                                                    <span class="badge bg-warning small">Featured</span>
                                                @endif
                                                @if($photo->is_public)
                                                    <span class="badge bg-info small">Public</span>
                                                @endif
                                            </div>
                                            
                                            @if(!$deletionCheck['can_delete'])
                                                <div class="mt-2">
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        <strong>Issues:</strong>
                                                    </small>
                                                    @foreach($deletionCheck['issues'] as $issue)
                                                        <br><small class="text-warning">• {{ $issue }}</small>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($hasIssues)
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Some photos have potential issues that should be considered before deletion. 
                                Photos marked with warnings may be referenced elsewhere in the system.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Deletion Impact Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Deletion Impact Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Photos by Status:</h6>
                                <ul class="list-unstyled">
                                    @php
                                        $statusCounts = $photos->groupBy('submission_status')->map->count();
                                    @endphp
                                    @foreach($statusCounts as $status => $count)
                                        <li>
                                            <span class="badge bg-{{ 
                                                $status === 'approved' ? 'success' : 
                                                ($status === 'submitted' ? 'warning' : 
                                                ($status === 'rejected' ? 'danger' : 'secondary')) 
                                            }}">{{ ucfirst($status) }}</span>
                                            : {{ $count }} photo(s)
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Special Photos:</h6>
                                <ul class="list-unstyled">
                                    <li>
                                        <i class="fas fa-star text-warning me-1"></i>
                                        Featured: {{ $photos->where('is_featured', true)->count() }} photo(s)
                                    </li>
                                    <li>
                                        <i class="fas fa-eye text-info me-1"></i>
                                        Public: {{ $photos->where('is_public', true)->count() }} photo(s)
                                    </li>
                                    <li>
                                        <i class="fas fa-comment text-primary me-1"></i>
                                        With Comments: {{ $photos->filter(function($p) { return $p->comments->count() > 0; })->count() }} photo(s)
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6>Total Storage Impact:</h6>
                                <p class="mb-0">
                                    <i class="fas fa-hdd me-1"></i>
                                    Approximately <strong>{{ number_format($photos->sum('file_size') / 1024 / 1024, 2) }} MB</strong> 
                                    will be freed from storage.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deletion Confirmation Form -->
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>Confirm Deletion
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.site-photos.bulk-delete') }}">
                            @csrf
                            <input type="hidden" name="photo_ids" value="{{ $photoIds }}">

                            <div class="mb-3">
                                <label for="deletion_reason" class="form-label">Reason for Deletion (Optional)</label>
                                <textarea class="form-control" id="deletion_reason" name="deletion_reason" rows="3" 
                                          placeholder="Optionally provide a reason for this bulk deletion for audit purposes..."></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm_deletion" name="confirm_deletion" required>
                                    <label class="form-check-label" for="confirm_deletion">
                                        I understand that this action will <strong>permanently delete {{ $photos->count() }} photo(s)</strong> 
                                        and cannot be undone
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm_bulk_delete" name="confirm_bulk_delete" required>
                                    <label class="form-check-label" for="confirm_bulk_delete">
                                        I confirm that I want to proceed with bulk deletion
                                    </label>
                                </div>
                            </div>

                            @if($hasIssues)
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="acknowledge_issues" name="acknowledge_issues" required>
                                        <label class="form-check-label" for="acknowledge_issues">
                                            I acknowledge that some photos have potential issues and still want to proceed
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>Delete {{ $photos->count() }} Photo(s) Permanently
                                </button>
                                <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- No Photos Selected -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                        <h5 class="text-muted">No Photos Selected</h5>
                        <p class="text-muted">No photos were selected for deletion.</p>
                        <a href="{{ route('admin.site-photos.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Photo List
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.border-warning {
    border-color: #ffc107 !important;
}
</style>
@endpush

@push('scripts')
<script>
// Add extra confirmation for dangerous action
document.querySelector('form').addEventListener('submit', function(e) {
    const photoCount = {{ $photos->count() }};
    const hasIssues = {{ isset($hasIssues) && $hasIssues ? 'true' : 'false' }};
    
    let confirmMessage = `Are you absolutely sure you want to permanently delete ${photoCount} photo(s)?\n\nThis action CANNOT be undone!`;
    
    if (hasIssues) {
        confirmMessage += '\n\nSome photos have potential issues that may affect other parts of the system.';
    }
    
    if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
    }
    
    // Final confirmation
    if (!confirm('FINAL CONFIRMATION: Click OK to permanently delete these photos.')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endpush