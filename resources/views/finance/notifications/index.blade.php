@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell"></i> Notifications
        </h1>
        <div class="d-flex gap-2">
            @if($notifications->where('read_at', null)->count() > 0)
                <button type="button" class="btn btn-success btn-sm" id="markAllReadBtn">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            @endif
            <a href="{{ route('finance.dashboard') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                All Notifications ({{ $notifications->total() }})
                @if($notifications->where('read_at', null)->count() > 0)
                    <span class="badge badge-danger ml-2">{{ $notifications->where('read_at', null)->count() }} New</span>
                @endif
            </h6>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                    <div class="list-group-item px-0 border-0 {{ !$notification->read_at ? 'bg-light' : '' }}">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <h6 class="mb-0">
                                        @if($notification->data['type'] === 'revision_requested')
                                            <i class="fas fa-redo text-warning"></i>
                                        @elseif($notification->data['type'] === 'clarification_requested')
                                            <i class="fas fa-question-circle text-info"></i>
                                        @else
                                            <i class="fas fa-bell text-primary"></i>
                                        @endif
                                        {{ $notification->data['type'] === 'revision_requested' ? 'Revision Requested' : ($notification->data['type'] === 'clarification_requested' ? 'Clarification Requested' : 'Notification') }}
                                        @if(!$notification->read_at)
                                            <span class="badge badge-danger ml-2">New</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted ml-auto">{{ $notification->created_at->format('M d, Y g:i A') }}</small>
                                </div>
                                
                                <div class="mb-2">
                                    @if($notification->data['type'] === 'revision_requested')
                                        <p class="mb-1">
                                            <strong>Form #{{ $notification->data['form_number'] }}</strong> - 
                                            {{ $notification->data['revision_reason'] }}
                                        </p>
                                    @elseif($notification->data['type'] === 'clarification_requested')
                                        @if(isset($notification->data['form_number']))
                                            <p class="mb-1">
                                                <strong>Form #{{ $notification->data['form_number'] }}</strong> - 
                                                {{ $notification->data['clarification_question'] }}
                                            </p>
                                        @elseif(isset($notification->data['receipt_number']))
                                            <p class="mb-1">
                                                <strong>Receipt #{{ $notification->data['receipt_number'] }}</strong> 
                                                ({{ $notification->data['vendor_name'] ?? 'N/A' }}) - 
                                                {{ $notification->data['clarification_question'] }}
                                            </p>
                                        @elseif(isset($notification->data['message']))
                                            <p class="mb-1">
                                                <strong>Bulk Clarification Request</strong> - 
                                                {{ $notification->data['message'] }}
                                            </p>
                                        @else
                                            <p class="mb-1">
                                                <strong>Clarification Requested</strong> - 
                                                {{ $notification->data['clarification_question'] ?? 'No details available' }}
                                            </p>
                                        @endif
                                    @else
                                        <p class="mb-1">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                    @endif
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Requested by: {{ $notification->data['requester_name'] ?? 'Admin' }}
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="{{ $notification->data['view_url'] ?? '#' }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    @if(!$notification->read_at)
                                        <button class="btn btn-sm btn-success mark-read-btn" data-notification-id="{{ $notification->id }}">
                                            <i class="fas fa-check"></i> Mark Read
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-danger delete-notification-btn" data-notification-id="{{ $notification->id }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No notifications found</h5>
                    <p class="text-gray-400">You're all caught up! No notifications to display.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this notification? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mark notification as read
    $('.mark-read-btn').click(function() {
        const notificationId = $(this).data('notification-id');
        const button = $(this);
        const listItem = button.closest('.list-group-item');
        
        $.post(`/finance/notifications/${notificationId}/mark-as-read`, {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                button.remove();
                listItem.removeClass('bg-light');
                listItem.find('.badge').remove();
                location.reload(); // Refresh to update counts
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to mark notification as read:', error);
            alert('Failed to mark notification as read. Please try again.');
        });
    });
    
    // Mark all notifications as read
    $('#markAllReadBtn').click(function() {
        if (confirm('Are you sure you want to mark all notifications as read?')) {
            $.post('/finance/notifications/mark-all-as-read', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Failed to mark all notifications as read:', error);
                alert('Failed to mark all notifications as read. Please try again.');
            });
        }
    });
    
    // Delete notification
    $('.delete-notification-btn').click(function() {
        const notificationId = $(this).data('notification-id');
        const form = $('#deleteForm');
        
        if (confirm('Are you sure you want to delete this notification?')) {
            form.attr('action', `/finance/notifications/${notificationId}`);
            form.submit();
        }
    });
});
</script>
@endpush
