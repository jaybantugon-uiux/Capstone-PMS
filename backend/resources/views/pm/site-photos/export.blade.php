@extends('app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Export Site Photos</h1>
            <p class="text-muted">Select criteria to export site photos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.site-photos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Photos
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Export Options</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pm.site-photos.export') }}" id="exportForm">
                @csrf

                <!-- Project Selection -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Date Range -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ old('date_from') }}">
                        @error('date_from')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}">
                        @error('date_to')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="statuses[]" value="submitted" {{ in_array('submitted', old('statuses', ['submitted', 'approved'])) ? 'checked' : '' }}>
                                <label class="form-check-label">Pending Review</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="statuses[]" value="approved" {{ in_array('approved', old('statuses', ['submitted', 'approved'])) ? 'checked' : '' }}>
                                <label class="form-check-label">Approved</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="statuses[]" value="rejected" {{ in_array('rejected', old('statuses', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Rejected</label>
                            </div>
                        </div>
                        @error('statuses')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Categories</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach(['progress', 'quality', 'safety', 'equipment', 'materials', 'workers', 'completion', 'other'] as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category }}" {{ in_array($category, old('categories', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ ucfirst($category) }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('categories')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Export Fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Fields to Export <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="title" {{ in_array('title', old('fields', ['title', 'description'])) ? 'checked' : '' }}>
                                <label class="form-check-label">Title</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="description" {{ in_array('description', old('fields', ['title', 'description'])) ? 'checked' : '' }}>
                                <label class="form-check-label">Description</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="project_name" {{ in_array('project_name', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Project Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="uploader_name" {{ in_array('uploader_name', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Uploader Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="photo_date" {{ in_array('photo_date', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Photo Date</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="category" {{ in_array('category', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Category</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="status" {{ in_array('status', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="tags" {{ in_array('tags', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Tags</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="admin_comments" {{ in_array('admin_comments', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Admin Comments</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input export-field" type="checkbox" name="fields[]" value="rejection_reason" {{ in_array('rejection_reason', old('fields', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">Rejection Reason</label>
                            </div>
                        </div>
                        @error('fields')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Export Format -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Export Format <span class="text-danger">*</span></label>
                        <select name="format" class="form-select" required>
                            <option value="csv" {{ old('format') == 'csv' ? 'selected' : '' }}>CSV</option>
                            <option value="pdf" {{ old('format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="zip" {{ old('format') == 'zip' ? 'selected' : '' }}>ZIP (Images)</option>
                        </select>
                        @error('format')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>Export Photos
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('exportForm');
    const fields = document.querySelectorAll('.export-field');

    form.addEventListener('submit', function(e) {
        const checkedFields = Array.from(fields).filter(field => field.checked);
        if (checkedFields.length === 0) {
            e.preventDefault();
            alert('Please select at least one field to export.');
        }
    });
});
</script>
@endpush
@endsection