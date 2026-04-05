// public/js/geofencing.js

class GeofenceManager {
    constructor(config) {
        this.targetLat = config.targetLat;
        this.targetLng = config.targetLng;
        this.maxRadius = config.maxRadius;
        this.logoutUrl = config.logoutUrl;
        this.csrfToken = config.csrfToken;
        this.redirectUrl = config.redirectUrl;
        this.watchId = null;
    }

    init() {
        if (navigator.geolocation) {
            this.watchId = navigator.geolocation.watchPosition(
                (pos) => this.checkLocation(pos),
                (err) => this.handleError(err),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            console.error("Geolocation tidak didukung oleh browser ini.");
        }
    }

    checkLocation(position) {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;
        const distance = this.calculateDistance(userLat, userLng, this.targetLat, this.targetLng);

        console.log(`Jarak saat ini: ${Math.round(distance)} meter dari target.`);

        if (distance > this.maxRadius) {
            this.doAutoLogout();
        }
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Meter
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    }

    async doAutoLogout() {
        navigator.geolocation.clearWatch(this.watchId);
        
        try {
            const response = await fetch(this.logoutUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                alert("Anda keluar dari radius lokasi penugasan! Sistem otomatis logout.");
                window.location.href = this.redirectUrl;
            }
        } catch (error) {
            console.error("Gagal melakukan auto-logout:", error);
        }
    }

    handleError(err) {
        console.warn(`ERROR(${err.code}): ${err.message}`);
    }
}   