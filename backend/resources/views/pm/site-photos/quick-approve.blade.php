@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Quick Approve Site Photo</h1>
            <p class="text-muted">Photo ID: {{ $sitePhoto->id }} - {{ $sitePhoto->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.show', $sitePhoto) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Photo
            </a>
            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>All Photos
            </a>
        </div>
    </div>

    @if ($sitePhoto->submission_status !== 'submitted')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            This photo is not in "Pending Review" status and cannot be approved.
            Current status: <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }}">{{ $sitePhoto->formatted_submission_status }}</span>
        </div>
    @else
        <div class="row">
            <!-- Photo Display -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $sitePhoto->photo_path) }}" 
                                 class="img-fluid rounded shadow" 
                                 alt="{{ $sitePhoto->title }}"
                                 style="max-height: 600px;">
                        </div>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="downloadPhoto()">
                                <i class="fas fa-download me-1"></i>Download
                            </button>
                            <button type="button Instituted="button" class="btn btn-outline-info" onclick="viewFullSize()">
                                <i class="fas fa-expand me-1"></i>Full Size
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Approve Photo</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pm.site-photos.quick-approve', $sitePhoto) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Comments (Optional)</label>
                                <textarea name="comments" class="form-control" rows="3" placeholder="Add approval comments...">{{ old('comments') }}</textarea>
                                @error('comments')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_public" id="isPublic" {{ old('is_public') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isPublic">Make Public</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" {{ old('is_featured') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isFeatured">Mark as Featured</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success flex-grow-1">
                                    <i class="fas fa-check me-1"></i>Approve Photo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Photo Information -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Photo Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Project:</th>
                                <td><a href="{{ route('projects.show', $sitePhoto->project) }}">{{ $sitePhoto->project->name }}</a></td>
                            </tr>
                            <tr>
                                <th>Uploader:</th>
                                <td>{{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $sitePhoto->formatted_photo_category }}</td>
                            </tr>
                            <tr>
                                <th>Photo Date:</th>
                                <td>{{ $sitePhoto->formatted_photo_date }}</td>
                            </tr>
                            <tr>
                                <th>Uploaded:</th>
                                <td>{{ $sitePhoto->created_at->format('M d, Y g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Photos -->
        @if ($relatedPhotos && $relatedPhotos->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Related Photos from {{ $sitePhoto->project->name }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($relatedPhotos as $relatedPhoto)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $relatedPhoto->photo_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $relatedPhoto->title }}"
                                         style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <small class="d-block">{{ Str::limit($relatedPhoto->title, 25) }}</small>
                                        <small class="text-muted">{{ $relatedPhoto->formatted_photo_date }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<!-- Full Size Modal -->
<div class="modal fade" id="fullSizeModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $sitePhoto->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center">
                <img src="{{ asset('storage/' . $sitePhoto->photo_path) }}" 
                     class="img-fluid" 
                     alt="{{ $sitePhoto->title }}">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewFullSize() {
    const modal = new bootstrap.Modal(document.getElementById('fullSizeModal'));
    modal.show();
}

function downloadPhoto() {
    const link = document.createElement('a');
    link.href = '{{ asset("storage/" . $sitePhoto->photo_path) }}';
    link.download = '{{ $sitePhoto->original_filename }}';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush
@endsection