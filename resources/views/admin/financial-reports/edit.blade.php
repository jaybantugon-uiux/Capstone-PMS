@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Financial Report</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.financial-reports.show', $financialReport) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Report
            </a>
            <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Report Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Report Details</h6>
                    <span class="badge badge-{{ $financialReport->status_badge_color }} badge-lg">
                        {{ $financialReport->formatted_status }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.financial-reports.update', $financialReport) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Report Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $financialReport->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type">Report Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('report_type') is-invalid @enderror" 
                                            id="report_type" name="report_type" required>
                                        <option value="">Select Report Type</option>
                                        @foreach($reportTypeOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('report_type', $financialReport->report_type) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('report_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id">Project</label>
                                    <select class="form-control @error('project_id') is-invalid @enderror" 
                                            id="project_id" name="project_id">
                                        <option value="">Select Project (Optional)</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id', $financialReport->project_id) == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currency">Currency <span class="text-danger">*</span></label>
                                    <select class="form-control @error('currency') is-invalid @enderror" 
                                            id="currency" name="currency" required>
                                        <option value="">Select Currency</option>
                                        @foreach($currencyOptions as $value => $label)
                                            <option value="{{ $value }}" {{ old('currency', $financialReport->currency) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_period_start">Report Period Start <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('report_period_start') is-invalid @enderror" 
                                           id="report_period_start" name="report_period_start" 
                                           value="{{ old('report_period_start', $financialReport->report_period_start->format('Y-m-d')) }}" required>
                                    @error('report_period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_period_end">Report Period End <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('report_period_end') is-invalid @enderror" 
                                           id="report_period_end" name="report_period_end" 
                                           value="{{ old('report_period_end', $financialReport->report_period_end->format('Y-m-d')) }}" required>
                                    @error('report_period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Brief description of the financial report">{{ old('description', $financialReport->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Additional notes or comments">{{ old('notes', $financialReport->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Financial Report
                            </button>
                            <a href="{{ route('admin.financial-reports.show', $financialReport) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Report Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Current Status:</strong> 
                        <span class="badge badge-{{ $financialReport->status_badge_color }}">
                            {{ $financialReport->formatted_status }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Created By:</strong> 
                        @if($financialReport->creator)
                            {{ $financialReport->creator->full_name }}
                        @else
                            <span class="text-muted">Unknown</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Created At:</strong> {{ $financialReport->created_at->format('M d, Y H:i') }}
                    </div>
                    @if($financialReport->generated_at)
                    <div class="mb-3">
                        <strong>Generated At:</strong> {{ $financialReport->generated_at->format('M d, Y H:i') }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Last Updated:</strong> {{ $financialReport->updated_at->format('M d, Y H:i') }}
                    </div>

                    @if($financialReport->status !== 'draft')
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> This report has been generated. Changes to the period or project may affect the calculated totals.
                    </div>
                    @endif
                </div>
            </div>



            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.financial-reports.show', $financialReport) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                        
                        @if($financialReport->canBeGenerated())
                            <form method="POST" action="{{ route('admin.financial-reports.force-generate', $financialReport) }}" 
                                  onsubmit="return confirm('Generate this financial report?')">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-cog"></i> Generate Report
                                </button>
                            </form>
                        @endif

                        @if($financialReport->canBeDeleted())
                            <form method="POST" action="{{ route('admin.financial-reports.destroy', $financialReport) }}" 
                                  onsubmit="return confirm('Delete this financial report? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Delete Report
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Links Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> All Financial Reports
                        </a>
                        @if($financialReport->project)
                            <a href="{{ route('projects.show', $financialReport->project) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-project-diagram"></i> View Project
                            </a>
                        @endif
                        <a href="{{ route('admin.expenditures.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-money-bill"></i> Manage Expenditures
                        </a>
                        <a href="{{ route('admin.receipts.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-receipt"></i> Manage Receipts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Date validation
    document.getElementById('report_period_end').addEventListener('change', function() {
        const startDate = document.getElementById('report_period_start').value;
        const endDate = this.value;
        
        if (startDate && endDate && startDate > endDate) {
            alert('End date must be after or equal to start date.');
            this.value = '';
        }
    });

    // Auto-fill end date based on report type
    document.getElementById('report_type').addEventListener('change', function() {
        const startDate = document.getElementById('report_period_start').value;
        if (!startDate) return;

        const start = new Date(startDate);
        let end = new Date(start);

        switch(this.value) {
            case 'daily':
                // Same day
                break;
            case 'weekly':
                end.setDate(start.getDate() + 6);
                break;
            case 'monthly':
                end.setMonth(start.getMonth() + 1);
                end.setDate(start.getDate() - 1);
                break;
            case 'quarterly':
                end.setMonth(start.getMonth() + 3);
                end.setDate(start.getDate() - 1);
                break;
            case 'annual':
                end.setFullYear(start.getFullYear() + 1);
                end.setDate(start.getDate() - 1);
                break;
        }

        if (this.value !== 'custom') {
            document.getElementById('report_period_end').value = end.toISOString().split('T')[0];
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const startDate = document.getElementById('report_period_start').value;
        const endDate = document.getElementById('report_period_end').value;
        
        if (startDate && endDate && startDate > endDate) {
            e.preventDefault();
            alert('End date must be after or equal to start date.');
            return false;
        }
    });
</script>
@endpush
