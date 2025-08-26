@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Financial Report Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Liquidated Forms
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Report Information</h6>
                    <span class="badge badge-{{ $financialReport->status_badge_color }} badge-lg">
                        {{ $financialReport->formatted_status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $financialReport->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><span class="badge badge-info">{{ ucfirst($financialReport->report_type) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>
                                        @if($financialReport->project)
                                            <span class="badge badge-primary">{{ $financialReport->project->name }}</span>
                                        @else
                                            <span class="text-muted">No Project</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Currency:</strong></td>
                                    <td>{{ $financialReport->currency }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Period:</strong></td>
                                    <td>{{ $financialReport->formatted_period }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>
                                        @if($financialReport->creator)
                                            {{ $financialReport->creator->full_name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $financialReport->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @if($financialReport->generated_at)
                                <tr>
                                    <td><strong>Generated At:</strong></td>
                                    <td>{{ $financialReport->generated_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($financialReport->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Description:</h6>
                            <p class="text-muted">{{ $financialReport->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($financialReport->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Notes:</h6>
                            <p class="text-muted">{{ $financialReport->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($financialReport->status === 'generated')
                            <a href="{{ route('finance.financial-reports.export.pdf', $financialReport) }}" 
                               class="btn btn-info" target="_blank">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <a href="{{ route('finance.financial-reports.export.excel', $financialReport) }}" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Liquidated Form Card -->
            @if($financialReport->liquidatedForm)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Liquidated Form</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Form Number:</strong> {{ $financialReport->liquidatedForm->form_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        <span class="badge badge-{{ $financialReport->liquidatedForm->status_badge_color }}">
                            {{ $financialReport->liquidatedForm->formatted_status }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Liquidation Date:</strong> {{ $financialReport->liquidatedForm->liquidation_date }}
                    </div>
                    <a href="{{ route('pm.liquidated-forms.show', $financialReport->liquidatedForm) }}" 
                       class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-eye"></i> View Liquidated Form
                    </a>
                </div>
            </div>
            @endif

            <!-- Quick Links Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Liquidated Forms
                        </a>
                        @if($financialReport->project)
                            <a href="{{ route('projects.show', $financialReport->project) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-project-diagram"></i> View Project
                            </a>
                        @endif
                        <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-money-bill"></i> Manage Expenditures
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
