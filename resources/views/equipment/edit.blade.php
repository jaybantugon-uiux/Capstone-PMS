@extends('app')

@section('content')
<div class="container">
    <h1>Edit Equipment</h1>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('equipment.update', $equipment->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group mb-3">
                                <div class="form-group mb-3">
            <label for="name">Equipment Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $equipment->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" 
                      id="description" name="description" rows="3">{{ old('description', $equipment->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="stock">Current Stock</label>
            <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                   id="stock" name="stock" value="{{ old('stock', $equipment->stock) }}" 
                   min="0" max="10000" required>
            @error('stock')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                Changing this value will create a stock adjustment log entry
            </small>
        </div>

        <div class="form-group mb-3">
            <label for="min_stock_level">Minimum Stock Level</label>
            <input type="number" class="form-control @error('min_stock_level') is-invalid @enderror" 
                   id="min_stock_level" name="min_stock_level" 
                   value="{{ old('min_stock_level', $equipment->min_stock_level) }}" 
                   min="0" max="1000">
            @error('min_stock_level')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Alert threshold for low stock warnings</small>
        </div>

        <button type="submit" class="btn btn-primary">Update Equipment</button>
        <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection