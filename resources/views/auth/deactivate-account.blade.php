@extends('app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deactivate Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-red-600">Deactivate Account</h2>
            <p class="text-gray-600 mt-2">⚠️ This action will deactivate your account. You can reactivate it later if needed.</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-2">What happens when you deactivate?</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Your account will be temporarily disabled</li>
                <li>• You'll be logged out from all devices</li>
                <li>• You can reactivate anytime with your credentials</li>
                <li>• Your data will be preserved</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('account.deactivate') }}">
            @csrf
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    placeholder="Enter your current password"
                >
            </div>

            <div class="mb-6">
                <label for="confirmation" class="block text-sm font-medium text-gray-700 mb-2">Type "DEACTIVATE" to confirm</label>
                <input 
                    type="text" 
                    id="confirmation" 
                    name="confirmation" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('confirmation') border-red-500 @enderror"
                    placeholder="Type DEACTIVATE"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200 font-medium"
                onclick="return confirm('Are you sure you want to deactivate your account?')"
            >
                Deactivate Account
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                Cancel & Go Back
            </a>
        </div>
    </div>
</body>
</html>
@endsection