<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Operator - Uji Petik</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/login.css'])
    <style>
        .guide-overlay {
            position: fixed;
            bottom: -100%;
            left: 0;
            right: 0;
            background: white;
            z-index: 9999;
            border-top-left-radius: 30px;
            border-top-right-radius: 30px;
            padding: 20px;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.2);
            transition: bottom 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .guide-overlay.active {
            bottom: 0;
        }
        .guide-handle {
            width: 40px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 2px;
            margin: 0 auto 15px;
        }
        #guide-map {
            height: 250px;
            width: 100%;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="glass-shape left"></div>
        <div class="glass-shape right"></div>

        <div class="login-card">
            <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo" class="logo">

            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Selamat Datang</h2>
                <p class="text-xs text-gray-500">Silakan masuk ke akun petugas anda</p>
                @if(session('error'))
                    <div class="mt-8 mb-4 py-2 px-4 bg-red-50 border border-red-200 rounded-lg inline-block animate-pulse">
                        <p class="text-red-600 !text-red-600 text-[10px] font-black uppercase tracking-tight flex items-center gap-1.5" style="color: #dc2626 !important;">
                            <iconify-icon icon="lucide:alert-circle" style="color: #dc2626 !important;"></iconify-icon>
                            {{ session('error') }}
                        </p>
                    </div>
                @endif
            </div>

            <form action="/cek_login" method="POST" id="loginForm">
                @csrf

                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <input type="text" class="input {{ session('error') ? 'input-error' : '' }}" id="username" name="username" placeholder="Username" required>
                <br><br>
                <input type="password" class="input {{ session('error') ? 'input-error' : '' }}" id="password" name="password" placeholder="Password" required>
                <br><br>

                <div class="form-helpers">
                    <a href="{{ route('password.request') }}" class="lupa-PASS">Lupa Kata Sandi</a>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>
        </div>
    </div>

    <div class="guide-overlay" id="guideOverlay">
        <div class="guide-handle"></div>
        <div class="flex justify-between items-center mb-4 px-2">
            <h3 class="text-sm font-black text-[#253D6B] uppercase tracking-wider">Pemandu Lokasi</h3>
            <button onclick="toggleGuide(false)" class="text-gray-400 hover:text-gray-600">
                <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
            </button>
        </div>
        <div id="guide-map"></div>
        <p id="distanceText" class="text-center text-sm font-bold text-red-600 mt-4"></p>
        <div class="mt-4 px-2">
            <button onclick="toggleGuide(false)" class="w-full py-3 bg-[#253D6B] text-white font-bold rounded-xl text-sm shadow-lg">
                SAYA MENGERTI
            </button>
        </div>
    </div>

    <script>
    let map, userMarker, targetMarker, targetCircle;
    const targetData = {
        lat: {{ session('target_lat') ?? 'null' }},
        lng: {{ session('target_lng') ?? 'null' }},
        radius: {{ session('target_radius') ?? 'null' }},
        name: "{{ session('nama_lokasi_target') ?? '' }}"
    };

    function toggleGuide(show) {
        const overlay = document.getElementById('guideOverlay');
        if (show) overlay.classList.add('active');
        else overlay.classList.remove('active');
    }

    function initGuideMap(userLat, userLng) {
        if (!targetData.lat) return;
        toggleGuide(true);
        if (!map) {
            map = L.map('guide-map').setView([userLat, userLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            targetMarker = L.marker([targetData.lat, targetData.lng]).addTo(map).bindPopup("<b>" + targetData.name + "</b>").openPopup();
            targetCircle = L.circle([targetData.lat, targetData.lng], {
                color: '#10b981', fillColor: '#10b981', fillOpacity: 0.15, radius: targetData.radius
            }).addTo(map);
            userMarker = L.circleMarker([userLat, userLng], {
                color: '#3b82f6', radius: 8, fillColor: '#3b82f6', fillOpacity: 1
            }).addTo(map);
        }
    }

    function updateDistance(userLat, userLng) {
        if (!targetData.lat) return;
        const from = L.latLng(userLat, userLng);
        const to = L.latLng(targetData.lat, targetData.lng);
        const distance = from.distanceTo(to);
        const distText = document.getElementById('distanceText');
        if (distance <= targetData.radius) {
            distText.innerHTML = "✅ Anda sudah berada di lokasi! Silakan Login kembali.";
            distText.classList.replace('text-red-600', 'text-green-600');
            setTimeout(() => toggleGuide(false), 5000);
        } else {
            distText.innerHTML = "📍 Jarak ke lokasi: " + Math.round(distance) + " meter lagi";
        }
        if (userMarker) {
            userMarker.setLatLng([userLat, userLng]);
            map.panTo([userLat, userLng]);
        }
    }

    if (navigator.geolocation) {
        const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };
        navigator.geolocation.watchPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                if (targetData.lat) {
                    initGuideMap(lat, lng);
                    updateDistance(lat, lng);
                }
            },
            function(error) { console.warn("GPS Error: ", error.message); },
            options
        );
    }
    </script>
    <script>
        window.suppressSessionAlerts = true; // Matikan modal otomatis khusus halaman ini
    </script>
    @include('template.shared_scripts')
</body>

</html>
