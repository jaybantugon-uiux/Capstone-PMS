@extends('app')

@section('content')
    <div class="container">
        <h1>Notifications</h1>
        <div class="alert alert-success">The notification has been deleted.</div>
        <a href="{{ route('notifications.index') }}" class="btn btn-primary">Back to Notifications</a>
    </div>
@endsection