@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Employee Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- Employee-specific content can be added here -->
    </div>
@endsection