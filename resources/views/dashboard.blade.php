<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Dashboard</h4>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Logout</button>
                        </form>
                    </div>
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <h5>Welcome, {{ Auth::user()->full_name }}!</h5>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                <p><strong>Username:</strong> {{ Auth::user()->username }}</p>
                                <p><strong>Role:</strong> 
                                    <span class="badge bg-primary">{{ ucfirst(Auth::user()->role) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email Verified:</strong> 
                                    @if(Auth::user()->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Verified
                                        </span>
                                        <small class="text-muted d-block">
                                            {{ Auth::user()->email_verified_at->format('M d, Y \a\t g:i A') }}
                                        </small>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle"></i> Not Verified
                                        </span>
                                    @endif
                                </p>
                                <p><strong>Member Since:</strong> {{ Auth::user()->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        @if(!Auth::user()->email_verified_at)
                            <div class="alert alert-warning mt-3">
                                <h6><i class="bi bi-exclamation-triangle"></i> Email Verification Required</h6>
                                <p class="mb-2">Please verify your email address to access all features.</p>
                                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning">
                                        Resend Verification Email
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="mt-4">
                            <h6>Quick Actions</h6>
                            <div class="d-flex gap-2">
                                @switch(Auth::user()->role)
                                    @case('admin')
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">Admin Panel</a>
                                        @break
                                    @case('emp')
                                        <a href="{{ route('employee.dashboard') }}" class="btn btn-primary btn-sm">Employee Dashboard</a>
                                        @break
                                    @case('finance')
                                        <a href="{{ route('finance.dashboard') }}" class="btn btn-primary btn-sm">Finance Dashboard</a>
                                        @break
                                    @case('pm')
                                        <a href="{{ route('pm.dashboard') }}" class="btn btn-primary btn-sm">Project Manager Dashboard</a>
                                        @break
                                    @case('sc')
                                        <a href="{{ route('sc.dashboard') }}" class="btn btn-primary btn-sm">Site Coordinator Dashboard</a>
                                        @break
                                    @case('client')
                                        <a href="{{ route('client.dashboard') }}" class="btn btn-primary btn-sm">Client Dashboard</a>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>