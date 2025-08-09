@extends('app')

@section('content')
<div class="container">
    <h1>Performance Report</h1>
    
    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.performance') }}">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date_from">From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date_to">To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary mt-4">Filter</button>
            </div>
        </div>
    </form>
    
    <!-- Performance Table -->
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Total Tasks</th>
                <th>Completed Tasks</th>
                <th>Pending Tasks</th>
                <th>Overdue Tasks</th>
                <th>Completion Rate</th>
                <th>Overdue Rate</th>
                <th>Performance Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performanceData as $data)
            <tr>
                <td>{{ $data['name'] }}</td>
                <td>{{ $data['email'] }}</td>
                <td>{{ $data['total_tasks'] }}</td>
                <td>{{ $data['completed_tasks'] }}</td>
                <td>{{ $data['pending_tasks'] }}</td>
                <td>{{ $data['overdue_tasks'] }}</td>
                <td>{{ $data['completion_rate'] }}%</td>
                <td>{{ $data['overdue_rate'] }}%</td>
                <td>{{ $data['performance_score'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9">No performance data available for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection