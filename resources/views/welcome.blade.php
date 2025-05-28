<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Welcome to Laravel Authentication</h4>
                    </div>
                    <div class="card-body text-center">
                        <p>Welcome to our Laravel authentication system!</p>
                        
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary me-2">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary">Register</a>
                        @else
                            <p>Hello, {{ Auth::user()->full_name }}!</p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">Logout</button>
                            </form>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>