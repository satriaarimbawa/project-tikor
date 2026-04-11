<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        /* Transisi halus untuk sub-menu */
        #subMenuLaporan {
            transition: all 0.3s ease-in-out;
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
    
                <div id="subMenuLaporan" class="flex flex-col py-0">
                    <a href="#" id="sub-lokasi" onclick="setActiveSubMenu('sub-lokasi')" 
                        class="py-3 pl-16 text-[12px] text-white flex items-center gap-4 transition-colors bg-[#253D6B]/55 font-bold">
                        <img src="{{ asset('assets/Laporan_Lokasi.png') }}" class="w-6 h-6 object-contain" alt="Laporan Lokasi">
                        <span>Berdasarkan Lokasi</span>
                    </a>
            
                    <a href="#" class="py-3 pl-16 text-[12px] text-white/70 hover:text-white flex items-center gap-4 transition-colors hover:bg-white/5">
                        <img src="{{ asset('assets/Laporan_Kedatangan.png') }}" class="w-6 h-6 object-contain" alt="Laporan Kedatangan">
                        <span>Berdasarkan Waktu</span>
                    </a>
            
                    <a href="#" class="py-3 pl-16 text-[12px] text-white/70 hover:text-white flex items-center gap-4 transition-colors hover:bg-white/5">
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

    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-start mb-6">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Laporan Summary Hasil Uji Petik - Berdasarkan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-12 object-contain" alt="Logo">
        </div>

        <div class="flex justify-between items-center mb-8">
    
            <button class="flex items-center gap-2 bg-[#4A6FA5] hover:bg-blue-800 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition text-sm">
                <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon>
                Unduh PDF
            </button>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <span class="text-sm font-medium">25 Maret 2026</span>
                        <iconify-icon icon="lucide:calendar" class="text-slate-400"></iconify-icon>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">S/D</span>
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <span class="text-sm font-medium">28 Maret 2026</span>
                        <iconify-icon icon="lucide:calendar" class="text-slate-400"></iconify-icon>
                    </div>
                </div>

                <select class="w-64 p-2 bg-white border border-slate-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    <option>Pilih Lokasi</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <h2 class="text-lg font-bold mb-6 text-slate-800">Ringkasan Harian</h2>
            <div class="grid grid-cols-5 gap-6">
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" 
                    style="background: linear-gradient(90deg, rgba(16, 86, 216, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
    
                    <div class="w-12 h-12 rounded-lg bg-[#406ABA]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Bus.png') }}" class="w-6 h-6 object-contain" alt="Bus">
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-700 font-semibold uppercase">Bus</p>
                        <p class="text-2xl font-bold text-gray-900">489</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" 
                    style="background: linear-gradient(90deg, rgba(233, 164, 38, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
    
                    <div class="w-12 h-12 rounded-lg bg-[#E9A426]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Motor.png') }}" class="w-6 h-6 object-contain" alt="Icon">
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-700 font-semibold uppercase">Motor</p>
                        <p class="text-2xl font-bold text-gray-900">20</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" 
                    style="background: linear-gradient(90deg, rgba(237, 233, 37, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
    
                    <div class="w-12 h-12 rounded-lg bg-[#EDE925]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Mini Bus.png') }}" class="w-6 h-6 object-contain" alt="Mini Bus">
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-700 font-semibold uppercase">Mini Bus</p>
                        <p class="text-2xl font-bold text-gray-900">11</p>
                    </div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" 
                    style="background: linear-gradient(90deg, rgba(149, 62, 225, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
    
                    <div class="w-12 h-12 rounded-lg bg-[#953EE1]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Truk.png') }}" class="w-6 h-6 object-contain" alt="Truk">
                    </div>

                    <div>
                        <p class="text-[10px] text-gray-700 font-semibold uppercase">Truk</p>
                        <p class="text-2xl font-bold text-gray-900">50</p>
                    </div>
                </div>
                <div class="flex flex-col justify-center items-center rounded-xl border border-slate-200 p-4 shadow-sm" 
                    style="background-color: rgba(217, 217, 217, 0.6);">
    
                    <p class="text-[9px] font-bold text-slate-600 uppercase text-center leading-tight">Total Kedatangan</p>
                    <p class="text-3xl font-black text-slate-800">650</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 mb-6 text-slate-800">
            <div class="col-span-8 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-10"> <h2 class="text-lg font-bold text-slate-800">Ringkasan Kinerja Keuangan</h2>
                    <div class="flex items-center gap-4 text-[9px] font-bold uppercase tracking-wider text-slate-500">
                        <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-[#A9C7F5]"></div> Target</span>
                        <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-[#E5D4C9]"></div> Hasil Uji Petik</span>
                        <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-[#A1C9A1]"></div> Realisasi</span>
                    </div>
                </div>

                <div class="flex items-center gap-10"> 
                    <div class="flex-1 h-64 flex items-center justify-center"> 
                        <canvas id="financialChart" class="w-full"></canvas>
                    </div>

                    <div class="w-52 space-y-4">
                        <div class="p-4 rounded-xl border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">Total Target</p>
                            <p class="text-lg font-black text-slate-800">Rp. 2.037.000</p>
                        </div>

                    <div class="p-4 rounded-xl border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Total Realisasi</p>
                        <p class="text-lg font-black text-slate-800">Rp. 1.473.000</p>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                        <p class="text-[9px] font-bold text-slate-400 uppercase">Selisih</p>
                        <p class="text-lg font-black text-slate-800">Rp. 564.000</p>
                    </div>
                </div>
            </div>
        </div>

            <div class="col-span-4 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold mb-6">Volume Kendaraan</h2>
                <div class="h-40 mb-6">
                     <canvas id="volumeChart"></canvas>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center p-3 rounded-lg border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Total Motor</span>
                        <span class="text-xs font-black text-slate-800">476</span>
                    </div>

                    <div class="flex justify-between items-center p-3 rounded-lg border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                            <span class="text-[10px] font-bold text-slate-500 uppercase">Total Mobil</span>
                        <span class="text-xs font-black text-slate-800">13</span>
                    </div>
                </div>
            </div>
        </div>

    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200 text-slate-700 flex flex-col">
    <div class="p-6 border-b border-slate-100">
        <h2 class="text-lg font-bold">Rekapitulasi Tarif dan Penerimaan Baru</h2>
    </div>

    <div class="overflow-y-auto max-h-80 custom-scrollbar">
        <table class="w-full text-xs border-collapse">
            <thead class="bg-slate-200 text-slate-500 font-bold uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                <tr>
                    <th class="px-6 py-3 text-center w-12 border-b border-slate-300">No</th>
                    <th class="px-6 py-3 text-left border-b border-slate-300">Tanggal</th>
                    <th class="px-6 py-3 text-left border-b border-slate-300">Jenis</th>
                    <th class="px-6 py-3 text-left border-b border-slate-300">Tarif</th>
                    <th class="px-6 py-3 text-center border-b border-slate-300">Vol</th>
                    <th class="px-6 py-3 text-left border-b border-slate-300">Total</th>
                    <th class="px-6 py-3 text-left border-b border-slate-300">Total Penerimaan</th>
                </tr>
            </thead>

            <tbody class="bg-white">
                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                    <td class="px-6 py-4 text-center align-top">1</td>
                    <td class="px-6 py-4 align-top font-medium whitespace-nowrap">25 Maret 2026</td>
                    <td class="px-6 py-4 space-y-2">
                        <p>Motor</p><p>Mini Bus</p><p>Bus</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-slate-500 font-medium">
                        <p>Rp. 2.000</p><p>Rp. 5.000</p><p>Rp. 10.000</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-center font-bold">
                        <p>42</p><p>10</p><p>2</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 font-bold">
                        <p>Rp. 84.000</p><p>Rp. 50.000</p><p>Rp. 20.000</p>
                    </td>
                    <td class="px-6 py-4 align-top font-black text-slate-800">Rp. 154.000</td>
                </tr>

                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                    <td class="px-6 py-4 text-center align-top">2</td>
                    <td class="px-6 py-4 align-top font-medium whitespace-nowrap">26 Maret 2026</td>
                    <td class="px-6 py-4 space-y-2">
                        <p>Motor</p><p>Truk</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-slate-500 font-medium">
                        <p>Rp. 2.000</p><p>Rp. 15.000</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-center font-bold text-slate-700">
                        <p>20</p><p>5</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 font-bold text-slate-600">
                        <p>Rp. 40.000</p><p>Rp. 75.000</p>
                    </td>
                    <td class="px-6 py-4 align-top font-black text-slate-800">Rp. 115.000</td>
                </tr>

                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                    <td class="px-6 py-4 text-center align-top">3</td>
                    <td class="px-6 py-4 align-top font-medium whitespace-nowrap">27 Maret 2026</td>
                    <td class="px-6 py-4 space-y-2">
                        <p>Mobil Pribadi</p><p>Pick Up</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-slate-500 font-medium">
                        <p>Rp. 5.000</p><p>Rp. 7.000</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-center font-bold">
                        <p>15</p><p>8</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 font-bold">
                        <p>Rp. 75.000</p><p>Rp. 56.000</p>
                    </td>
                    <td class="px-6 py-4 align-top font-black text-slate-800">Rp. 131.000</td>
                </tr>

                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                    <td class="px-6 py-4 text-center align-top">4</td>
                    <td class="px-6 py-4 align-top font-medium whitespace-nowrap">28 Maret 2026</td>
                    <td class="px-6 py-4 space-y-2">
                        <p>Motor</p><p>Bus Lintas</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-slate-500 font-medium">
                        <p>Rp. 2.000</p><p>Rp. 20.000</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 text-center font-bold">
                        <p>50</p><p>3</p>
                    </td>
                    <td class="px-6 py-4 space-y-2 font-bold">
                        <p>Rp. 100.000</p><p>Rp. 60.000</p>
                    </td>
                    <td class="px-6 py-4 align-top font-black text-slate-800">Rp. 160.000</td>
                </tr>
            </tbody>

            <tfoot class="bg-slate-100 font-black text-slate-800 border-t-2 border-slate-300 sticky bottom-0 z-10 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center uppercase tracking-[0.2em] text-sm">Total</td>
                    <td class="px-6 py-4 text-center text-sm">489</td>
                    <td class="px-6 py-4 text-center">-</td>
                    <td class="px-6 py-4 text-sm text-blue-600">Rp. 991.000</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</div>

<style>
    /* Styling scrollbar tipis agar tetap elegan */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    /**
     * UI Logic: Toggle Submenu Laporan pada Sidebar
     */
    function toggleSubMenu() {
        const elements = {
            subMenu: document.getElementById('subMenuLaporan'),
            chevron: document.getElementById('chevron-icon'),
            btn: document.getElementById('btn-laporan'),
            indicator: document.getElementById('line-indicator'),
            icon: document.getElementById('icon-laporan')
        };

        const isHidden = elements.subMenu.classList.toggle('hidden');
        elements.chevron.classList.toggle('rotate-180');

        if (!isHidden) {
            // State: Submenu Terbuka (Aktif)
            elements.indicator.classList.remove('hidden');
            elements.btn.style.backgroundColor = 'rgba(37, 61, 107, 0.55)';
            elements.btn.classList.add('text-white');
            elements.btn.classList.remove('text-white/60');
            if (elements.icon) elements.icon.classList.add('opacity-100');
        } else {
            // State: Submenu Tertutup (Default)
            elements.indicator.classList.add('hidden');
            elements.btn.style.backgroundColor = 'transparent';
            elements.btn.classList.remove('text-white');
            elements.btn.classList.add('text-white/60');
            if (elements.icon) elements.icon.classList.remove('opacity-100');
        }
    }

    /**
 * Charts Initialization - Fixed for Figma Area Style
 */
document.addEventListener('DOMContentLoaded', () => {

    const canvasFinancial = document.getElementById('financialChart');
    
    if (canvasFinancial) {
        const ctxFinancial = canvasFinancial.getContext('2d');
        
        new Chart(ctxFinancial, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                datasets: [
    {
        label: 'Target',
        data: [210, 140, 200, 160, 130, 240],
        backgroundColor: 'rgba(102, 141, 231, 0.6)', // #668DE7 opacity 60%
        borderColor: '#668DE7',
        borderWidth: 2,
        fill: true,
        pointRadius: 3,
        pointBackgroundColor: '#668DE7',
        tension: 0,
        order: 3 // Memaksa layer ini ke paling belakang
    },
    {
        label: 'Hasil Uji Petik',
        data: [195, 125, 175, 140, 115, 200],
        backgroundColor: 'rgba(196, 163, 156, 0.6)', // #C4A39C opacity 60%
        borderColor: '#C4A39C',
        borderWidth: 2,
        fill: true,
        pointRadius: 3,
        pointBackgroundColor: '#C4A39C',
        tension: 0,
        order: 2
    },
    {
        label: 'Realisasi',
        data: [150, 100, 150, 120, 100, 180],
        backgroundColor: 'rgba(80, 167, 99, 0.6)', // #50A763 opacity 60%
        borderColor: '#50A763',
        borderWidth: 2,
        fill: true,
        pointRadius: 3,
        pointBackgroundColor: '#50A763',
        tension: 0,
        order: 1 // Memaksa layer ini ke paling depan
    }
]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false } 
                },
                scales: {
                    y: {
                        stacked: false, // MATIKAN agar nilai tidak dijumlahkan
                        beginAtZero: true,
                        min: 0,
                        max: 250,
                        ticks: {
                            stepSize: 50,
                            color: '#94a3b8',
                            font: { size: 10 }
                        },
                        grid: { 
                            color: '#f1f5f9',
                            drawBorder: false 
                        }
                    },
                    x: {
                        stacked: false, // MATIKAN
                        grid: { display: false },
                        ticks: { 
                            color: '#94a3b8', 
                            font: { size: 10 } 
                        }
                    }
                }
            }
        });
    }
});
        // 2. Volume Kendaraan (Bar Chart)
        const ctxVolume = document.getElementById('volumeChart');
        if (ctxVolume) {
            new Chart(ctxVolume.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Motor', 'Mobil'],
                    datasets: [{
                        data: [476, 13],
                        backgroundColor: ['#4A6FA5', '#94A3B8'],
                        borderRadius: 6,
                        barThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 10 } }
                        }
                    }
                }
            });
        };
</script>
</body>
</html>