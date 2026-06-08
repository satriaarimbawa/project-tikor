<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Operator</title>


    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>

<body class="flex bg-[#F5F7FA]">

    @include('admin.template.navbar')



    @php
    $selisih = $keuangan['target'] - $keuangan['realisasi'];
    @endphp

    <main class="main-content lg:ml-64 p-4 md:p-8 w-full font-sans">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Hasil Uji Petik - Operator</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-12 object-contain" alt="Logo">
        </div>

        <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-12 gap-6 mb-6">
            <div class="col-span-12 xl:col-span-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                    <h2 class="text-lg font-bold mb-4 text-black">Ringkasan Harian</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $iconMap = [
                                'motor' => ['icon' => 'mdi:motorbike', 'color' => '#FCA24C', 'bg' => 'from-white via-orange-100 to-white'],
                                'mobil' => ['icon' => 'mdi:car', 'color' => '#4169E1', 'bg' => 'from-white via-blue-100 to-white'],
                                'bus' => ['icon' => 'mdi:bus', 'color' => '#4169E1', 'bg' => 'from-white via-blue-100 to-white'],
                                'minibus' => ['icon' => 'mdi:bus-side', 'color' => '#F0C13D', 'bg' => 'from-white via-yellow-100 to-white'],
                                'truk' => ['icon' => 'mdi:truck', 'color' => '#A020F0', 'bg' => 'from-white via-purple-100 to-white'],
                            ];
                        @endphp

                        @foreach($dataRingkasan as $key => $total)
                        @php 
                            $style = $iconMap[$key] ?? ['icon' => 'mdi:file-document-edit-outline', 'color' => '#6B7280', 'bg' => 'from-white via-gray-100 to-white'];
                        @endphp
                        <div class="flex items-center p-4 rounded-xl border border-gray-100 shadow-sm bg-gradient-to-r {{ $style['bg'] }}">
                            <div class="p-3 rounded-lg mr-4" style="background-color: {{ $style['color'] }}">
                                <iconify-icon icon="{{ $style['icon'] }}" class="text-white text-3xl"></iconify-icon>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-500 truncate">{{ $objekNames[$key] ?? ucfirst($key) }}</p>
                                <p class="text-xl font-bold">{{ number_format($total) }}</p>
                            </div>
                        </div>
                        @endforeach

                        <div class="col-span-2 flex items-center justify-between px-8 py-4 bg-[#E5E7EB] rounded-xl mt-2">
                            <p class="text-sm font-bold text-gray-700 uppercase">Total Kedatangan</p>
                            <p class="text-4xl font-black text-gray-700">{{ number_format($totalKedatangan) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4 space-y-4">
                <select name="lokasi_id" onchange="this.form.submit()"
                    class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm">
                    <option value="">Pilih Lokasi</option>
                    @foreach($lokasiMaster as $id => $loc)
                    <option value="{{ $id }}" {{ $lokasiId == $id ? 'selected' : '' }}>
                        {{ $loc['nama_lokasi'] ?? ($loc['alamat'] ?? 'Tanpa Nama') }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                        class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm text-sm">
                    <select name="user_id" onchange="this.form.submit()"
                        class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm">
                        <option value="">Semua Petugas</option>
                        @foreach($userMaster as $id => $u)
                        @if(($u['role_user'] ?? '') == 'operator')
                        <option value="{{ $id }}" {{ $userId == $id ? 'selected' : '' }}>{{ $u['username'] }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="bg-gray-100 p-4 rounded-xl space-y-3">
                    <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Total Target</span>
                        <span class="font-bold">Rp. {{ number_format($keuangan['target'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Total Realisasi</span>
                        <span class="font-bold">Rp. {{ number_format($keuangan['realisasi'], 0, ',', '.') }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200 text-red-600">
                        <span class="text-xs font-semibold uppercase">Selisih</span>
                        <span class="font-bold">Rp. {{ number_format($selisih, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit" formaction="{{ route('laporan.operator.download') }}"
                    class="w-full bg-[#3B82F6] text-white py-3 rounded-xl flex items-center justify-center gap-2 font-bold shadow-lg">
                    <iconify-icon icon="mdi:printer"></iconify-icon> Unduh PDF
                </button>
            </div>
        </form>

        <div class="grid grid-cols-12 gap-6 mb-6">
            <div class="col-span-12 xl:col-span-7 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold mb-4 text-gray-800">Grafik Berdasarkan Waktu Kedatangan</h3>
                <div class="h-64 w-full">
                    <canvas id="lineChart" data-labels="{{ json_encode($grafikWaktu['labels']) }}"
                        data-values="{{ json_encode($grafikWaktu['data']) }}">
                    </canvas>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold mb-4 text-gray-800">Volume Kendaraan</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    <div class="h-64 w-full sm:w-2/3 relative">
                        <canvas id="barChart" data-labels="{{ json_encode($grafikVolume['labels']) }}"
                            data-values="{{ json_encode($grafikVolume['data']) }}">
                        </canvas>
                    </div>

                    <div class="w-full sm:w-1/3 space-y-2 overflow-y-auto max-h-64 pr-2">
                        @foreach($dataRingkasan as $k => $v)
                        <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase truncate">Total {{ $objekNames[$k] ?? $k }}</p>
                            <p class="font-bold text-lg text-blue-500">{{ number_format($v) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold mb-6 text-lg text-gray-800">Rekapitulasi Tarif dan Penerimaan</h3>

            <div class="overflow-x-auto max-h-[600px] overflow-y-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider sticky top-0 z-10 shadow-sm">
                            <th class="p-4 border-b">No</th>
                            <th class="p-4 border-b">Waktu</th>
                            <th class="p-4 border-b">Jenis</th>
                            <th class="p-4 border-b text-center">Jumlah</th>
                            <th class="p-4 border-b">Tarif Lama</th>
                            <th class="p-4 border-b">Tarif Baru</th>
                            <th class="p-4 border-b">Total Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @php $no = 1; @endphp
                        @forelse($rekapitulasi as $row)
                        @foreach($row['details'] as $index => $detail)
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100">
                            @if($index === 0)
                            <td class="p-4 font-medium text-center bg-white" rowspan="{{ count($row['details']) }}">
                                {{ $no++ }}
                            </td>
                            <td class="p-4 text-center font-medium bg-white" rowspan="{{ count($row['details']) }}">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs">
                                    {{ $row['waktu'] }}
                                </span>
                            </td>
                            @endif

                            <td class="p-4 text-gray-600">
                                {{ $detail['jenis'] }}
                            </td>
                            <td class="p-4 text-center font-bold text-gray-700">
                                {{ number_format($detail['jumlah']) }}
                            </td>
                            <td class="p-4 text-gray-400 font-mono">
                                Rp {{ number_format($detail['tarif_lama'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-blue-600 font-bold font-mono">
                                Rp {{ number_format($detail['tarif'], 0, ',', '.') }}
                            </td>

                            @if($index === 0)
                            <td class="p-4 font-bold text-gray-800 bg-white" rowspan="{{ count($row['details']) }}">
                                <div class="text-blue-600">
                                    Rp {{ number_format($row['total_penerimaan'], 0, ',', '.') }}
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-gray-400 italic">
                                Belum ada data survei untuk filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/navbar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js">
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Line Chart
        const lineCanvas = document.getElementById('lineChart');
        const lineLabels = JSON.parse(lineCanvas.getAttribute('data-labels'));
        const lineValues = JSON.parse(lineCanvas.getAttribute('data-values'));

        new Chart(lineCanvas, {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [{
                    label: 'Jumlah Kedatangan',
                    data: lineValues,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // 2. Bar Chart
        const barCanvas = document.getElementById('barChart');
        const barLabels = JSON.parse(barCanvas.getAttribute('data-labels'));
        const barValues = JSON.parse(barCanvas.getAttribute('data-values'));

        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    data: barValues,
                    backgroundColor: ['#60A5FA', '#818CF8'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
    </script>
</body>

</html>