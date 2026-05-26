<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Dishub Klungkung</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="{{ asset('css/loginadmin.css') }}">

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
                    @if(session('error'))
                        <div class="mt-10 mb-6 py-3 px-6 bg-red-50 border border-red-200 rounded-xl inline-block mx-auto animate-pulse">
                            <p class="text-red-600 !text-red-600 text-[11px] font-black uppercase tracking-wider flex items-center gap-2" style="color: #dc2626 !important;">
                                <i class="fas fa-exclamation-circle" style="color: #dc2626 !important;"></i>
                                {{ session('error') }}
                            </p>
                        </div>
                    @endif
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
        window.suppressSessionAlerts = true; // Matikan modal otomatis khusus halaman ini
    </script>
    @include('template.shared_scripts')
</body>

</html>