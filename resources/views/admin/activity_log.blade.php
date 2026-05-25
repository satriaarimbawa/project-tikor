@extends('admin.template.app')

@section('title', 'Log Aktivitas Operator')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-[30px] shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-[#253D6B] p-8 text-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold flex items-center gap-3">
                        <iconify-icon icon="lucide:history" class="text-3xl text-yellow-400"></iconify-icon>
                        Log Aktivitas Operator
                    </h2>
                    <p class="text-white/60 text-sm mt-1">Riwayat login, logout, dan pelanggaran radius operator secara real-time.</p>
                </div>
                <div class="bg-white/10 px-4 py-2 rounded-xl backdrop-blur-sm border border-white/10 text-center">
                    <p class="text-[10px] uppercase font-bold text-white/50 tracking-widest">Total Aktivitas</p>
                    <p class="text-2xl font-black text-white">{{ count($logs) }}</p>
                </div>
            </div>
            
            <!-- SEARCH & FILTER -->
            <form action="{{ url()->current() }}" method="GET" class="mt-8 flex flex-wrap gap-4 items-center bg-white/5 p-4 rounded-2xl border border-white/10">
                <div class="flex-1 min-w-[250px] relative">
                    <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40"></iconify-icon>
                    <input type="text" name="search" value="{{ $searchTerm }}" placeholder="Cari username atau pesan..." 
                        class="w-full bg-white/10 border border-white/10 rounded-xl py-2.5 pl-11 pr-4 text-white text-sm placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-yellow-400/50 transition-all">
                </div>
                
                <div class="w-full md:w-auto relative">
                    <iconify-icon icon="lucide:calendar" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/40"></iconify-icon>
                    <input type="date" name="date" value="{{ $searchDate }}" 
                        class="w-full md:w-auto bg-white/10 border border-white/10 rounded-xl py-2.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400/50 transition-all [color-scheme:dark]">
                </div>

                <div class="flex items-center gap-2 bg-white/5 px-3 py-1 rounded-xl border border-white/10">
                    <span class="text-[10px] font-bold text-white/50 uppercase whitespace-nowrap">Tampilkan:</span>
                    <select name="perPage" onchange="this.form.submit()" class="bg-transparent text-white text-sm focus:outline-none cursor-pointer appearance-none px-2 py-1.5 min-w-[60px] text-center">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }} class="bg-[#253D6B]">10</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }} class="bg-[#253D6B]">25</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }} class="bg-[#253D6B]">50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }} class="bg-[#253D6B]">100</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-[#253D6B] font-bold px-6 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm">
                        <iconify-icon icon="lucide:filter"></iconify-icon>
                        Filter
                    </button>
                    @if($searchTerm || $searchDate)
                        <a href="{{ url()->current() }}" class="bg-white/10 hover:bg-white/20 text-white font-bold px-6 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm border border-white/10">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="p-6 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[#253D6B] font-bold text-sm uppercase tracking-wider border-b-2 border-gray-50">
                        <th class="px-4 py-4">Waktu</th>
                        <th class="px-4 py-4">Operator</th>
                        <th class="px-4 py-4">Aktivitas</th>
                        <th class="px-4 py-4 text-center">Tipe</th>
                    </tr>
                </thead>
                <tbody id="logTableBody" class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                    <tr id="log-{{ $log['id'] }}" class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($log['timestamp'])->translatedFormat('d F Y') }}</span>
                                <span class="text-xs text-blue-500 font-mono">{{ \Carbon\Carbon::parse($log['timestamp'])->format('H:i:s') }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[#253D6B] font-bold text-xs border border-gray-200">
                                    {{ substr($log['username'], 0, 1) }}
                                </div>
                                <span class="font-semibold text-gray-700">{{ $log['username'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {!! $log['message'] !!}
                            </p>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @php
                                $badgeClass = match($log['type']) {
                                    'login' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                    'logout' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    'violation' => 'bg-red-100 text-red-600 border-red-200',
                                    default => 'bg-blue-100 text-blue-600 border-blue-200'
                                };
                                $icon = match($log['type']) {
                                    'login' => 'lucide:log-in',
                                    'logout' => 'lucide:log-out',
                                    'violation' => 'lucide:alert-triangle',
                                    default => 'lucide:info'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $badgeClass }}">
                                <iconify-icon icon="{{ $icon }}"></iconify-icon>
                                {{ $log['type'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <iconify-icon icon="lucide:database" class="text-5xl text-gray-200"></iconify-icon>
                                <p class="text-gray-400 italic">Belum ada riwayat aktivitas yang tercatat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-6 border-t border-gray-50 flex justify-center items-center gap-4">
            <a href="{{ $currentPage > 1 ? url()->current().'?page='.($currentPage - 1).'&search='.$searchTerm.'&date='.$searchDate.'&perPage='.$perPage : '#' }}" 
                class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-xl shadow-sm hover:bg-[#1a2e52] transition-all text-white {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                <iconify-icon icon="lucide:chevron-left" class="text-xl"></iconify-icon>
            </a>
            
            <span class="text-sm font-bold text-gray-600">Halaman {{ $currentPage }} dari {{ $totalPages }}</span>

            <a href="{{ $currentPage < $totalPages ? url()->current().'?page='.($currentPage + 1).'&search='.$searchTerm.'&date='.$searchDate.'&perPage='.$perPage : '#' }}" 
                class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-xl shadow-sm hover:bg-[#1a2e52] transition-all text-white {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : '' }}">
                <iconify-icon icon="lucide:chevron-right" class="text-xl"></iconify-icon>
            </a>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }
</style>
@endsection

@section('extra_js')
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>

<script>
    // Konfigurasi Firebase dari Laravel
    const firebaseConfig = @json($firebaseConfig);
    
    // Pastikan databaseURL terisi (mengambil dari config Laravel)
    if (!firebaseConfig.databaseURL) {
        firebaseConfig.databaseURL = "{{ config('firebase.projects.app.database.url') }}";
    }
    
    // Inisialisasi Firebase
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    const database = firebase.database();

    // Referensi ke activity_logs
    const logRef = database.ref('activity_logs');
    const tableBody = document.getElementById('logTableBody');
    const pageLoadTime = Date.now();

    // Listener untuk data baru
    logRef.limitToLast(10).on('child_added', (snapshot) => {
        const log = snapshot.val();
        const logId = snapshot.key;

        // 1. Cek apakah log sudah ada di tabel untuk menghindari duplikasi
        if (document.getElementById(`log-${logId}`)) {
            return;
        }

        // 2. Filter tipe log yang ingin ditampilkan
        if (['login', 'logout', 'violation'].includes(log.type)) {
            renderNewLogRow(logId, log);
        }
    }, (error) => {
        console.error("Firebase Error:", error);
    });

    function renderNewLogRow(id, data) {
        const body = document.getElementById('logTableBody');
        if (!body) return;

        // Hapus baris "Data Kosong" jika ada
        const emptyRow = body.querySelector('td[colspan="4"]');
        if (emptyRow) body.parentElement.parentElement.innerHTML = '<tbody id="logTableBody" class="divide-y divide-gray-50"></tbody>';
        
        const currentBody = document.getElementById('logTableBody');

        const date = new Date(data.timestamp);
        const day = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        const time = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');

        let badgeClass = '';
        let icon = '';
        
        switch(data.type) {
            case 'login':
                badgeClass = 'bg-emerald-100 text-emerald-600 border-emerald-200';
                icon = 'lucide:log-in';
                break;
            case 'logout':
                badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                icon = 'lucide:log-out';
                break;
            case 'violation':
                badgeClass = 'bg-red-100 text-red-600 border-red-200';
                icon = 'lucide:alert-triangle';
                break;
            default:
                badgeClass = 'bg-blue-100 text-blue-600 border-blue-200';
                icon = 'lucide:info';
        }

        const newRow = `
            <tr id="log-${id}" class="hover:bg-gray-50/50 transition-colors group animate-slide-in">
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700">${day}</span>
                        <span class="text-xs text-blue-500 font-mono">${time}</span>
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[#253D6B] font-bold text-xs border border-gray-200">
                            ${data.username ? data.username.charAt(0).toUpperCase() : '?'}
                        </div>
                        <span class="font-semibold text-gray-700">${data.username || 'Unknown'}</span>
                    </div>
                </td>
                <td class="px-4 py-4">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        ${data.message || '-'}
                    </p>
                </td>
                <td class="px-4 py-4 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider ${badgeClass}">
                        <iconify-icon icon="${icon}"></iconify-icon>
                        ${data.type}
                    </span>
                </td>
            </tr>
        `;

        // Masukkan di paling atas tabel
        tableBody.insertAdjacentHTML('afterbegin', newRow);
        
        // Update Counter (Opsional jika ingin real-time juga angkanya)
        const counter = document.querySelector('.text-2xl.font-black.text-white');
        if (counter) {
            counter.innerText = parseInt(counter.innerText) + 1;
        }
    }
</script>

<style>
    @keyframes slide-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-in {
        animation: slide-in 0.4s ease-out forwards;
    }
</style>
@endsection
