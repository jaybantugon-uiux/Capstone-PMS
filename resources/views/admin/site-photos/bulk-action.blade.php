{{-- Admin Site Photos Bulk Action - views/admin/site-photos/bulk-action.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2">Bulk Action: {{ ucfirst(str_replace('_', ' ', $action)) }}</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.site-photos.index') }}">Site Photos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bulk {{ ucfirst($action) }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.site-photos.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>

            @if($photos->count() > 0)
                <!-- Selected Photos Preview -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Selected Photos ({{ $photos->count() }})
                            @if($action === 'approve')
                                <span class="badge bg-success ms-2">Ready to Approve</span>
                            @elseif($action === 'reject')
                                <span class="badge bg-danger ms-2">Ready to Reject</span>
                            @elseif($action === 'feature')
                                <span class="badge bg-warning ms-2">Ready to Feature</span>
                            @elseif($action === 'unfeature')
                                <span class="badge bg-secondary ms-2">Ready to Unfeature</span>
                            @elseif($action === 'make_public')
                                <span class="badge bg-info ms-2">Ready to Make Public</span>
                            @elseif($action === 'make_private')
                                <span class="badge bg-secondary ms-2">Ready to Make Private</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($photos as $photo)
                                <div class="col-md-2 mb-3">
                                    <div class="card">
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
                                            <span class="badge bg-{{ $photo->submission_status_badge_color }} small">
                                                {{ $photo->formatted_submission_status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            @if($action === 'approve')
                                <i class="fas fa-check text-success me-2"></i>Approve Photos
                            @elseif($action === 'reject')
                                <i class="fas fa-times text-danger me-2"></i>Reject Photos
                            @elseif($action === 'feature')
                                <i class="fas fa-star text-warning me-2"></i>Feature Photos
                            @elseif($action === 'unfeature')
                                <i class="fas fa-star-o text-secondary me-2"></i>Unfeature Photos
                            @elseif($action === 'make_public')
                                <i class="fas fa-eye text-info me-2"></i>Make Photos Public
                            @elseif($action === 'make_private')
                                <i class="fas fa-eye-slash text-secondary me-2"></i>Make Photos Private
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.site-photos.bulk-action') }}">
                            @csrf
                            <input type="hidden" name="action" value="{{ $action }}">
                            <input type="hidden" name="photo_ids" value="{{ $photoIds }}">

                            @if($action === 'approve')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You are about to approve {{ $photos->count() }} photo(s). This will make them visible to site coordinators and potentially to clients if marked as public.
                                </div>

                                <div class="mb-3">
                                    <label for="admin_comments" class="form-label">Comments (Optional)</label>
                                    <textarea class="form-control" id="admin_comments" name="bulk_admin_comments" rows="3" 
                                              placeholder="Add comments for all approved photos..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_rating" class="form-label">Rating (Optional)</label>
                                    <select class="form-select" id="admin_rating" name="bulk_admin_rating">
                                        <option value="">No Rating</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="bulk_is_featured">
                                            <label class="form-check-label" for="is_featured">
                                                Mark all as Featured
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_public" name="bulk_is_public">
                                            <label class="form-check-label" for="is_public">
                                                Make all Public
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            @elseif($action === 'reject')
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    You are about to reject {{ $photos->count() }} photo(s). They will be returned to site coordinators for revision.
                                </div>

                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="rejection_reason" name="bulk_rejection_reason" rows="3" 
                                              placeholder="Please provide a reason for rejecting these photos..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="admin_comments" class="form-label">Additional Comments (Optional)</label>
                                    <textarea class="form-control" id="admin_comments" name="bulk_admin_comments" rows="2" 
                                              placeholder="Add additional comments..."></textarea>
                                </div>

                            @elseif($action === 'feature')
                                <div class="alert alert-success">
                                    <i class="fas fa-star me-2"></i>
                                    You are about to mark {{ $photos->count() }} photo(s) as featured. Featured photos are highlighted in project galleries.
                                </div>

                                <div class="alert alert-info">
                                    <strong>Note:</strong> Only approved photos can be featured. Draft, submitted, or rejected photos will be skipped.
                                </div>

                            @elseif($action === 'unfeature')
                                <div class="alert alert-secondary">
                                    <i class="fas fa-star-o me-2"></i>
                                    You are about to remove featured status from {{ $photos->count() }} photo(s).
                                </div>

                            @elseif($action === 'make_public')
                                <div class="alert alert-info">
                                    <i class="fas fa-eye me-2"></i>
                                    You are about to make {{ $photos->count() }} photo(s) public. Public photos can be viewed by clients and appear in project galleries.
                                </div>

                                <div class="alert alert-warning">
                                    <strong>Note:</strong> Only approved photos can be made public. Draft, submitted, or rejected photos will be skipped.
                                </div>

                            @elseif($action === 'make_private')
                                <div class="alert alert-secondary">
                                    <i class="fas fa-eye-slash me-2"></i>
                                    You are about to make {{ $photos->count() }} photo(s) private. They will no longer be visible to clients.
                                </div>
                            @endif

                            <!-- Confirmation -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirm_action" required>
                                <label class="form-check-label" for="confirm_action">
                                    I confirm that I want to 
                                    @if($action === 'approve')
                                        <strong class="text-success">approve</strong>
                                    @elseif($action === 'reject')
                                        <strong class="text-danger">reject</strong>
                                    @elseif($action === 'feature')
                                        <strong class="text-warning">feature</strong>
                                    @elseif($action === 'unfeature')
                                        <strong class="text-secondary">unfeature</strong>
                                    @elseif($action === 'make_public')
                                        <strong class="text-info">make public</strong>
                                    @elseif($action === 'make_private')
                                        <strong class="text-secondary">make private</strong>
                                    @endif
                                    these {{ $photos->count() }} photo(s)
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-{{ $action === 'approve' ? 'success' : ($action === 'reject' ? 'danger' : ($action === 'feature' ? 'warning' : ($action === 'make_public' ? 'info' : 'secondary'))) }}">
                                    @if($action === 'approve')
                                        <i class="fas fa-check me-1"></i>Approve All Photos
                                    @elseif($action === 'reject')
                                        <i class="fas fa-times me-1"></i>Reject All Photos
                                    @elseif($action === 'feature')
                                        <i class="fas fa-star me-1"></i>Feature All Photos
                                    @elseif($action === 'unfeature')
                                        <i class="fas fa-star-o me-1"></i>Unfeature All Photos
                                    @elseif($action === 'make_public')
                                        <i class="fas fa-eye me-1"></i>Make All Public
                                    @elseif($action === 'make_private')
                                        <i class="fas fa-eye-slash me-1"></i>Make All Private
                                    @endif
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
                        <p class="text-muted">No photos were selected for this bulk action.</p>
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
@endpush