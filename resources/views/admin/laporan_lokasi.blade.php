<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Summary - Dashboard</title>
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

    <main class="flex-1 ml-64 p-8">
        <div class="flex justify-between items-start mb-6">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Laporan Summary Hasil Uji Petik - Berdasarkan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-12 object-contain" alt="Logo">
        </div>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('laporan.lokasi.filter') }}" method="POST" class="flex justify-between items-center mb-8">
            @csrf
            <button type="button" class="flex items-center gap-2 bg-[#4A6FA5] hover:bg-blue-800 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition text-sm">
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
            <div class="grid grid-cols-5 gap-6">
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" style="background: linear-gradient(90deg, rgba(16, 86, 216, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
                    <div class="w-12 h-12 rounded-lg bg-[#406ABA]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Bus.png') }}" class="w-6 h-6 object-contain">
                    </div>
                    <div><p class="text-[10px] text-gray-700 font-semibold uppercase">Bus</p><p class="text-2xl font-bold text-gray-900">{{ $totals['bus'] }}</p></div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" style="background: linear-gradient(90deg, rgba(233, 164, 38, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
                    <div class="w-12 h-12 rounded-lg bg-[#E9A426]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Motor.png') }}" class="w-6 h-6 object-contain">
                    </div>
                    <div><p class="text-[10px] text-gray-700 font-semibold uppercase">Motor</p><p class="text-2xl font-bold text-gray-900">{{ $totals['motor'] }}</p></div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" style="background: linear-gradient(90deg, rgba(237, 233, 37, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
                    <div class="w-12 h-12 rounded-lg bg-[#EDE925]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Mini Bus.png') }}" class="w-6 h-6 object-contain">
                    </div>
                    <div><p class="text-[10px] text-gray-700 font-semibold uppercase">Mini Bus</p><p class="text-2xl font-bold text-gray-900">{{ $totals['minibus'] }}</p></div>
                </div>
                <div class="p-4 rounded-xl shadow-md flex items-center gap-4 border border-gray-100" style="background: linear-gradient(90deg, rgba(149, 62, 225, 0.4) 50%, rgba(255, 255, 255, 0.4) 100%);">
                    <div class="w-12 h-12 rounded-lg bg-[#953EE1]/80 flex items-center justify-center shadow-md">
                        <img src="{{ asset('assets/Truk.png') }}" class="w-6 h-6 object-contain">
                    </div>
                    <div><p class="text-[10px] text-gray-700 font-semibold uppercase">Truk</p><p class="text-2xl font-bold text-gray-900">{{ $totals['truk'] }}</p></div>
                </div>
                <div class="flex flex-col justify-center items-center rounded-xl border border-slate-200 p-4 shadow-sm" style="background-color: rgba(217, 217, 217, 0.6);">
                    <p class="text-[9px] font-bold text-slate-600 uppercase text-center leading-tight">Total Kedatangan</p>
                    <p class="text-3xl font-black text-slate-800">{{ $totalVolume }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 mb-6 text-slate-800">
            <div class="col-span-8 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold mb-10">Ringkasan Kinerja Keuangan</h2>
                <div class="flex items-center gap-10"> 
                    <div class="flex-1 h-64"><canvas id="financialChart"></canvas></div>
                    <div class="w-52 space-y-4">
                        <div class="p-4 rounded-xl border border-slate-200" style="background-color: rgba(217, 217, 217, 0.6);">
                            <p class="text-[9px] font-bold text-slate-500 uppercase">Total Realisasi</p>
                            <p class="text-lg font-black text-slate-800">Rp. {{ number_format($totalPenerimaan, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold mb-6">Volume Kendaraan</h2>
                <div class="h-40 mb-6"><canvas id="volumeChart"></canvas></div>
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
                    labels: ['Motor', 'Mini Bus', 'Bus', 'Truk'],
                    datasets: [{
                        data: [{{ $totals['motor'] }}, {{ $totals['minibus'] }}, {{ $totals['bus'] }}, {{ $totals['truk'] }}],
                        backgroundColor: ['#E9A426', '#EDE925', '#406ABA', '#953EE1']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        });
    </script>
    <script src="{{ asset('js/navbar.js') }}"></script>
</body>
</html>
