@extends('app')

@section('content')
<div class="container">
    <h1>{{ $project->name }} - Photo Gallery</h1>
    <p class="text-muted">{{ $photos->total() }} photos</p>

    @if($photos->count() > 0)
        <div class="row">
            @foreach($photos as $photo)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="{{ $photo->thumbnail_url }}" class="card-img-top" alt="{{ $photo->title }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ Str::limit($photo->title, 30) }}</h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    By {{ $photo->uploader->first_name }} {{ $photo->uploader->last_name }}<br>
                                    {{ $photo->photo_date->format('M d, Y') }}
                                </small>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 p-0">
                            <a href="{{ route('photos.show', $photo) }}" class="btn btn-primary btn-sm w-100">View Photo</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">
            {{ $photos->links() }}
        </div>
    @else
        <p class="text-muted text-center">No public approved photos available for this project.</p>
    @endif

    <div class="mt-4">
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Project
        </a>
    </div>
</div>
@endsection