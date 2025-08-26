@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Financial Report</h1>
        <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Create Report Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.financial-reports.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Report Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
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
                                            <option value="{{ $value }}" {{ old('report_type') == $value ? 'selected' : '' }}>
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
                                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
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
                                            <option value="{{ $value }}" {{ old('currency') == $value ? 'selected' : '' }}>
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
                                           value="{{ old('report_period_start') }}" required>
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
                                           value="{{ old('report_period_end') }}" required>
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
                                      placeholder="Brief description of the financial report">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Additional notes or comments">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Financial Report
                            </button>
                            <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Report Types:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Daily:</strong> Daily financial summaries</li>
                            <li><strong>Weekly:</strong> Weekly financial summaries</li>
                            <li><strong>Monthly:</strong> Monthly financial summaries</li>
                            <li><strong>Quarterly:</strong> Quarterly financial summaries</li>
                            <li><strong>Annual:</strong> Annual financial summaries</li>
                            <li><strong>Project Summary:</strong> Project-specific summaries</li>
                            <li><strong>Custom:</strong> Custom period summaries</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Status Flow:</h6>
                        <div class="text-muted">
                            <small>
                                <strong>Draft</strong> → <strong>Generated</strong> → <strong>Liquidated</strong>
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">What happens when generated:</h6>
                        <ul class="list-unstyled text-muted">
                            <li>• Calculates total expenditures</li>
                            <li>• Calculates total receipts</li>
                            <li>• Computes variance amounts</li>
                            <li>• Creates liquidated form</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> The financial report will be created as a draft. You can generate it later to calculate totals and create the liquidated form.
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.financial-reports.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> View All Reports
                        </a>
                        <a href="{{ route('admin.expenditures.index') }}" class="btn btn-outline-info btn-sm">
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
</script>
@endpush
