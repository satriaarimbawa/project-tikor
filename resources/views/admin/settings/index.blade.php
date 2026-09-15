<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - IT Support Command Center</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, #E7EFF6 0%, #F8FAFC 100%);
            min-height: 100vh;
        }

        .sidebar-navy {
            background-color: rgba(37, 61, 107, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        .tab-btn.active {
            background: #253D6B;
            color: #FFFFFF;
            box-shadow: 0 4px 14px 0 rgba(37, 61, 107, 0.35);
        }

        .tab-btn:not(.active) {
            background: #FFFFFF;
            color: #64748B;
        }

        .tab-btn:not(.active):hover {
            background: #F1F5F9;
            color: #1E293B;
        }
    </style>
</head>

<body class="flex">

    @include('admin.template.navbar')

    <main class="main-content lg:ml-64 p-4 md:p-8 flex-1 w-full max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#253D6B] flex items-center justify-center text-cyan-400 shadow-md">
                        <iconify-icon icon="lucide:sliders" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-[#253D6B] tracking-tight">Pengaturan Sistem</h1>
                        <p class="text-xs md:text-sm text-slate-500 font-medium">Pusat Kendali Konfigurasi Server, Radius Geofencing & Notifikasi IT Support</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    Akses Khusus IT Support
                </span>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-14 object-contain drop-shadow" alt="Logo Klungkung">
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-sm animate-fade-in">
                <iconify-icon icon="lucide:check-circle" class="text-xl text-emerald-600 shrink-0"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-3 shadow-sm animate-fade-in">
                <iconify-icon icon="lucide:alert-triangle" class="text-xl text-rose-600 shrink-0"></iconify-icon>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 mb-6 p-1.5 bg-slate-200/70 backdrop-blur rounded-2xl border border-slate-300/60 shadow-inner">
            <button onclick="switchTab('radius')" id="tab-btn-radius" class="tab-btn active flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all">
                <iconify-icon icon="lucide:map-pin" class="text-base"></iconify-icon>
                <span>Radius Operator</span>
            </button>
            <button onclick="switchTab('session')" id="tab-btn-session" class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all">
                <iconify-icon icon="lucide:shield-check" class="text-base"></iconify-icon>
                <span>Batas Multi-Login</span>
            </button>
            <button onclick="switchTab('telegram')" id="tab-btn-telegram" class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all">
                <iconify-icon icon="lucide:send" class="text-base text-sky-400"></iconify-icon>
                <span>Bot Telegram & Monitor</span>
            </button>
            <button onclick="switchTab('operational')" id="tab-btn-operational" class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all">
                <iconify-icon icon="lucide:clock" class="text-base"></iconify-icon>
                <span>Operasional</span>
            </button>
            <button onclick="switchTab('backup')" id="tab-btn-backup" class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all">
                <iconify-icon icon="lucide:database" class="text-base"></iconify-icon>
                <span>Cadangan Database</span>
            </button>
        </div>

        <!-- TAB 1: RADIUS GEOFENCING & OPERATOR -->
        <div id="tab-content-radius" class="tab-content">
            <form action="{{ route('settings.radius.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#253D6B] flex items-center justify-center font-bold">1</div>
                        <div>
                            <h2 class="text-lg font-bold text-[#253D6B]">Konfigurasi Global Geofencing</h2>
                            <p class="text-xs text-slate-500">Nilai standar sistem untuk seluruh titik pos survei</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Default Radius Global (Meter)</label>
                            <div class="relative">
                                <input type="number" name="default_radius" value="{{ old('default_radius', $geofencing['default_radius'] ?? 100) }}" min="10" max="5000" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                                <span class="absolute right-4 top-3 text-xs text-slate-400 font-bold">Meter</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Acuan jika operator tidak memiliki radius khusus.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Batas Jam Operasional (Cut-Off)</label>
                            <select name="cutoff_hour" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                                @for($i = 17; $i <= 23; $i++)
                                    <option value="{{ $i }}" {{ ($geofencing['cutoff_hour'] ?? 22) == $i ? 'selected' : '' }}>
                                        Pukul {{ sprintf('%02d:00', $i) }} WITA
                                    </option>
                                @endfor
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Sesi operator diakhiri otomatis setelah jam ini.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Interval Cek GPS (Heartbeat)</label>
                            <select name="heartbeat_interval_seconds" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                                <option value="30" {{ ($geofencing['heartbeat_interval_seconds'] ?? 60) == 30 ? 'selected' : '' }}>30 Detik (Sangat Akurat)</option>
                                <option value="60" {{ ($geofencing['heartbeat_interval_seconds'] ?? 60) == 60 ? 'selected' : '' }}>60 Detik (Rekomendasi Standar)</option>
                                <option value="120" {{ ($geofencing['heartbeat_interval_seconds'] ?? 60) == 120 ? 'selected' : '' }}>120 Detik (Hemat Baterai)</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Frekuensi refresh lokasi background di browser/HP.</p>
                        </div>
                    </div>
                </div>

                <!-- Custom Radius per Operator Table -->
                <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-slate-100 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#253D6B] flex items-center justify-center font-bold">2</div>
                            <div>
                                <h2 class="text-lg font-bold text-[#253D6B]">Radius Khusus per Operator Lapangan</h2>
                                <p class="text-xs text-slate-500">Atur toleransi jarak khusus untuk operator yang bertugas di pos bergedung/sinyal GPS lemah</p>
                            </div>
                        </div>
                        <div class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                            Total: {{ count($operators) }} Operator
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase border-b border-slate-100">
                                <tr>
                                    <th class="py-3.5 px-4">Nama Operator</th>
                                    <th class="py-3.5 px-4">Email</th>
                                    <th class="py-3.5 px-4 text-center">Status Sesi</th>
                                    <th class="py-3.5 px-4 w-48">Radius Khusus (Meter)</th>
                                    <th class="py-3.5 px-4 text-center">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($operators as $uid => $op)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-800">
                                            {{ $op['username'] }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 text-xs">
                                            {{ $op['email'] }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($op['is_online'])
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                                                    Offline
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="relative">
                                                <input type="number" 
                                                    name="operator_radius[{{ $uid }}]" 
                                                    id="op-rad-{{ $uid }}"
                                                    value="{{ $op['custom_radius'] ?? '' }}" 
                                                    placeholder="Default ({{ $geofencing['default_radius'] ?? 100 }}m)"
                                                    min="10" max="5000"
                                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                                                <span class="absolute right-3 top-2 text-[10px] text-slate-400 font-bold">M</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <button type="button" onclick="resetOpRadius('{{ $uid }}')" 
                                                class="text-xs font-semibold text-slate-400 hover:text-rose-600 transition px-2 py-1 rounded-lg hover:bg-rose-50">
                                                Reset ke Default
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 italic">
                                            Belum ada data operator yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                            <iconify-icon icon="lucide:save" class="text-lg"></iconify-icon>
                            Simpan Pengaturan Radius
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB 2: KEAMANAN SESI & BATAS MULTI-LOGIN -->
        <div id="tab-content-session" class="tab-content hidden">
            <form action="{{ route('settings.session.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <iconify-icon icon="lucide:smartphone" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-[#253D6B]">Kebijakan Batas Akses Multi-Device</h2>
                            <p class="text-xs text-slate-500">Tentukan berapa banyak perangkat yang diizinkan mengakses 1 akun yang sama secara bersamaan</p>
                        </div>
                    </div>

                    <div class="space-y-4 max-w-2xl mb-8">
                        @php $currentLimit = $sessionSecurity['max_sessions_per_account'] ?? 1; @endphp
                        
                        <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition {{ $currentLimit == 1 ? 'border-[#253D6B] bg-blue-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="max_sessions_per_account" value="1" {{ $currentLimit == 1 ? 'checked' : '' }} class="mt-1 text-[#253D6B] focus:ring-[#253D6B]">
                            <div>
                                <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    1 Perangkat Aktif (Strict Single Device) ⭐ Rekomendasi Lapangan
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">Jika akun sedang login di HP A, maka login dari HP B akan otomatis ditolak sampai HP A logout.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition {{ $currentLimit == 2 ? 'border-[#253D6B] bg-blue-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="max_sessions_per_account" value="2" {{ $currentLimit == 2 ? 'checked' : '' }} class="mt-1 text-[#253D6B] focus:ring-[#253D6B]">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Maksimal 2 Perangkat</p>
                                <p class="text-xs text-slate-500 mt-0.5">Mengizinkan 1 akun diakses dari 2 perangkat secara bersamaan (misal Laptop + HP).</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition {{ $currentLimit == 3 ? 'border-[#253D6B] bg-blue-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="max_sessions_per_account" value="3" {{ $currentLimit == 3 ? 'checked' : '' }} class="mt-1 text-[#253D6B] focus:ring-[#253D6B]">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Maksimal 3 Perangkat</p>
                                <p class="text-xs text-slate-500 mt-0.5">Maksimal 3 sesi aktif per akun.</p>
                            </div>
                        </label>

                        <label class="flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition {{ $currentLimit == 0 ? 'border-[#253D6B] bg-blue-50/40' : 'border-slate-200 hover:border-slate-300' }}">
                            <input type="radio" name="max_sessions_per_account" value="0" {{ $currentLimit == 0 ? 'checked' : '' }} class="mt-1 text-[#253D6B] focus:ring-[#253D6B]">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Tanpa Batas (Unlimited Multi-Device)</p>
                                <p class="text-xs text-slate-500 mt-0.5">Semua perangkat bebas login tanpa ada pembatasan sesi.</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-6 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Inactivity / Stale Timeout (Detik)</label>
                            <input type="number" name="stale_timeout_seconds" value="{{ old('stale_timeout_seconds', $sessionSecurity['stale_timeout_seconds'] ?? 300) }}" min="60" max="3600" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">Batas waktu akun dianggap offline jika browser/aplikasi ditutup mendadak (Default: 300s = 5 menit).</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                            <iconify-icon icon="lucide:save" class="text-lg"></iconify-icon>
                            Simpan Keamanan Sesi
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB 3: BOT TELEGRAM & MONITORING KESEHATAN -->
        <div id="tab-content-telegram" class="tab-content hidden space-y-6">
            <!-- Server Health Monitor Live Card -->
            <div class="bg-gradient-to-r from-[#1e345c] to-[#253D6B] rounded-[24px] p-6 md:p-8 text-white shadow-xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-cyan-400/20 text-cyan-300 text-[11px] font-bold tracking-wider uppercase inline-block mb-2">Proactive Health Monitor</span>
                        <h2 class="text-xl font-extrabold flex items-center gap-2">
                            <iconify-icon icon="lucide:activity" class="text-cyan-400 text-2xl animate-pulse"></iconify-icon>
                            Status Kesehatan Server VM Kominfo
                        </h2>
                        <p class="text-xs text-white/70">Pemeriksaan otomatis 4 parameter kritis sebelum pengguna mengakses web</p>
                    </div>

                    <button onclick="runHealthCheckNow()" id="btn-run-health" 
                        class="bg-cyan-500 hover:bg-cyan-400 text-[#1e345c] font-bold px-5 py-2.5 rounded-xl text-xs transition flex items-center gap-2 shadow-lg">
                        <iconify-icon icon="lucide:refresh-cw" id="icon-health-spin" class="text-base"></iconify-icon>
                        Cek Kesehatan Sekarang
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="health-cards-container">
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-white/60 font-semibold">Firebase RTDB</span>
                            <span id="badge-firebase" class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-sm font-bold text-white" id="text-firebase">Koneksi Aktif (Ready)</p>
                        <p class="text-[10px] text-white/50 mt-1" id="subtext-firebase">uji-petik-default-rtdb</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-white/60 font-semibold">Jam Server (NTP)</span>
                            <span id="badge-ntp" class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-sm font-bold text-white" id="text-ntp">Sinkron ke Internet</p>
                        <p class="text-[10px] text-white/50 mt-1" id="subtext-ntp">WITA / UTC Standard</p>
                    </div>

                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-white/60 font-semibold">Penyimpanan VM</span>
                            <span id="badge-disk" class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-sm font-bold text-white" id="text-disk">Ruang Cukup</p>
                        <p class="text-[10px] text-white/50 mt-1" id="subtext-disk">storage/ disk safe</p>
                    </div>
                </div>
            </div>

            <!-- Telegram Bot Config Form -->
            <form action="{{ route('settings.telegram.update') }}" method="POST">
                @csrf
                <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                <iconify-icon icon="lucide:send" class="text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#253D6B]">Konfigurasi Bot Telegram Notifier</h2>
                                <p class="text-xs text-slate-500">Menerima alert otomatis detik itu juga saat terjadi error 500 atau kendala server</p>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ ($telegram['is_active'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#253D6B]"></div>
                            <span class="ml-3 text-xs font-bold text-slate-700">Aktifkan Notifikasi</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Telegram Bot Token</label>
                            <input type="text" name="bot_token" id="input_bot_token" value="{{ old('bot_token', $telegram['bot_token'] ?? '') }}" placeholder="Contoh: 7123456789:AAHkxxxxxx"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">Dibuat melalui bot resmi <b>@BotFather</b> di aplikasi Telegram.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Telegram Chat ID (IT Support)</label>
                            <input type="text" name="chat_id" id="input_chat_id" value="{{ old('chat_id', $telegram['chat_id'] ?? '') }}" placeholder="Contoh: 123456789 atau -100xxxx (Grup)"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-mono text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">ID akun Telegram Anda (Dapat dicek via <b>@userinfobot</b>).</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-sky-50 border border-sky-100 flex items-start gap-3 mb-8">
                        <iconify-icon icon="lucide:info" class="text-sky-600 text-lg shrink-0 mt-0.5"></iconify-icon>
                        <div class="text-xs text-sky-900 leading-relaxed">
                            <b>Komunikasi Direct Telegram:</b> 100% Permanen, Gratis Selamanya, dan Tanpa Kuota. Setiap kali server mengalami kendala (misal koneksi Firebase terputus atau error unhandled), bot langsung mengirimkan detail file dan solusi perbaikan ke Telegram Anda.
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <button type="button" onclick="testTelegramNotification()" id="btn-test-tg"
                            class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 rounded-xl transition flex items-center justify-center gap-2 text-sm border border-slate-200">
                            <iconify-icon icon="lucide:send" class="text-base text-sky-500"></iconify-icon>
                            Test Kirim Pesan ke Telegram
                        </button>

                        <button type="submit" class="w-full sm:w-auto bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-sm">
                            <iconify-icon icon="lucide:save" class="text-lg"></iconify-icon>
                            Simpan Konfigurasi Telegram
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB 4: OPERASIONAL SURVEI -->
        <div id="tab-content-operational" class="tab-content hidden">
            <form action="{{ route('settings.operational.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <iconify-icon icon="lucide:coffee" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-[#253D6B]">Aturan Operasional & Istirahat Operator</h2>
                            <p class="text-xs text-slate-500">Ketentuan jam istirahat dan fleksibilitas bantuan rekan satu pos</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Batas Durasi Istirahat (Menit)</label>
                            <input type="number" name="max_break_minutes" value="{{ old('max_break_minutes', $operational['max_break_minutes'] ?? 60) }}" min="15" max="300" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-[#253D6B] focus:bg-white focus:ring-2 focus:ring-[#253D6B] focus:outline-none">
                            <p class="text-[11px] text-slate-400 mt-1">Durasi maksimal operator dalam status istirahat.</p>
                        </div>

                        <div class="space-y-4 pt-2">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="allow_cross_claim" value="1" {{ ($operational['allow_cross_claim'] ?? true) ? 'checked' : '' }} class="mt-1 rounded text-[#253D6B] focus:ring-[#253D6B]">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Izin Bantuan Rekan Pos (Claim Task)</span>
                                    <p class="text-[11px] text-slate-400">Mengizinkan operator lain di pos yang sama membantu mencatat kendaraan saat rekannya istirahat.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="sound_alert_violation" value="1" {{ ($operational['sound_alert_violation'] ?? true) ? 'checked' : '' }} class="mt-1 rounded text-[#253D6B] focus:ring-[#253D6B]">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">Suara Lonceng Notifikasi Pelanggaran</span>
                                    <p class="text-[11px] text-slate-400">Memutar audio lonceng di Dashboard Live jika operator keluar batas radius.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center gap-2 text-sm">
                            <iconify-icon icon="lucide:save" class="text-lg"></iconify-icon>
                            Simpan Operasional
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB 5: CADANGAN DATABASE -->
        <div id="tab-content-backup" class="tab-content hidden">
            <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <iconify-icon icon="lucide:hard-drive-download" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-[#253D6B]">Cadangan Data Firebase Instan</h2>
                        <p class="text-xs text-slate-500">Unduh seluruh snapshot database (users, survei, penugasan, lokasi, log) dalam format JSON</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="text-xs text-slate-400 font-bold uppercase">Database Terhubung</span>
                        <p class="text-base font-bold text-slate-800 mt-1">uji-petik-default-rtdb</p>
                        <p class="text-xs text-emerald-600 font-semibold mt-1">● Production Firebase</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="text-xs text-slate-400 font-bold uppercase">Format Cadangan</span>
                        <p class="text-base font-bold text-slate-800 mt-1">Standard JSON Tree (.json)</p>
                        <p class="text-xs text-slate-500 mt-1">Kompatibel Import Firebase</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="text-xs text-slate-400 font-bold uppercase">Cakupan Data</span>
                        <p class="text-base font-bold text-slate-800 mt-1">Root Node (/)</p>
                        <p class="text-xs text-slate-500 mt-1">Seluruh Koleksi & Riwayat</p>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-emerald-50/60 border border-emerald-200 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-sm font-bold text-emerald-900">Unduh Salinan Cadangan Offline</h3>
                        <p class="text-xs text-emerald-700 mt-0.5">Disarankan mencadangkan database secara berkala sebelum melakukan perubahan besar.</p>
                    </div>

                    <a href="{{ route('settings.backup.download') }}" 
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 text-sm shrink-0">
                        <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                        Download Backup Sekarang (.json)
                    </a>
                </div>
            </div>
        </div>

    </main>

    <script>
        function switchTab(tabId) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Deactivate all buttons
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

            // Show active content & button
            const content = document.getElementById('tab-content-' + tabId);
            const btn = document.getElementById('tab-btn-' + tabId);
            if (content) content.classList.remove('hidden');
            if (btn) btn.classList.add('active');
        }

        function resetOpRadius(uid) {
            const input = document.getElementById('op-rad-' + uid);
            if (input) {
                input.value = '';
                input.focus();
            }
        }

        function testTelegramNotification() {
            const botToken = document.getElementById('input_bot_token').value;
            const chatId = document.getElementById('input_chat_id').value;
            const btn = document.getElementById('btn-test-tg');

            if (!botToken || !chatId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Silakan isi Bot Token dan Chat ID Telegram terlebih dahulu.',
                    confirmButtonColor: '#253D6B'
                });
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader" class="text-base animate-spin"></iconify-icon> Mengirim ke Telegram...';

            fetch("{{ route('settings.telegram.test') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ bot_token: botToken, chat_id: chatId })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base text-sky-500"></iconify-icon> Test Kirim Pesan ke Telegram';
                
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terkirim!',
                        text: 'Pesan tes berhasil dikirim. Silakan periksa aplikasi Telegram Anda.',
                        confirmButtonColor: '#253D6B'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim',
                        text: data.message || 'Periksa kembali Token dan Chat ID Anda.',
                        confirmButtonColor: '#253D6B'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="lucide:send" class="text-base text-sky-500"></iconify-icon> Test Kirim Pesan ke Telegram';
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: err.message,
                    confirmButtonColor: '#253D6B'
                });
            });
        }

        function runHealthCheckNow() {
            const btn = document.getElementById('btn-run-health');
            const spinIcon = document.getElementById('icon-health-spin');
            
            spinIcon.classList.add('animate-spin');
            btn.disabled = true;

            fetch("{{ route('settings.health.check') }}")
            .then(res => res.json())
            .then(res => {
                spinIcon.classList.remove('animate-spin');
                btn.disabled = false;

                if (res.status && res.data) {
                    const d = res.data;
                    
                    // Update Firebase Card
                    document.getElementById('badge-firebase').className = 'w-2.5 h-2.5 rounded-full ' + (d.firebase.status ? 'bg-emerald-400' : 'bg-rose-500 animate-ping');
                    document.getElementById('text-firebase').innerText = d.firebase.status ? 'Normal & Terhubung' : 'Terputus / Error';
                    document.getElementById('subtext-firebase').innerText = d.firebase.message;

                    // Update NTP Card
                    document.getElementById('badge-ntp').className = 'w-2.5 h-2.5 rounded-full ' + (d.ntp.status ? 'bg-emerald-400' : 'bg-amber-400 animate-ping');
                    document.getElementById('text-ntp').innerText = d.ntp.status ? 'Waktu Sinkron' : 'Waktu Meleset';
                    document.getElementById('subtext-ntp').innerText = d.ntp.message;

                    // Update Disk Card
                    document.getElementById('badge-disk').className = 'w-2.5 h-2.5 rounded-full ' + (d.disk.status ? 'bg-emerald-400' : 'bg-rose-500');
                    document.getElementById('text-disk').innerText = d.disk.status ? 'Kapasitas Normal' : 'Hampir Penuh';
                    document.getElementById('subtext-disk').innerText = d.disk.message;

                    Swal.fire({
                        icon: 'success',
                        title: 'Health Check Selesai',
                        text: 'Pemeriksaan server berhasil dijalankan pada ' + res.time,
                        confirmButtonColor: '#253D6B'
                    });
                }
            })
            .catch(err => {
                spinIcon.classList.remove('animate-spin');
                btn.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Health Check',
                    text: err.message,
                    confirmButtonColor: '#253D6B'
                });
            });
        }
    </script>
</body>

</html>
