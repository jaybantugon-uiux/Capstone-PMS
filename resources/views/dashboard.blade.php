<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <h5>Welcome, {{ Auth::user()->full_name }}!</h5>
                        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                        <p><strong>Username:</strong> {{ Auth::user()->username }}</p>
                        <p><strong>Email Verified:</strong> 
                            @if(Auth::user()->email_verified_at)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-warning">No</span>
                            @endif
                        </p>
                        <p><strong>Member Since:</strong> {{ Auth::user()->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
