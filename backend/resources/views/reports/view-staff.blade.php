@extends('app')

@section('content')
<div class="container">
    <h1>View Available Staff</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Active Tasks</th>
                <th>Overdue Tasks</th>
                <th>Workload Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $user)
                <tr>
                    <td><a href="{{ route('reports.staff-workload.detail', $user->id) }}">{{ $user->full_name }}</a></td>
                    <td>{{ $user->active_tasks }}</td>
                    <td>{{ $user->overdue_tasks }}</td>
                    <td>
                        <span class="badge bg-{{ $user->workload_status == 'available' ? 'success' : ($user->workload_status == 'busy' ? 'warning' : 'danger') }}">
                            {{ ucfirst($user->workload_status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection