@if(isset($project))
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Edit Project</h1>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Project
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Project Information</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('projects.update', $project) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Project Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $project->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="5"
                                          placeholder="Enter project description...">{{ old('description', $project->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary me-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Project
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Active Projects</h2>
            @foreach ($activeProjects as $project)
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $project->name }}</h5>
            <p class="card-text">{{ Str::limit($project->description, 100) }}</p>
            <form action="{{ route('projects.archive', $project->id) }}" method="POST">
                @csrf
                @method('POST')
                <button type="submit" class="btn btn-warning">Archive</button>
            </form>
            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Project
            </a>
        </div>
    </div>
@endforeach

            <h2>Archived Projects</h2>
            @foreach ($archivedProjects as $project)
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">{{ $project->name }}</h5>
                        <p class="card-text">{{ Str::limit($project->description, 100) }}</p>
                        <form action="{{ route('projects.restore', $project->id) }}" method="POST">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-success">Restore</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>