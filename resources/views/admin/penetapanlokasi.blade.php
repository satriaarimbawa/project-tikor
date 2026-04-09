<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/daftar-user.css') }}?v={{ time() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #E7EFF6 70%, #FFFFFF 100%);
            min-height: 100vh;
        }

        .sidebar-navy {
            background-color: rgba(37, 61, 107, 0.75) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="flex">

    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
        <div class="py-10 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-28 h-28 object-contain drop-shadow-xl" alt="Logo Dishub">
        </div>

        <nav class="flex-1 space-y-1">
            <a href="#" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white group transition-all">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Beranda.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Beranda</span>
                </div>
            </a>

            <a href="#" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white group transition-all">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Objek Survei & Tarif</span>
                </div>
            </a>

            <a href="#" class="relative flex items-center px-6 py-4 group overflow-hidden transition-all bg-[#253D6B]/50">
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 object-contain">
                    </div>
                    <span class="text-sm font-bold text-white">Penetapan Lokasi</span>
                </div>
            </a>

            <a href="#" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white transition-all group">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                    </div>
                    <span class="text-sm font-medium">Penugasan</span>
                </div>
            </a>

            <a href="#" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white transition-all group">
                <div class="flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4">
                        <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
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

    <main class="flex-1 ml-64 p-12 min-h-screen" style="background: linear-gradient(180deg, #E7EFF6 0%, #FFFFFF 100%);">
        
        <div class="flex justify-between items-start mb-10">
            <h1 class="text-[32px] font-bold text-[#1e293b] tracking-tight">Penetapan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-auto" alt="Logo Klungkung">
        </div>

        <div class="flex gap-10 items-start">
            
            <div class="flex-[3] bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] p-10 border border-gray-50/50">
                <h2 class="text-xl font-bold text-[#1e293b] mb-8 uppercase tracking-widest">Daftar Lokasi</h2>

                <div class="relative mb-8">
                    <input type="text" placeholder="Search" class="w-full pl-12 pr-4 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-2xl focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-all font-medium placeholder:text-gray-300">
                    <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xl"></iconify-icon>
                </div>

                <div class="overflow-x-auto mt-6">
                    <table class="w-full border-separate border-spacing-y-2">
                        <thead class="bg-[#E5E7EB] text-[#1E293B] text-[12px] uppercase font-bold">
                            <tr>
                                <th class="py-3 px-4 rounded-l-lg text-center w-12">No</th>
                                <th class="py-3 px-4 text-left">Nama Lokasi</th>
                                <th class="py-3 px-4 text-left">Koordinat</th>
                                <th class="py-3 px-4 text-left">Target</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-r-lg text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[12px] font-bold text-[#1E293B]">
                            @forelse($daftarLokasi as $index => $lokasi)
                            <tr class="bg-white hover:bg-gray-50 transition-colors shadow-sm">
                                <td class="py-4 px-4 text-center border-y border-l border-gray-100 rounded-l-xl">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 border-y border-gray-100">{{ $lokasi->nama_lokasi }}</td>
                                <td class="py-4 px-4 border-y border-gray-100 text-gray-400 font-medium italic">
                                    {{ $lokasi->koordinat ?? 'Belum diset' }}
                                </td>
                                <td class="py-4 px-4 border-y border-gray-100 uppercase">
                                    Rp. {{ number_format($lokasi->target_harian, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-center border-y border-gray-100">
                                    <span class="{{ $lokasi->status == 'Aktif' ? 'bg-[#BCF0DA] text-[#03543F]' : 'bg-gray-200 text-gray-600' }} px-4 py-1.5 rounded-lg text-[10px] uppercase font-black tracking-tighter">
                                        {{ $lokasi->status ?? 'Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center border-y border-r border-gray-100 rounded-r-xl">
                                    <div class="flex justify-center gap-2">
                                        <button class="text-gray-800 hover:text-blue-600 transition">
                                            <iconify-icon icon="lucide:pencil" class="text-lg"></iconify-icon>
                                        </button>
                                        <button class="text-gray-800 hover:text-red-600 transition">
                                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-gray-400 italic bg-white rounded-xl border border-gray-100">
                                    Data lokasi tidak ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex-[2] bg-white rounded-[2rem] shadow-[0_30px_90px_rgba(0,0,0,0.15)] border border-gray-50/50 overflow-hidden flex flex-col">
                <div class="h-64 bg-[#F8FAFC] m-4 rounded-[1.5rem] overflow-hidden border border-gray-100 relative">
                    <img src="{{ asset('assets/map_placeholder.png') }}" class="w-full h-full object-cover opacity-60" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:16px_16px] opacity-30"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <iconify-icon icon="ri:map-pin-2-fill" class="text-[#EF4444] text-5xl drop-shadow-lg"></iconify-icon>
                        <div class="w-4 h-1.5 bg-black/10 rounded-full mt-1 blur-[2px]"></div>
                    </div>
                </div>

                <div class="p-8 pt-2 flex-1">
                    <h2 class="text-[12px] font-black text-[#1e293b] mb-6 uppercase tracking-[0.2em]">Form Penetapan Lokasi</h2>
                    <form action="#" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Nama Lokasi</label>
                            <input type="text" name="nama_lokasi" placeholder="Jl. Diponegoro" class="w-full px-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-sm font-bold text-[#1e293b] placeholder:text-gray-300 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Koordinat</label>
                            <div class="relative">
                                <input type="text" name="koordinat" placeholder="-8.53532264617003, 115.4042257919066" class="w-full px-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-[11px] font-medium text-[#1e293b] placeholder:text-gray-300 transition-all">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center">
                                    <iconify-icon icon="lucide:locate-fixed" class="text-gray-400 hover:text-blue-500 cursor-pointer transition-colors"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Target Harian</label>
                            <div class="relative">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 flex items-center border-r border-gray-200 pr-3">
                                    <span class="text-sm font-bold text-[#1e293b]">Rp.</span>
                                </div>
                                <input type="text" name="target_harian" placeholder="250.000" class="w-full pl-16 pr-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-sm font-bold text-[#1e293b] placeholder:text-gray-300 transition-all">
                            </div>
                        </div>
                        <div class="flex items-start gap-2 pt-2">
                            <iconify-icon icon="lucide:info" class="text-red-400 text-sm mt-0.5"></iconify-icon>
                            <p class="text-[10px] text-red-400 font-bold italic leading-tight">Periksa kembali kebenaran data sebelum dikirim</p>
                        </div>
                        <button type="submit" class="w-full bg-[#22C55E] hover:bg-[#16A34A] text-white font-black py-4 rounded-[1.25rem] shadow-[0_12px_30px_-5px_rgba(34,197,94,0.4)] transition-all text-xl mt-4 tracking-tight hover:-translate-y-1 active:scale-[0.98]">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        </main>

    <script>
        function toggleSubMenu() {
            const subMenu = document.getElementById('subMenuLaporan');
            const icon = document.getElementById('chevron-icon');

            if (subMenu.classList.contains('hidden')) {
                subMenu.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                subMenu.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
</body>
</html>