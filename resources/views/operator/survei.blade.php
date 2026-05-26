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
            <div class="mt-4 flex justify-between items-center">
                <button type="button" id="btn-lapor-main" onclick="akhirSurvei()" style="background-color: #D9D9D9;"
                    class="flex-1 max-w-xs hover:bg-gray-300 py-2 px-6 rounded-2xl flex items-center justify-center gap-4 shadow-sm border border-gray-200 transition-all active:scale-95 group">
                    <div class="p-2 rounded-xl border-2 border-red-500"><i class="fas fa-exclamation-triangle text-lg text-red-500"></i></div>
                    <span class="font-bold text-slate-700 text-xl">Lapor</span>
                </button>
                <div class="flex flex-col gap-2 ml-4">
                    <button onclick="window.location.href='{{ route('operator.download.pdf') }}'" class="hover:scale-110 transition-transform"><i class="far fa-file-alt text-2xl text-slate-600"></i></button>
                    <button onclick="window.location.href='{{ route('operator.download.pdf') }}'" class="hover:scale-110 transition-transform"><i class="fas fa-download text-2xl text-slate-600"></i></button>
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
        // --- Firebase Initialization ---
        let firebaseConfig = @json(config('firebase.projects.app'));
        firebaseConfig.apiKey = "AIzaSyA_raJzGxDNyvpn1OIFczKdB6I-mpdTYdI";
        if (!firebaseConfig.databaseURL) firebaseConfig.databaseURL = "{{ config('firebase.projects.app.database.url') }}";
        if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
        const db = firebase.database();
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

        function akhirSurvei() {
            confirmAction("Akhiri Survei", "Yakin ingin mengakhiri survei?", 'warning').then((result) => {
                if (result.isConfirmed) {
                    const btn = document.getElementById('btn-lapor-main');
                    if(btn) btn.disabled = true;
                    fetch("{{ route('lapor.survei') }}", {
                        method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ id_penugasan: "{{ $idPenugasan }}" })
                    }).then(r => r.json()).then(d => { 
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
                        }
                    });
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            startGeofencing("{{ route('check.location.radius') }}", "{{ csrf_token() }}", "{{ url('/') }}");
        });
    </script>
    @include('template.shared_scripts')
</body>
</html>