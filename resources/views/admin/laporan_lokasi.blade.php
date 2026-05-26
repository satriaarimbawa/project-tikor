<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Summary - Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #E7EFF6 70%, #FFFFFF 100%); min-height: 100vh; }
        .sidebar-navy { background-color: rgba(37, 61, 107, 0.75) !important; backdrop-filter: blur(10px); }
    </style>
</head>
<body class="flex">
    @include('admin.template.navbar')

    <main class="flex-1 lg:ml-64 p-4 md:p-8 min-w-0 overflow-x-hidden">
        <div class="flex justify-between items-start mb-6">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Laporan Summary Hasil Uji Petik - Berdasarkan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-12 object-contain" alt="Logo">
        </div>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
        @endif

        <form id="filterForm" action="{{ route('laporan.lokasi.filter') }}" method="POST" class="flex justify-between items-center mb-8">
            @csrf
            <button type="button" onclick="downloadFilteredPdf()" class="flex items-center gap-2 bg-[#4A6FA5] hover:bg-blue-800 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition text-sm">
                <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> Unduh PDF
            </button>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()" class="text-sm font-medium border-none outline-none">
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">S/D</span>
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()" class="text-sm font-medium border-none outline-none">
                    </div>
                </div>

                <select name="lokasi_id" onchange="this.form.submit()" class="w-64 p-2 bg-white border border-slate-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    <option value="">Pilih Lokasi</option>
                    @foreach($daftarLokasi as $id => $loc)
                        <option value="{{ $id }}" {{ $id == $lokasiId ? 'selected' : '' }}>{{ $loc['nama_lokasi'] ?? ($loc['alamat'] ?? 'Tanpa Nama') }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <h2 class="text-lg font-bold mb-6 text-slate-800">Ringkasan Harian</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                @php
                    $colors = [
                        ['rgba(16, 86, 216, 0.4)', '#406ABA'],
                        ['rgba(233, 164, 38, 0.4)', '#E9A426'],
                        ['rgba(237, 233, 37, 0.4)', '#EDE925'],
                        ['rgba(149, 62, 225, 0.4)', '#953EE1'],
                        ['rgba(16, 185, 129, 0.4)', '#10B981'],
                        ['rgba(244, 63, 94, 0.4)', '#F43F5E'],
                        ['rgba(107, 114, 128, 0.4)', '#6B7280']
                    ];
                    $icons = [
                        'motor' => 'Motor.png',
                        'bus' => 'Bus.png',
                        'minibus' => 'Mini Bus.png',
                        'mobil' => 'Mini Bus.png',
                        'truk' => 'Truk.png',
                        'traktor' => 'Truk.png',
                        'pickup' => 'Truk.png'
                    ];
                @endphp

                @foreach($totals as $key => $val)
                    @php 
                        $c = $colors[$loop->index % count($colors)]; 
                        $iconFile = 'Bus.png'; // Default
                        foreach($icons as $iconKey => $file) {
                            if(strpos($key, $iconKey) !== false) {
                                $iconFile = $file;
                                break;
                            }
                        }
                    @endphp
                    <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" style="background: linear-gradient(90deg, {{ $c[0] }} 50%, rgba(255, 255, 255, 0.4) 100%);">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-md flex-shrink-0" style="background-color: {{ $c[1] }};">
                            <img src="{{ asset('assets/' . $iconFile) }}" class="w-6 h-6 object-contain">
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] text-gray-700 font-semibold uppercase truncate">{{ $objekNames[$key] ?? $key }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $val }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="flex flex-col justify-center items-center rounded-xl border border-slate-200 p-4 shadow-sm" style="background-color: rgba(217, 217, 217, 0.6);">
                    <p class="text-[9px] font-bold text-slate-600 uppercase text-center leading-tight">Total Kedatangan</p>
                    <p class="text-3xl font-black text-slate-800">{{ $totalVolume }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 mb-6 text-slate-800">
            <div class="col-span-12 xl:col-span-8 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold mb-10">Ringkasan Kinerja Keuangan</h2>
                <div class="flex flex-col md:flex-row items-center gap-10"> 
                    <div class="w-full h-64"><canvas id="financialChart"></canvas></div>
                    <div class="w-full md:w-52 space-y-4">
                        <div class="p-4 rounded-xl border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                            <p class="text-[9px] font-bold text-slate-500 uppercase">Total Realisasi</p>
                            <p class="text-lg font-black text-slate-800">Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 xl:col-span-4 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold mb-6">Volume Kendaraan</h2>
                <div class="h-64 mb-6">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200 text-slate-700">
            <div class="p-6 border-b border-slate-100"><h2 class="text-lg font-bold">Rekapitulasi Tarif dan Penerimaan</h2></div>
            <div class="overflow-y-auto max-h-80">
                <table class="w-full text-xs border-collapse">
                    <thead class="bg-slate-200 text-slate-500 font-bold uppercase tracking-wider sticky top-0">
                        <tr>
                            <th class="px-6 py-3 text-center">No</th>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Jenis</th>
                            <th class="px-6 py-3 text-left">Tarif</th>
                            <th class="px-6 py-3 text-center">Vol</th>
                            <th class="px-6 py-3 text-left">Total</th>
                            <th class="px-6 py-3 text-left">Total Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @php $n = 1; @endphp
                        @forelse($summary as $tgl => $data)
                        <tr class="border-b border-slate-200">
                            <td class="px-6 py-4 text-center align-top">{{ $n++ }}</td>
                            <td class="px-6 py-4 align-top font-medium">{{ $data['tgl_display'] }}</td>
                            <td class="px-6 py-4 space-y-1">@foreach($data['details'] as $item) <p>{{ $item['nama'] }}</p> @endforeach</td>
                            <td class="px-6 py-4 space-y-1">@foreach($data['details'] as $item) <p>Rp. {{ number_format($item['tarif'], 0, ',', '.') }}</p> @endforeach</td>
                            <td class="px-6 py-4 space-y-1 text-center font-bold">@foreach($data['details'] as $item) <p>{{ $item['vol'] }}</p> @endforeach</td>
                            <td class="px-6 py-4 space-y-1 font-bold">@foreach($data['details'] as $item) <p>Rp. {{ number_format($item['total'], 0, ',', '.') }}</p> @endforeach</td>
                            <td class="px-6 py-4 align-top font-black text-slate-800">Rp. {{ number_format($data['total_harian'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-slate-500">Pilih lokasi dan tanggal untuk melihat data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function downloadFilteredPdf() {
            const form = document.getElementById('filterForm');
            const baseUrl = "{{ route('laporan.lokasi.download') }}";
            
            // Ambil data manual karena FormData butuh elemen input
            const lokasiId = form.querySelector('select[name="lokasi_id"]').value;
            const startDate = form.querySelector('input[name="start_date"]').value;
            const endDate = form.querySelector('input[name="end_date"]').value;

            if (!lokasiId) {
                showAlert("Lokasi Belum Dipilih", "Silakan pilih lokasi terlebih dahulu sebelum mengunduh PDF.", "warning");
                return;
            }

            const finalUrl = `${baseUrl}?lokasi_id=${lokasiId}&start_date=${startDate}&end_date=${endDate}`;
            window.location.href = finalUrl;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const ctxFin = document.getElementById('financialChart').getContext('2d');
            new Chart(ctxFin, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Realisasi',
                        data: {!! json_encode($chartValues) !!},
                        borderColor: '#50A763',
                        backgroundColor: 'rgba(80, 167, 99, 0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            const ctxVol = document.getElementById('volumeChart').getContext('2d');
            new Chart(ctxVol, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($volumeChartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($volumeChartValues) !!},
                        backgroundColor: ['#E9A426', '#EDE925', '#406ABA', '#953EE1', '#10B981', '#F43F5E', '#6B7280']
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { display: false } 
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
    <script src="{{ asset('js/navbar.js') }}"></script>
    @include('template.shared_scripts')
</body>
</html>
