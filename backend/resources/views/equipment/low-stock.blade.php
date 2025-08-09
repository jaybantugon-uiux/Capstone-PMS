@extends('app')

@section('content')
<div class="container">
    <h1>Low Stock Equipment</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p>Showing equipment with stock less than or equal to {{ $threshold }}</p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Stock</th>
                <th>Min Stock Level</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lowStockEquipment as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->stock }}</td>
                <td>{{ $item->min_stock_level }}</td>
                <td>
                    <a href="{{ route('equipment.restock.form', $item->id) }}" class="btn btn-sm btn-success">Restock</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('equipment.index') }}" class="btn btn-secondary mt-3">Back to Equipment</a>
</div>
@endsection