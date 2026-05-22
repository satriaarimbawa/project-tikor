    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
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
                        <a href="#" class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
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

    <style>
        @keyframes slideIn {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in { animation: slideIn 0.3s ease-out forwards; }
    </style>