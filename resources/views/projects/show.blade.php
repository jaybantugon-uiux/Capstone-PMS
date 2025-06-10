 <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Project Details</h1>
                    <div>
                        @if(auth()->user()->role === 'admin' || $project->created_by === auth()->id())
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Project
                            </a>
                        @endif
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Projects
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">{{ $project->name }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Description</h5>
                                <p class="text-muted">
                                    {{ $project->description ?? 'No description provided.' }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h5>Project Information</h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Created By:</strong></td>
                                        <td>{{ $project->creator->full_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created At:</strong></td>
                                        <td>{{ $project->created_at->format('F d, Y \a\t g:i A') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>{{ $project->updated_at->format('F d, Y \a\t g:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>