@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Financial Report</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Create Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.financial-reports.store') }}" method="POST" id="createForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title" class="font-weight-bold">Report Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title') }}" required maxlength="255">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Enter a descriptive title for the financial report</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type" class="font-weight-bold">Report Type <span class="text-danger">*</span></label>
                                    <select name="report_type" id="report_type" class="form-control @error('report_type') is-invalid @enderror" required>
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
                                    <label for="report_period_start" class="font-weight-bold">Period Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="report_period_start" id="report_period_start" 
                                           class="form-control @error('report_period_start') is-invalid @enderror" 
                                           value="{{ old('report_period_start') }}" required>
                                    @error('report_period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_period_end" class="font-weight-bold">Period End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="report_period_end" id="report_period_end" 
                                           class="form-control @error('report_period_end') is-invalid @enderror" 
                                           value="{{ old('report_period_end') }}" required>
                                    @error('report_period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id" class="font-weight-bold">Project</label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror">
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
                                    <small class="form-text text-muted">Leave empty for general financial reports</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="currency" class="font-weight-bold">Currency <span class="text-danger">*</span></label>
                                    <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
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



                        <div class="form-group">
                            <label for="description" class="font-weight-bold">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      maxlength="1000">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Optional description of the report purpose</small>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="font-weight-bold">Notes</label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="form-control @error('notes') is-invalid @enderror" 
                                      maxlength="1000">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Additional notes or comments</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Report
                            </button>
                            <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Financial reports are initially created as drafts. 
                        You can generate them later to calculate totals and create detailed reports.
                    </div>
                    
                    <h6 class="font-weight-bold">Report Types:</h6>
                    <ul class="list-unstyled">
                        @foreach($reportTypeOptions as $value => $label)
                            <li><i class="fas fa-check text-success"></i> {{ $label }}</li>
                        @endforeach
                    </ul>

                    <h6 class="font-weight-bold mt-3">Process:</h6>
                    <ol class="small">
                        <li>Create report (Draft status)</li>
                        <li>Generate report (Calculates totals)</li>
                        <li>Export or create liquidated form</li>
                    </ol>
                </div>
            </div>

            <!-- Validation Rules Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Validation Rules</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-asterisk text-danger"></i> Title: Required, max 255 characters</li>
                        <li><i class="fas fa-asterisk text-danger"></i> Report Type: Required</li>
                        <li><i class="fas fa-asterisk text-danger"></i> Period: Start date must be before or equal to end date</li>
                        <li><i class="fas fa-asterisk text-danger"></i> Currency: Required</li>
                        <li><i class="fas fa-asterisk text-danger"></i> Exchange Rate: Required, minimum 0.0001</li>
                        <li><i class="fas fa-circle text-muted"></i> Description: Optional, max 1000 characters</li>
                        <li><i class="fas fa-circle text-muted"></i> Notes: Optional, max 1000 characters</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Date validation
    $('#report_period_start, #report_period_end').change(function() {
        const startDate = $('#report_period_start').val();
        const endDate = $('#report_period_end').val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('Start date cannot be after end date');
            $(this).val('');
        }
    });

    // Auto-calculate exchange rate for common currencies
    $('#currency').change(function() {
        const currency = $(this).val();
        const exchangeRates = {
            'PHP': '1.0000',
            'USD': '0.0180',
            'EUR': '0.0165',
            'GBP': '0.0140',
            'JPY': '2.7000'
        };
        
        if (exchangeRates[currency]) {
            $('#exchange_rate').val(exchangeRates[currency]);
        }
    });

    // Form validation
    $('#createForm').submit(function(e) {
        const title = $('#title').val().trim();
        const reportType = $('#report_type').val();
        const startDate = $('#report_period_start').val();
        const endDate = $('#report_period_end').val();
        const currency = $('#currency').val();
        const exchangeRate = $('#exchange_rate').val();

        if (!title) {
            alert('Please enter a report title');
            $('#title').focus();
            e.preventDefault();
            return false;
        }

        if (!reportType) {
            alert('Please select a report type');
            $('#report_type').focus();
            e.preventDefault();
            return false;
        }

        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            e.preventDefault();
            return false;
        }

        if (startDate > endDate) {
            alert('Start date cannot be after end date');
            e.preventDefault();
            return false;
        }

        if (!currency) {
            alert('Please select a currency');
            $('#currency').focus();
            e.preventDefault();
            return false;
        }

        if (!exchangeRate || parseFloat(exchangeRate) < 0.0001) {
            alert('Please enter a valid exchange rate (minimum 0.0001)');
            $('#exchange_rate').focus();
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
@endsection
