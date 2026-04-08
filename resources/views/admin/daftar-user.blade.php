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

        <a href="#" class="relative flex items-center px-6 py-4 group overflow-hidden transition-all bg-[#253D6B]/50">
            <div class="relative z-10 flex items-center w-full">
                <div class="w-6 flex justify-center mr-4">
                    <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 object-contain">
                </div>
                <span class="text-sm font-bold text-white">Daftar User</span>
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

        <main class="main-content">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-extrabold text-[#253D6B] tracking-tight">Daftar User</h1>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-16 object-contain" alt="Logo Klungkung">
             </div>

    <div class="card-figma">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-1/3">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <iconify-icon icon="lucide:search" class="text-white/50 text-xl"></iconify-icon>
                </span>
                <input type="text" placeholder="Cari data user" 
                    class="w-full bg-[#253D6B] text-white text-sm rounded-full py-2.5 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder:text-white/50">
            </div>
            
            <button class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white text-sm font-bold py-2.5 px-6 rounded-full flex items-center gap-2 transition-all shadow-lg">
                <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                Tambah Data
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                    <tr class="table-row-hover">
                        <td class="text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                        <td class="font-bold text-[#253D6B]">{{ $user->username }}</td>
                        <td class="text-gray-600">{{ $user->email }}</td>
                        <td>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="flex justify-center gap-2">
                            <button class="w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md flex items-center justify-center transition-colors">
                                <iconify-icon icon="lucide:edit-3"></iconify-icon>
                            </button>
                            <button class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center justify-center transition-colors">
                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8 gap-4">
            <button class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition">
                <iconify-icon icon="lucide:chevron-left" class="text-xl"></iconify-icon>
            </button>
            <button class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition">
                <iconify-icon icon="lucide:chevron-right" class="text-xl"></iconify-icon>
            </button>
        </div>
    </div>
</main>
    <script>
        function toggleSubMenu() {
            const subMenu = document.getElementById('subMenuLaporan');
            const icon = document.getElementById('chevron-icon');
            
            // Toggle class hidden
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