@extends('app')

@section('title', 'Progress Report Details')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">{{ $report->title }}</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Report Details</h6>
            <span class="badge bg-primary">PM Access</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Client:</strong> {{ $report->client->first_name }} {{ $report->client->last_name }}</p>
                    <p><strong>Project:</strong> {{ $report->project ? $report->project->name : 'General' }}</p>
                    <p><strong>Report Date:</strong> {{ $report->report_date->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $report->status_color }}">{{ $report->formatted_status }}</span>
                    </p>
                    <p><strong>Views:</strong> {{ $report->view_count }}</p>
                    @if($report->attachment_path)
                        <p><strong>Attachment:</strong> 
                            <a href="{{ route('admin.progress-reports.download', $report) }}" class="text-primary">
                                {{ $report->original_filename }} <i class="fas fa-download"></i>
                            </a>
                        </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>Created:</strong> {{ $report->created_at->format('M d, Y H:i') }}</p>
                    @if($report->sent_at)
                        <p><strong>Sent:</strong> {{ $report->sent_at->format('M d, Y H:i') }}</p>
                    @endif
                    @if($report->first_viewed_at)
                        <p><strong>First Viewed:</strong> {{ $report->first_viewed_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>
            
            <hr>
            
            <h5>Progress Summary</h5>
            <p>{{ $report->description }}</p>
            
            <div class="mt-4">
                <a href="{{ route('admin.progress-reports.edit', $report) }}" class="btn btn-secondary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('admin.progress-reports.destroy', $report) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this report?')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
                <a href="{{ route('pm.progress-reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection