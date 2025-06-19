@extends('app')

@section('content')
<div class="container">
    <h1>Stock Logs for {{ $equipment->name }}</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Change</th>
                <th>Note</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
            <tr>
                <td>{{ $log->user->name }}</td>
                <td>{{ $log->change }}</td>
                <td>{{ $log->note }}</td>
                <td>{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links() }}
    <a href="{{ route('equipment.index') }}" class="btn btn-secondary mt-3">Back to Equipment</a>
</div>
@endsection