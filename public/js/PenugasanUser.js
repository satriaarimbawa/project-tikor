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
                const selectLokasi = document.getElementById('select-lokasi');
                if (window.tsLokasi) {
                    window.tsLokasi.addItem(id);
                } else if (selectLokasi) {
                    selectLokasi.value = id;
                }
                document.getElementById('mapSearch').value = namaLokasi;
                map.setView(posisi, 16);
            });
        }
    });

    // Event saat dropdown lokasi berubah
    const selectLokasiEl = document.getElementById('select-lokasi');
    if (selectLokasiEl) {
        selectLokasiEl.addEventListener('change', function() {
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
    }

    const fileInput = document.getElementById('fileSpt');
    const uploadBox = document.getElementById('uploadBox');
    const fileError = document.getElementById('fileError');
    const fileHint = document.getElementById('fileHint');
    const uploadIcon = document.getElementById('uploadIcon');
    const removeFileBtn = document.getElementById('removeFileBtn');

    if(fileInput && uploadBox) {
        
        // Klik pada box memicu input file
        uploadBox.addEventListener('click', (e) => {
            // Jangan buka file explorer jika yang diklik adalah tombol hapus
            if (removeFileBtn && removeFileBtn.contains(e.target)) {
                return;
            }
            fileInput.click();
        });

        // Tombol Hapus File
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Cegah uploadBox terpicu
                fileInput.value = ""; // Reset input
                resetUploadBox();
            });
        }

        function resetUploadBox() {
            uploadBox.className = "border-2 border-dashed border-gray-300 rounded-xl p-10 flex flex-col justify-center items-center bg-gray-50 transition-all cursor-pointer min-h-[150px] gap-3 relative";
            const textSpan = uploadBox.querySelector('span');
            if (textSpan) {
                textSpan.innerText = "Tarik dan lepas file SPT di sini atau klik untuk memilih";
                textSpan.className = "text-gray-400 text-sm font-medium text-center";
            }
            if (uploadIcon) {
                uploadIcon.className = "text-gray-400 text-5xl";
                uploadIcon.setAttribute('icon', 'lucide:upload-cloud');
            }
            if (fileError) fileError.classList.add('hidden');
            if (fileHint) fileHint.classList.remove('hidden');
            if (removeFileBtn) removeFileBtn.classList.add('hidden');
        }

        // Mencegah behavior default browser secara global (penting agar file tidak terbuka di tab baru)
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            document.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
            
            uploadBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        // Efek visual saat drag masuk area box
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadBox.addEventListener(eventName, () => {
                uploadBox.classList.remove('border-gray-300', 'bg-gray-50');
                uploadBox.classList.add('border-blue-500', 'bg-blue-100', 'ring-4', 'ring-blue-50');
            }, false);
        });

        // Efek visual saat drag keluar area box
        ['dragleave', 'drop'].forEach(eventName => {
            uploadBox.addEventListener(eventName, () => {
                uploadBox.classList.remove('border-blue-500', 'bg-blue-100', 'ring-4', 'ring-blue-50');
                if (!fileInput.files.length) {
                    uploadBox.classList.add('border-gray-300', 'bg-gray-50');
                }
            }, false);
        });

        // Menangani file yang dilepaskan (drop)
        uploadBox.addEventListener('drop', (e) => {
            const droppedFiles = e.dataTransfer.files;
            if (droppedFiles.length > 0) {
                // Masukkan file ke input asli menggunakan DataTransfer API agar tersinkronisasi sempurna
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(droppedFiles[0]);
                fileInput.files = dataTransfer.files;
                
                // Trigger event change manual agar handleFile berjalan
                fileInput.dispatchEvent(new Event('change'));
            }
        }, false);

        // Menangani pemilihan file (baik lewat klik maupun drop)
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFile(this.files[0]);
            }
        });

        function handleFile(file) {
            const textSpan = uploadBox.querySelector('span');
            if (!textSpan) return;
            
            const fileName = file.name;
            const fileSize = file.size / 1024 / 1024; // Convert ke MB
            const fileExt = fileName.split('.').pop().toLowerCase();
            const allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
            
            let errorMsg = "";
            if (!allowedExts.includes(fileExt)) {
                errorMsg = "Format file tidak didukung! Gunakan PDF, JPG, atau PNG.";
            } else if (fileSize > 2) {
                errorMsg = "Ukuran file terlalu besar! Maksimal 2MB.";
            }

            if (errorMsg) {
                // Tampilan Gagal (Merah)
                uploadBox.className = "border-2 border-dashed border-red-500 rounded-xl p-10 flex flex-col justify-center items-center bg-red-50 transition-all cursor-pointer min-h-[150px] gap-3 relative";
                textSpan.innerText = fileName;
                textSpan.className = "text-red-600 text-sm font-bold text-center";
                if (uploadIcon) {
                    uploadIcon.className = "text-red-500 text-5xl";
                    uploadIcon.setAttribute('icon', 'lucide:alert-triangle');
                }
                
                if (fileError) {
                    fileError.innerText = "❌ " + errorMsg;
                    fileError.classList.remove('hidden');
                }
                if (fileHint) fileHint.classList.add('hidden');
                if (removeFileBtn) removeFileBtn.classList.remove('hidden');
                fileInput.value = ""; // Reset input jika salah
            } else {
                // Tampilan Berhasil (Hijau)
                uploadBox.className = "border-2 border-dashed border-green-500 rounded-xl p-10 flex flex-col justify-center items-center bg-green-50 transition-all cursor-pointer min-h-[150px] gap-3 relative";
                textSpan.innerText = "File Siap di-Upload: " + fileName;
                textSpan.className = "text-green-700 text-sm font-bold text-center";
                if (uploadIcon) {
                    uploadIcon.className = "text-green-600 text-5xl";
                    uploadIcon.setAttribute('icon', 'lucide:file-check');
                }

                if (fileError) fileError.classList.add('hidden');
                if (fileHint) fileHint.classList.remove('hidden');
                if (removeFileBtn) removeFileBtn.classList.remove('hidden');
            }
        }
    }

    // --- FITUR GPS (LOCATE ME) ---
    const locateBtn = document.getElementById('locateBtn');
    let userMarker;

    if (locateBtn) {
        locateBtn.addEventListener('click', function() {
            const iconElement = this.querySelector('iconify-icon');
            iconElement.setAttribute('icon', 'lucide:loader-2');
            iconElement.classList.add('animate-spin');

            map.locate({
                setView: true,
                maxZoom: 16,
                enableHighAccuracy: true
            });
        });
    }

    map.on('locationfound', function(e) {
        const locateBtn = document.getElementById('locateBtn');
        if (locateBtn) {
            const iconElement = locateBtn.querySelector('iconify-icon');
            iconElement.setAttribute('icon', 'lucide:locate-fixed');
            iconElement.classList.remove('animate-spin');
        }

        // Hapus marker lama jika ada
        if (userMarker) {
            map.removeLayer(userMarker);
        }

        // Tambahkan marker posisi user (Warna Merah untuk membedakan dengan lokasi tugas)
        userMarker = L.marker(e.latlng, {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);
        
        userMarker.bindPopup("<b>Posisi Anda Sekarang</b>").openPopup();
    });

    map.on('locationerror', function(e) {
        const locateBtn = document.getElementById('locateBtn');
        if (locateBtn) {
            const iconElement = locateBtn.querySelector('iconify-icon');
            iconElement.setAttribute('icon', 'lucide:locate-fixed');
            iconElement.classList.remove('animate-spin');
        }
        alert("Gagal mendapatkan lokasi GPS: " + e.message);
    });
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
            .catch(err => {});
    }
}

function toggleObjek(objek, providedId = null) {
    const index = selectedObjects.indexOf(objek);
    const safeId = providedId || ('row-' + objek.toLowerCase().replace(/\s+/g, '-'));
    const targetId = providedId ? ('row-' + providedId) : safeId;
    
    const row = document.getElementById(targetId);
    if (!row) {
        // Tetap proses penambahan ke input jika baris tidak ditemukan (agar data tetap tersimpan)
    }

    const displayInput = document.getElementById('displayObjek');
    const hiddenInput = document.getElementById('hiddenObjekInput');

    if (index === -1) {
        selectedObjects.push(objek);
        if (row) {
            const statusText = row.querySelector('.status-text');
            const btn = row.querySelector('.action-btn');
            const icon = btn.querySelector('iconify-icon');
            statusText.innerText = 'Terpilih';
            statusText.classList.add('text-green-600', 'font-bold');
            icon.setAttribute('icon', 'lucide:minus-circle');
            btn.classList.replace('text-green-500', 'text-red-500');
        }
    } else {
        selectedObjects.splice(index, 1);
        if (row) {
            const statusText = row.querySelector('.status-text');
            const btn = row.querySelector('.action-btn');
            const icon = btn.querySelector('iconify-icon');
            statusText.innerText = '-';
            statusText.classList.remove('text-green-600', 'font-bold');
            icon.setAttribute('icon', 'lucide:plus-circle');
            btn.classList.replace('text-red-500', 'text-green-500');
        }
    }

    const formattedText = selectedObjects.map(word => word.toUpperCase()).join(', ');
    displayInput.value = formattedText;
    hiddenInput.value = selectedObjects.join(', ');
}

function syncFromDropdown(select) {
    const safeId = select.value;
    const option = select.options[select.selectedIndex];
    const namaAsli = option.getAttribute('data-nama');
    
    if (safeId && namaAsli) {
        if (!selectedObjects.includes(namaAsli)) {
            toggleObjek(namaAsli, safeId);
        }
        select.value = ""; 
    }
}
