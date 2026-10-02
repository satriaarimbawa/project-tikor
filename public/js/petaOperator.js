/**
 * Peta Posisi Operator (khusus IT Support)
 *
 * Data posisi diambil dari route JSON milik server sendiri, bukan dari
 * Firebase. Jangan tambah listener Firebase di file ini: koordinat GPS
 * operator tidak boleh keluar ke browser yang tidak lolos middleware
 * it_support.
 *
 * Halaman ini read-only. Tidak ada tombol yang menulis apa pun.
 */
(function () {
    'use strict';

    var AWAL = window.PETA_AWAL || {};

    // Titik tengah default: Kabupaten Klungkung, Bali.
    var DEFAULT_PUSAT = [-8.55, 115.42];

    var petakan = null;
    var grupPosUji = null;
    var grupOperator = null;

    var penandaOperator = {};
    var lingkaranRadius = {};

    var stateOperator = [];
    var stateLokasi = [];

    var indeksUji = {};

    // Umur denyut terakhir yang sudah pernah dilihat per operator, dipakai
    // untuk mendeteksi denyut baru tanpa perlu menambah field baru di API.
    var umurTerlihat = {};
    var denyutMuncul = {};

    // -----------------------------------------------------------------
    // Util
    // -----------------------------------------------------------------

    function $(id) {
        return document.getElementById(id);
    }

    function bersihkan(nilai) {
        // Selalu jaga agar null tidak berubah jadi "null" atau "NaN"
        // di layar: nilai float dari PHP hanya masuk kalau bisa dipakai.
        if (nilai === null || nilai === undefined) {
            return '-';
        }
        var n = Number(nilai);
        if (!isFinite(n)) {
            return '-';
        }
        return (Math.round(n * 10) / 10).toString();
    }

    function meter(nilai) {
        if (nilai === null || nilai === undefined) {
            return '-';
        }
        var n = Number(nilai);
        if (!isFinite(n)) {
            return '-';
        }
        if (n >= 1000) {
            return (Math.round(n / 10) / 100).toString() + ' km';
        }
        return Math.round(n) + ' m';
    }

    function umurRingkas(detik) {
        if (detik === null || detik === undefined) {
            return '-';
        }
        var n = Number(detik);
        if (!isFinite(n)) {
            return '-';
        }
        if (n < 60) {
            return Math.round(n) + ' detik lalu';
        }
        if (n < 3600) {
            return Math.round(n / 60) + ' menit lalu';
        }
        return Math.round(n / 360) / 10 + ' jam lalu';
    }

    /**
     * Kelas warna pin.
     *
     * Status "basi" menang atas status geofence, karena operator yang
     * denyutnya sudah lama tidak mengirim posisi tidak boleh dibaca
     * seolah-olah sedang di dalam atau di luar radius sekarang.
     */
    function kelasStatus(op) {
        if (op.basi) {
            return 'pin-basi';
        }
        if (op.status === 'luar') {
            return 'pin-luar';
        }
        if (op.status === 'istirahat') {
            return 'pin-istirahat';
        }
        if (op.status === 'dalam') {
            return 'pin-dalam';
        }
        return 'pin-basi';
    }

    function labelStatus(op) {
        if (op.basi) {
            return 'Denyut basi';
        }
        if (op.status === 'luar') {
            return 'Luar radius';
        }
        if (op.status === 'istirahat') {
            return 'Istirahat';
        }
        if (op.status === 'dalam') {
            return 'Dalam radius';
        }
        return 'Tanpa lokasi';
    }

    function warnaStatus(op) {
        if (op.basi) {
            return '#64748b';
        }
        if (op.status === 'luar') {
            return '#ef4444';
        }
        if (op.status === 'istirahat') {
            return '#f59e0b';
        }
        if (op.status === 'dalam') {
            return '#10b981';
        }
        return '#94a3b8';
    }

    function punyaPosisi(op) {
        return op.lat !== null && op.lat !== undefined && op.lng !== null && op.lng !== undefined;
    }

    /**
     * Ubah warna hex dari warnaStatus() jadi rgba supaya bisa dipakai
     * untuk warna halo berdenyut.
     */
    function rgbaDari(hex, alpha) {
        var h = String(hex || '').replace('#', '');

        if (h.length === 3) {
            h = h.charAt(0) + h.charAt(0) + h.charAt(1) + h.charAt(1) + h.charAt(2) + h.charAt(2);
        }

        var n = parseInt(h, 16);

        if (h.length !== 6 || isNaN(n)) {
            return 'rgba(56,189,248,' + alpha + ')';
        }

        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + alpha + ')';
    }

    /**
     * Tandai operator yang denyutnya baru saja sampai.
     *
     * Yang dikirim server cuma umur denyut, yaitu selisih detik sejak
     * operator menyimpan koordinat. Umur itu selalu bertambah, kecuali
     * ada koordinat baru yang baru saja disimpan. Jadi umur yang tiba
     * tiba lebih kecil daripada poll sebelumnya adalah tanda denyut baru,
     * dan hanya penanda itu yang membuat pin berdenyut.
     *
     * Poll pertama hanya mengisi catatan, tidak menandai apa pun. Kalau
     * tidak dijaga begini, semua pin akan berdenyut begitu halaman dibuka.
     */
    function tandaiDenyutBaru() {
        var baru = {};

        stateOperator.forEach(function (op) {
            if (op.umur === null || op.umur === undefined) {
                return;
            }

            var umur = Number(op.umur);
            if (!isFinite(umur)) {
                return;
            }

            var sebelum = umurTerlihat[op.uid];

            if (sebelum !== undefined && umur < sebelum) {
                baru[op.uid] = true;
            }

            umurTerlihat[op.uid] = umur;
        });

        return baru;
    }

    // -----------------------------------------------------------------
    // Peta
    // -----------------------------------------------------------------

    function buatPeta() {
        petakan = L.map('peta', {
            zoomControl: true,
            attributionControl: true
        }).setView(DEFAULT_PUSAT, 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(petakan);

        grupPosUji = L.layerGroup().addTo(petakan);
        grupOperator = L.layerGroup().addTo(petakan);
    }

    function gambarPosUji() {
        grupPosUji.clearLayers();
        indeksUji = {};

        stateLokasi.forEach(function (loc) {
            indeksUji[loc.id] = loc;

            L.circleMarker([loc.lat, loc.lng], {
                radius: 6,
                color: '#ffffff',
                weight: 2,
                fillColor: '#253D6B',
                fillOpacity: 1
            }).bindPopup(
                '<strong>Pos Uji</strong><br>' +
                esc(loc.nama) +
                (loc.radius !== null && loc.radius !== undefined
                    ? '<br>Radius: ' + meter(loc.radius)
                    : '')
            ).addTo(grupPosUji);
        });
    }

    function gambarOperator() {
        grupOperator.clearLayers();
        penandaOperator = {};
        lingkaranRadius = {};

        stateOperator.forEach(function (op) {
            if (!punyaPosisi(op)) {
                return;
            }

            var warna = warnaStatus(op);
            var nama = esc(op.username || op.uid);

            // Pin berdenyut hanya setelah denyut baru benar-benar sampai,
            // yaitu setelah operator memakai script-nya untuk mengecek
            // lokasi lalu menyimpan koordinat baru. Warna statusnya sendiri
            // tidak diubah, jadi warna dan denyut tidak saling menimpa.
            var denyutBaru = denyutMuncul[op.uid] === true;
            var kelas = 'pin ' + kelasStatus(op) + (denyutBaru ? ' pin-denyut' : '');

            var ikon = L.divIcon({
                className: '',
                html: '<div class="' + kelas + '" style="--halo:' + rgbaDari(warna, 0.55) + '"></div>',
                iconSize: [18, 18],
                iconAnchor: [9, 18],
                popupAnchor: [0, -16]
            });

            var tanda = L.marker([op.lat, op.lng], { icon: ikon }).bindPopup(
                '<strong>' + nama + '</strong><br>' +
                'Status: ' + labelStatus(op) + '<br>' +
                'Tugas: ' + esc(op.nama_lokasi || '-') + '<br>' +
                'Jarak: ' + meter(op.jarak) +
                (op.radius !== null && op.radius !== undefined
                    ? ' dari radius ' + meter(op.radius)
                    : '') +
                '<br>Akurasi GPS: ' + meter(op.akurasi) + '<br>' +
                'Denyut: ' + umurRingkas(op.umur)
            );

            tanda.addTo(grupOperator);
            penandaOperator[op.uid] = tanda;

            // Lingkaran radius hanya digambar kalau radiusnya benar-benar
            // ada. Tanpa itu, lingkaran dengan radius 0 akan menumpuk di
            // tengah marker dan menyesatkan.
            if (op.radius !== null && op.radius !== undefined && op.radius > 0) {
                lingkaranRadius[op.uid] = L.circle([op.lat, op.lng], {
                    radius: op.radius,
                    color: warna,
                    weight: 1,
                    opacity: 0.35,
                    fillColor: warna,
                    fillOpacity: 0.06,
                    interactive: false
                }).addTo(grupOperator);
            }
        });
    }

    function pusatkanSemua() {
        var titik = [];

        stateLokasi.forEach(function (loc) {
            titik.push([loc.lat, loc.lng]);
        });

        stateOperator.forEach(function (op) {
            if (punyaPosisi(op)) {
                titik.push([op.lat, op.lng]);
            }
        });

        if (titik.length === 0) {
            petakan.setView(DEFAULT_PUSAT, 12);
            return;
        }

        if (titik.length === 1) {
            petakan.setView(titik[0], 16);
            return;
        }

        petakan.fitBounds(titik, { padding: [40, 40], maxZoom: 16 });
    }

    // -----------------------------------------------------------------
    // Daftar operator
    // -----------------------------------------------------------------

    function gambarDaftar() {
        var wadah = $('daftarOperator');
        wadah.innerHTML = '';

        if (!stateOperator.length) {
            wadah.innerHTML = '<div class="text-center py-10 text-slate-400 italic text-sm">Tidak ada operator terdaftar.</div>';
            return;
        }

        stateOperator.forEach(function (op) {
            var warna = warnaStatus(op);
            var adaPosisi = punyaPosisi(op);

            var baris = document.createElement('div');
            baris.className = 'flex items-center gap-3 p-3 rounded-2xl border border-slate-100 bg-slate-50/60 cursor-pointer hover:bg-slate-100 transition';
            baris.dataset.uid = op.uid;

            var titik = '<span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + warna + '"></span>';

            var meta = '<span class="text-[11px] font-bold" style="color:' + warna + '">' + labelStatus(op) + '</span>';

            if (!adaPosisi) {
                meta = '<span class="text-[11px] font-bold text-amber-600">Tidak ada koordinat</span>';
            } else if (op.nama_lokasi) {
                meta = '<span class="text-[11px] font-bold" style="color:' + warna + '">' + labelStatus(op) + '</span>';
                meta += '<span class="text-[11px] text-slate-500 font-medium"> &middot; ' + esc(op.nama_lokasi) + '</span>';
            }

            var ket = adaPosisi
                ? meter(op.jarak) + ' dari tugas &middot; akurasi ' + meter(op.akurasi) + ' &middot; ' + umurRingkas(op.umur)
                : 'Denyut geofencing tidak tercatat';

            baris.innerHTML =
                titik +
                '<div class="flex-1 min-w-0">' +
                '<p class="text-sm font-bold text-slate-800 truncate">' + esc(op.username || op.uid) +
                (op.is_online ? ' <span class="text-[10px] text-emerald-600 font-bold">(online)</span>' : '') +
                '</p>' +
                '<p class="text-[11px] font-medium truncate">' + meta + '</p>' +
                '<p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">' + ket + '</p>' +
                '</div>' +
                (adaPosisi
                    ? '<iconify-icon icon="lucide:crosshair" class="text-slate-300 text-lg shrink-0"></iconify-icon>'
                    : '');

            baris.addEventListener('click', function () {
                pilihOperator(op.uid);
            });

            wadah.appendChild(baris);
        });
    }

    function pilihOperator(uid) {
        var semua = document.querySelectorAll('#daftarOperator [data-uid]');
        for (var i = 0; i < semua.length; i++) {
            semua[i].classList.remove('baris-terpilih');
        }

        var baris = document.querySelector('#daftarOperator [data-uid="' + cssEscape(uid) + '"]');
        if (baris) {
            baris.classList.add('baris-terpilih');
            baris.scrollIntoView({ block: 'nearest' });
        }

        var tanda = penandaOperator[uid];
        if (!tanda) {
            return;
        }

        petakan.setView(tanda.getLatLng(), 16, { animate: true });
        tanda.openPopup();
    }

    function cssEscape(nilai) {
        var teks = String(nilai);
        return teks.replace(/["\\]/g, '\\$&');
    }

    // -----------------------------------------------------------------
    // Ringkasan dan status
    // -----------------------------------------------------------------

    function gambarRingkasan() {
        var ada = 0;
        var luar = 0;
        var tanpa = 0;
        var online = 0;

        stateOperator.forEach(function (op) {
            if (punyaPosisi(op)) {
                ada++;
                if (!op.basi && op.status === 'luar') {
                    luar++;
                }
            } else {
                tanpa++;
            }

            if (op.is_online) {
                online++;
            }
        });

        $('jmlAdaPosisi').textContent = ada;
        $('jmlLuar').textContent = luar;
        $('jmlTanpa').textContent = tanpa;
        $('jmlOnline').textContent = online;
    }

    function gambarStatus(ok, pesan) {
        var lencana = $('badgeStatus');
        var titik = lencana.querySelector('span');
        var teks = $('badgeStatusText');

        if (ok) {
            titik.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
            teks.textContent = 'Terhubung';
            lencana.className = 'px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-1.5 shadow-sm';
        } else {
            titik.className = 'w-2 h-2 rounded-full bg-red-500';
            teks.textContent = pesan || 'Gagal terhubung';
            lencana.className = 'px-3.5 py-1.5 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-bold flex items-center gap-1.5 shadow-sm';
        }
    }

    // -----------------------------------------------------------------
    // Pengaman teks
    // -----------------------------------------------------------------

    function esc(teks) {
        if (teks === null || teks === undefined) {
            return '-';
        }
        return String(teks)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // -----------------------------------------------------------------
    // Alur
    // -----------------------------------------------------------------

    function terapkan(data) {
        stateOperator = Array.isArray(data.operator) ? data.operator : [];
        stateLokasi = Array.isArray(data.lokasi) ? data.lokasi : [];

        // Dihitung sebelum marker digambar supaya kelas pin-denyut ikut
        // terpasang pada render yang sama.
        denyutMuncul = tandaiDenyutBaru();

        gambarPosUji();
        gambarOperator();
        gambarDaftar();
        gambarRingkasan();

        $('infoWaktu').textContent = 'Diperbarui ' + (data.waktu || '-');
    }

    function muatAwal() {
        if (AWAL.gagalBaca) {
            gambarStatus(false, 'Gagal membaca');
        } else {
            gambarStatus(true, 'Terhubung');
        }

        terapkan({ operator: AWAL.operator, lokasi: AWAL.lokasi, waktu: 'halaman dimuat' });

        setTimeout(function () {
            petakan.invalidateSize();
            pusatkanSemua();
        }, 150);
    }

    function segarkan() {
        var tombol = $('btnSegarkan');
        var ikon = tombol.querySelector('iconify-icon');

        if (ikon) {
            ikon.style.transform = 'rotate(360deg)';
        }
        tombol.style.opacity = '0.6';

        fetch(AWAL.urlPosisi, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data && data.ok) {
                    gambarStatus(true, 'Terhubung');
                    terapkan(data);
                } else {
                    gambarStatus(false, (data && data.pesan) || 'Gagal membaca');
                }
            })
            .catch(function () {
                gambarStatus(false, 'Tidak bisa menghubungi server');
            })
            .then(function () {
                tombol.style.opacity = '1';
                if (ikon) {
                    setTimeout(function () {
                        ikon.style.transform = 'rotate(0deg)';
                    }, 350);
                }
            });
    }

    function mulai() {
        buatPeta();
        muatAwal();

        $('btnSegarkan').addEventListener('click', segarkan);

        // Poll 20 detik. Denyut operator 60 detik sekali, jadi 20 detik
        // cukup untuk menampilkan denyut baru begitu sampai tanpa membebani
        // Firebase dengan request terlalu rapat.
        setInterval(segarkan, (AWAL.intervalDetik || 20) * 1000);

        // Berhenti polling saat tab tidak terlihat, supaya tidak sia-sia
        // ketika IT Support reimbuk ke tab lain.
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                segarkan();
                petakan.invalidateSize();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();