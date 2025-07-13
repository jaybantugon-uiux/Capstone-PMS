{{-- Create task-reports/create.blade.php --}}
@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">
                    <i class="fas fa-plus-circle me-2"></i>Create Task Report
                </h1>
                <a href="{{ route('sc.task-reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Reports
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle me-1"></i> Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sc.task-reports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-md-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="task_id" class="form-label">Task <span class="text-danger">*</span></label>
                                            <select name="task_id" id="task_id" class="form-select" required>
                                                <option value="">Select a task</option>
                                                @foreach($tasks as $task)
                                                    <option value="{{ $task->id }}" 
                                                            {{ old('task_id', $selectedTaskId) == $task->id ? 'selected' : '' }}
                                                            data-project="{{ $task->project->name }}">
                                                        {{ $task->task_name }} ({{ $task->project->name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($tasks->isEmpty())
                                                <div class="form-text text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    No tasks assigned to you. Please contact your project manager.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="report_date" class="form-label">Report Date <span class="text-danger">*</span></label>
                                            <input type="date" name="report_date" id="report_date" 
                                                   class="form-control" value="{{ old('report_date', date('Y-m-d')) }}" 
                                                   max="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="report_title" class="form-label">Report Title <span class="text-danger">*</span></label>
                                    <input type="text" name="report_title" id="report_title" 
                                           class="form-control" value="{{ old('report_title') }}" 
                                           placeholder="e.g., Daily Progress Report - Foundation Work" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="task_status" class="form-label">Task Status <span class="text-danger">*</span></label>
                                            <select name="task_status" id="task_status" class="form-select" required>
                                                <option value="">Select status</option>
                                                <option value="pending" {{ old('task_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ old('task_status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="completed" {{ old('task_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="on_hold" {{ old('task_status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                                <option value="cancelled" {{ old('task_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="progress_percentage" class="form-label">Progress Percentage <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" name="progress_percentage" id="progress_percentage" 
                                                       class="form-control" value="{{ old('progress_percentage', 0) }}" 
                                                       min="0" max="100" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 10px;">
                                                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Work Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-hammer me-2"></i>Work Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="work_description" class="form-label">Work Description <span class="text-danger">*</span></label>
                                    <textarea name="work_description" id="work_description" 
                                              class="form-control" rows="4" required
                                              placeholder="Describe the work completed, activities performed, and any significant accomplishments...">{{ old('work_description') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="hours_worked" class="form-label">Hours Worked</label>
                                            <div class="input-group">
                                                <input type="number" name="hours_worked" id="hours_worked" 
                                                       class="form-control" value="{{ old('hours_worked') }}" 
                                                       min="0" max="24" step="0.5">
                                                <span class="input-group-text">hours</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="weather_conditions" class="form-label">Weather Conditions</label>
                                            <select name="weather_conditions" id="weather_conditions" class="form-select">
                                                <option value="">Select weather</option>
                                                <option value="sunny" {{ old('weather_conditions') === 'sunny' ? 'selected' : '' }}>
                                                    ☀️ Sunny
                                                </option>
                                                <option value="cloudy" {{ old('weather_conditions') === 'cloudy' ? 'selected' : '' }}>
                                                    ☁️ Cloudy
                                                </option>
                                                <option value="rainy" {{ old('weather_conditions') === 'rainy' ? 'selected' : '' }}>
                                                    🌧️ Rainy
                                                </option>
                                                <option value="stormy" {{ old('weather_conditions') === 'stormy' ? 'selected' : '' }}>
                                                    ⛈️ Stormy
                                                </option>
                                                <option value="windy" {{ old('weather_conditions') === 'windy' ? 'selected' : '' }}>
                                                    💨 Windy
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="materials_used" class="form-label">Materials Used</label>
                                    <textarea name="materials_used" id="materials_used" 
                                              class="form-control" rows="3"
                                              placeholder="List materials consumed or used (e.g., cement bags, steel bars, etc.)">{{ old('materials_used') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="equipment_used" class="form-label">Equipment Used</label>
                                    <textarea name="equipment_used" id="equipment_used" 
                                              class="form-control" rows="3"
                                              placeholder="List equipment and tools used (e.g., excavator, concrete mixer, etc.)">{{ old('equipment_used') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Issues and Next Steps -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Issues & Planning
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="issues_encountered" class="form-label">Issues Encountered</label>
                                    <textarea name="issues_encountered" id="issues_encountered" 
                                              class="form-control" rows="3"
                                              placeholder="Describe any problems, delays, or challenges faced...">{{ old('issues_encountered') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="next_steps" class="form-label">Next Steps</label>
                                    <textarea name="next_steps" id="next_steps" 
                                              class="form-control" rows="3"
                                              placeholder="Outline planned activities for the next reporting period...">{{ old('next_steps') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="additional_notes" class="form-label">Additional Notes</label>
                                    <textarea name="additional_notes" id="additional_notes" 
                                              class="form-control" rows="3"
                                              placeholder="Any other relevant information or observations...">{{ old('additional_notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-md-4">
                        <!-- Photo Upload -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-camera me-2"></i>Photos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="photos" class="form-label">Upload Photos</label>
                                    <input type="file" name="photos[]" id="photos" 
                                           class="form-control" multiple accept="image/*">
                                    <div class="form-text">
                                        <small>Max 5MB per image. Supported formats: JPG, PNG, GIF</small>
                                    </div>
                                </div>
                                <div id="photo-preview" class="row g-2"></div>
                            </div>
                        </div>

                        <!-- Help Card -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Helpful Tips
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Be specific and detailed in your descriptions
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Include measurements and quantities when relevant
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Report all safety incidents or concerns
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Take clear photos showing work progress
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Submit reports daily or as required
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sc.task-reports.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-1"></i> Submit Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress bar update
    const progressInput = document.getElementById('progress_percentage');
    const progressBar = document.getElementById('progress-bar');
    
    function updateProgressBar() {
        const value = progressInput.value;
        progressBar.style.width = value + '%';
        progressBar.setAttribute('aria-valuenow', value);
        progressBar.textContent = value + '%';
        
        // Change color based on progress
        progressBar.className = 'progress-bar';
        if (value >= 80) {
            progressBar.classList.add('bg-success');
        } else if (value >= 60) {
            progressBar.classList.add('bg-info');
        } else if (value >= 40) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }
    }
    
    progressInput.addEventListener('input', updateProgressBar);
    updateProgressBar(); // Initial update
    
    // Photo preview
    const photoInput = document.getElementById('photos');
    const photoPreview = document.getElementById('photo-preview');
    
    photoInput.addEventListener('change', function() {
        photoPreview.innerHTML = '';
        
        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-6';
                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                            </div>
                        </div>
                    `;
                    photoPreview.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Auto-generate report title based on task selection
    const taskSelect = document.getElementById('task_id');
    const reportTitleInput = document.getElementById('report_title');
    
    taskSelect.addEventListener('change', function() {
        if (this.value && !reportTitleInput.value) {
            const selectedOption = this.options[this.selectedIndex];
            const taskName = selectedOption.text.split(' (')[0];
            const today = new Date().toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            reportTitleInput.value = `Daily Report - ${taskName} - ${today}`;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card-img-top {
    transition: transform 0.2s;
}
.card-img-top:hover {
    transform: scale(1.05);
}
</style>
@endpush