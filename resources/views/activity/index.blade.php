@extends('app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-activity"></i> Recent Activities</h4>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if(empty($activities))
                        <div class="text-center py-5">
                            <i class="bi bi-activity display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No Recent Activities</h5>
                            <p class="text-muted">Activities will appear here as you work with projects and tasks.</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($activities as $activity)
                                <div class="timeline-item mb-4">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="timeline-icon">
                                                @if($activity['type'] === 'project')
                                                    <i class="bi bi-folder text-primary"></i>
                                                @elseif($activity['type'] === 'task')
                                                    <i class="bi bi-check-square text-success"></i>
                                                @else
                                                    <i class="bi bi-circle text-info"></i>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body py-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                @if(isset($activity['link']))
                                                                    <a href="{{ $activity['link'] }}" class="text-decoration-none">
                                                                        {{ $activity['title'] }}
                                                                    </a>
                                                                @else
                                                                    {{ $activity['title'] }}
                                                                @endif
                                                            </h6>
                                                            <p class="mb-2 text-muted">{{ $activity['description'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="bi bi-person"></i> {{ $activity['user'] }}
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge bg-{{ $activity['type'] === 'project' ? 'primary' : 'success' }}">
                                                                {{ ucfirst($activity['type']) }}
                                                            </span>
                                                            <small class="d-block text-muted mt-1">
                                                                {{ $activity['date']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    border: 2px solid #e9ecef;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 60px;
    height: calc(100% - 20px);
    width: 2px;
    background-color: #e9ecef;
    z-index: -1;
}

.timeline {
    position: relative;
}
</style>
@endsection