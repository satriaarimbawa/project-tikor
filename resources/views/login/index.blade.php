<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DASHBOARD || LOGIN</title>
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

    <a href="/dashboard-admin">mau ke admin ?</a>
    <hr>
    <a href="/dashboard-operator">mau ke operator ?</a>

    <form action="/cek_login" method="post">
        @csrf

        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit">Login</button>
    </form>




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

    // Tambahkan fungsi ini agar user tahu jika GPS gagal
function getLocation() {
const options = {
        enableHighAccuracy: true, // WAJIB: Memaksa menggunakan GPS, bukan IP
        timeout: 10000,           // Menunggu maksimal 10 detik
        maximumAge: 0             // Jangan gunakan lokasi yang tersimpan di cache
    };

    navigator.geolocation.getCurrentPosition(
        (position) => {
            document.getElementById('lat').value = position.coords.latitude;
            document.getElementById('lon').value = position.coords.longitude;
            console.log("GPS Terkunci: " + position.coords.latitude);
        },
        (error) => {
            alert("Gagal ambil GPS: " + error.message);
        }, 
        options
    );
}

    // Jalankan saat halaman dibuka
    window.onload = getLocation;
    </script>
</body>

</html>