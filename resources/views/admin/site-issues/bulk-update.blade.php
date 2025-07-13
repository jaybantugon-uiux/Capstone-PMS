@extends('app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Bulk Update Site Issues
                    </h5>
                    <a href="{{ route('admin.site-issues.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Issues
                    </a>
                </div>
                <div class="card-body">
                    @if(request('ids'))
                        @php
                            $selectedIds = explode(',', request('ids'));
                            $selectedIssues = \App\Models\SiteIssue::whereIn('id', $selectedIds)->with(['project', 'reporter'])->get();
                        @endphp
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-1"></i> Selected Issues ({{ $selectedIssues->count() }})</h6>
                            <div class="row">
                                @foreach($selectedIssues->take(6) as $issue)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                            <div>
                                                <strong>{{ Str::limit($issue->issue_title, 30) }}</strong>
                                                <br><small class="text-muted">{{ $issue->project->name }}</small>
                                            </div>
                                            <div>
                                                <span class="badge bg-{{ $issue->status_badge_color }}">
                                                    {{ $issue->formatted_status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($selectedIssues->count() > 6)
                                    <div class="col-12">
                                        <small class="text-muted">And {{ $selectedIssues->count() - 6 }} more issues...</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('admin.site-issues.bulk-update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="issue_ids" value="{{ request('ids') }}">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="bulk_status" class="form-label">Update Status</label>
                                    <select name="bulk_status" id="bulk_status" class="form-select">
                                        <option value="">Don't Change</option>
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                        <option value="escalated">Escalated</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bulk_assigned_to" class="form-label">Assign To</label>
                                    <select name="bulk_assigned_to" id="bulk_assigned_to" class="form-select">
                                        <option value="">Don't Change</option>
                                        <option value="unassign">Unassign All</option>
                                        @php
                                            $assignableUsers = \App\Models\User::whereIn('role', ['admin', 'pm'])->get();
                                        @endphp
                                        @foreach($assignableUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="bulk_priority" class="form-label">Update Priority</label>
                                    <select name="bulk_priority" id="bulk_priority" class="form-select">
                                        <option value="">Don't Change</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bulk_comment" class="form-label">Add Comment to All (Optional)</label>
                                <textarea name="bulk_comment" id="bulk_comment" rows="3" class="form-control" 
                                          placeholder="This comment will be added to all selected issues..."></textarea>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="bulk_comment_internal" id="bulk_comment_internal" class="form-check-input" value="1">
                                <label for="bulk_comment_internal" class="form-check-label">
                                    Make comment internal (admin only)
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update {{ $selectedIssues->count() }} Issues
                                </button>
                                <a href="{{ route('admin.site-issues.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-1"></i> No Issues Selected</h6>
                            <p class="mb-0">Please select issues from the main list to perform bulk updates.</p>
                            <a href="{{ route('admin.site-issues.index') }}" class="btn btn-sm btn-primary mt-2">
                                Go to Issues List
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection