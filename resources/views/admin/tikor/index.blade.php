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

    var batasBali = [
        [-8.9000, 114.4000], 
        [-8.0000, 115.7000]  
    ];


    var map = L.map('map', {
        maxBounds: batasBali,        
        maxBoundsViscosity: 1.0,     
        minZoom: 10                  
    }).setView([-8.5414, 115.4057], 13); 


    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markerKantor; 

    map.locate({setView: false, maxZoom: 16});

    map.on('locationfound', function(e) {
        L.circleMarker(e.latlng, {
            radius: 8,
            fillColor: "#0078ff",
            color: "#ffffff",
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map).bindPopup("Lokasi Anda saat ini").openPopup();
    });

    map.on('locationerror', function(e) {
        alert("Tidak dapat mendeteksi lokasi Anda. Pastikan GPS aktif dan browser diizinkan mengakses lokasi.");
    });


    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        
        document.getElementById('latInput').value = lat;
        document.getElementById('lngInput').value = lng;
        document.getElementById('alamatInput').value = "Mencari alamat...";
        var url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                var alamatLengkap = data.display_name; 
                document.getElementById('alamatInput').value = alamatLengkap;
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
        defaultMarkGeocode: false, 
        placeholder: "Cari nama tempat / jalan di Bali..." 
    })
    .on('markgeocode', function(e) {
       
        var titikLokasi = e.geocode.center;
        var alamatLengkap = e.geocode.name; 

       
        map.setView(titikLokasi, 20);

        
        if (markerKantor) {
            markerKantor.setLatLng(titikLokasi).setPopupContent(alamatLengkap).openPopup();
        } else {
            markerKantor = L.marker(titikLokasi).addTo(map).bindPopup(alamatLengkap).openPopup();
        }

        
        document.getElementById('latInput').value = titikLokasi.lat;
        document.getElementById('lngInput').value = titikLokasi.lng;
        document.getElementById('alamatInput').value = alamatLengkap;
    })
    .addTo(map); 
</script>