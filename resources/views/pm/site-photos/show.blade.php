@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Photo Details</h1>
            <p class="text-muted">{{ $sitePhoto->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Photos
            </a>
            
            @if($sitePhoto->submission_status === 'submitted')
                <button type="button" class="btn btn-success" onclick="showApproveModal()">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            @endif
            
            @if($sitePhoto->submission_status === 'approved')
                <button type="button" class="btn btn-warning" onclick="toggleFeatured()" id="featureBtn">
                    <i class="fas fa-star me-1"></i>
                    {{ $sitePhoto->is_featured ? 'Unfeature' : 'Feature' }}
                </button>
                <button type="button" class="btn btn-info" onclick="togglePublic()" id="publicBtn">
                    <i class="fas fa-eye me-1"></i>
                    {{ $sitePhoto->is_public ? 'Make Private' : 'Make Public' }}
                </button>
            @endif
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
                    
                    <!-- Photo Description -->
                    @if($sitePhoto->description)
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Description</h6>
                                <p class="card-text">{{ $sitePhoto->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Photo Information -->
        <div class="col-md-4">
            <!-- Status and Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Photo Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Status:</span>
                        <span class="badge bg-{{ $sitePhoto->submission_status_badge_color }}">
                            {{ $sitePhoto->formatted_submission_status }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Category:</span>
                        <span class="badge bg-{{ $sitePhoto->photo_category_badge_color }}">
                            {{ $sitePhoto->formatted_photo_category }}
                        </span>
                    </div>
                    
                    @if($sitePhoto->admin_rating)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Rating:</span>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $sitePhoto->admin_rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <small class="text-muted">({{ $sitePhoto->admin_rating }}/5)</small>
                            </div>
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Featured:</span>
                        <span class="badge bg-{{ $sitePhoto->is_featured ? 'success' : 'secondary' }}">
                            {{ $sitePhoto->is_featured ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Public:</span>
                        <span class="badge bg-{{ $sitePhoto->is_public ? 'info' : 'secondary' }}">
                            {{ $sitePhoto->is_public ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Photo Details -->
            <div class="card mb-3">
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
                <div class="card mb-3">
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

            <!-- Camera Information -->
            @if($sitePhoto->camera_info && !empty($sitePhoto->camera_info))
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Camera Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @if(isset($sitePhoto->camera_info['width']) && isset($sitePhoto->camera_info['height']))
                                <tr>
                                    <th>Dimensions:</th>
                                    <td>{{ $sitePhoto->camera_info['width'] }} × {{ $sitePhoto->camera_info['height'] }}</td>
                                </tr>
                            @endif
                            @if(isset($sitePhoto->camera_info['camera_make']))
                                <tr>
                                    <th>Camera:</th>
                                    <td>{{ $sitePhoto->camera_info['camera_make'] }}</td>
                                </tr>
                            @endif
                            @if(isset($sitePhoto->camera_info['camera_model']))
                                <tr>
                                    <th>Model:</th>
                                    <td>{{ $sitePhoto->camera_info['camera_model'] }}</td>
                                </tr>
                            @endif
                            @if(isset($sitePhoto->camera_info['gps_latitude']) && isset($sitePhoto->camera_info['gps_longitude']))
                                <tr>
                                    <th>GPS:</th>
                                    <td>
                                        <a href="https://maps.google.com/?q={{ $sitePhoto->camera_info['gps_latitude'] }},{{ $sitePhoto->camera_info['gps_longitude'] }}" target="_blank">
                                            {{ number_format($sitePhoto->camera_info['gps_latitude'], 6) }}, {{ number_format($sitePhoto->camera_info['gps_longitude'], 6) }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            @endif

            <!-- Admin Comments -->
            @if($sitePhoto->admin_comments)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Admin Comments</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $sitePhoto->admin_comments }}</p>
                    </div>
                </div>
            @endif

            <!-- Rejection Reason -->
            @if($sitePhoto->submission_status === 'rejected' && $sitePhoto->rejection_reason)
                <div class="card mb-3 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Rejection Reason</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $sitePhoto->rejection_reason }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Comments Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Comments ({{ $sitePhoto->comments->count() }})</h6>
                </div>
                <div class="card-body">
                    <!-- Add Comment Form -->
                    <form action="{{ route('pm.site-photos.add-comment', $sitePhoto) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Add Comment</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Add your comment..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal">
                                <label class="form-check-label" for="isInternal">
                                    Internal comment (not visible to site coordinator)
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-comment me-1"></i>Add Comment
                        </button>
                    </form>

                    <!-- Comments List -->
                    @if($sitePhoto->comments->count() > 0)
                        @foreach($sitePhoto->comments->sortByDesc('created_at') as $comment)
                            <div class="d-flex mb-3 {{ $comment->is_internal ? 'border-start border-warning border-3 ps-3' : '' }}">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($comment->user->first_name, 0, 1)) }}{{ strtoupper(substr($comment->user->last_name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $comment->user->first_name }} {{ $comment->user->last_name }}</h6>
                                            <small class="text-muted">
                                                {{ $comment->created_at->format('M d, Y g:i A') }}
                                                @if($comment->is_internal)
                                                    <span class="badge bg-warning ms-1">Internal</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0">{{ $comment->comment }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No comments yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modals -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pm.site-photos.update-review', $sitePhoto) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="approve">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea name="admin_comments" class="form-control" rows="3" placeholder="Add approval comments..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (Optional)</label>
                        <select name="admin_rating" class="form-select">
                            <option value="">No Rating</option>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★☆ Good</option>
                            <option value="3">★★★☆☆ Average</option>
                            <option value="2">★★☆☆☆ Poor</option>
                            <option value="1">★☆☆☆☆ Very Poor</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_public" id="isPublic">
                                <label class="form-check-label" for="isPublic">
                                    Make Public
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured">
                                <label class="form-check-label" for="isFeatured">
                                    Mark as Featured
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pm.site-photos.update-review', $sitePhoto) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a clear reason for rejection..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Comments (Optional)</label>
                        <textarea name="admin_comments" class="form-control" rows="2" placeholder="Additional feedback..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Photo</button>
                </div>
            </form>
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
function showApproveModal() {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function showRejectModal() {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function toggleFeatured() {
    const photoId = {{ $sitePhoto->id }};
    const isFeatured = {{ $sitePhoto->is_featured ? 'true' : 'false' }};
    
    fetch(`/pm/site-photos/${photoId}/toggle-feature`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            is_featured: !isFeatured
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating featured status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating featured status');
    });
}

function togglePublic() {
    const photoId = {{ $sitePhoto->id }};
    const isPublic = {{ $sitePhoto->is_public ? 'true' : 'false' }};
    
    fetch(`/pm/site-photos/${photoId}/toggle-public`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            is_public: !isPublic
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating public status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating public status');
    });
}

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
        // Create a temporary toast notification
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