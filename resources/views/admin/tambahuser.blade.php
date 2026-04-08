<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/tambahuser.css') }}?v={{ time() }}">
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
<body class="bg-gradient-to-b from-[#E7EFF6] to-[#FFFFFF] min-h-screen flex">

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

    <main class="flex-1 ml-64 p-8 flex justify-center items-start pt-20">
    
    <div class="fixed top-6 right-8">
        <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-12 object-contain" alt="Logo">
    </div>

    <div class="bg-white w-full max-w-3xl rounded-[25px] shadow-[0_10px_40px_rgba(0,0,0,0.1)] p-12 relative">
    <h2 class="text-4xl font-bold text-center text-black mb-10">Form Tambah User</h2>

    <form action="{{ route('user.store') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Id_User</label>
            <input type="text" name="id_user" 
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan ID User">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" name="username" 
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Username">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" name="email" 
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Email">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Level User</label>
            <div class="relative">
                <select name="level_user" 
                    class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 appearance-none focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm text-gray-600">
                    <option value="" disabled selected>Pilih Level</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Operator">Operator</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input type="password" name="password" 
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Password">
        </div>

        <p class="text-red-600 text-[13px] italic mt-4">
            *Periksa kembali kebenaran data sebelum dikirim
        </p>

        <div class="pt-8">
            <button type="submit" 
                class="w-full bg-[#24AD45] hover:bg-[#1e913a] text-white font-bold py-4 rounded-xl text-2xl transition-all shadow-lg transform active:scale-[0.98]">
                Simpan User
            </button>
        </div>
    </form>
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