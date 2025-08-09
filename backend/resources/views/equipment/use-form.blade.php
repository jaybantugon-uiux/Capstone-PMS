@extends('app')

@section('content')
<div class="container">
    <h1>Use {{ $equipment->name }}</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p>Current Stock: {{ $equipment->stock }}</p>

    <form method="POST" action="{{ route('equipment.use', $equipment->id) }}">
        @csrf
        <div class="form-group mb-3">
            <label for="amount">Amount to Use</label>
            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" min="1" max="{{ $equipment->stock }}" required>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-3">
            <label for="note">Note (Optional)</label>
            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror"></textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-warning">Use Equipment</button>
        <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection