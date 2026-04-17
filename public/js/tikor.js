document.addEventListener('DOMContentLoaded', function() {
    const defaultCoord = [-8.5353, 115.4042]; // Pusat Klungkung
    const map = L.map('map').setView(defaultCoord, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // 1. Ambil data lokasi terdaftar dari global variable
    const lokasiTerdaftar = window.daftarLokasi || {};
    const inputKoordinat = document.querySelector('input[name="koordinat"]');
    const inputNama = document.querySelector('input[name="nama_lokasi"]');
    const inputTarget = document.querySelector('input[name="target_harian"]');

    // 2. Render Pin untuk Lokasi yang sudah ada
    Object.keys(lokasiTerdaftar).forEach(id => {
        const d = lokasiTerdaftar[id];
        let lat = d.latitude;
        let lng = d.longitude;

        // Fallback untuk koordinat string lama
        if (!lat && d.koordinat) {
            const parts = d.koordinat.split(',');
            lat = parseFloat(parts[0]);
            lng = parseFloat(parts[1]);
        }

        if (lat && lng) {
            const markerOld = L.marker([lat, lng]).addTo(map);
            markerOld.bindPopup(`<b>${d.nama_lokasi}</b><br>Target: Rp. ${parseInt(d.target_harian).toLocaleString()}`);
            
            // Tambahkan circle radius 50m
            L.circle([lat, lng], {
                color: '#3062f3',
                fillColor: '#3062f3',
                fillOpacity: 0.1,
                radius: 50
            }).addTo(map);

            // Jika diklik, isi form (memudahkan update)
            markerOld.on('click', function() {
                inputNama.value = d.nama_lokasi;
                inputKoordinat.value = `${lat}, ${lng}`;
                inputTarget.value = d.target_harian;
                map.setView([lat, lng], 16);
            });
        }
    });

    // 3. Marker Draggable untuk Penentuan Lokasi Baru
    let markerNew = L.marker(defaultCoord, {
        draggable: true,
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);

    markerNew.bindPopup("Geser saya ke lokasi baru").openPopup();

    // Update input saat marker digeser
    markerNew.on('dragend', function (e) {
        const position = markerNew.getLatLng();
        inputKoordinat.value = `${position.lat}, ${position.lng}`;
    });

    // Update marker saat peta diklik
    map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        markerNew.setLatLng([lat, lng]);
        inputKoordinat.value = `${lat}, ${lng}`;
    });

    // Fitur GPS (Locate Me)
    const locateBtn = document.querySelector('[icon="lucide:locate-fixed"]');
    if (locateBtn) {
        locateBtn.parentElement.addEventListener('click', function() {
            map.locate({setView: true, maxZoom: 16});
        });
    }

    map.on('locationfound', function(e) {
        markerNew.setLatLng(e.latlng);
        inputKoordinat.value = `${e.latlng.lat}, ${e.latlng.lng}`;
    });

    // --- FITUR LIVE SEARCH TABEL ---
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                // Kolom Nama Lokasi ada di td kedua (index 1)
                const namaLokasiCell = row.querySelectorAll('td')[1];
                if (namaLokasiCell) {
                    const textValue = namaLokasiCell.textContent || namaLokasiCell.innerText;
                    if (textValue.toLowerCase().indexOf(searchTerm) > -1) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                }
            });
        });
    }
});
