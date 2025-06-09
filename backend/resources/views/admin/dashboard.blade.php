@extends('app')

@section('content')
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- Admin-specific content can be added here -->
    </div>
@endsection