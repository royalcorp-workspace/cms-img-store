<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>IMG Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .brand-icon {
            width: 52px; height: 52px; background: #7267ef;
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 1rem;
        }
        .brand-icon i { color: #fff; font-size: 24px; }
    </style>
</head>
<body>
    <div class="welcome-card">
        <div class="brand-icon">
            <i class="bi bi-lightning-fill"></i>
        </div>
        <h1 class="mb-4">IMG Admin</h1>
        <p class="text-muted mb-4">Selamat datang di Dashboard Admin</p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ route('login') }}" class="btn btn-primary px-4">Masuk</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary px-4">Dashboard</a>
        </div>
    </div>
</body>
</html>