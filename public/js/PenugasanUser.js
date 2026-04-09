let selectedObjects = [];
let map;

document.addEventListener('DOMContentLoaded', function() {

    map = L.map('map').setView([-8.65, 115.21], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    
    const lokasiTerdaftar = window.LokasiTerdaftar || {}; 

 Object.keys(lokasiTerdaftar).forEach(id => {
        const data = lokasiTerdaftar[id];
        
        if (data.latitude && data.longitude) {
            const posisi = [data.latitude, data.longitude];
            const besarRadius = data.radius || 50; 

            // --- TAMBAHKAN PAKU (MARKER) ---
            const marker = L.marker(posisi).addTo(map);
            marker.bindPopup(`<b>${data.alamat}</b><br>Radius: ${besarRadius} meter`);

            const circle = L.circle(posisi, {
                color: 'blue',          
                fillColor: '#3062f3',   
                fillOpacity: 0.2,       
                radius: besarRadius    
            }).addTo(map);

            marker.on('click', function() {
                const selectLokasi = document.getElementsByName('id_lokasi')[0];
                selectLokasi.value = id;
                document.getElementById('mapSearch').value = data.alamat;
                map.setView(posisi, 15);
            });
        }
    });

    document.getElementsByName('id_lokasi')[0].addEventListener('change', function() {
        const id = this.value;
        if (lokasiTerdaftar[id]) {
            const d = lokasiTerdaftar[id];
            map.setView([d.latitude, d.longitude], 15);
            document.getElementById('mapSearch').value = d.alamat;
        }
    });

    const fileInput = document.getElementById('fileSpt');
    if(fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : "Pilih file...";
            const textSpan = this.parentElement.querySelector('span');
            textSpan.innerText = fileName;
            textSpan.classList.replace('text-gray-400', 'text-navy-900');
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
                    map.setView([res.lat, res.lon], 15);
                } else {
                    alert("Lokasi tidak ditemukan!");
                }
            })
            .catch(err => console.error("Error mencari lokasi:", err));
    }
}

function toggleObjek(objek) {
    const index = selectedObjects.indexOf(objek);
    const row = document.getElementById('row-' + objek);
    if (!row) return;

    const statusText = row.querySelector('.status-text');
    const btn = row.querySelector('.action-btn');
    const icon = btn.querySelector('iconify-icon');
    const displayInput = document.getElementById('displayObjek');
    const hiddenInput = document.getElementById('hiddenObjekInput');

    if (index === -1) {
        // Tambahkan ke daftar terpilih
        selectedObjects.push(objek);
        statusText.innerText = 'Terpilih';
        statusText.classList.add('text-green-600', 'font-bold');
        icon.setAttribute('icon', 'lucide:minus-circle');
        btn.classList.replace('text-green-500', 'text-red-500');
    } else {
        // Hapus dari daftar
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

function toggleSubMenu() {
    const subMenu = document.getElementById('subMenuLaporan');
    const icon = document.getElementById('chevron-icon');
    subMenu.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
