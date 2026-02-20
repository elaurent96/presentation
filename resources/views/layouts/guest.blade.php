<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Presentation Studio') }}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            @media (max-width: 480px) {
                .card { width: 90% !important; padding: 20px !important; }
                .card h4 { font-size: 18px !important; }
                .form-control { font-size: 14px; }
                .btn { font-size: 14px; }
            }
        </style>
    </head>
    <body style="background: #1a1a1a; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="width: 400px; background: #2a2a2a; border: none; border-radius: 12px; padding: 30px;">
            <div class="text-center mb-4">
                <div class="app-icon mx-auto mb-3" style="width: 60px; height: 60px; background: #0d6efd; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h4 style="color: white; margin-bottom: 5px;">Presentation Studio</h4>
<?php $title = request()->routeIs('register') ? 'Inscription' : 'Connexion'; ?>
                <p style="color: #a0a0a0; margin: 0;">{{ $title }}</p>
            </div>
            {{ $slot }}
        </div>
    </body>
</html>
