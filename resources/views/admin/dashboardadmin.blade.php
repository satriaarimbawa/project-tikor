<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Petik - Dashboard Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
    
    <!-- Firebase SDK for Realtime Updates -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>
<body class="flex bg-[#F5F7FA]">

    @include('admin.template.navbar')

    <main class="main-content lg:ml-64 p-4 md:p-8 flex-1 min-w-0 overflow-x-hidden">
        <header class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-gray-800 font-bold text-[30px] tracking-tight">Pendapatan Harian</h1>
                <p class="text-xs text-gray-500 font-medium">Monitoring Real-time Survei & Retribusi Dinas Perhubungan</p>
            </div>
    
            <div class="flex items-center gap-6">
                <div onclick="toggleNotifModal()" class="cursor-pointer flex items-center gap-2 text-orange-600 font-bold text-sm bg-orange-50 px-5 py-2 rounded-full border border-orange-100 shadow-sm relative hover:bg-orange-100 transition-all">
                    <iconify-icon icon="lucide:mail" class="text-lg"></iconify-icon>
                    <span>Pesan Masuk</span>
                    @if($unreadCount > 0)
                        <span id="notifBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </div>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain" alt="Logo Klungkung">
            </div>
        </header>

        <style>
            @keyframes slideIn {
                from { transform: translateX(50px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .animate-slide-in { animation: slideIn 0.3s ease-out forwards; }
            .pulse-green { animation: pulse-g 0.5s ease-in-out; }
            @keyframes pulse-g { 0% { transform: scale(1); } 50% { transform: scale(1.04); color: #059669; } 100% { transform: scale(1); } }
        </style>

        <!-- TOTAL PENDAPATAN CARD -->
        <div class="card-revenue p-10 flex items-center gap-8 mb-10 relative overflow-hidden bg-white rounded-[30px] shadow-[0_10px_25px_rgba(0,0,0,0.1)]">
           <div class="absolute inset-0 opacity-40" 
                style="background: linear-gradient(90deg, #9AE95B 30%, #FFFFFF 100%);">
            </div>
            <div class="w-24 h-24 flex items-center justify-center relative z-10">
                <img src="{{ asset('assets/Pendapatan.png') }}" class="w-20 h-20 object-contain" alt="Icon Pendapatan">
            </div>
            <div class="relative z-10">
                <h2 id="admin-total-revenue" class="text-6xl font-bold tracking-tight text-gray-900 transition-all">Rp. {{ number_format($totalPendapatan, 0, ',', '.') }}</h2>
                <p class="text-[12px] text-gray-500 font-bold uppercase tracking-wider mt-2 flex items-center gap-2">
                    <span>Total Pendapatan Harian ( Hari Ini )</span>
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                </p>
            </div>
        </div>

        @php
            $colors = [
                'bg' => ['#4A78D7', '#E9A426', '#953EE1', '#E95BA4', '#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
                'gradient' => [
                    'rgba(16, 86, 216, 0.4)', 
                    'rgba(233, 164, 38, 0.4)', 
                    'rgba(149, 62, 225, 0.4)', 
                    'rgba(233, 91, 164, 0.4)',
                    'rgba(16, 185, 129, 0.4)',
                    'rgba(59, 130, 246, 0.4)',
                    'rgba(245, 158, 11, 0.4)',
                    'rgba(239, 68, 68, 0.4)'
                ]
            ];
            $icons = [
                'sepedamotor' => 'Motor.png',
                'mobilpenumpang' => 'Mini Bus.png',
                'bus' => 'Bus.png',
                'truk' => 'Truk.png',
                'motor' => 'Motor.png',
                'minibus' => 'Mini Bus.png'
            ];
        @endphp

        <!-- STATISTIK KENDARAAN GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
            @foreach($stats as $key => $count)
                @php 
                    $index = $loop->index % count($colors['bg']);
                    $iconFile = $icons[$key] ?? 'Bus.png';
                @endphp
                <div class="relative overflow-hidden p-6 rounded-[25px] flex items-center gap-4 border border-gray-100 transition-transform hover:scale-105 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)]"
                     style="background: linear-gradient(90deg, {{ $colors['gradient'][$index] }} 50%, rgba(255, 255, 255, 0.32) 100%);">
                    <div class="relative z-10 w-14 h-14 rounded-2xl flex items-center justify-center shadow-md flex-shrink-0" style="background-color: {{ $colors['bg'][$index] }};">
                        <img src="{{ asset('assets/' . $iconFile) }}" alt="{{ $key }}" class="w-9 h-9 object-contain">
                    </div>
                    <div class="relative z-10">
                        <p class="text-[15px] font-black text-[#000000] uppercase tracking-wider">{{ $objekNames[$key] ?? $key }}</p>
                        <h3 id="admin-count-{{ $key }}" class="text-3xl font-black text-gray-800 transition-all">{{ $count }}</h3>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- GRAFIK MINGGUAN -->
        <div class="bg-white p-10 rounded-[30px] shadow-sm mb-10 border border-gray-100 w-full">
            <h3 class="font-extrabold text-gray-800 mb-8 flex items-center gap-3">
                <iconify-icon icon="lucide:trending-up" class="text-blue-500 text-xl"></iconify-icon>
                STATUS MONITORING MINGGUAN (HARIAN)
            </h3>

            <div class="flex flex-col xl:flex-row gap-8">
                <div class="flex-1 h-[350px] relative">
                    <canvas id="weeklyMonitoringChart"></canvas>
                </div>

                <div class="flex flex-col justify-center gap-3 min-w-[120px]">
                    @foreach($objekNames as $key => $name)
                        @php $index = $loop->index % count($colors['bg']); @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-4 h-4 rounded-full" style="background-color: {{ $colors['bg'][$index] }};"></span>
                            <span class="text-sm font-semibold text-gray-700">{{ $name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TABEL DETAIL PENDAPATAN -->
        <div class="bg-white p-10 rounded-[30px] shadow-sm border border-gray-100 overflow-hidden mb-20">
            <div class="p-8 flex items-center justify-between">
                <h3 class="font-extrabold text-gray-800 text-lg">Tabel Detail Pendapatan Harian</h3>
            </div>
            
            <div class="overflow-x-auto px-8 pb-8">
                <table class="w-full border-separate border-spacing-y-3">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-4 text-gray-800 font-bold text-center rounded-l-xl border-y border-l border-black">Objek</th>
                            <th class="p-4 text-gray-800 font-bold text-center border-y border-black">Lokasi</th>
                            <th class="p-4 text-gray-800 font-bold text-center border-y border-black">Jumlah Unit</th>
                            <th class="p-4 text-gray-800 font-bold text-center rounded-r-xl border-y border-r border-black">Nominal Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody id="admin-detail-tbody">
                        @forelse($detailPendapatan as $row)
                        <tr class="bg-white group transition-all">
                            <td class="p-4 text-center font-bold text-gray-800 border-y border-l border-black rounded-l-2xl">{{ $row['objek'] }}</td>
                            <td class="p-4 text-center font-bold text-gray-800 border-y border-black">{{ $row['nama_lokasi'] }}</td>
                            <td class="p-4 text-center font-bold text-gray-800 border-y border-black">{{ $row['jumlah'] }}</td>
                            <td class="p-4 text-center font-bold text-gray-800 border-y border-r border-black rounded-r-2xl">Rp. {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-10 text-center text-gray-400 italic bg-white rounded-2xl border border-black">
                                Belum ada data pendapatan untuk hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/navbar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleNotifModal() {
            const modal = document.getElementById('notifModal');
            modal.classList.toggle('hidden');
            if (!modal.classList.contains('hidden')) {
                loadNotifications();
            }
        }

        async function loadNotifications() {
            const container = document.getElementById('notifContainer');
            try {
                const response = await fetch('/api/notifications');
                const data = await response.json();
                
                if (Object.keys(data).length === 0) {
                    container.innerHTML = '<div class="text-center py-10 text-gray-400 italic text-sm">Tidak ada notifikasi pelanggaran.</div>';
                    return;
                }

                container.innerHTML = '';
                for (const key in data) {
                    const notif = data[key];
                    const isUnread = notif.status === 'unread';
                    const html = `
                        <div class="p-4 rounded-2xl ${isUnread ? 'bg-orange-50 border border-orange-100' : 'bg-gray-50 border border-gray-100'} transition-all">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-bold text-[#253D6B] uppercase tracking-tighter">${notif.judul}</span>
                                <span class="text-[9px] text-gray-400">${notif.waktu}</span>
                            </div>
                            <p class="text-xs text-gray-700 leading-relaxed">${notif.pesan}</p>
                            ${isUnread ? '<div class="mt-2 w-2 h-2 bg-red-500 rounded-full"></div>' : ''}
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                }
            } catch (error) {
                container.innerHTML = '<div class="text-center py-10 text-red-400 italic text-sm">Gagal memuat notifikasi.</div>';
            }
        }

        async function markAllAsRead() {
            try {
                await fetch('/api/notifications/mark-read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                document.getElementById('notifBadge')?.remove();
                loadNotifications();
            } catch (error) {
                console.error("Gagal memperbarui notifikasi:", error);
            }
        }

        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num || 0);
        };

        const objekNames = @json($objekNames);
        const tarifMap = @json($tarifMap);
        const subKeyMap = @json($subKeyMap);
        const validKeys = Object.keys(objekNames);
        const todayStr = "{{ \Carbon\Carbon::now('Asia/Makassar')->toDateString() }}";

        // REALTIME FIREBASE SYNC FOR ADMIN DASHBOARD
        try {
            const firebaseConfig = {
                apiKey: "AIzaSyA_raJzGxDNyvpn1OIFczKdB6I-mpdTYdI",
                databaseURL: "{{ config('firebase.projects.app.database.url') }}",
                projectId: "uji-petik-default-rtdb"
            };
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            const db = firebase.database();
            const emulatorHost = "{{ env('FIREBASE_DATABASE_EMULATOR_HOST') }}";
            if (emulatorHost) {
                const parts = emulatorHost.split(':');
                db.useEmulator(parts[0], parseInt(parts[1]) || 9000);
            }

            db.ref('survei_harian').on('value', (snap) => {
                const dataRaw = snap.val() || {};
                let totals = {};
                validKeys.forEach(k => totals[k] = 0);
                let totalRevenue = 0;

                const dates = [todayStr, new Date().toLocaleDateString('en-CA')];

                for (let idL in dataRaw) {
                    dates.forEach(tgl => {
                        if (dataRaw[idL] && dataRaw[idL][tgl]) {
                            const hours = dataRaw[idL][tgl];
                            for (let h in hours) {
                                for (let p in hours[h]) {
                                    const s = hours[h][p];
                                    if (s) {
                                        for (let fieldKey in s) {
                                            const vol = parseInt(s[fieldKey] || 0);
                                            if (vol > 0 && subKeyMap[fieldKey]) {
                                                const parent = subKeyMap[fieldKey];
                                                totals[parent] = (totals[parent] || 0) + vol;
                                                const price = tarifMap[parent] || 0;
                                                totalRevenue += (vol * price);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Update Cards
                validKeys.forEach(k => {
                    const el = document.getElementById('admin-count-' + k);
                    if (el) {
                        const count = totals[k] || 0;
                        if (parseInt(el.innerText) !== count) {
                            el.innerText = count;
                            el.classList.add('pulse-green');
                            setTimeout(() => el.classList.remove('pulse-green'), 500);
                        }
                    }
                });

                // Update Total Revenue
                const revEl = document.getElementById('admin-total-revenue');
                if (revEl) {
                    revEl.innerText = formatRupiah(totalRevenue);
                }
            });
        } catch (err) {
            console.warn("Realtime sync warning:", err);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('weeklyMonitoringChart').getContext('2d');
            
            const colors = ['#4A78D7', '#E9A426', '#953EE1', '#E95BA4', '#10B981', '#3B82F6', '#F59E0B', '#EF4444'];
            const chartDataRaw = @json($chartData);
            const labelsMingguan = @json($labelsMingguan);

            const datasets = Object.keys(chartDataRaw).map((key, index) => {
                return {
                    label: objekNames[key] || key,
                    data: chartDataRaw[key],
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length],
                    tension: 0.1,
                    borderWidth: 3,
                    fill: false
                };
            });
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labelsMingguan,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                color: '#9CA3AF',
                                font: { size: 11 }
                            }, 
                            grid: { color: '#F3F4F6' } 
                        },
                        x: { 
                            grid: { display: false }, 
                            ticks: { 
                                color: '#4B5563', 
                                font: { weight: 'bold', size: 12 } 
                            } 
                        }
                    },
                    elements: {
                        point: { 
                            radius: 4, 
                            hoverRadius: 7, 
                            backgroundColor: '#FFFFFF', 
                            borderWidth: 2.5 
                        }
                    }
                }
            });
        });

    </script>
</body>
</html>
