<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DASHBOARD || LOGIN</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    @vite(['resources/css/app.css', 'resources/css/login.css'])
</head>

<body>

    @if(session('error'))
    <div style="color: white; background-color: #ff4d4d; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('error') }}
    </div>
    @endif

    @if(session('success'))
    <div style="color: white; background-color: #28a745; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('success') }}
    </div>
    @endif

    <div class="container">
        <div class="glass-shape left"></div>
        <div class="glass-shape right"></div>

        <div class="login-card">
            <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo" class="logo">

            <form action="/cek_login" method="POST">
                @csrf

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <input type="text" class="input" id="username" name="username" placeholder="Username" required>
                <br><br>
                <input type="password" class="input" id="password" name="password" placeholder="Password" required>
                <br><br>

                <div class="form-helpers">
                    <label class="remember-me">
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="lupa-PASS">Lupa Kata Sandi</a>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>
        </div>
    </div>

    <script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            },
            function(error) {
                console.log("Akses lokasi ditolak atau gagal: ", error);
            }
        );
    } else {
        alert("Browser Anda tidak mendukung fitur lokasi.");
    }

    function getLocation() {
        const options = {
            enableHighAccuracy: true,
            timeout: 1000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = document.getElementById('latitude');
                const lon = document.getElementById('longitude');
                if(lat) lat.value = position.coords.latitude;
                if(lon) lon.value = position.coords.longitude;
            },
            (error) => {
                console.log("Gagal ambil GPS: " + error.message);
            },
            options
        );
    }

    window.onload = getLocation;
    </script>
</body>

</html>
