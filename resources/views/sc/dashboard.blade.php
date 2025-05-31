@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>SC Dashboard</h1>
        <p>Welcome, {{ auth()->user()->first_name }}!</p>
        <!-- SC-specific content can be added here -->
    </div>
@endsection