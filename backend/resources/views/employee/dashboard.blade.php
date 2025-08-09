@extends('app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Employee Dashboard</h1>
        <div class="text-muted">
            Welcome, {{ auth()->user()->first_name }}!
        </div>
    </div>

    <!-- Equipment Management Cards -->
    <div class="row mb-4">
        <!-- Equipment Inventory Card -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title text-primary">
                                <i class="fas fa-boxes me-2"></i>Equipment Inventory
                            </h5>
                            <p class="text-muted mb-3">Monitor and manage equipment stock levels</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('equipment.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list me-1"></i>View All Equipment
                                </a>
                                <a href="{{ route('equipment.create') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Add New
                                </a>
                            </div>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-warehouse fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Alerts Card -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Stock Alerts
                            </h5>
                            <p class="text-muted mb-3">Items needing immediate attention</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('equipment.low-stock') }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-exclamation-triangle me-1"></i>View Low Stock
                                </a>
                                <a href="{{ route('equipment.bulk-restock.form') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-plus-circle me-1"></i>Bulk Restock
                                </a>
                            </div>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-bell fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('equipment.index') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-list fa-2x mb-2 d-block"></i>
                                    View Equipment
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('equipment.create') }}" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-plus fa-2x mb-2 d-block"></i>
                                    Add Equipment
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('equipment.bulk-restock.form') }}" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-truck fa-2x mb-2 d-block"></i>
                                    Bulk Restock
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('equipment.low-stock') }}" class="btn btn-outline-warning btn-lg">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                    Low Stock Alert
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity or Stats (Optional) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Equipment Management Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-chart-line fa-2x text-success mb-3"></i>
                                <h6>Monitor Stock Levels</h6>
                                <p class="text-muted small">Keep track of equipment usage and maintain optimal stock levels to avoid shortages.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-clock fa-2x text-info mb-3"></i>
                                <h6>Regular Updates</h6>
                                <p class="text-muted small">Update stock levels promptly when equipment is used or restocked to maintain accuracy.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-bell fa-2x text-warning mb-3"></i>
                                <h6>Set Alerts</h6>
                                <p class="text-muted small">Configure minimum stock levels to receive alerts when equipment needs restocking.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.opacity-75 {
    opacity: 0.75;
}
</style>
@endpush