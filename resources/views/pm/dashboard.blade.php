@extends('app')

@section('content')
    <div class="container">
        <h1>PM Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- PM-specific content can be added here -->
    </div>
@endsection