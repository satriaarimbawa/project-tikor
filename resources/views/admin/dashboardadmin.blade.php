<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Petik - Dashboard Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>
<body class="flex bg-[#F5F7FA]">

    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
        <div class="py-10 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-28 h-28 object-contain drop-shadow-xl" alt="Logo Dishub">
        </div>

        <nav class="flex-1 space-y-1">

           <a href="/dashboard-admin"
               class="relative flex items-center px-6 py-4 text-white group overflow-hidden transition-all">
                @if(request()->is('dashboard') || request()->is('/'))
                    <div class="absolute inset-0" style="background-color: rgba(37, 61, 107, 0.55);"></div>
                @endif
                <div class="relative z-10 flex items-center gap-4 ml-2">
                    <img src="{{ asset('assets/Beranda.png') }}" class="w-6 h-6 object-contain" alt="Beranda">
                    <span class="font-bold text-sm">Beranda</span>
                </div>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Objek Survey">
                <span>Objek Survey & Tarif</span>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Lokasi">
                <span>Penetapan Lokasi</span>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Penugasan">
                <span>Penugasan</span>
            </a>
            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Daftar User">
                <span>Daftar User</span>
            </a>

            <div class="relative">
                <button onclick="toggleSubMenu()" class="nav-link w-full flex items-center px-6 py-3 text-sm rounded-r-full transition-all focus:outline-none">
                    <img src="{{ asset('assets/Laporan.png') }}" class="w-6 h-6 mr-4 object-contain" alt="Laporan">
                    <span>Laporan</span>
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

    <main class="main-content ml-64 p-8 w-full">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-gray-800 font-bold text-[30px] tracking-tight">Pendapatan Harian</h1>
    
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 text-orange-600 font-bold text-sm bg-orange-50 px-5 py-2 rounded-full border border-orange-100 shadow-sm">
                    <iconify-icon icon="lucide:mail" class="text-lg"></iconify-icon>
                    <span>Pesan Masuk</span>
                </div>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain" alt="Logo Klungkung">
            </div>
        </header>

        <div class="card-revenue p-10 flex items-center gap-8 mb-10 relative overflow-hidden bg-white rounded-[30px] shadow-[0_10px_25px_rgba(0,0,0,0.1)]">
           <div class="absolute inset-0 opacity-40" 
                style="background: linear-gradient(90deg, #9AE95B 30%, #FFFFFF 100%);">
            </div>
            <div class="w-24 h-24 flex items-center justify-center relative z-10">
                <img src="{{ asset('assets/Pendapatan.png') }}" class="w-20 h-20 object-contain" alt="Icon Pendapatan">
            </div>
            <div class="relative z-10">
                <h2 class="text-6xl font-bold tracking-tight text-gray-900">Rp. 7.000.000</h2>
                <p class="text-[12px] text-gray-500 font-bold uppercase tracking-wider mt-2">Total Pendapatan Harian ( Today )</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="relative overflow-hidden p-6 rounded-[25px] flex items-center gap-4 border border-blue-100 transition-transform hover:scale-105 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)]"
                 style="background: linear-gradient(90deg, rgba(16, 86, 216, 0.4) 50%, rgba(255, 255, 255, 0.32) 100%);">
                <div class="relative z-10 w-14 h-14 bg-[#4A78D7] rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                    <img src="{{ asset('assets/bus.png') }}" alt="Bus" class="w-9 h-9 object-contain">
                </div>
                <div class="relative z-10">
                    <p class="text-[16px] font-black text-[#000000] uppercase tracking-wider">Bus</p>
                    <h3 class="text-2xl font-black text-gray-700">200</h3>
                </div>
            </div>

            <div class="relative overflow-hidden p-6 rounded-[25px] flex items-center gap-4 border border-orange-100 transition-transform hover:scale-105 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)]"
                 style="background: linear-gradient(90deg, rgba(233, 164, 38, 0.4) 63%, rgba(255, 255, 255, 0.32) 100%);">
                <div class="relative z-10 w-14 h-14 bg-[#E9A426] rounded-2xl flex items-center justify-center shadow-lg shadow-orange-600/30 flex-shrink-0">
                    <img src="{{ asset('assets/motor.png') }}" alt="Motor" class="w-9 h-9 object-contain">
                </div>
                <div class="relative z-10">
                    <p class="text-[16px] font-black text-[#000000] uppercase tracking-wider">Motor</p>
                    <h3 class="text-2xl font-black text-gray-700">1000</h3>
                </div>
            </div>

            <div class="relative overflow-hidden p-6 rounded-[25px] flex items-center gap-4 border border-purple-100 transition-transform hover:scale-105 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)]"
                 style="background: linear-gradient(90deg, rgba(149, 62, 225, 0.4) 61%, rgba(255, 255, 255, 0.32) 100%);">
                <div class="relative z-10 w-14 h-14 bg-[#953EE1] rounded-2xl flex items-center justify-center shadow-lg shadow-purple-600/30 flex-shrink-0">
                    <img src="{{ asset('assets/mini bus.png') }}" alt="Mini Bus" class="w-9 h-9 object-contain">
                </div>
                <div class="relative z-10">
                    <p class="text-[16px] font-black text-[#000000] uppercase tracking-wider">Mini Bus</p>
                    <h3 class="text-2xl font-black text-gray-700">600</h3>
                </div>
            </div>

            <div class="relative overflow-hidden p-6 rounded-[25px] flex items-center gap-4 border border-pink-100 transition-transform hover:scale-105 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)]"
                 style="background: linear-gradient(90deg, rgba(233, 91, 164, 0.4) 50%, rgba(255, 255, 255, 0.32) 100%);">
                <div class="relative z-10 w-14 h-14 bg-[#E95BA4] rounded-2xl flex items-center justify-center shadow-lg shadow-pink-600/30 flex-shrink-0">
                    <img src="{{ asset('assets/truk.png') }}" alt="Truk" class="w-9 h-9 object-contain">
                </div>
                <div class="relative z-10">
                    <p class="text-[16px] font-black text-[#000000] uppercase tracking-wider">Truk</p>
                    <h3 class="text-2xl font-black text-gray-700">50</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[30px] shadow-sm mb-10 border border-gray-100 w-full">
            <h3 class="font-extrabold text-gray-800 mb-8 flex items-center gap-3">
                <iconify-icon icon="lucide:trending-up" class="text-blue-500 text-xl"></iconify-icon>
                STATUS MONITORING MINGGUAN (HARIAN)
            </h3>

            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-1 h-[350px] relative">
                    <canvas id="weeklyMonitoringChart"></canvas>
                </div>

                <div class="flex flex-col justify-center gap-3 min-w-[120px]">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-[#4A78D7]"></span>
                        <span class="text-sm font-semibold text-gray-700">Bus</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-[#E9A426]"></span>
                        <span class="text-sm font-semibold text-gray-700">Motor</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-[#953EE1]"></span>
                        <span class="text-sm font-semibold text-gray-700">Mini Bus</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-[#E95BA4]"></span>
                        <span class="text-sm font-semibold text-gray-700">Truk</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[30px] shadow-sm border border-gray-100 overflow-hidden mb-20">
    <div class="p-8 flex items-center justify-between">
        <h3 class="font-extrabold text-gray-800 text-lg">Tabel Detail Pendapatan Harian</h3>
    </div>
    
    <div class="overflow-x-auto px-8 pb-8">
        <table class="w-full border-separate border-spacing-y-3">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-4 text-gray-800 font-bold text-center rounded-l-xl border-y border-l border-black">Objek</th>
                    <th class="p-4 text-gray-800 font-bold text-center border-y border-black">Lokasi</th>
                    <th class="p-4 text-gray-800 font-bold text-center border-y border-black">Jumlah Unit</th>
                    <th class="p-4 text-gray-800 font-bold text-center rounded-r-xl border-y border-r border-black">Nominal Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white group transition-all">
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-l border-black rounded-l-2xl">Mini Bus</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">Pelabuhan Kusamba</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">20</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-r border-black rounded-r-2xl">Rp. 1000.000</td>
                </tr>
                <tr class="bg-white">
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-l border-black rounded-l-2xl">Truk</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">Terminal Galiran</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">150</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-r border-black rounded-r-2xl">Rp. 1.500.000</td>
                </tr>
                <tr class="bg-white">
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-l border-black rounded-l-2xl">Motor</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">Terminal Galiran</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">50</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-r border-black rounded-r-2xl">Rp. 1.250.000</td>
                </tr>
                <tr class="bg-white">
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-l border-black rounded-l-2xl">Mini Bus</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">Pelabuhan Kusamba</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-black">75</td>
                    <td class="p-4 text-center font-bold text-gray-800 border-y border-r border-black rounded-r-2xl">Rp. 1.350.000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('weeklyMonitoringChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    datasets: [
                        { 
                            label: 'Bus', 
                            data: [600, 350, 280, 580, 480, 850], 
                            borderColor: '#4A78D7', 
                            backgroundColor: '#4A78D7', 
                            tension: 0.1, // Membuat garis melengkung halus
                            borderWidth: 3,
                            fill: false 
                        },
                        { 
                            label: 'Motor', 
                            data: [980, 150, 780, 850, 350, 500], 
                            borderColor: '#E9A426', 
                            backgroundColor: '#E9A426', 
                            tension: 0.1, 
                            borderWidth: 3,
                            fill: false
                        },
                        { 
                            label: 'Mini Bus', 
                            data: [650, 220, 750, 480, 50, 620], 
                            borderColor: '#953EE1', 
                            backgroundColor: '#953EE1', 
                            tension: 0.1, 
                            borderWidth: 3,
                            fill: false
                        },
                        { 
                            label: 'Truk', 
                            data: [150, 950, 980, 550, 650, 580], 
                            borderColor: '#E95BA4', 
                            backgroundColor: '#E95BA4', 
                            tension: 0.1, 
                            borderWidth: 3,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            display: false // Legenda menggunakan HTML manual di samping canvas
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            max: 1000, 
                            ticks: { 
                                stepSize: 250, 
                                color: '#9CA3AF',
                                font: { size: 11 }
                            }, 
                            grid: { color: '#F3F4F6' } 
                        },
                        x: { 
                            grid: { display: false }, 
                            ticks: { 
                                color: '#4B5563', 
                                font: { weight: 'bold', size: 12 } 
                            } 
                        }
                    },
                    elements: {
                        point: { 
                            radius: 4, 
                            hoverRadius: 7, 
                            backgroundColor: '#FFFFFF', 
                            borderWidth: 2.5 
                        }
                    }
                }
            });
        });

        function toggleSubMenu() {
            const subMenu = document.getElementById('subMenuLaporan');
            const icon = document.getElementById('chevron-icon');
            subMenu.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    </script>
</body>
</html>