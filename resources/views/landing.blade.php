<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Petik - Dishub Klungkung</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="hero-section">
       <header>
            <div class="logo-group">
                <img src="{{ asset('assets/logo_klungkung.png') }}" alt="Logo Klungkung">
                <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo Dishub">
            </div>
            <nav>
                <a href="#">Beranda</a>
                <a href="#">Layanan</a>
                <a href="#">Tentang Kami</a>
                <a href="#">Kontak</a>
            </nav>
        </header>

        <main class="main-content">
            <div class="hero-text">
            <h1>DIGITALISASI UJI<br>PETIK DISHUB</h1>
                <div class="button-group">
                    <a href="/login-admin" class="btn-dashboard">Login Admin</a>
                    <a href="/login-operator" class="btn-dashboard">Login Operator</a>
                </div>
            </div>
        </main>

        <footer>
            <div class="footer-container">
                <p>Dinas Perhubungan Kab. Klungkung 2026</p>
            </div>
        </footer>
    </div>
</body>
</html>