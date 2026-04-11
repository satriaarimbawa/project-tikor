    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
        <div class="py-10 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-28 h-28 object-contain drop-shadow-xl" alt="Logo Dishub">
        </div>
        <nav class="flex-1 space-y-1">
            <a href="/dashboard-admin" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white group transition-all">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4"> 
                    <img src="{{ asset('assets/Beranda.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">   
                </div>
                    <span class="text-sm font-medium">Beranda</span>
                </div>
            </a>
            <a href="/objek-tarif" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white group transition-all">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4"> 
                    <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">   
                </div>
                    <span class="text-sm font-medium">Objek Survei & Tarif</span>
                </div>
            </a>
            <a href="/penetapan-lokasi" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white transition-all group">
            <div class="flex items-center w-full">
                <div class="w-6 flex justify-center mr-4">
                    <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                </div>
                <span class="text-sm font-medium">Penetapan Lokasi</span>
            </div>
        </a>

        <a href="/dashboard-penugasan" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white transition-all group">
            <div class="flex items-center w-full">
                <div class="w-6 flex justify-center mr-4">
                    <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                </div>
                <span class="text-sm font-medium">Penugasan</span>
            </div>
        </a>

        <a href="/daftar-user" class="relative flex items-center px-6 py-4 group overflow-hidden transition-all">
            <div class="relative z-10 flex items-center w-full">
                <div class="w-6 flex justify-center mr-4">
                    <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 object-contain">
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
                    <a href="#" class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Lokasi.png') }}" class="w-6 h-6 object-contain" alt="Laporan Lokasi">
                        <span>Berdasarkan Lokasi</span>
                    </a>
                    <a href="#" class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Kedatangan.png') }}" class="w-6 h-6 object-contain" alt="Laporan Kedatangan">
                        <span>Berdasarkan Waktu</span>
                    </a>
                    <a href="#" class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
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
                <p class="text-xs font-bold leading-none truncate text-white">Rani</p>
                <p class="text-[10px] text-white/50 uppercase tracking-tighter mt-1">Administrator</p>
            </div>
            <button class="text-white/30 hover:text-white transition">
                <iconify-icon icon="lucide:log-out" class="text-xl"></iconify-icon>
            </button>
        </div>
    </aside>