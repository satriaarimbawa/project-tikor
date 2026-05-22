<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIVE MONITORING - UJI PETIK DISHUB</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; color: white; overflow: hidden; margin: 0; height: 100vh; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .stat-value { transition: all 0.3s ease; }
        .pulse-update { animation: pulse-blue 0.5s ease-in-out; }
        @keyframes pulse-blue { 0% { transform: scale(1); color: white; } 50% { transform: scale(1.05); color: #3B82F6; } 100% { transform: scale(1); color: white; } }
        .marquee-container { overflow: hidden; white-space: nowrap; width: 100%; }
        .marquee-text { display: inline-block; animation: marquee 30s linear infinite; padding-left: 100%; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        
        .log-entry { animation: slide-in 0.3s ease-out; border-left: 3px solid transparent; }
        @keyframes slide-in { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body class="flex flex-col p-4 gap-4">

    <!-- HEADER -->
    <header class="flex justify-between items-center glass-card p-4 rounded-2xl shrink-0">
        <div class="flex items-center gap-4">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-12 h-12" alt="Logo">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight">COMMAND CENTER</h1>
                <p class="text-xs text-blue-400 font-bold uppercase tracking-widest">Live Monitoring Uji Petik</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p id="liveClock" class="text-xl font-mono font-bold text-white">--:--:--</p>
                <p id="liveDate" class="text-[10px] text-slate-400 uppercase font-bold tracking-tighter">Memuat Tanggal...</p>
            </div>
            <div id="connStatus" class="flex items-center gap-2 bg-slate-700/50 text-slate-400 px-3 py-1 rounded-full border border-white/10">
                <div id="connDot" class="w-2 h-2 bg-slate-500 rounded-full"></div>
                <span id="connText" class="text-[10px] font-bold uppercase">Connecting...</span>
            </div>
        </div>
    </header>

    <!-- MAIN GRID -->
    <div class="flex-1 grid grid-cols-12 gap-4 min-h-0">
        
        <!-- KIRI: TOTAL & OBJEK -->
        <div class="col-span-3 flex flex-col gap-4">
            <div class="glass-card p-6 rounded-3xl flex-1 flex flex-col items-center text-center relative overflow-hidden">
                <div class="mt-6">
                    <p class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Total Kendaraan Hari Ini</p>
                    <h2 id="totalGlobal" class="text-8xl font-extrabold text-white stat-value">{{ $totalGlobal }}</h2>
                </div>
                
                <!-- TABEL PENDAPATAN (Diletakkan di bawah dengan mt-auto) -->
                <div class="mt-auto w-full bg-black/30 rounded-2xl p-4 border border-white/5 flex flex-col">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[10px] text-yellow-400 font-bold uppercase text-left">Estimasi Pendapatan per Lokasi</p>
                        <iconify-icon icon="lucide:banknote" class="text-yellow-400 text-xs"></iconify-icon>
                    </div>
                    <div id="incomeTable" class="overflow-y-auto custom-scrollbar pr-1 max-h-[100px] min-h-[40px]">
                        <div class="text-[10px] text-slate-500 italic py-2">Menghitung data...</div>
                    </div>
                    <div class="border-t border-white/10 mt-3 pt-3 flex justify-between items-center">
                        <span class="text-xs font-bold text-white uppercase tracking-tighter">Total Pendapatan</span>
                        <span id="totalIDR" class="text-lg font-black text-emerald-400 font-mono tracking-tighter">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach($objekNames as $key => $name)
                <div class="glass-card p-3 rounded-2xl border-l-4 border-blue-500">
                    <p class="text-white text-xs font-bold uppercase truncate">{{ $name }}</p>
                    <p id="count-{{ $key }}" class="text-2xl font-extrabold stat-value">{{ $initialGlobal[$key] ?? 0 }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- TENGAH: DIAGRAM & CARD LOKASI -->
        <div class="col-span-6 flex flex-col gap-4">
            <div class="glass-card p-6 rounded-3xl flex-1 min-h-0 flex flex-col">
                <h3 class="text-yellow-400 text-sm font-bold uppercase tracking-wider mb-6">Distribusi Kendaraan Per Lokasi</h3>
                <div class="flex-1 relative w-full">
                    <canvas id="lokasiChart"></canvas>
                </div>
            </div>
            <div class="glass-card p-4 rounded-3xl shrink-0">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-3">Volume Lokasi Real-time</h3>
                <div id="lokasiList" class="flex gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @foreach($lokasiMaster as $id => $loc)
                    <div class="flex-none p-3 bg-slate-800/50 rounded-2xl border border-white/5 min-w-[160px] location-item">
                        <p class="text-[10px] font-bold text-slate-300 truncate loc-name">{{ $loc['nama_lokasi'] }}</p>
                        <p class="text-2xl font-extrabold text-blue-400 mt-1" id="loc-total-{{ $id }}">{{ $initialLokasi[$id] ?? 0 }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- KANAN: STATUS & LOG -->
        <div class="col-span-3 flex flex-col gap-4 min-h-0">
            <!-- PERSONIL AKTIF -->
            <div class="glass-card p-5 rounded-3xl flex-[4.5] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center mb-4 shrink-0">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">User Online</h3>
                    <span id="onlineCount" class="bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded text-[10px] font-bold uppercase">0 ONLINE</span>
                </div>
                <div id="operatorList" class="flex-1 overflow-y-auto pr-2 flex flex-col gap-2 custom-scrollbar">
                    @foreach($operators as $uid => $op)
                        @if($op['is_online'])
                        <div id="op-card-{{ $uid }}" class="p-2 {{ $op['role'] === 'admin' ? 'bg-blue-500/10 border-blue-500/20' : 'bg-emerald-500/10 border-emerald-500/20' }} rounded-xl border flex items-center gap-3 transition-all duration-300">
                            <div class="relative shrink-0">
                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center border-2 border-white/10">
                                    <iconify-icon icon="{{ $op['role'] === 'admin' ? 'lucide:shield-check' : 'lucide:user' }}" class="text-lg {{ $op['role'] === 'admin' ? 'text-blue-400' : 'text-slate-400' }}"></iconify-icon>
                                </div>
                                <div id="op-dot-{{ $uid }}" class="absolute bottom-0 right-0 w-2.5 h-2.5 {{ $op['role'] === 'admin' ? 'bg-blue-500' : 'bg-emerald-500' }} rounded-full border-2 border-[#0F172A]"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between items-start">
                                    <p class="text-[11px] font-bold text-white truncate">{{ $op['username'] }}</p>
                                    <span class="text-[7px] px-1 rounded bg-white/10 text-white/50 font-black uppercase tracking-tighter">{{ $op['role'] }}</span>
                                </div>
                                <p id="op-text-{{ $uid }}" class="text-[8px] {{ $op['role'] === 'admin' ? 'text-blue-400' : 'text-emerald-400' }} font-bold uppercase truncate">
                                    <span class="animate-pulse">●</span> {{ $op['location'] }}
                                </p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- LOG AKTIVITAS -->
            <div class="glass-card p-5 rounded-3xl flex-[5.5] flex flex-col overflow-hidden border-t-4 border-blue-500/50">
                <div class="flex justify-between items-center mb-4 shrink-0">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Log Aktivitas Terkini</h3>
                    <iconify-icon icon="lucide:history" class="text-slate-500"></iconify-icon>
                </div>
                <div id="activityLog" class="flex-1 overflow-y-auto pr-2 flex flex-col gap-3 custom-scrollbar">
                    <div class="text-[10px] text-slate-500 italic text-center py-4">Menunggu aktivitas...</div>
                </div>
            </div>
        </div>

    </div>

    <!-- FOOTER: PEAK HOURS MARQUEE -->
    <footer class="glass-card p-3 rounded-xl overflow-hidden shrink-0 border-t border-white/5">
        <div class="flex items-center gap-4">
            <div class="bg-blue-600 px-3 py-1 rounded-lg font-bold text-[10px] whitespace-nowrap uppercase">Peak Hours</div>
            <div class="marquee-container">
                <div id="peakHoursMarquee" class="marquee-text text-xs text-slate-300 font-medium italic">
                    Menganalisis data jam puncak di seluruh lokasi...
                </div>
            </div>
        </div>
    </footer>

    <script>
        function updateClock() { document.getElementById('liveClock').innerText = new Date().toLocaleTimeString('id-ID'); }
        setInterval(updateClock, 1000); updateClock();
        document.getElementById('liveDate').innerText = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

        const objekNames = @json($objekNames);
        const objekPrices = @json($objekPrices);
        let lokasiMaster = @json($lokasiMaster);
        const lokasiNamesMap = @json($lokasiNamesMap);
        let allPenugasan = {};
        const validKeys = Object.keys(objekNames);
        let activeLocIds = new Set(); // Menyimpan ID Lokasi yang punya penugasan aktif hari ini
        const today = "{{ \Carbon\Carbon::now('Asia/Makassar')->toDateString() }}";
        
        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        };

        const firebaseConfig = @json($firebaseConfig);
        if (!firebaseConfig.databaseURL) {
            firebaseConfig.databaseURL = "{{ config('firebase.projects.app.database.url') }}";
        }

        const ctx = document.getElementById('lokasiChart').getContext('2d');
        const lokasiChart = new Chart(ctx, {
            type: 'bar',
            data: { 
                labels: {!! json_encode(array_values(collect($lokasiMaster)->pluck('nama_lokasi')->toArray())) !!}, 
                datasets: [{ 
                    label: 'Volume', 
                    data: {!! json_encode(array_values($initialLokasi)) !!}, 
                    backgroundColor: 'rgba(59, 130, 246, 0.6)', 
                    borderColor: '#3B82F6', 
                    borderWidth: 2, 
                    borderRadius: 8 
                }] 
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', font: { size: 10 } } }, x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 9 } } } }, plugins: { legend: { display: false } } }
        });

        function setConnStatus(status, isOnline = false) {
            const dot = document.getElementById('connDot'); const text = document.getElementById('connText'); const container = document.getElementById('connStatus');
            if(isOnline) {
                dot.className = 'w-2 h-2 bg-emerald-500 rounded-full animate-pulse';
                container.className = 'flex items-center gap-2 bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full border border-emerald-500/30';
                text.innerText = 'STREAMING AKTIF';
            } else {
                dot.className = 'w-2 h-2 bg-red-500 rounded-full';
                container.className = 'flex items-center gap-2 bg-red-500/20 text-red-400 px-3 py-1 rounded-full border border-red-500/30';
                text.innerText = status;
            }
        }

        // --- LOGIKA FEED AKTIVITAS ---
        function addLog(message, type = 'info') {
            const logContainer = document.getElementById('activityLog');
            const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            if (logContainer.querySelector('div.italic')) logContainer.innerHTML = '';
            const colors = { 'login': 'border-emerald-500 bg-emerald-500/5', 'logout': 'border-slate-500 bg-slate-500/5', 'update': 'border-blue-500 bg-blue-500/5', 'violation': 'border-red-500 bg-red-500/5', 'info': 'border-slate-700 bg-slate-700/5' };
            const icons = { 'login': 'lucide:log-in text-emerald-500', 'logout': 'lucide:log-out text-slate-400', 'update': 'lucide:database text-blue-500', 'violation': 'lucide:alert-triangle text-red-500', 'info': 'lucide:info text-slate-500' };
            const div = document.createElement('div');
            div.className = `log-entry p-2.5 rounded-lg border-l-4 ${colors[type] || colors.info} flex items-start gap-3`;
            div.innerHTML = `<div class="mt-0.5"><iconify-icon icon="${icons[type] || icons.info}" class="text-sm"></iconify-icon></div><div class="min-w-0 flex-1"><p class="text-[10px] text-white leading-tight">${message}</p><p class="text-[8px] text-slate-500 mt-1 font-mono">${time}</p></div>`;
            logContainer.prepend(div);
            if (logContainer.children.length > 20) logContainer.lastElementChild.remove();
        }

        const prevStatus = {};
        let detailedLokasi = {}; // Menyimpan data { idLokasi: { motor: 5, mobil: 10 } }

        try {
            if (!firebase.apps.length) { firebase.initializeApp(firebaseConfig); }
            const db = firebase.database();
            
            // Listener Master Lokasi (Real-time!)
            db.ref('lokasi').on('value', (snap) => {
                const data = snap.val();
                if (data) {
                    lokasiMaster = data;
                }
            });

            function updateOperatorStatus(uid, u) {
                const role = u.role_user || 'user';
                
                const now = Math.floor(Date.now() / 1000);
                const lastSeen = u.last_seen || 0;
                // Hanya anggap stale jika last_seen > 0 (pernah ping) dan khusus untuk operator
                const isStale = role === 'operator' && lastSeen > 0 && (now - lastSeen) > 150; 
                
                let isOnline = u.is_online === true || u.is_online === 'true' || u.is_online == 1;
                
                // AUTO-CLEANUP: Jika status online tapi heartbeat mati
                if (isOnline && isStale) {
                    isOnline = false;
                    db.ref('users/' + uid).update({ is_online: false });
                }

                // Kalkulasi Lokasi Aktif secara Real-time
                let currentLocationName = u.location || 'Tanpa Lokasi';
                let hasReported = false;

                if (role === 'operator') {
                    const currentTime = new Date();
                    for (let idTugas in allPenugasan) {
                        const t = allPenugasan[idTugas];
                        if (t.id_user == uid && t.status === 'aktif') {
                            const mulai = new Date(t.waktu_mulai);
                            const selesai = new Date(t.waktu_selesai);
                            if (currentTime >= mulai && currentTime <= selesai) {
                                currentLocationName = lokasiNamesMap[t.id_lokasi] || 'Lokasi Aktif';
                                // Cek apakah sudah lapor hari ini
                                if (t.laporan_harian && (t.laporan_harian[today] === true || t.laporan_harian[today] === "true")) {
                                    hasReported = true;
                                }
                                break;
                            }
                        }
                    }
                } else if (role === 'admin') {
                    currentLocationName = 'Administrator';
                }

                let card = document.getElementById('op-card-' + uid);
                const container = document.getElementById('operatorList');
                prevStatus[uid] = isOnline;
                
                if (isOnline) {
                    let statusText = currentLocationName;
                    let statusClass = (role === 'admin' ? 'text-blue-400' : 'text-emerald-400');
                    let dotClass = (role === 'admin' ? 'bg-blue-500' : 'bg-emerald-500');

                    if (u.status_istirahat) {
                        statusText = 'Sedang Istirahat';
                        statusClass = 'text-amber-400';
                        dotClass = 'bg-amber-500';
                    } else if (hasReported) {
                        statusText = 'Selesai Tugas';
                        statusClass = 'text-emerald-400 font-black'; // Tetap emerald tapi lebih tegas
                    }

                    const cardClass = role === 'admin' ? 'bg-blue-500/10 border-blue-500/20' : 'bg-emerald-500/10 border-emerald-500/20';
                    const icon = role === 'admin' ? 'lucide:shield-check' : 'lucide:user';
                    const iconColor = role === 'admin' ? 'text-blue-400' : 'text-slate-400';

                    if (!card) {
                        card = document.createElement('div'); card.id = 'op-card-' + uid;
                        container.appendChild(card);
                    }
                    card.className = `p-2 ${cardClass} rounded-xl border flex items-center gap-3 transition-all duration-300`;
                    card.innerHTML = `
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center border-2 border-white/10">
                                <iconify-icon icon="${icon}" class="text-lg ${iconColor}"></iconify-icon>
                            </div>
                            <div id="op-dot-${uid}" class="absolute bottom-0 right-0 w-2.5 h-2.5 ${dotClass} rounded-full border-2 border-[#0F172A]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex justify-between items-start">
                                <p class="text-[11px] font-bold text-white truncate">${u.username || 'User'}</p>
                                <span class="text-[7px] px-1 rounded bg-white/10 text-white/50 font-black uppercase tracking-tighter">${role}</span>
                            </div>
                            <p id="op-text-${uid}" class="text-[8px] ${statusClass} animate-pulse font-bold uppercase">
                                <span class="animate-pulse">●</span> ${statusText}
                            </p>
                        </div>`;
                } else if (card) {
                    card.style.opacity = '0'; card.style.transform = 'translateX(15px)'; setTimeout(() => card.remove(), 300);
                }
            }

            db.ref('users').on('value', (snap) => {
                const users = snap.val(); let onlineCount = 0;
                if (users) { 
                    for (let uid in users) { 
                        const u = users[uid]; 
                        const isOnline = u.is_online === true || u.is_online === 'true' || u.is_online == 1; 
                        if (isOnline) onlineCount++; 
                        updateOperatorStatus(uid, u); 
                    } 
                }
                document.getElementById('onlineCount').innerText = `${onlineCount} ONLINE`;
            });

            db.ref('.info/connected').on('value', (s) => { if(s.val()) setConnStatus('ONLINE', true); else setConnStatus('OFFLINE'); });

            // REAL-TIME ACTIVITY LOGS
            db.ref('activity_logs').limitToLast(15).on('child_added', (snapshot) => {
                const log = snapshot.val();
                if (log && log.message) {
                    const logTime = new Date(log.timestamp).getTime();
                    // Hanya tampilkan jika log terjadi dalam 1 jam terakhir untuk menghindari banjir data saat load
                    if (logTime > Date.now() - 3600000) {
                        addLog(log.message, log.type);
                    }
                }
            });

            // Listener Penugasan untuk memfilter lokasi yang aktif hari ini
            db.ref('penugasan').on('value', (snap) => {
                const data = snap.val();
                allPenugasan = data || {}; // Simpan ke variabel global untuk digunakan di updateOperatorStatus
                activeLocIds.clear();
                if (data) {
                    const now = new Date();
                    for (let id in data) {
                        const t = data[id];
                        if (t.status === 'aktif') {
                            const mulai = new Date(t.waktu_mulai);
                            const selesai = new Date(t.waktu_selesai);
                            if (now >= mulai && now <= selesai) {
                                if (t.id_lokasi) activeLocIds.add(t.id_lokasi.toString());
                            }
                        }
                    }
                }
                // Paksa update UI setelah daftar lokasi aktif berubah
                if (prevData) updateUI(prevData, lastNewLokasi);
            });

            let prevData = null;
            let lastNewLokasi = {};
            db.ref('survei_harian').on('value', (snap) => {
                const dataRaw = snap.val(); 
                let newGlobal = {}; 
                let newLokasiTotal = {};
                detailedLokasi = {}; // Reset data detail
                
                validKeys.forEach(k => newGlobal[k] = 0);
                
                if (dataRaw) {
                    for (let idL in dataRaw) {
                        newLokasiTotal[idL] = 0;
                        detailedLokasi[idL] = {};
                        validKeys.forEach(k => detailedLokasi[idL][k] = 0);

                        if(dataRaw[idL][today]) {
                            const tgl = dataRaw[idL][today];
                            for (let j in tgl) { 
                                for (let p in tgl[j]) { 
                                    const s = tgl[j][p]; 
                                    if(s) {
                                        validKeys.forEach(k => { 
                                            const v = parseInt(s[k] || 0); 
                                            newGlobal[k] += v; 
                                            newLokasiTotal[idL] += v; 
                                            detailedLokasi[idL][k] += v;
                                        }); 
                                    }
                                } 
                            }
                        }
                    }
                }

                prevData = {...newGlobal};
                lastNewLokasi = {...newLokasiTotal};
                updateUI(newGlobal, newLokasiTotal);
                updatePeakHours(dataRaw);
            });
        } catch (e) { console.error(e); }

        function updatePeakHours(dataRaw) {
            const marquee = document.getElementById('peakHoursMarquee');
            if (!dataRaw) return;

            const startHour = 7;
            const endHour = 22;
            const hours = [];
            for(let h=startHour; h<=endHour; h++) hours.push(h.toString().padStart(2, '0'));

            let peakInfo = [];
            
            // Urutkan lokasi berdasarkan volume tertinggi agar yang ramai muncul duluan
            const sortedLocIds = Object.keys(lokasiMaster).filter(id => activeLocIds.has(id));

            sortedLocIds.forEach(idL => {
                const locName = lokasiMaster[idL].nama_lokasi;
                const dailyData = dataRaw[idL] ? dataRaw[idL][today] : null;
                
                let maxCount = 0;
                let peakHour = null;

                hours.forEach(h => {
                    let totalHour = 0;
                    if (dailyData && dailyData[h]) {
                        for (let p in dailyData[h]) {
                            const s = dailyData[h][p];
                            validKeys.forEach(k => { totalHour += parseInt(s[k] || 0); });
                        }
                    }
                    if (totalHour > maxCount) {
                        maxCount = totalHour;
                        peakHour = h;
                    }
                });

                if (peakHour) {
                    peakInfo.push(`<span class="text-blue-500 font-bold">[ANALISIS TRAFIK]</span> <span class="text-white font-semibold">${locName}:</span> Peak Hour ${peakHour}:00 hrs (${maxCount} units)`);
                }
            });

            if (peakInfo.length > 0) {
                marquee.innerHTML = peakInfo.join(' <span class="mx-4 text-slate-600">|</span> ');
            } else {
                marquee.innerHTML = "Tidak ada penugasan aktif saat ini.";
            }
        }

        function updateUI(newGlobal, newLokasiTotal) {
            let total = 0;
            validKeys.forEach(k => {
                const el = document.getElementById('count-' + k); const v = newGlobal[k] || 0; total += v;
                if (el && parseInt(el.innerText) !== v) { el.innerText = v; el.classList.add('pulse-update'); setTimeout(() => el.classList.remove('pulse-update'), 500); }
            });
            document.getElementById('totalGlobal').innerText = total;

            const labels = []; const values = [];
            const lokasiListContainer = document.getElementById('lokasiList');
            const incomeTable = document.getElementById('incomeTable');
            incomeTable.innerHTML = '';
            
            let totalGlobalIncome = 0;
            
            // Filter hanya lokasi yang ada dalam penugasan aktif
            const filteredLocIds = Object.keys(lokasiMaster).filter(id => activeLocIds.has(id));
            const sorted = filteredLocIds.sort((a,b) => (newLokasiTotal[b] || 0) - (newLokasiTotal[a] || 0));
            
            // Bersihkan UI yang tidak aktif
            const currentCards = lokasiListContainer.querySelectorAll('.location-item');
            currentCards.forEach(card => {
                const id = card.querySelector('p[id^="loc-total-"]').id.replace('loc-total-', '');
                if (!activeLocIds.has(id)) card.remove();
            });

            sorted.forEach(id => {
                const count = newLokasiTotal[id] || 0;
                
                // Hitung Pendapatan per Lokasi
                let locIncome = 0;
                if (detailedLokasi[id]) {
                    validKeys.forEach(k => {
                        locIncome += (detailedLokasi[id][k] || 0) * (objekPrices[k] || 0);
                    });
                }
                totalGlobalIncome += locIncome;

                // Update Row Tabel Pendapatan
                const locNameForTable = lokasiMaster[id] ? lokasiMaster[id].nama_lokasi : ('Lokasi ' + id);
                const row = document.createElement('div');
                row.className = 'flex justify-between text-[10px] py-1 border-b border-white/5 last:border-0';
                row.innerHTML = `<span class="text-slate-300 truncate pr-2">${locNameForTable}</span><span class="text-emerald-400 font-mono font-bold">${formatRupiah(locIncome)}</span>`;
                incomeTable.appendChild(row);

                // Update Card Lokasi di bawah
                let el = document.getElementById('loc-total-' + id); 
                if (!el) {
                    const locData = lokasiMaster[id];
                    const newLocCard = document.createElement('div');
                    newLocCard.className = "flex-none p-3 bg-slate-800/50 rounded-2xl border border-white/5 min-w-[160px] location-item";
                    newLocCard.innerHTML = `<p class="text-[10px] font-bold text-slate-300 truncate loc-name">${locData.nama_lokasi}</p><p class="text-2xl font-extrabold text-blue-400 mt-1" id="loc-total-${id}">${count}</p>`;
                    lokasiListContainer.appendChild(newLocCard);
                    el = document.getElementById('loc-total-' + id);
                }
                if (el) el.innerText = count;
                labels.push(locNameForTable); values.push(count);
            });

            document.getElementById('totalIDR').innerText = formatRupiah(totalGlobalIncome);
            
            if (incomeTable.innerHTML === '') {
                incomeTable.innerHTML = '<div class="text-[10px] text-slate-500 italic py-2 text-center">Tidak ada penugasan aktif</div>';
            }

            lokasiChart.data.labels = labels; 
            lokasiChart.data.datasets[0].data = values; 
            lokasiChart.update('none');
        }
    </script>
</body>
</html>