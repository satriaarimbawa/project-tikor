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
        @keyframes pulse-blue { 0% { transform: scale(1); color: white; } 50% { transform: scale(1.08); color: #38BDF8; } 100% { transform: scale(1); color: white; } }
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
                <h1 class="text-xl font-extrabold tracking-tight flex items-center gap-2">
                    COMMAND CENTER
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-red-500/20 text-red-400 border border-red-500/30 animate-pulse">
                        ● LIVE STREAM
                    </span>
                </h1>
                <p class="text-xs text-blue-400 font-bold uppercase tracking-widest">Real-time Traffic Monitoring Uji Petik</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <a href="/dashboard-admin" class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 border border-white/10 transition-all">
                <iconify-icon icon="lucide:layout-dashboard"></iconify-icon>
                <span>Ke Admin Panel</span>
            </a>
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
    <div class="flex-1 grid grid-cols-12 gap-4 min-h-0 overflow-y-auto custom-scrollbar pr-1 lg:overflow-visible">
        
        <!-- KIRI: TOTAL & OBJEK -->
        <div class="col-span-12 lg:col-span-4 xl:col-span-3 flex flex-col gap-4">
            <div class="glass-card p-6 rounded-3xl flex-1 flex flex-col items-center text-center relative overflow-hidden">
                <div class="mt-4">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Total Kendaraan Hari Ini</p>
                    <h2 id="totalGlobal" class="text-8xl font-black text-white stat-value tracking-tight">{{ $totalGlobal }}</h2>
                </div>
                
                <!-- TABEL PENDAPATAN -->
                <div class="mt-auto w-full bg-black/30 rounded-2xl p-4 border border-white/5 flex flex-col">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-[10px] text-yellow-400 font-bold uppercase text-left">Estimasi Pendapatan per Pos</p>
                        <iconify-icon icon="lucide:banknote" class="text-yellow-400 text-sm"></iconify-icon>
                    </div>
                    <div id="incomeTable" class="overflow-y-auto custom-scrollbar pr-1 max-h-[110px] min-h-[40px]">
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
                <div class="glass-card p-3 rounded-2xl border-l-4 border-blue-500 flex flex-col justify-between">
                    <p class="text-white text-xs font-bold uppercase truncate">{{ $name }}</p>
                    <p id="count-{{ $key }}" class="text-2xl font-extrabold stat-value text-blue-400 mt-1">{{ $initialGlobal[$key] ?? 0 }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- TENGAH: DIAGRAM & CARD LOKASI -->
        <div class="col-span-12 lg:col-span-8 xl:col-span-6 flex flex-col gap-4">
            <div class="glass-card p-6 rounded-3xl flex-1 min-h-0 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-yellow-400 text-sm font-bold uppercase tracking-wider">Distribusi Kendaraan Per Pos Uji</h3>
                    <span class="text-[10px] text-slate-400 font-mono">Real-time Volume</span>
                </div>
                <div class="flex-1 relative w-full">
                    <canvas id="lokasiChart"></canvas>
                </div>
            </div>
            <div class="glass-card p-4 rounded-3xl shrink-0">
                <h3 class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-3">Volume Tiap Pos (Real-time)</h3>
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
        <div class="col-span-12 xl:col-span-3 flex flex-col gap-4 min-h-0">
            <!-- PERSONIL AKTIF -->
            <div class="glass-card p-5 rounded-3xl flex-[4] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center mb-4 shrink-0">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Operator Online</h3>
                    <span id="onlineCount" class="bg-emerald-500/20 text-emerald-400 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border border-emerald-500/30">0 ONLINE</span>
                </div>
                <div id="operatorList" class="flex-1 overflow-y-auto pr-1 flex flex-col gap-2 custom-scrollbar">
                    <div class="text-[10px] text-slate-500 italic text-center py-4">Memantau operator...</div>
                </div>
            </div>

            <!-- LOG AKTIVITAS -->
            <div class="glass-card p-5 rounded-3xl flex-[6] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center mb-3 shrink-0">
                    <h3 class="text-slate-400 text-xs font-bold uppercase tracking-wider">Log Aktivitas Real-time</h3>
                    <iconify-icon icon="lucide:history" class="text-slate-500"></iconify-icon>
                </div>
                <div id="activityLog" class="flex-1 overflow-y-auto pr-2 flex flex-col gap-2.5 custom-scrollbar">
                    <div class="text-[10px] text-slate-500 italic text-center py-4">Menunggu aktivitas survei...</div>
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
                    Menganalisis data jam puncak di seluruh pos penugasan...
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
        let lokasiMaster = @json($lokasiMaster) || {};
        const lokasiNamesMap = @json($lokasiNamesMap) || {};
        let allPenugasan = {};
        const validKeys = Object.keys(objekNames);
        let activeLocIds = new Set();
        
        // Penanggalan robust: Dukung tanggal server dan lokal
        const serverToday = "{{ \Carbon\Carbon::now('Asia/Makassar')->toDateString() }}";
        function getClientToday() {
            const d = new Date();
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        const today = serverToday || getClientToday();
        
        const formatRupiah = (num) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num || 0);
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#94a3b8', font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 10, weight: 'bold' } }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        function setConnStatus(status, isOnline = false) {
            const dot = document.getElementById('connDot');
            const text = document.getElementById('connText');
            const container = document.getElementById('connStatus');
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
            if (!logContainer) return;
            const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if (logContainer.querySelector('div.italic')) logContainer.innerHTML = '';
            const colors = {
                'login': 'border-emerald-500 bg-emerald-500/5',
                'logout': 'border-slate-500 bg-slate-500/5',
                'update': 'border-blue-500 bg-blue-500/5',
                'count': 'border-cyan-400 bg-cyan-500/10',
                'violation': 'border-red-500 bg-red-500/5',
                'info': 'border-slate-700 bg-slate-700/5'
            };
            const icons = {
                'login': 'lucide:log-in text-emerald-500',
                'logout': 'lucide:log-out text-slate-400',
                'update': 'lucide:database text-blue-500',
                'count': 'lucide:check-circle-2 text-cyan-400',
                'violation': 'lucide:alert-triangle text-red-500',
                'info': 'lucide:info text-slate-500'
            };
            const div = document.createElement('div');
            div.className = `log-entry p-2.5 rounded-xl border-l-4 ${colors[type] || colors.info} flex items-start gap-2.5`;
            div.innerHTML = `<div class="mt-0.5"><iconify-icon icon="${icons[type] || icons.info}" class="text-sm"></iconify-icon></div><div class="min-w-0 flex-1"><p class="text-[11px] text-white leading-tight">${message}</p><p class="text-[9px] text-slate-400 mt-0.5 font-mono">${time}</p></div>`;
            logContainer.prepend(div);
            if (logContainer.children.length > 25) logContainer.lastElementChild.remove();
        }

        const prevStatus = {};
        let detailedLokasi = {};

        try {
            if (!firebase.apps.length) { firebase.initializeApp(firebaseConfig); }
            const db = firebase.database();
            const emulatorHost = "{{ env('FIREBASE_DATABASE_EMULATOR_HOST') }}";
            if (emulatorHost) {
                const parts = emulatorHost.split(':');
                db.useEmulator(parts[0], parseInt(parts[1]) || 9000);
            }
            
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
                const isStale = role === 'operator' && lastSeen > 0 && (now - lastSeen) > 300; 
                
                let isOnline = u.is_online === true || u.is_online === 'true' || u.is_online == 1;
                
                if (isOnline && isStale) {
                    isOnline = false;
                }

                let currentLocationName = u.location || 'Pos Uji Klungkung';
                let hasReported = false;

                if (role === 'operator') {
                    for (let idTugas in allPenugasan) {
                        const t = allPenugasan[idTugas];
                        if (t.id_user == uid && (t.status === 'aktif' || t.status === 'active')) {
                            currentLocationName = lokasiNamesMap[t.id_lokasi] || (lokasiMaster[t.id_lokasi]?.nama_lokasi) || 'Pos Uji';
                            if (t.laporan_harian && (t.laporan_harian[today] || t.laporan_harian[getClientToday()])) {
                                hasReported = true;
                            }
                            break;
                        }
                    }
                } else if (role === 'admin') {
                    currentLocationName = 'Administrator';
                }

                let card = document.getElementById('op-card-' + uid);
                const container = document.getElementById('operatorList');
                if (!container) return;
                
                // Hapus placeholder jika ada
                const placeholder = container.querySelector('div.italic');
                if (placeholder && isOnline) placeholder.remove();

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
                        statusClass = 'text-emerald-400 font-black';
                    }

                    const cardClass = role === 'admin' ? 'bg-blue-500/10 border-blue-500/20' : 'bg-emerald-500/10 border-emerald-500/20';
                    const icon = role === 'admin' ? 'lucide:shield-check' : 'lucide:user';
                    const iconColor = role === 'admin' ? 'text-blue-400' : 'text-emerald-400';

                    if (!card) {
                        card = document.createElement('div'); card.id = 'op-card-' + uid;
                        container.appendChild(card);
                    }
                    card.className = `p-2.5 ${cardClass} rounded-2xl border flex items-center gap-3 transition-all duration-300`;
                    card.innerHTML = `
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center border-2 border-white/10">
                                <iconify-icon icon="${icon}" class="text-base ${iconColor}"></iconify-icon>
                            </div>
                            <div id="op-dot-${uid}" class="absolute bottom-0 right-0 w-2.5 h-2.5 ${dotClass} rounded-full border-2 border-[#0F172A]"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex justify-between items-start">
                                <p class="text-[11px] font-bold text-white truncate">${u.username || 'User'}</p>
                                <span class="text-[7px] px-1 rounded bg-white/10 text-white/50 font-black uppercase tracking-tighter">${role}</span>
                            </div>
                            <p id="op-text-${uid}" class="text-[9px] ${statusClass} font-bold uppercase truncate mt-0.5">
                                <span class="animate-pulse">●</span> ${statusText}
                            </p>
                        </div>`;
                } else if (card) {
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(15px)';
                    setTimeout(() => card.remove(), 300);
                }
            }

            db.ref('users').on('value', (snap) => {
                const users = snap.val();
                let onlineCount = 0;
                if (users) { 
                    for (let uid in users) { 
                        const u = users[uid]; 
                        if (u.username === 'IT Support' || (u.email && u.email.toLowerCase().includes('itsupport'))) {
                            continue;
                        }
                        let isOnline = u.is_online === true || u.is_online === 'true' || u.is_online == 1; 
                        if (isOnline) onlineCount++; 
                        updateOperatorStatus(uid, u); 
                    } 
                }
                const countEl = document.getElementById('onlineCount');
                if (countEl) countEl.innerText = `${onlineCount} ONLINE`;
            });

            db.ref('.info/connected').on('value', (s) => {
                if(s.val()) setConnStatus('ONLINE', true);
                else setConnStatus('OFFLINE');
            });

            // REAL-TIME ACTIVITY LOGS
            db.ref('activity_logs').limitToLast(20).on('child_added', (snapshot) => {
                const log = snapshot.val();
                if (log && log.username && log.username.toLowerCase().includes('it support')) return;
                if (log && log.message) {
                    addLog(log.message, log.type || 'info');
                }
            });

            // Listener Penugasan
            db.ref('penugasan').on('value', (snap) => {
                const data = snap.val();
                allPenugasan = data || {};
                activeLocIds.clear();
                if (data) {
                    for (let id in data) {
                        const t = data[id];
                        if (t.id_lokasi) activeLocIds.add(t.id_lokasi.toString());
                    }
                }
                if (prevData) updateUI(prevData, lastNewLokasi);
            });

            let prevData = null;
            let lastNewLokasi = {};
            let lastTotalGlobal = -1;

            db.ref('survei_harian').on('value', (snap) => {
                const dataRaw = snap.val() || {}; 
                let newGlobal = {}; 
                let newLokasiTotal = {};
                detailedLokasi = {};
                
                validKeys.forEach(k => newGlobal[k] = 0);
                const datesToCheck = Array.from(new Set([today, getClientToday()]));

                for (let idL in dataRaw) {
                    newLokasiTotal[idL] = 0;
                    detailedLokasi[idL] = {};
                    validKeys.forEach(k => detailedLokasi[idL][k] = 0);

                    datesToCheck.forEach(dateKey => {
                        if (dataRaw[idL] && dataRaw[idL][dateKey]) {
                            const tgl = dataRaw[idL][dateKey];
                            for (let j in tgl) { 
                                for (let p in tgl[j]) { 
                                    const s = tgl[j][p]; 
                                    if(s) {
                                        validKeys.forEach(k => { 
                                            const subKeys = k.split(',');
                                            let v = 0;
                                            subKeys.forEach(sub => {
                                                v += parseInt(s[sub] || 0);
                                            });
                                            newGlobal[k] += v; 
                                            newLokasiTotal[idL] += v; 
                                            detailedLokasi[idL][k] += v;
                                        }); 
                                    }
                                } 
                            }
                        }
                    });
                }

                // Hitung total baru
                let currentTotal = 0;
                validKeys.forEach(k => currentTotal += (newGlobal[k] || 0));

                if (lastTotalGlobal !== -1 && currentTotal > lastTotalGlobal) {
                    const added = currentTotal - lastTotalGlobal;
                    addLog(`Hitungan survei baru masuk (+${added} kendaraan). Total: ${currentTotal}`, 'count');
                }
                lastTotalGlobal = currentTotal;

                prevData = {...newGlobal};
                lastNewLokasi = {...newLokasiTotal};
                updateUI(newGlobal, newLokasiTotal);
                updatePeakHours(dataRaw);
            });
        } catch (e) { console.error('Firebase realtime error:', e); }

        function updatePeakHours(dataRaw) {
            const marquee = document.getElementById('peakHoursMarquee');
            if (!marquee || !dataRaw) return;

            const startHour = 0;
            const endHour = 23;
            const hours = [];
            for(let h=startHour; h<=endHour; h++) hours.push(h.toString().padStart(2, '0'));

            let peakInfo = [];
            const datesToCheck = Array.from(new Set([today, getClientToday()]));
            const allLocKeys = Object.keys(lokasiMaster || {});

            allLocKeys.forEach(idL => {
                const locData = lokasiMaster[idL] || {};
                const locName = locData.nama_lokasi || ('Pos ' + idL);
                
                let maxCount = 0;
                let peakHour = null;

                datesToCheck.forEach(dateKey => {
                    const dailyData = dataRaw[idL] ? dataRaw[idL][dateKey] : null;
                    if (dailyData) {
                        hours.forEach(h => {
                            let totalHour = 0;
                            if (dailyData[h]) {
                                for (let p in dailyData[h]) {
                                    const s = dailyData[h][p];
                                    validKeys.forEach(k => { 
                                        const subKeys = k.split(',');
                                        subKeys.forEach(sub => {
                                            totalHour += parseInt(s[sub] || 0); 
                                        });
                                    });
                                }
                            }
                            if (totalHour > maxCount) {
                                maxCount = totalHour;
                                peakHour = h;
                            }
                        });
                    }
                });

                if (peakHour && maxCount > 0) {
                    peakInfo.push(`<span class="text-cyan-400 font-bold">[ANALISIS TRAFIK]</span> <span class="text-white font-semibold">${locName}:</span> Peak Hour ${peakHour}:00 (${maxCount} unit)`);
                }
            });

            if (peakInfo.length > 0) {
                marquee.innerHTML = peakInfo.join(' <span class="mx-4 text-slate-600">|</span> ');
            } else {
                marquee.innerHTML = "Survei aktif sedang berlangsung. Menunggu data jam puncak...";
            }
        }

        function updateUI(newGlobal, newLokasiTotal) {
            let total = 0;
            validKeys.forEach(k => {
                const el = document.getElementById('count-' + k);
                const v = newGlobal[k] || 0;
                total += v;
                if (el && parseInt(el.innerText || '0') !== v) {
                    el.innerText = v;
                    el.classList.add('pulse-update');
                    setTimeout(() => el.classList.remove('pulse-update'), 500);
                }
            });

            const totalEl = document.getElementById('totalGlobal');
            if (totalEl) {
                if (parseInt(totalEl.innerText || '0') !== total) {
                    totalEl.classList.add('pulse-update');
                    setTimeout(() => totalEl.classList.remove('pulse-update'), 500);
                }
                totalEl.innerText = total;
            }

            const labels = [];
            const values = [];
            const lokasiListContainer = document.getElementById('lokasiList');
            const incomeTable = document.getElementById('incomeTable');
            if (incomeTable) incomeTable.innerHTML = '';
            
            let totalGlobalIncome = 0;
            
            const allKnownLocIds = new Set([
                ...Object.keys(lokasiMaster || {}),
                ...Object.keys(newLokasiTotal || {}),
                ...Array.from(activeLocIds)
            ]);

            const sorted = Array.from(allKnownLocIds).sort((a,b) => (newLokasiTotal[b] || 0) - (newLokasiTotal[a] || 0));

            sorted.forEach(id => {
                const count = newLokasiTotal[id] || 0;
                const locData = (lokasiMaster && lokasiMaster[id]) ? lokasiMaster[id] : { nama_lokasi: lokasiNamesMap[id] || ('Pos ' + id) };
                const locNameForTable = locData.nama_lokasi || ('Pos ' + id);

                // Hitung Pendapatan per Lokasi
                let locIncome = 0;
                if (detailedLokasi[id]) {
                    validKeys.forEach(k => {
                        locIncome += (detailedLokasi[id][k] || 0) * (objekPrices[k] || 0);
                    });
                }
                totalGlobalIncome += locIncome;

                // Update Row Tabel Pendapatan
                if (incomeTable) {
                    const row = document.createElement('div');
                    row.className = 'flex justify-between text-[10px] py-1 border-b border-white/5 last:border-0';
                    row.innerHTML = `<span class="text-slate-300 truncate pr-2">${locNameForTable}</span><span class="text-emerald-400 font-mono font-bold">${formatRupiah(locIncome)}</span>`;
                    incomeTable.appendChild(row);
                }

                // Update Card Lokasi di bawah
                let el = document.getElementById('loc-total-' + id); 
                if (!el && lokasiListContainer) {
                    const newLocCard = document.createElement('div');
                    newLocCard.className = "flex-none p-3 bg-slate-800/50 rounded-2xl border border-white/5 min-w-[160px] location-item";
                    newLocCard.innerHTML = `<p class="text-[10px] font-bold text-slate-300 truncate loc-name">${locNameForTable}</p><p class="text-2xl font-extrabold text-blue-400 mt-1" id="loc-total-${id}">${count}</p>`;
                    lokasiListContainer.appendChild(newLocCard);
                    el = document.getElementById('loc-total-' + id);
                }
                if (el) el.innerText = count;
                labels.push(locNameForTable);
                values.push(count);
            });

            const totalIDREl = document.getElementById('totalIDR');
            if (totalIDREl) totalIDREl.innerText = formatRupiah(totalGlobalIncome);
            
            if (incomeTable && incomeTable.innerHTML === '') {
                incomeTable.innerHTML = '<div class="text-[10px] text-slate-500 italic py-2 text-center">Belum ada aktivitas hari ini</div>';
            }

            if (lokasiChart) {
                lokasiChart.data.labels = labels; 
                lokasiChart.data.datasets[0].data = values; 
                lokasiChart.update('none');
            }
        }
    </script>
</body>
</html>
