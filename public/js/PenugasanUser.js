let selectedObjects = [];
let map;

document.addEventListener('DOMContentLoaded', function() {
    // Pusat peta di Klungkung agar pin langsung terlihat
    const klungkungCenter = [-8.5353, 115.4042];
    map = L.map('map').setView(klungkungCenter, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const lokasiTerdaftar = window.LokasiTerdaftar || {}; 

    // Loop semua lokasi dari Firebase untuk dijadikan pin
    Object.keys(lokasiTerdaftar).forEach(id => {
        const data = lokasiTerdaftar[id];
        
        let lat = data.latitude;
        let lng = data.longitude;

        // Fallback jika data masih menggunakan format koordinat string lama
        if (!lat && data.koordinat) {
            const parts = data.koordinat.split(',');
            lat = parseFloat(parts[0]);
            lng = parseFloat(parts[1]);
        }

        if (lat && lng) {
            const posisi = [lat, lng];
            const besarRadius = data.radius || 50; 
            const namaLokasi = data.nama_lokasi || data.alamat || 'Lokasi Terdaftar';

            // Tambahkan Marker Biru
            const marker = L.marker(posisi).addTo(map);
            marker.bindPopup(`<b>${namaLokasi}</b><br>Radius: ${besarRadius}m`);

            // Tambahkan Lingkaran Radius 50m
            L.circle(posisi, {
                color: '#3062f3',
                fillColor: '#3062f3',
                fillOpacity: 0.1,
                radius: besarRadius    
            }).addTo(map);

            // Jika pin diklik, otomatis pilih di dropdown
            marker.on('click', function() {
                const selectLokasi = document.getElementsByName('id_lokasi')[0];
                selectLokasi.value = id;
                document.getElementById('mapSearch').value = namaLokasi;
                map.setView(posisi, 16);
            });
        }
    });

    // Event saat dropdown lokasi berubah
    document.getElementsByName('id_lokasi')[0].addEventListener('change', function() {
        const id = this.value;
        if (lokasiTerdaftar[id]) {
            const d = lokasiTerdaftar[id];
            const lat = d.latitude || (d.koordinat ? parseFloat(d.koordinat.split(',')[0]) : 0);
            const lng = d.longitude || (d.koordinat ? parseFloat(d.koordinat.split(',')[1]) : 0);
            const namaLokasi = d.nama_lokasi || d.alamat || 'Lokasi';
            
            map.setView([lat, lng], 16);
            document.getElementById('mapSearch').value = namaLokasi;
        }
    });

    const fileInput = document.getElementById('fileSpt');
    if(fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : "Pilih file...";
            const textSpan = this.parentElement.querySelector('span');
            if (textSpan) {
                textSpan.innerText = fileName;
                textSpan.classList.add('text-navy-900');
            }
        });
    }
});

function searchLocation() {
    const query = document.getElementById('mapSearch').value;
    if (query) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const res = data[0];
                    map.setView([res.lat, res.lon], 16);
                } else {
                    alert("Lokasi tidak ditemukan!");
                }
            })
            .catch(err => console.error("Error mencari lokasi:", err));
    }
}

function toggleObjek(objek) {
    const index = selectedObjects.indexOf(objek);
    // Buat ID yang sama dengan di Blade: row-nama-objek-kecil
    const safeId = 'row-' + objek.toLowerCase().replace(/\s+/g, '-');
    const row = document.getElementById(safeId);
    if (!row) return;

    const statusText = row.querySelector('.status-text');
    const btn = row.querySelector('.action-btn');
    const icon = btn.querySelector('iconify-icon');
    const displayInput = document.getElementById('displayObjek');
    const hiddenInput = document.getElementById('hiddenObjekInput');

    if (index === -1) {
        selectedObjects.push(objek);
        statusText.innerText = 'Terpilih';
        statusText.classList.add('text-green-600', 'font-bold');
        icon.setAttribute('icon', 'lucide:minus-circle');
        btn.classList.replace('text-green-500', 'text-red-500');
    } else {
        selectedObjects.splice(index, 1);
        statusText.innerText = '-';
        statusText.classList.remove('text-green-600', 'font-bold');
        icon.setAttribute('icon', 'lucide:plus-circle');
        btn.classList.replace('text-red-500', 'text-green-500');
    }

    const formattedText = selectedObjects.map(word => word.toUpperCase()).join(', ');
    displayInput.value = formattedText;
    hiddenInput.value = selectedObjects.join(', ');
}

function syncFromDropdown(select) {
    const val = select.value;
    if (val) {
        if (!selectedObjects.includes(val)) {
            toggleObjek(val);
        }
        select.value = ""; 
    }
}
