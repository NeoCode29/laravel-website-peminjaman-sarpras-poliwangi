<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="text-center mt-3">
            <h1>Selamat Datang di Sistem Peminjaman Sarana Prasarana</h1>
            <p class="mb-3">Sistem ini menggunakan Laravel dengan modular architecture</p>
            
            <div class="card">
                <div class="card-header">
                    <h3>Fitur yang Tersedia</h3>
                </div>
                <div class="card-body">
                    <ul style="text-align: left; max-width: 300px; margin: 0 auto;">
                        <li>Laravel Framework v8.75</li>
                        <li>Laravel Sanctum untuk API Authentication</li>
                        <li>Laravel Modules untuk Modular Architecture</li>
                        <li>Spatie Laravel Permission untuk Role & Permission</li>
                        <li>Laravel Tinker untuk Development</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
