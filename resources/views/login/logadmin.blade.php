<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Dishub Klungkung</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="{{ asset('css/loginadmin.css') }}">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ time() }}">
    <meta name="theme-color" content="#253D6B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Uji Petik">
    <link rel="apple-touch-icon" href="{{ asset('assets/logo_dishub.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700;800&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="login-container">

        <div class="left-section">
            <div class="header-logos-login">
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" alt="Logo">
                <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo">
            </div>

            <div class="hero-content-wrapper">
                <div class="hero-text-login">
                    <h1 class="playfair">SISTEM DIGITALISASI UJI<br>PETIK DISHUB</h1>
                </div>
            </div>
        </div>

        <div class="right-section">
            <div class="form-wrapper">
                <div class="header-form text-center">
                    <h2>Selamat Datang</h2>
                    <p class="welcome-p">Silakan masuk ke akun petugas anda</p>
                </div>

                <form action="/cek_login" method="POST">
                    @csrf

                    <div class="input-group">
                        <label>Username</label>
                        <div class="input-container {{ session('error') ? 'error' : '' }}">
                            <i class="fas fa-user icon"></i>
                            <input type="text" name="username" placeholder="Masukan Username" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-container {{ session('error') ? 'error' : '' }}">
                            <i class="fas fa-lock icon"></i>
                            <input type="password" name="password" id="passwordField" placeholder="Masukan Password"
                                required>
                            <i class="fas fa-eye eye-icon" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <div class="form-options">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="lupa-sandi">Lupa Kata Sandi?</a>
                    </div>

                    <button type="submit" class="btn-login">Login</button>
                </form>
            </div>

            <div class="footer-login text-center">
                <div class="footer-info">
                    <i class="fas fa-info-circle"></i> Tentang Kami
                </div>
                <div class="copyright">
                    &copy; Dinas Perhubungan Kab. Klungkung 2026
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const x = document.getElementById("passwordField");
        x.type = x.type === "password" ? "text" : "password";
    }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    // Registered
                }).catch(err => {
                    // Failed
                });
            });
        }
    </script>
    @include('template.shared_scripts')
</body>

</html>