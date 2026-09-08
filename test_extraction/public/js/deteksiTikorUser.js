// public/js/geofencing.js

function startGeofencing(checkUrl, csrfToken, loginUrl, interval = 60000) {
    console.log("Geofencing started...");

    function performCheck() {
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
                        alert(result.message || "Anda berada di luar radius penugasan. Otomatis Logout.");
                        window.location.href = loginUrl;
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