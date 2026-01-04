<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Site Verification --}}
    <meta name="google-site-verification" content="xNGJHYuGpHJBuivESbON96TW4_RdbtDtBjbt3oiyyxI" />

    <title>{{ config('app.name', 'Tapeli') }} - Inventory Management System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">

    <style>
        body {
            background-color: #f0f2f5;
        }
        .btn-primary {
            background-color: #727cf5;
            border-color: #727cf5;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #626af0;
            border-color: #626af0;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .content-wrapper {
            max-width: 600px;
            padding: 2rem;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }
        .logo {
            height: 50px;
            margin-bottom: 1rem;
        }
        .button-group a {
            margin: 0 0.5rem;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="main-container">
        <div class="content-wrapper">
            <img src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="logo" class="logo mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Welcome to Tapeli
            </h1>
            <p class="text-gray-600 mb-6">
                Your modern and efficient Inventory Management System.
            </p>

            @if (Route::has('login'))
                <div class="button-group">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 btn-primary rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 btn-primary rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 btn-secondary rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </div>
</body>
</html>
