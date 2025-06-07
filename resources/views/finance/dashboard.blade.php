@extends('app')

@section('content')
    <div class="container">
        <h1>Finance Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- Finance-specific content can be added here -->
    </div>
@endsection