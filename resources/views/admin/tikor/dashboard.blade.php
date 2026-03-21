

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <!-- Pisahkan peta dengan tabel menggunakan sedikit jarak (margin) -->
<div style="margin-top: 30px;">
    <h3>Daftar Lokasi Kantor Terdaftar</h3>
    
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead style="background-color: #f3f4f6;">
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Alamat / Nama Tempat</th>
                <th style="width: 30%;">Titik Koordinat (Lat, Lng)</th>
                <th style="width: 15%;">Radius Absen</th>
                <th style="width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Cek apakah data lokasi dari Firebase tidak kosong -->
            @if(!empty($lokasiKantor) && is_array($lokasiKantor))
                
                @php $no = 1; @endphp
                
                <!-- Looping setiap titik lokasi yang ada di Firebase -->
                @foreach($lokasiKantor as $id_firebase => $lokasi)
                    <tr>
                        <td>{{ $no++ }}</td>
                        
                        <!-- Gunakan operator ?? untuk mencegah error jika field kosong -->
                        <td>{{ $lokasi['alamat'] ?? 'Nama jalan/alamat tidak tersedia' }}</td>
                        
                        <td>
                            {{ $lokasi['latitude'] ?? '-' }}, <br>
                            {{ $lokasi['longitude'] ?? '-' }}
                        </td>
                        
                        <td>{{ $lokasi['radius'] ?? '100' }} Meter</td>
                        
                        <td>
                            <!-- Kolom aksi disiapkan untuk fitur selanjutnya -->
                            <button style="background-color: #ff4d4d; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px;">Hapus</button>
                        </td>
                    </tr>
                @endforeach

            @else
                <!-- Tampilan jika database Firebase masih kosong -->
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Belum ada titik lokasi cabang yang terdaftar.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>


    <!-- Panggil CSS dan JS Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Wadah Peta -->
<h3>Titik Absen Saat Ini</h3>
<div id="mapIndex" style="height: 400px; width: 100%; border-radius: 8px;"></div>
<script>
    // 1. Ambil data dari Firebase (Bentuknya sekarang adalah Object/Kumpulan Data)
    var dataLokasi = @json($lokasiKantor);

    // 2. Siapkan peta dasar (Zoom out sedikit agar seluruh area Bali terlihat)
    var mapIndex = L.map('mapIndex').setView([-8.5414, 115.4057], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapIndex);

    // 3. Siapkan fitur "Fit Bounds" agar peta otomatis menyesuaikan zoom 
    // untuk memperlihatkan SEMUA titik yang ada
    var markersLayer = L.featureGroup().addTo(mapIndex);

    // 4. Cek apakah ada data di Firebase
    if (dataLokasi && typeof dataLokasi === 'object') {
        
        // Lakukan perulangan (Looping) untuk setiap titik koordinat yang ada di Firebase
        Object.values(dataLokasi).forEach(function(lokasi) {
            
            // Pastikan data memiliki latitude dan longitude
            if (lokasi.latitude && lokasi.longitude) {
                var lat = lokasi.latitude;
                var lng = lokasi.longitude;
                var radius = lokasi.radius || 100;
                var alamat = lokasi.alamat || "Cabang Kantor";

                // Buat Pin (Marker) untuk titik ini
                var marker = L.marker([lat, lng])
                    .bindPopup("<b>" + alamat + "</b><br>Radius: " + radius + " meter");
                
                // Masukkan Pin ke dalam grup penanda (markersLayer)
                markersLayer.addLayer(marker);

                // Gambar Lingkaran Radiusnya
                L.circle([lat, lng], {
                    color: 'blue',       
                    fillColor: '#30f',
                    fillOpacity: 0.2,
                    radius: radius
                }).addTo(mapIndex); // Masukkan langsung ke peta
            }
        });

        // 5. Fitur Profesional: Otomatis memfokuskan layar peta agar 
        // semua titik (3 titik Anda) masuk ke dalam layar tanpa terpotong!
        if (markersLayer.getLayers().length > 0) {
            mapIndex.fitBounds(markersLayer.getBounds(), { padding: [50, 50] });
        }

    } else {
        // Jika belum ada data sama sekali
        L.popup()
            .setLatLng([-8.5414, 115.4057])
            .setContent("Belum ada titik cabang yang disimpan.")
            .openOn(mapIndex);
    }
</script>
</body>
</html>