    <!-- Mobile Toggle Button -->
    <button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-[60] bg-[#253D6B] text-white p-2 rounded-lg shadow-lg flex items-center justify-center transition-all hover:bg-[#1a2e52]">
        <iconify-icon icon="lucide:menu" class="text-2xl"></iconify-icon>
    </button>

    <!-- Overlay for Mobile -->
    <div id="mobile-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-[40] hidden transition-opacity duration-300 opacity-0"></div>

    <aside id="sidebar" class="sidebar-navy w-64 h-screen text-white flex flex-col fixed z-50 shadow-2xl transition-transform duration-300 -translate-x-full lg:translate-x-0 overflow-y-auto custom-scrollbar">
        <div class="py-10 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-28 h-28 object-contain drop-shadow-xl" alt="Logo Dishub">
        </div>
        <nav class="flex-1 space-y-1">
            <a href="/dashboard-admin" class="relative flex items-center px-6 py-4 {{ request()->is('dashboard-admin') ? 'text-white' : 'text-white/60' }} hover:text-white group transition-all">
                @if(request()->is('dashboard-admin'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4"> 
                        <img src="{{ asset('assets/Beranda.png') }}" class="w-5 h-5 object-contain {{ request()->is('dashboard-admin') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100">   
                    </div>
                    <span class="text-sm font-medium">Beranda</span>
                </div>
            </a>

            <a href="/daftar-user" class="relative flex items-center px-6 py-4 {{ request()->is('daftar-user') || request()->is('tambahuser') ? 'text-white' : 'text-white/60' }} group overflow-hidden transition-all hover:text-white">
                @if(request()->is('daftar-user') || request()->is('tambahuser'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 object-contain {{ request()->is('daftar-user') || request()->is('tambahuser') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Daftar User</span>
                </div>
            </a>
            
            <a href="/Objek_Tarif" class="relative flex items-center px-6 py-4 {{ request()->is('Objek_Tarif') ? 'text-white' : 'text-white/60' }} hover:text-white group transition-all">
                @if(request()->is('Objek_Tarif'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4"> 
                        <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 object-contain {{ request()->is('Objek_Tarif') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100">   
                    </div>
                    <span class="text-sm font-medium">Objek Survei & Tarif</span>
                </div>
            </a>

            <a href="/dashboard-tikor" class="relative flex items-center px-6 py-4 {{ request()->is('dashboard-tikor') ? 'text-white' : 'text-white/60' }} hover:text-white transition-all group">
                @if(request()->is('dashboard-tikor'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 object-contain {{ request()->is('dashboard-tikor') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Penetapan Lokasi</span>
                </div>
            </a>

            <a href="/dashboard-penugasan" class="relative flex items-center px-6 py-4 {{ request()->is('dashboard-penugasan*') ? 'text-white' : 'text-white/60' }} hover:text-white transition-all group">
                @if(request()->is('dashboard-penugasan*'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 object-contain {{ request()->is('dashboard-penugasan*') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Penugasan</span>
                </div>
            </a>

            <a href="/log-aktivitas" class="relative flex items-center px-6 py-4 {{ request()->is('log-aktivitas') ? 'text-white' : 'text-white/60' }} group overflow-hidden transition-all hover:text-white">
                @if(request()->is('log-aktivitas'))
                    <div class="absolute inset-0 bg-[#253D6B]/50 border-r-4 border-yellow-400"></div>
                @endif
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <iconify-icon icon="lucide:history" class="text-xl {{ request()->is('log-aktivitas') ? 'opacity-100' : 'opacity-60' }} group-hover:opacity-100"></iconify-icon>
                    </div>
                    <span class="text-sm font-medium ml-1">Log Aktivitas</span>
                </div>
            </a>

            <div class="relative">
                <button onclick="toggleSubMenu()" class="nav-link w-full flex items-center px-6 py-4 text-sm text-white/60 hover:text-white transition-all focus:outline-none group">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Laporan.png') }}" class="w-6 h-6 object-contain opacity-60 group-hover:opacity-100" alt="Laporan">
                    </div>
                    <span class="font-medium">Laporan</span>
                    <iconify-icon icon="lucide:chevron-down" id="chevron-icon" class="ml-auto transition-transform duration-300"></iconify-icon>
                </button>
                
                <div id="subMenuLaporan" class="hidden flex flex-col mt-2 space-y-2 mx-2 transition-all">
                        <a href="/laporan_lokasi" class="py-2 pl-10 text-[11px] {{ request()->is('laporan_lokasi') ? 'text-white bg-[#253D6B]/50 rounded-lg' : 'text-white/70' }} hover:text-white flex items-center gap-4 transition-colors">
                            <img src="{{ asset('assets/Laporan_Lokasi.png') }}" class="w-6 h-6 object-contain" alt="Laporan Lokasi">
                            <span>Berdasarkan Lokasi</span>
                        </a>
                        <a href="javascript:void(0)" onclick="showDevModal()" class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                            <img src="{{ asset('assets/Laporan_Kedatangan.png') }}" class="w-6 h-6 object-contain" alt="Laporan Kedatangan">
                            <span>Berdasarkan Waktu</span>
                        </a>
                        <a href="/lapOperator" class="py-2 pl-10 text-[11px] {{ request()->is('lapOperator') ? 'text-white bg-[#253D6B]/50 rounded-lg' : 'text-white/70' }} hover:text-white flex items-center gap-4 transition-colors">
                            <img src="{{ asset('assets/Laporan_Operator.png') }}" class="w-6 h-6 object-contain" alt="Laporan Operator">
                            <span>Berdasarkan Operator</span>
                        </a>
                </div>
            </div>
        </nav>

        <div class="p-6 mt-auto flex items-center gap-3" style="background-color: rgba(37, 61, 107, 0.55);">
            <div class="w-10 h-10 flex items-center justify-center">
                <img src="{{ asset('assets/Profil.png') }}" class="w-10 h-10 object-contain rounded-full" alt="User Profile">
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-xs font-bold leading-none truncate text-white">{{ session('username') ?? 'Admin' }}</p>
                <p class="text-[10px] text-white/50 uppercase tracking-tighter mt-1">Administrator</p>
            </div>
            <a href="/logout" class="text-white/30 hover:text-white transition">
                <iconify-icon icon="lucide:log-out" class="text-xl"></iconify-icon>
            </a>
        </div>
    </aside>

    <div id="notifModal" class="hidden fixed inset-0 z-[100] flex items-start justify-end p-8 bg-black/20 backdrop-blur-sm">
        <div class="bg-white w-full max-w-sm rounded-[25px] shadow-2xl overflow-hidden animate-slide-in">
            <div class="bg-[#253D6B] p-6 text-white flex justify-between items-center">
                <h3 class="font-bold flex items-center gap-2 text-white">
                    <iconify-icon icon="lucide:bell" class="text-xl"></iconify-icon>
                    Notifikasi Pelanggaran
                </h3>
                <button onclick="toggleNotifModal()" class="hover:rotate-90 transition-transform">
                    <iconify-icon icon="lucide:x" class="text-2xl text-white"></iconify-icon>
                </button>
            </div>
            <div class="max-h-[400px] overflow-y-auto p-4 space-y-3 bg-white" id="notifContainer">
                <div class="text-center py-10 text-gray-400 italic text-sm">
                    Memuat notifikasi...
                </div>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-center">
                <button onclick="markAllAsRead()" class="text-[11px] font-bold text-[#253D6B] hover:underline uppercase tracking-widest">
                    Tandai Semua Dibaca
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive Dev Modal -->
    <div id="devModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0F172A]/40 backdrop-blur-md">
        <div class="bg-white w-full max-w-sm rounded-[35px] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="devModalContent">
            <div class="relative p-8 text-center">
                <!-- Decorative Background -->
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-yellow-400 to-orange-500 opacity-10 rounded-b-[50px]"></div>
                
                <!-- Icon Area -->
                <div class="relative mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <iconify-icon icon="lucide:construction" class="text-5xl text-yellow-600 animate-bounce"></iconify-icon>
                    <div class="absolute -top-1 -right-1 w-8 h-8 bg-white rounded-full shadow-sm flex items-center justify-center">
                        <iconify-icon icon="lucide:settings" class="text-orange-500 animate-spin-slow"></iconify-icon>
                    </div>
                </div>

                <!-- Text Area -->
                <h3 class="text-2xl font-black text-[#253D6B] mb-2 tracking-tight">Mohon Maaf!</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    Fitur <span class="font-bold text-[#253D6B]">Laporan Berdasarkan Waktu</span> saat ini masih dalam tahap pengembangan untuk memberikan pengalaman terbaik bagi Anda.
                </p>

                <!-- Button Area -->
                <button onclick="hideDevModal()" class="w-full bg-[#253D6B] hover:bg-[#1a2e52] text-white font-bold py-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    Siap, Saya Mengerti
                    <iconify-icon icon="lucide:check-circle" class="text-xl text-yellow-400"></iconify-icon>
                </button>
            </div>
        </div>
    </div>

    <script>
        function showDevModal() {
            const modal = document.getElementById('devModal');
            const content = document.getElementById('devModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function hideDevModal() {
            const content = document.getElementById('devModalContent');
            const modal = document.getElementById('devModal');
            content.classList.add('scale-95', 'opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideDevModal();
        });
    </script>

    <style>
        .animate-spin-slow { animation: spin 4s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes slideIn {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in { animation: slideIn 0.3s ease-out forwards; }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>