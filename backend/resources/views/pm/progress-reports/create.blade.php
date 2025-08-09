@extends('app')

@section('title', 'Create Progress Report')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Create Progress Report</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Progress Report</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('pm.progress-reports.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Client Selection -->
                <div class="form-group mb-3">
                    <label for="client_id">Client</label>
                    <select name="client_id" id="client_id" class="form-control" required>
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Project Selection (Optional) -->
                <div class="form-group mb-3">
                    <label for="project_id">Project (Optional)</label>
                    <select name="project_id" id="project_id" class="form-control">
                        <option value="">None (General Report)</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Report Title -->
                <div class="form-group mb-3">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Report Date -->
                <div class="form-group mb-3">
                    <label for="report_date">Report Date</label>
                    <input type="date" name="report_date" id="report_date" class="form-control" 
                           value="{{ old('report_date', date('Y-m-d')) }}" required>
                    @error('report_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Progress Summary -->
                <div class="form-group mb-3">
                    <label for="description">Progress Summary</label>
                    <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Attachment -->
                <div class="form-group mb-3">
                    <label for="attachment">Attachment (Optional)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                    @error('attachment')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Report</button>
                    <a href="{{ route('pm.progress-reports.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection