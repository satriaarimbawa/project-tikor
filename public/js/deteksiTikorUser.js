// public/js/geofencing.js

function startGeofencing(checkUrl, csrfToken, loginUrl, interval = 60000) {
    console.log("Geofencing started...");

    function checkTime() {
        const now = new Date();
        const hours = now.getHours();
        
        // Cek jika jam sudah menunjukkan pukul 22:00 (10 Malam) atau lebih
        if (hours >= 23) {
            alert("Waktu operasional berakhir (Batas Pukul 22:00). Sesi Anda akan diakhiri.");
            window.location.href = "/logout";
            return true;
        }
        return false;
    }

    function performCheck() {
        // Cek waktu operasional sebelum cek lokasi
        if (checkTime()) return;

        if (!navigator.geolocation) {
            console.error("Geolocation is not supported by this browser.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const data = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };

                fetch(checkUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'logout') {
                        // Gunakan SweetAlert2 jika tersedia, fallback ke alert biasa
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: result.message.includes('Terima kasih') ? 'Selesai Bekerja' : 'Otomatis Logout',
                                text: result.message || "Anda berada di luar radius penugasan. Otomatis Logout.",
                                icon: result.message.includes('Terima kasih') ? 'success' : 'warning',
                                confirmButtonColor: '#253D6B',
                                confirmButtonText: 'Oke',
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = loginUrl;
                            });
                        } else {
                            alert(result.message || "Anda berada di luar radius penugasan. Otomatis Logout.");
                            window.location.href = loginUrl;
                        }
                    } else {
                        console.log("Location Check:", result.distance || "In Radius");
                    }
                })
                .catch(error => console.error("Geofencing Error:", error));
            },
            (error) => {
                console.warn("GPS Error:", error.message);
            },
            { enableHighAccuracy: true }
        );
    }

    // Jalankan segera saat load
    performCheck();
    
    // Set interval berkala
    setInterval(performCheck, interval);
}