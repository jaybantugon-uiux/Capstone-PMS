@extends('app')

@section('content')
    <div class="container">
        <h1>Notifications</h1>
        
        <div class="mb-3">
            <form action="{{ route('notifications.mark.all.read') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">Mark All as Read</button>
            </form>
        </div>
        
        @if($notifications->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Received</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifications as $notification)
                        <tr class="{{ $notification->read_at ? '' : 'table-info' }}">
                            <td>
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}">
                                        {{ $notification->data['message'] }}
                                    </a>
                                @else
                                    {{ $notification->data['message'] }}
                                @endif
                            </td>
                            <td>{{ $notification->created_at->diffForHumans() }}</td>
                            <td>
                                @if(!$notification->read_at)
                                    <form action="{{ route('notifications.mark.read', $notification->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">Mark as Read</button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.delete', $notification->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if($notifications->hasPages())
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <p class="text-muted">No notifications found.</p>
        @endif
    </div>
@endsection