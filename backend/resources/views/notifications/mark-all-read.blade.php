@extends('app')

@section('content')
    <div class="container">
        <h1>Notifications</h1>
        <div class="alert alert-success">All notifications have been marked as read.</div>
        <a href="{{ route('notifications.index') }}" class="btn btn-primary">Back to Notifications</a>
    </div>
@endsection