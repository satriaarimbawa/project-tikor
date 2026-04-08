<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objek & Tarif - Dishub</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/objek_tarif.css') }}?v={{ time() }}">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
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
                    <div class="w-6 flex justify-center mr-4"> <img src="{{ asset('assets/Beranda.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100"></div>
                    <span class="text-sm font-medium">Beranda</span>
                </div>
            </a>
            <a href="#" class="relative flex items-center px-6 py-4 group overflow-hidden transition-all bg-[#253D6B]/50">
                <div class="relative z-10 flex items-center w-full">
                    <div class="w-6 flex justify-center mr-4"><img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 object-contain"></div>
                    <span class="text-sm font-bold text-white">Objek Survei & Tarif</span>
                </div>
            </a>
            <a href="#" class="relative flex items-center px-6 py-4 text-white/60 hover:text-white transition-all group">
            <div class="flex items-center w-full">
                <div class="w-6 flex justify-center mr-4">
                    <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 object-contain opacity-60 group-hover:opacity-100">
                </div>
                <span class="text-sm font-medium">Penetapan Lokasi</span>
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
                <div class="w-9 flex justify-center mr-1">
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
            <img src="{{ asset('assets/Profil.png') }}" class="w-8 h-8 object-contain" alt="User Profile">
        </div>
        <div class="flex-1 overflow-hidden">
            <p class="text-xs font-bold leading-none truncate text-white">Rani</p>
            <p class="text-[10px] text-white/50 uppercase tracking-tighter mt-1">Administrator</p>
        </div>
        <button class="text-white/30 hover:text-white transition">
            <iconify-icon icon="lucide:log-out" class="text-lg"></iconify-icon>
        </button>
    </div>
</aside>

    <main class="ml-64 p-10 w-full min-h-screen">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-[28px] font-extrabold text-[#2D3748] tracking-tight">Objek & Tarif</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" alt="Logo Klungkung" class="w-12 h-12 object-contain">
        </header>

        <div class="grid grid-cols-12 gap-8 items-start">
            
            <div class="col-span-12 lg:col-span-7 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Daftar Objek & Tarif</h2>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-300 text-[10px]"></i>
                        </span>
                        <input type="text" placeholder="Search" class="pl-9 pr-4 py-2 bg-[#F7FAFC] border border-gray-100 rounded-xl text-xs outline-none focus:ring-1 focus:ring-blue-100 w-44">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#F7FAFC]">
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">No</th>
                                <th class="py-4 px-4 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase text-left">Nama Objek</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Tarif</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Status</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $index => $item)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-2 border border-gray-100 text-center text-xs font-bold text-gray-400">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 border border-gray-100 text-[13px] font-bold text-gray-700">{{ $item['nama'] }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center font-black text-[13px] text-blue-600">Rp. {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center">
                                    <span class="{{ $item['status'] == 'Aktif' ? 'bg-[#EBFFFF] text-[#38B2AC]' : 'bg-gray-100 text-gray-400' }} px-3 py-1 rounded-md text-[9px] font-black uppercase">{{ $item['status'] }}</span>
                                </td>
                                <td class="py-4 px-2 border border-gray-100 text-center text-gray-300">
                                    <button class="hover:text-gray-900 mr-2"><i class="fas fa-pencil-alt text-[10px]"></i></button>
                                    <button class="hover:text-red-500"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> <div class="col-span-12 lg:col-span-5 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8 sticky top-5">
                <div class="flex flex-col items-center mb-8">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-4">Icon Objek</p>
                    <div class="w-32 h-32 bg-[#D9D9D9] rounded-[25px] flex items-center justify-center border-2 border-dashed border-[#E2E8F0]">
                        <img src="{{ asset('assets/motor.png') }}" class="w-20 h-20 object-contain filter invert brightness-0 opacity-80">
                    </div>
                    <button class="mt-4 text-[10px] font-black text-blue-500 uppercase tracking-widest">Ganti Icon</button>
                </div>

                <h3 class="text-sm font-black text-gray-800 uppercase mb-6">Form Objek & Tarif</h3>

                <form class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Nama Objek</label>
                        <input type="text" class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-bold text-gray-700 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Tarif (Rp)</label>
                        <input type="text" class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-black text-blue-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Keterangan</label>
                        <textarea rows="3" class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl text-sm text-gray-600 outline-none"></textarea>
                    </div>
                    <div class="pt-4 text-center">
                        <button type="submit" class="w-full bg-[#24B445] hover:bg-[#1f9d3a] text-white font-black py-5 rounded-2xl shadow-lg uppercase text-xs tracking-widest transition-all">
                            Simpan Lokasi
                        </button>
                        <p class="text-[9px] text-red-500 mt-4 italic font-medium">*Periksa kembali kebenaran data sebelum dikirim</p>
                    </div>
                </form>
            </div> </div> </main>

    <script>
        function toggleSubMenu() {
            const subMenu = document.getElementById('subMenuLaporan');
            const icon = document.getElementById('chevron-icon');
            subMenu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>