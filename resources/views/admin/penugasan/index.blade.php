<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Penugasan</title>


    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>

<body class="flex bg-[#F5F7FA]">

    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
        <div class="py-8 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}"
                class="w-20 h-20 object-contain drop-shadow-xl transition-all duration-300" alt="Logo Dishub">
        </div>

        <nav class="flex-1 space-y-1">
            <a href="/dashboard-admin"
                class="relative flex items-center px-6 py-4 text-white group overflow-hidden transition-all">
                @if(request()->is('dashboard') || request()->is('/'))
                <div class="absolute inset-0" style="background-color: rgba(37, 61, 107, 0.55);"></div>
                @endif
                <div class="relative z-10 flex items-center gap-4 ml-2">
                    <img src="{{ asset('assets/Beranda.png') }}" class="w-6 h-6 mr-4 object-contain" alt="Beranda">
                    <span class="font-bold text">Beranda</span>
                </div>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 mr-4 object-contain"
                    alt="Objek Survey">
                <span>Objek Survey & Tarif</span>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Lokasi">
                <span>Penetapan Lokasi</span>
            </a>

            <a href="/dashboard-penugasan"
                class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <div class="absolute inset-0" style="background-color: rgba(37, 61, 107, 0.55);"></div>
                <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Penugasan">
                <span>Penugasan</span>
            </a>
            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Daftar User">
                <span>Daftar User</span>
            </a>

            <div class="relative">
                <button onclick="toggleSubMenu()"
                    class="nav-link w-full flex items-center px-6 py-3 text-sm rounded-r-full transition-all focus:outline-none">
                    <img src="{{ asset('assets/Laporan.png') }}" class="w-6 h-6 mr-4 object-contain" alt="Laporan">
                    <span>Laporan</span>
                    <iconify-icon icon="lucide:chevron-down" id="chevron-icon"
                        class="ml-auto transition-transform duration-300"></iconify-icon>
                </button>

                <div id="subMenuLaporan" class="hidden flex flex-col mt-2 space-y-2 mx-2 transition-all">
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Lokasi.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Lokasi">
                        <span>Berdasarkan Lokasi</span>
                    </a>
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Kedatangan.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Kedatangan">
                        <span>Berdasarkan Waktu</span>
                    </a>
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Operator.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Operator">
                        <span>Berdasarkan Operator</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-6 mt-auto flex items-center gap-3" style="background-color: rgba(37, 61, 107, 0.55);">
            <div class="w-5 h-5 flex items-center justify-center">
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

    <main class="main-content ml-64 p-8 w-full">
        <header class="flex justify-end items-center mb-8">
            <div class="flex items-center">
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain"
                    alt="Logo Klungkung">
            </div>
        </header>

        <h1 class="text-3xl font-bold text-black mb-6">Data Penugasan</h1>

        <div class="bg-white rounded-xl shadow-lg p-8 min-h-[600px] relative">

            <div class="flex justify-between items-center mb-6">
                <div class="relative group">
                    <a href="/dashboard-penugasan/create">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <iconify-icon icon="lucide:search" class="text-white text-xl"></iconify-icon>
                        </span>
                    </a>
                    <input type="text" placeholder="Cari data penugasan"
                        class="bg-[#253D6B] text-white text-sm rounded-full pl-10 pr-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-white/70 shadow-md">
                </div>

                <a href="/dashboard-penugasan/create"
                    class="bg-[#253D6B] hover:bg-[#1a2e52] text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md transition-all active:scale-95 inline-flex">
                    <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                    <span class="font-semibold text-sm">Tambah Data</span>
                </a>
            </div>

            <div class="overflow-hidden border border-gray-300 rounded-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FDE047] border-b border-gray-300">
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800 w-12 text-center">
                                No</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Username</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Nama lokasi</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Waktu</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">SPT</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Status</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Objek</th>
                            <th class="py-3 px-4 font-semibold text-gray-800 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#FFFBEB]">
                        <tr class="border-b border-gray-300">
                            <td class="py-3 px-4 border-r border-gray-300 text-center text-sm">1</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Alex</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Terminal Galiran</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">08:00 - 12:00</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">SPT/2024/001</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Aktif</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Motor</td>
                            <td class="py-3 px-4 flex justify-center gap-2">
                                <button
                                    class="bg-yellow-400 p-1.5 rounded hover:bg-yellow-500 flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit-3" class="text-white text-lg"></iconify-icon>
                                </button>
                                <button
                                    class="bg-red-500 p-1.5 rounded hover:bg-red-600 flex items-center justify-center">
                                    <iconify-icon icon="lucide:trash-2" class="text-white text-lg"></iconify-icon>
                                </button>
                            </td>
                        </tr>

                        @for ($i = 2; $i <= 10; $i++) <tr class="border-b border-gray-300">
                            <td class="py-3 px-4 border-r border-gray-300 text-center h-10"></td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 flex justify-center gap-2">
                                <button
                                    class="bg-yellow-400 p-1.5 rounded hover:bg-yellow-500 flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit-3" class="text-white text-lg"></iconify-icon>
                                </button>
                                <button
                                    class="bg-red-500 p-1.5 rounded hover:bg-red-600 flex items-center justify-center">
                                    <iconify-icon icon="lucide:trash-2" class="text-white text-lg"></iconify-icon>
                                </button>
                            </td>
                            </tr>
                            @endfor
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center gap-4 mt-8">
                <button
                    class="w-10 h-10 flex items-center justify-center bg-[#E29A81] rounded-full shadow-md hover:bg-[#d18970] transition-all">
                    <iconify-icon icon="lucide:chevron-left" class="text-white text-xl"></iconify-icon>
                </button>
                <button
                    class="w-10 h-10 flex items-center justify-center bg-[#E29A81] rounded-full shadow-md hover:bg-[#d18970] transition-all">
                    <iconify-icon icon="lucide:chevron-right" class="text-white text-xl"></iconify-icon>
                </button>
            </div>
        </div>
    </main>
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