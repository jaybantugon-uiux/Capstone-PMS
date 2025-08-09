@extends('app')

@section('content')
<div class="container">
    <h1>Bulk Restock Equipment</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('equipment.bulk-restock') }}">
        @csrf
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Current Stock</th>
                    <th>Amount to Restock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipment as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->stock }}</td>
                    <td>
                        <input type="number" name="equipment[{{ $item->id }}][amount]" class="form-control" min="0" value="0">
                        <input type="hidden" name="equipment[{{ $item->id }}][id]" value="{{ $item->id }}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="form-group mb-3">
            <label for="note">Note (Optional)</label>
            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror"></textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Restock Selected</button>
        <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection