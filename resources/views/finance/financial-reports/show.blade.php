@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Financial Report Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($financialReport->canBeEdited())
                <a href="{{ route('finance.financial-reports.edit', $financialReport) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endif
            @if($financialReport->canBeGenerated())
                <form method="POST" action="{{ route('finance.financial-reports.generate', $financialReport) }}" 
                      style="display: inline;" onsubmit="return confirm('Generate this report?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-cog"></i> Generate Report
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- General Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">ID:</td>
                                    <td>{{ $financialReport->id }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Title:</td>
                                    <td><strong>{{ $financialReport->title }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Project:</td>
                                    <td>
                                        @if($financialReport->project)
                                            <span class="font-weight-bold text-primary">{{ $financialReport->project->name }}</span>
                                        @else
                                            <span class="text-muted">General Report</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        <span class="badge badge-{{ $financialReport->status_color }} badge-lg">
                                            {{ $financialReport->formatted_status }}
                                        </span>
                                        @if($financialReport->is_overdue)
                                            <br><small class="text-danger">Overdue</small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Report Type:</td>
                                    <td>
                                        <span class="badge badge-info">{{ $financialReport->report_type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Period:</td>
                                    <td>{{ $financialReport->formatted_period }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Currency:</td>
                                    <td>{{ $financialReport->currency }}</td>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Description and Notes Card -->
            @if($financialReport->description || $financialReport->notes)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Description & Notes</h6>
                </div>
                <div class="card-body">
                    @if($financialReport->description)
                        <div class="mb-3">
                            <h6 class="font-weight-bold">Description:</h6>
                            <p class="text-justify">{{ $financialReport->description }}</p>
                        </div>
                    @endif
                    
                    @if($financialReport->notes)
                        <div>
                            <h6 class="font-weight-bold">Notes:</h6>
                            <p class="text-justify">{{ $financialReport->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif





            <!-- Liquidated Form Card -->
            @if($financialReport->liquidatedForm)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Liquidated Form</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Form Number:</td>
                                    <td>{{ $financialReport->liquidatedForm->form_number }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Title:</td>
                                    <td>{{ $financialReport->liquidatedForm->title }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        <span class="badge badge-{{ $financialReport->liquidatedForm->status_color }}">
                                            {{ $financialReport->liquidatedForm->formatted_status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('finance.liquidated-forms.show', $financialReport->liquidatedForm) }}" 
                                   class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Liquidated Form
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Current Status:</label>
                        <div class="mt-2">
                            <span class="badge badge-{{ $financialReport->status_color }} badge-lg">
                                {{ $financialReport->formatted_status }}
                            </span>
                        </div>
                    </div>

                    @if($financialReport->generated_at)
                        <div class="mb-3">
                            <label class="font-weight-bold">Generated:</label>
                            <div class="mt-2">
                                <p class="mb-1">{{ $financialReport->generated_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $financialReport->generated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif



                    <div class="mb-3">
                        <label class="font-weight-bold">Created:</label>
                        <div class="mt-2">
                            <p class="mb-1">{{ $financialReport->created_at->format('M d, Y H:i') }}</p>
                            <small class="text-muted">{{ $financialReport->created_at->diffForHumans() }}</small>
                            @if($financialReport->creator)
                                <br><small class="text-muted">by {{ $financialReport->creator->name }}</small>
                            @endif
                        </div>
                    </div>

                    @if($financialReport->updated_at != $financialReport->created_at)
                        <div class="mb-3">
                            <label class="font-weight-bold">Last Updated:</label>
                            <div class="mt-2">
                                <p class="mb-1">{{ $financialReport->updated_at->format('M d, Y H:i') }}</p>
                                <small class="text-muted">{{ $financialReport->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($financialReport->canBeEdited())
                            <a href="{{ route('finance.financial-reports.edit', $financialReport) }}" 
                               class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Edit Report
                            </a>
                        @endif
                        
                        @if($financialReport->canBeGenerated())
                            <form method="POST" action="{{ route('finance.financial-reports.generate', $financialReport) }}" 
                                  onsubmit="return confirm('Generate this report?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-cog"></i> Generate Report
                                </button>
                            </form>
                        @endif

                        @if($financialReport->status === 'generated')
                            <a href="{{ route('finance.financial-reports.export.pdf', $financialReport) }}" 
                               class="btn btn-danger btn-block" target="_blank">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            
                            <a href="{{ route('finance.financial-reports.export.excel', $financialReport) }}" 
                               class="btn btn-success btn-block" target="_blank">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        @endif

                        @if($financialReport->canBeLiquidated() && !$financialReport->liquidatedForm)
                            <form method="POST" action="{{ route('finance.financial-reports.create-liquidated-form', $financialReport) }}" 
                                  onsubmit="return confirm('Create liquidated form from this report?')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-file-invoice-dollar"></i> Create Liquidated Form
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('finance.financial-reports.index') }}" 
                           class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> View All Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Financial reports can be generated to calculate totals, 
                        exported in various formats, and used to create liquidated forms.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
