<!-- 1. Panggil CSS dan JS bawaan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- CSS & JS bawaan Leaflet (Sudah ada sebelumnya) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- TAMBAHKAN INI: CSS & JS untuk Plugin Pencarian (Geocoder) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<!-- 2. Siapkan wadah (container) untuk Peta -->
<div id="map" style="height: 400px; width: 100%; margin-bottom: 20px;"></div>


<!-- 3. Form untuk menyimpan data ke Firebase -->
<form action="/update-lokasi-kantor" method="POST">
    @csrf
    
    <!-- Anda bisa menyembunyikan input ini (type="hidden") nanti, 
    tapi pakai type="text" dulu agar terlihat perubahannya -->
    <label>Latitude:</label>
    <input type="text" name="latitude" id="latInput" required readonly>
    <br><br>

    <label>Longitude:</label>
    <input type="text" name="longitude" id="lngInput" required readonly>
    <br><br>

    <label>Radius Absen (meter):</label>
    <input type="number" name="radius" value="100" required>
    <br><br>

    <label>Alamat Lokasi:</label>
    <!-- Kita pakai textarea agar alamat yang panjang bisa terlihat semua -->
    <textarea name="alamat" id="alamatInput" rows="3" style="width: 100%;" readonly placeholder="Klik peta untuk memuat alamat..."></textarea>
    <br><br>

    <button type="submit">Simpan Titik Lokasi</button>
</form>

<script>
    // 1. Tentukan batas koordinat (Bounding Box) untuk wilayah Pulau Bali
    // Format: [[Batas Selatan, Batas Barat], [Batas Utara, Batas Timur]]
    var batasBali = [
        [-8.9000, 114.4000], // Barat Daya (Sekitar Gilimanuk / Jembrana)
        [-8.0000, 115.7000]  // Timur Laut (Sekitar Karangasem)
    ];

    // 2. Inisialisasi Peta
    var map = L.map('map', {
        maxBounds: batasBali,        // Mengunci peta agar tidak bisa digeser keluar Bali
        maxBoundsViscosity: 1.0,     // Efek memantul jika ditarik keluar batas
        minZoom: 10                  // Batas maksimal zoom out agar peta tidak terlalu kecil
    }).setView([-8.5414, 115.4057], 13); // Set titik tengah awal langsung ke Klungkung, Bali

    // 3. Muat gambar peta dari OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markerKantor; // Variabel untuk pin lokasi kantor yang akan disimpan

    // 4. Fitur Menampilkan Lokasi Anda Saat Ini (Admin)
    // Leaflet akan meminta izin lokasi ke browser secara otomatis
    map.locate({setView: false, maxZoom: 16});

    // Jika lokasi berhasil ditemukan
    map.on('locationfound', function(e) {
        // Tambahkan lingkaran biru untuk menandai lokasi admin saat ini
        L.circleMarker(e.latlng, {
            radius: 8,
            fillColor: "#0078ff",
            color: "#ffffff",
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map).bindPopup("Lokasi Anda saat ini").openPopup();
    });

    // Jika akses lokasi ditolak oleh browser admin
    map.on('locationerror', function(e) {
        alert("Tidak dapat mendeteksi lokasi Anda. Pastikan GPS aktif dan browser diizinkan mengakses lokasi.");
    });

// 5. Fitur Klik untuk Memilih Lokasi dan Mencari Alamat (Reverse Geocoding)
    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Masukkan koordinat ke input HTML
        document.getElementById('latInput').value = lat;
        document.getElementById('lngInput').value = lng;
        
        // Tampilkan teks loading sementara
        document.getElementById('alamatInput').value = "Mencari alamat...";

        // Panggil API Nominatim OpenStreetMap
        var url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Ambil alamat lengkap dari hasil API
                var alamatLengkap = data.display_name; 
                
                // Masukkan teks alamat ke textarea
                document.getElementById('alamatInput').value = alamatLengkap;

                // Pindahkan marker dan update teks popup-nya dengan alamat
                if (markerKantor) {
                    markerKantor.setLatLng(e.latlng).setPopupContent(alamatLengkap).openPopup();
                } else {
                    markerKantor = L.marker(e.latlng).addTo(map).bindPopup(alamatLengkap).openPopup();
                }
            })
            .catch(error => {
                console.error('Gagal mendapatkan alamat:', error);
                document.getElementById('alamatInput').value = "Gagal memuat alamat. Pastikan ada koneksi internet.";
            });
    });

    // === KODE FITUR PENCARIAN (GEOCODER) ===
    
    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false, // Matikan bawaan agar kita bisa mengatur marker sendiri
        placeholder: "Cari nama tempat / jalan di Bali..." // Teks petunjuk di kotak pencarian
    })
    .on('markgeocode', function(e) {
        // e.geocode menyimpan data hasil pencarian
        var titikLokasi = e.geocode.center; // Mengambil latitude & longitude
        var alamatLengkap = e.geocode.name; // Mengambil nama tempat/alamat

        // 1. Zoom dan pindahkan layar peta ke lokasi yang dicari
        map.setView(titikLokasi, 16); // Angka 16 adalah tingkat zoom

        // 2. Pindahkan pin merah (marker) ke lokasi tersebut
        if (markerKantor) {
            markerKantor.setLatLng(titikLokasi).setPopupContent(alamatLengkap).openPopup();
        } else {
            markerKantor = L.marker(titikLokasi).addTo(map).bindPopup(alamatLengkap).openPopup();
        }

        // 3. Masukkan data ke kotak input Form HTML agar siap disimpan ke Firebase
        document.getElementById('latInput').value = titikLokasi.lat;
        document.getElementById('lngInput').value = titikLokasi.lng;
        document.getElementById('alamatInput').value = alamatLengkap;
    })
    .addTo(map); // Pasang kotak pencariannya ke dalam peta
</script>