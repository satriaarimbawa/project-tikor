<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DASHBOARD || OPERATOR</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/css/dash_operator.css'])
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-800">
    <div class="p-4 md:p-6 lg:p-8">
        {{-- Header Section --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="w-14 h-14">
                <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo">
            </div>
            <div>
                <h1 class="font-bold text-lg lg:text-xl leading-tight text-slate-900">Uji Petik - {{ $namaLokasi }}</h1>
                <p class="text-xs lg:text-sm text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</p>
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200 mb-6 relative">
            <h2 class="font-bold mb-4 text-sm lg:text-base text-slate-900">
                Ringkasan Harian ( Status : <span class="text-emerald-500">● AKTIF</span> )
            </h2>

            <div class="bg-slate-100 rounded-2xl p-4 text-center border border-slate-100">
                <p class="text-slate-500 text-sm">Total Survei Kendaraan : 
                    <span class="text-slate-900 font-bold text-lg" id="total-survei">{{ $dataSurvei['total_survei'] ?? 0 }}</span>
                </p>
                <hr class="my-3 border-slate-200">

                <div class="grid grid-cols-2 gap-4" id="summaryGrid">
                    @php
                        $miniConfig = [
                            'motor' => ['icon' => 'fas fa-motorcycle', 'label' => 'Motor'],
                            'minibus' => ['icon' => 'fas fa-car-side', 'label' => 'Mini Bus'],
                            'bus' => ['icon' => 'fas fa-bus', 'label' => 'Bus'],
                            'truk' => ['icon' => 'fas fa-truck', 'label' => 'Truk'],
                            'default' => ['icon' => 'fas fa-car', 'label' => 'Lainnya']
                        ];
                        $semuaObjekUnik = array_unique(array_merge($objekSaya, ...array_column($objekRekan, 'objek')));
                    @endphp

                    @foreach($semuaObjekUnik as $obj)
                        @php 
                            $key = strtolower(str_replace(' ', '', $obj));
                            $conf = $miniConfig[$key] ?? $miniConfig['default'];
                            $label = isset($miniConfig[$key]) ? $conf['label'] : ucfirst($obj);
                            $isAlt = !in_array($obj, $objekSaya);
                        @endphp
                        <div class="flex items-center gap-3 border-r border-slate-200 pr-2 last:border-r-0 {{ $isAlt ? 'opacity-50' : '' }}" 
                             id="summary-{{ $key }}">
                            <i class="{{ $conf['icon'] }} text-2xl text-slate-700"></i>
                            <div class="text-left">
                                <p class="text-[10px] text-slate-500 leading-none mb-1">{{ $label }}{{ $isAlt ? ' (Alt)' : '' }}</p>
                                <p id="count-{{ $key }}" class="font-bold text-sm leading-none">{{ $dataSurvei[$key] ?? 0 }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-4 flex justify-between items-center gap-3">
                <div class="relative flex-1 max-w-xs">
                    <button type="button" id="btn-lapor-hold"
                        @if($sudahLapor) disabled @endif
                        class="relative w-full overflow-hidden {{ $sudahLapor ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-slate-200 hover:bg-slate-300 text-slate-800 active:scale-98' }} py-3 px-4 rounded-2xl flex items-center justify-center gap-3 shadow-sm border border-slate-300 font-bold transition-all select-none touch-none">
                        <div id="hold-progress" class="absolute left-0 top-0 bottom-0 bg-red-500/25 w-0 transition-none pointer-events-none rounded-2xl"></div>
                        <div class="p-1.5 rounded-xl border-2 {{ $sudahLapor ? 'border-slate-400' : 'border-red-500' }} flex items-center justify-center">
                            <i class="fas {{ $sudahLapor ? 'fa-check text-slate-400' : 'fa-hand-pointer text-red-500' }} text-sm" id="hold-icon"></i>
                        </div>
                        <span id="hold-text" class="font-bold text-sm leading-tight text-center">
                            {{ $sudahLapor ? 'Laporan Hari Ini Terkirim' : 'Tahan 3 Detik untuk Lapor' }}
                        </span>
                    </button>
                </div>
                <div class="flex flex-col gap-2">
                    <button onclick="window.location.href='{{ route('operator.download.pdf') }}'" class="hover:scale-110 transition-transform p-1" title="Lihat Laporan"><i class="far fa-file-alt text-2xl text-slate-600"></i></button>
                    <button onclick="window.location.href='{{ route('operator.download.pdf') }}'" class="hover:scale-110 transition-transform p-1" title="Unduh PDF"><i class="fas fa-download text-2xl text-slate-600"></i></button>
                </div>
            </div>
        </div>

        {{-- Survey Buttons --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 mb-28">
            <h2 class="font-bold mb-6 text-sm text-slate-900 px-2">Pilih Jenis Kendaraan</h2>
            <div id="vehicleContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $config = [
                        'motor' => ['icon' => 'fas fa-motorcycle', 'bg' => 'bg-orange-500', 'grad' => 'linear-gradient(to right, #fdba74, #ffedd5)', 'label' => 'Motor'],
                        'minibus' => ['icon' => 'fas fa-car-side', 'bg' => 'bg-yellow-500', 'grad' => 'linear-gradient(to right, #fde047, #fef9c3)', 'label' => 'Mini Bus'],
                        'bus' => ['icon' => 'fas fa-bus', 'bg' => 'bg-blue-500', 'grad' => 'linear-gradient(to right, #93c5fd, #dbeafe)', 'label' => 'Bus'],
                        'truk' => ['icon' => 'fas fa-truck', 'bg' => 'bg-purple-500', 'grad' => 'linear-gradient(to right, #c4b5fd, #ede9fe)', 'label' => 'Truk'],
                        'default' => ['icon' => 'fas fa-car', 'bg' => 'bg-slate-500', 'grad' => 'linear-gradient(to right, #cbd5e1, #f1f5f9)', 'label' => 'Lainnya']
                    ];
                @endphp

                @foreach($objekSaya as $obj)
                    @php 
                        $key = strtolower(str_replace(' ', '', $obj));
                        $style = $config[$key] ?? $config['default'];
                        if(!isset($config[$key])) $style['label'] = ucfirst($obj);
                    @endphp
                    <button @if($sudahLapor) disabled @else onclick="hitungKendaraan('{{ $key }}')" @endif
                        style="background: {{ $sudahLapor ? '#f1f5f9' : $style['grad'] }};"
                        class="flex items-center p-4 rounded-2xl shadow-md {{ $sudahLapor ? 'opacity-60 cursor-not-allowed' : 'hover:scale-105 hover:shadow-xl transition-all active:scale-95' }} w-full">
                        <div class="{{ $sudahLapor ? 'bg-slate-400' : $style['bg'] }} w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                            <i class="{{ $style['icon'] }} text-white text-xl"></i>
                        </div>
                        <span class="ml-4 font-bold text-slate-800 text-lg">{{ $style['label'] }}</span>
                    </button>
                @endforeach

                @foreach($objekRekan as $uidRekan => $dataRekan)
                    @foreach($dataRekan['objek'] as $obj)
                        @php 
                            $key = strtolower(str_replace(' ', '', $obj));
                            $style = $config[$key] ?? $config['default'];
                            if(!isset($config[$key])) $style['label'] = ucfirst($obj);
                        @endphp
                        <button disabled id="btn-rekan-{{ $uidRekan }}-{{ $key }}" data-owner="{{ $uidRekan }}" data-key="{{ $key }}" data-label="{{ $style['label'] }}"
                            style="background: #f1f5f9;"
                            class="flex items-center p-4 rounded-2xl shadow-md opacity-40 transition-all w-full border-2 border-transparent btn-rekan">
                            <div class="bg-slate-400 w-12 h-12 rounded-xl flex items-center justify-center shadow-md icon-box">
                                <i class="{{ $style['icon'] }} text-white text-xl"></i>
                            </div>
                            <div class="ml-4 text-left">
                                <span class="block font-bold text-slate-800 text-lg label-text">{{ $style['label'] }}</span>
                                <span class="block text-[9px] font-black text-slate-500 uppercase tracking-tighter status-text">Tugas {{ $dataRekan['nama'] }}</span>
                            </div>
                        </button>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Bottom Navigation --}}
        <div class="fixed bottom-0 left-0 right-0 bg-[#5A6C8F] shadow-2xl rounded-t-2xl z-50">
            <div class="flex justify-around p-3 text-slate-300 max-w-md mx-auto">
                <a href="/dashboard-operator" class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition"><i class="fas fa-home text-lg mb-1"></i>Beranda</a>
                <a href="/dashboard-operator-penugasan" class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition"><i class="fas fa-clipboard-list text-lg mb-1"></i>Penugasan</a>
                <button class="flex flex-col items-center text-xs text-white transition"><i class="fas fa-poll text-lg mb-1"></i>Survei</button>
                <a href="/dashboard-operator-profile" class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition"><i class="fas fa-user-circle text-lg mb-1"></i>Profil</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/deteksiTikorUser.js') }}"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>

    <script>
        let firebaseConfig = @json(config('firebase.projects.app'));
        firebaseConfig.apiKey = "AIzaSyA_raJzGxDNyvpn1OIFczKdB6I-mpdTYdI";
        if (!firebaseConfig.databaseURL) firebaseConfig.databaseURL = "{{ config('firebase.projects.app.database.url') }}";
        if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
        const db = firebase.database();
        const emulatorHost = "{{ env('FIREBASE_DATABASE_EMULATOR_HOST') }}";
        if (emulatorHost) {
            const parts = emulatorHost.split(':');
            db.useEmulator(parts[0], parseInt(parts[1]) || 9000);
        }
        const currentUserId = "{{ session('user_id') }}";

        // Real-time Delegation Logic
        db.ref('users').on('value', snapshot => {
            const users = snapshot.val();
            if (!users) return;

            document.querySelectorAll('.btn-rekan').forEach(btn => {
                const uidRekan = btn.getAttribute('data-owner');
                const key = btn.getAttribute('data-key');
                const userRekan = users[uidRekan];
                if (!userRekan) return;

                const isResting = userRekan.status_istirahat === true || userRekan.status_istirahat === "true";
                const claimerUid = userRekan.claimer_id;
                const statusTextEl = btn.querySelector('.status-text');
                const iconBox = btn.querySelector('.icon-box');
                if (!statusTextEl || !iconBox) return;

                if (isResting) {
                    if (!claimerUid) {
                        btn.disabled = false; btn.style.opacity = '1'; btn.style.background = '#FEF3C7'; 
                        btn.style.borderColor = '#F59E0B'; btn.onclick = () => klaimTugas(uidRekan);
                        statusTextEl.innerText = "KLIK UNTUK KLAIM TUGAS"; statusTextEl.classList.add("animate-pulse");
                        statusTextEl.style.color = "#D97706";
                    } else if (claimerUid === currentUserId) {
                        btn.disabled = false; btn.style.opacity = '1'; btn.style.background = 'linear-gradient(to right, #6EE7B7, #D1FAE5)';
                        btn.style.borderColor = '#10B981'; btn.onclick = () => hitungKendaraan(key);
                        statusTextEl.innerText = "BANTU REKAN (ANDA)"; statusTextEl.classList.remove("animate-pulse");
                        statusTextEl.style.color = "#047857";
                        iconBox.className = "bg-emerald-500 w-12 h-12 rounded-xl flex items-center justify-center shadow-md icon-box";
                    } else {
                        btn.disabled = true; btn.style.opacity = '0.4'; btn.style.background = '#F1F5F9';
                        const namaClaimer = users[claimerUid] ? users[claimerUid].username : 'Rekan';
                        statusTextEl.innerText = "DIKLAIM OLEH " + namaClaimer; statusTextEl.classList.remove("animate-pulse");
                        statusTextEl.style.color = "#94a3b8";
                    }
                } else {
                    btn.disabled = true; btn.style.opacity = '0.4'; btn.style.background = '#f1f5f9';
                    btn.style.borderColor = 'transparent'; btn.onclick = null;
                    statusTextEl.innerText = "Tugas " + userRekan.username;
                    statusTextEl.classList.remove("animate-pulse");
                    statusTextEl.style.color = "#64748b";
                    iconBox.className = "bg-slate-400 w-12 h-12 rounded-xl flex items-center justify-center shadow-md icon-box";
                }
            });
        });

        // Real-time Survey Count Sync
        const idLokasi = "{{ session('id_lokasi_aktif') }}";
        const today = "{{ \Carbon\Carbon::now('Asia/Makassar')->toDateString() }}";
        const surveyRef = db.ref(`survei_harian/${idLokasi}/${today}`);

        surveyRef.on('value', snapshot => {
            const dataHariIni = snapshot.val();
            if (!dataHariIni) return;

            // Ambil semua key objek survei yang ada di halaman
            const counts = {};
            let totalSemua = 0;

            // Inisialisasi awal agar 0 jika tidak ada data
            document.querySelectorAll('[id^="count-"]').forEach(el => {
                const key = el.id.replace('count-', '');
                counts[key] = 0;
            });

            // Agregasi semua data dari semua operator/jam di lokasi ini
            Object.values(dataHariIni).forEach(dataJam => {
                Object.values(dataJam).forEach(dataTugas => {
                    Object.keys(counts).forEach(key => {
                        const val = parseInt(dataTugas[key]) || 0;
                        counts[key] += val;
                        totalSemua += val;
                    });
                });
            });

            // Update ke UI secara dinamis
            Object.keys(counts).forEach(key => {
                const el = document.getElementById(`count-${key}`);
                if (el) el.innerText = counts[key];
            });

            const totalEl = document.getElementById('total-survei');
            if (totalEl) totalEl.innerText = totalSemua;
        });

        function klaimTugas(uidRekan) {
            confirmAction("Ambil Alih Tugas", "Ambil alih tugas rekan selama ia istirahat?", 'question').then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('operator.claim-tugas') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ uid_rekan: uidRekan })
                    }).then(r => r.json()).then(data => { if (!data.success) showAlert("Gagal", data.message, "error"); });
                }
            });
        }

        function hitungKendaraan(jenis) {
            // Hapus update angka lokal, biarkan Firebase Listener yang melakukan update
            fetch("{{ route('simpan.hitung.kendaraan') }}", {
                method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ jenis_kendaraan: jenis, id_penugasan: "{{ $idPenugasan }}" })
            }).catch(e => console.error(e));
        }

        // --- HOLD-TO-CONFIRM LOGIC (3 DETIK) ---
        let holdTimer = null;
        let isHolding = false;
        const HOLD_DURATION = 3000; // 3000ms = 3 detik

        const btnLaporHold = document.getElementById('btn-lapor-hold');
        const holdProgress = document.getElementById('hold-progress');
        const holdText = document.getElementById('hold-text');
        const holdIcon = document.getElementById('hold-icon');

        if (btnLaporHold && !btnLaporHold.disabled) {
            const startHold = (e) => {
                if (e.type === 'touchstart') {
                    // prevent ghost click
                }
                if (isHolding) return;
                isHolding = true;

                holdProgress.style.transition = 'width 3s linear';
                holdProgress.style.width = '100%';
                holdText.innerText = 'Tahan terus...';
                if (holdIcon) holdIcon.className = 'fas fa-spinner fa-spin text-red-500 text-sm';

                holdTimer = setTimeout(() => {
                    isHolding = false;
                    if (navigator.vibrate) {
                        try { navigator.vibrate(100); } catch(err) {}
                    }
                    resetHold();
                    bukaKonfirmasiLapor();
                }, HOLD_DURATION);
            };

            const cancelHold = () => {
                if (!isHolding) return;
                isHolding = false;
                clearTimeout(holdTimer);
                resetHold();
            };

            const resetHold = () => {
                if (holdProgress) {
                    holdProgress.style.transition = 'none';
                    holdProgress.style.width = '0%';
                }
                if (holdText) holdText.innerText = 'Tahan 3 Detik untuk Lapor';
                if (holdIcon) holdIcon.className = 'fas fa-hand-pointer text-red-500 text-sm';
            };

            // Touch events for Mobile Phones
            btnLaporHold.addEventListener('touchstart', startHold, { passive: false });
            btnLaporHold.addEventListener('touchend', cancelHold);
            btnLaporHold.addEventListener('touchcancel', cancelHold);

            // Mouse events for Desktop Browser
            btnLaporHold.addEventListener('mousedown', startHold);
            btnLaporHold.addEventListener('mouseup', cancelHold);
            btnLaporHold.addEventListener('mouseleave', cancelHold);
        }

        function bukaKonfirmasiLapor() {
            const total = document.getElementById('total-survei') ? document.getElementById('total-survei').innerText : 0;

            Swal.fire({
                title: 'Konfirmasi Akhir Shift',
                html: `
                    <div class="text-left bg-slate-50 p-4 rounded-xl text-sm mb-2 border border-slate-200">
                        <p class="font-bold text-slate-700 mb-1">📊 Rangkuman Survei Anda Hari Ini:</p>
                        <p class="text-slate-600">Total Kendaraan Tercatat: <span class="font-black text-slate-900">${total} unit</span></p>
                        <p class="text-xs text-amber-600 mt-2 font-medium">⚠️ Perhatian: Setelah melapor, tombol pencatatan akan dikunci dan shift Anda hari ini selesai.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#253D6B',
                confirmButtonText: 'Ya, Selesaikan Shift',
                cancelButtonText: '❌ Batal / Lanjut Survei',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    kirimLaporanKeServer();
                }
            });
        }

        function kirimLaporanKeServer() {
            if (btnLaporHold) btnLaporHold.disabled = true;

            fetch("{{ route('lapor.survei') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ id_penugasan: "{{ $idPenugasan }}" })
            })
            .then(r => r.json())
            .then(d => { 
                if (d.success) {
                    Swal.fire({
                        title: 'Laporan Berhasil!',
                        text: 'Terima kasih atas kerja keras Anda hari ini. Hati-hati di jalan!',
                        icon: 'success',
                        confirmButtonColor: '#253D6B',
                        confirmButtonText: 'Kembali ke Penugasan',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "/dashboard-operator-penugasan";
                    });
                } else {
                    Swal.fire('Gagal', d.message || 'Terjadi kesalahan saat melapor.', 'error');
                    if (btnLaporHold) btnLaporHold.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                if (btnLaporHold) btnLaporHold.disabled = false;
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            startGeofencing("{{ route('check.location.radius') }}", "{{ csrf_token() }}", "{{ url('/') }}");
        });
    </script>
    @include('template.shared_scripts')
</body>
</html>