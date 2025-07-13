@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Review Site Photo</h1>
            <p class="text-muted">{{ $sitePhoto->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.show', $sitePhoto) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Photo
            </a>
            <a href="{{ route('pm.site-photos.edit', $sitePhoto) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Photo
            </a>
        </div>
    </div>

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
                    <!-- Photo Actions -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary" onclick="downloadPhoto()">
                            <i class="fas fa-download me-1"></i>Download
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="viewFullSize()">
                            <i class="fas fa-expand me-1"></i>Full Size
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="copyPhotoUrl()">
                            <i class="fas fa-link me-1"></i>Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Review Photo</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pm.site-photos.update-review', $sitePhoto) }}" method="POST" id="reviewForm">
                        @csrf
                        @method('PATCH')

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="submission_status" id="submission_status" class="form-select" required>
                                <option value="submitted" {{ old('submission_status', $sitePhoto->submission_status) == 'submitted' ? 'selected' : '' }}>Pending Review</option>
                                <option value="approved" {{ old('submission_status', $sitePhoto->submission_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('submission_status', $sitePhoto->submission_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('submission_status')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rejection Reason (shown only when status is rejected) -->
                        <div class="mb-3" id="rejection_reason_container" style="display: {{ old('submission_status', $sitePhoto->submission_status) == 'rejected' ? 'block' : 'none' }};">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" placeholder="Please provide a clear reason for rejection...">{{ old('rejection_reason', $sitePhoto->rejection_reason) }}</textarea>
                            @error('rejection_reason')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comments -->
                        <div class="mb-3">
                            <label class="form-label">Comments (Optional)</label>
                            <textarea name="admin_comments" class="form-control" rows="3" placeholder="Add your comments...">{{ old('admin_comments', $sitePhoto->admin_comments) }}</textarea>
                            @error('admin_comments')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rating -->
                        <div class="mb-3">
                            <label class="form-label">Rating (Optional)</label>
                            <select name="admin_rating" class="form-select">
                                <option value="">No Rating</option>
                                <option value="5" {{ old('admin_rating', $sitePhoto->admin_rating) == 5 ? 'selected' : '' }}>★★★★★ Excellent</option>
                                <option value="4" {{ old('admin_rating', $sitePhoto->admin_rating) == 4 ? 'selected' : '' }}>★★★★☆ Good</option>
                                <option value="3" {{ old('admin_rating', $sitePhoto->admin_rating) == 3 ? 'selected' : '' }}>★★★☆☆ Average</option>
                                <option value="2" {{ old('admin_rating', $sitePhoto->admin_rating) == 2 ? 'selected' : '' }}>★★☆☆☆ Poor</option>
                                <option value="1" {{ old('admin_rating', $sitePhoto->admin_rating) == 1 ? 'selected' : '' }}>★☆☆☆☆ Very Poor</option>
                            </select>
                            @error('admin_rating')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Public and Featured Checkboxes -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_public" id="isPublic" {{ old('is_public', $sitePhoto->is_public) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPublic">Make Public</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" {{ old('is_featured', $sitePhoto->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isFeatured">Mark as Featured</label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-save me-1"></i>Update Review
                        </button>
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
                        @if($sitePhoto->task)
                            <tr>
                                <th>Task:</th>
                                <td><a href="{{ route('tasks.show', $sitePhoto->task) }}">{{ $sitePhoto->task->task_name }}</a></td>
                            </tr>
                        @endif
                        <tr>
                            <th>Uploader:</th>
                            <td>{{ $sitePhoto->uploader->first_name }} {{ $sitePhoto->uploader->last_name }}</td>
                        </tr>
                        <tr>
                            <th>Photo Date:</th>
                            <td>{{ $sitePhoto->formatted_photo_date }}</td>
                        </tr>
                        <tr>
                            <th>Uploaded:</th>
                            <td>{{ $sitePhoto->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        @if($sitePhoto->submitted_at)
                            <tr>
                                <th>Submitted:</th>
                                <td>{{ $sitePhoto->formatted_submitted_at }}</td>
                            </tr>
                        @endif
                        @if($sitePhoto->reviewed_at)
                            <tr>
                                <th>Reviewed:</th>
                                <td>{{ $sitePhoto->formatted_reviewed_at }}</td>
                            </tr>
                            <tr>
                                <th>Reviewer:</th>
                                <td>{{ $sitePhoto->reviewer->first_name }} {{ $sitePhoto->reviewer->last_name }}</td>
                            </tr>
                        @endif
                        @if($sitePhoto->location)
                            <tr>
                                <th>Location:</th>
                                <td>{{ $sitePhoto->location }}</td>
                            </tr>
                        @endif
                        @if($sitePhoto->weather_conditions)
                            <tr>
                                <th>Weather:</th>
                                <td>
                                    <i class="{{ $sitePhoto->weather_icon }} me-1"></i>
                                    {{ $sitePhoto->formatted_weather_conditions }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th>File Size:</th>
                            <td>{{ $sitePhoto->formatted_file_size }}</td>
                        </tr>
                        <tr>
                            <th>Original Name:</th>
                            <td>{{ $sitePhoto->original_filename }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tags -->
            @if($sitePhoto->tags && count($sitePhoto->tags) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Tags</h6>
                    </div>
                    <div class="card-body">
                        @foreach($sitePhoto->tags as $tag)
                            <span class="badge bg-secondary me-1 mb-1">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Toggle rejection reason field based on status
    const statusSelect = document.getElementById('submission_status');
    const rejectionReasonContainer = document.getElementById('rejection_reason_container');
    const rejectionReasonInput = document.getElementById('rejection_reason');

    statusSelect.addEventListener('change', function() {
        if (this.value === 'rejected') {
            rejectionReasonContainer.style.display = 'block';
            rejectionReasonInput.required = true;
        } else {
            rejectionReasonContainer.style.display = 'none';
            rejectionReasonInput.required = false;
        }
    });

    // Client-side validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        if (statusSelect.value === 'rejected' && !rejectionReasonInput.value.trim()) {
            e.preventDefault();
            alert('Please provide a rejection reason.');
        }
    });
});

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

function copyPhotoUrl() {
    const url = '{{ asset("storage/" . $sitePhoto->photo_path) }}';
    navigator.clipboard.writeText(url).then(function() {
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close" onclick="this.closest('.toast').remove()"></button>
                </div>
                <div class="toast-body">
                    Photo URL copied to clipboard!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(function(err) {
        alert('Failed to copy URL to clipboard');
    });
}
</script>
@endpush
@endsection