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
    $dataRingkasan = [
    'bus' => 489,
    'motor' => 30,
    'minibus' => 11,
    'truk' => 50
    ];

    $totalKedatangan = array_sum($dataRingkasan);

    $keuangan = [
    'target' => 2037000,
    'realisasi' => 1473000,
    ];
    $selisih = $keuangan['target'] - $keuangan['realisasi'];

    $rekapitulasi = [
    [
    'no' => 1,
    'waktu' => '08 - 09',
    'details' => [
    ['jenis' => 'Motor', 'jumlah' => 476, 'tarif' => 1000, 'penerimaan_lama' => 476000],
    ['jenis' => 'Mini Bus', 'jumlah' => 13, 'tarif' => 2000, 'penerimaan_lama' => 26000],
    ],
    'total_penerimaan' => 952000
    ],
    [
    'no' => 2,
    'waktu' => '09 - 10',
    'details' => [
    ['jenis' => 'Motor', 'jumlah' => 476, 'tarif' => 1000, 'penerimaan_lama' => 476000],
    ['jenis' => 'Mini Bus', 'jumlah' => 13, 'tarif' => 2000, 'penerimaan_lama' => 26000],
    ],
    'total_penerimaan' => 952000
    ]
    ];

    $totalJumlah = 0;
    $totalPenerimaanLama = 0;
    $totalPenerimaanBaru = 0;

    foreach($rekapitulasi as $row) {
    foreach($row['details'] as $detail) {
    $totalJumlah += $detail['jumlah'];
    $totalPenerimaanLama += $detail['penerimaan_lama'];
    }
    $totalPenerimaanBaru += $row['total_penerimaan'];
    }




    $grafikWaktu = [
    'labels' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00',
    '17:00','18.00','19.00','20.00','21.00','22.00'],
    'data' => [210, 145, 120, 110, 185, 130, 115, 200, 120, 225, 120, 200, 195, 90,220]
    ];

    // Data untuk Grafik Batang (Volume Kendaraan)
    $grafikVolume = [
    'labels' => ['Motor', 'Mobil', 'Mini Bus', 'Truk'],
    'data' => [476, 313, 145, 98]
    ];
    @endphp




    <main class="main-content ml-64 p-8 w-full font-sans">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Hasil Uji Petik - Operator</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-12 object-contain" alt="Logo">
        </div>

        <div class="grid grid-cols-12 gap-6 mb-6">
            <div class="col-span-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                    <h2 class="text-lg font-bold mb-4 text-black">Ringkasan Harian</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="flex items-center p-4 rounded-xl border border-blue-100 shadow-sm flex-1 bg-gradient-to-r from-white via-blue-100 to-white">
                            <div class="bg-[#4169E1] p-3 rounded-lg mr-4">
                                <iconify-icon icon="mdi:bus" class="text-white text-3xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Bus</p>
                                <p class="text-xl font-bold">{{ number_format($dataRingkasan['bus']) }}</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center p-4 rounded-xl border border-orange-100 shadow-sm flex-1 bg-gradient-to-r from-white via-orange-100 to-white">
                            <div class="bg-[#FCA24C] p-3 rounded-lg mr-4">
                                <iconify-icon icon="mdi:motorbike" class="text-white text-3xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Motor</p>
                                <p class="text-xl font-bold">{{ number_format($dataRingkasan['motor']) }}</p>
                            </div>
                        </div>
                        <!-- logo mini bus coba sesuain sama punya operator -->
                        <div
                            class="flex items-center p-4 rounded-xl border border-yellow-100 shadow-sm flex-1 bg-gradient-to-r from-white via-yellow-100 to-white">
                            <div class="bg-[#F0C13D] p-3 rounded-lg mr-4">
                                <iconify-icon icon="mdi:bus-side" class="text-white text-3xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Mini Bus</p>
                                <p class="text-xl font-bold"> {{ number_format($dataRingkasan['minibus']) }}</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center p-4 rounded-xl border border-purple-100 shadow-sm flex-1 bg-gradient-to-r from-white via-purple-100 to-white">
                            <div class="bg-[#A020F0] p-3 rounded-lg mr-4">
                                <iconify-icon icon="mdi:truck" class="text-white text-3xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Truk</p>
                                <p class="text-xl font-bold">{{ number_format($dataRingkasan['truk']) }}</p>
                            </div>
                        </div>
                        <div
                            class="col-span-2 flex items-center justify-between px-8 py-4 bg-[#E5E7EB] rounded-xl mt-2">
                            <p class="text-sm font-bold text-gray-700 uppercase">Total Kedatangan</p>
                            <p class="text-4xl font-black text-gray-700">{{ number_format($totalKedatangan) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-4 space-y-4">
                <select class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm">
                    <option>Pilih Lokasi</option>
                </select>
                <div class="flex gap-2">
                    <input type="date" class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm text-sm">
                    <select class="w-full p-3 bg-white border border-gray-300 rounded-xl shadow-sm">
                        <option>[ Nama Petugas ]</option>
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

                <button
                    class="w-full bg-[#3B82F6] text-white py-3 rounded-xl flex items-center justify-center gap-2 font-bold shadow-lg">
                    <iconify-icon icon="mdi:printer"></iconify-icon> Unduh PDF
                </button>
            </div>
        </div>



        <div class="grid grid-cols-12 gap-6 mb-6">
            <div class="col-span-7 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold mb-4 text-gray-800">Grafik Berdasarkan Waktu Kedatangan</h3>
                <div class="h-64 w-full">
                    <canvas id="lineChart" data-labels="{{ json_encode($grafikWaktu['labels']) }}"
                        data-values="{{ json_encode($grafikWaktu['data']) }}">
                    </canvas>
                </div>
            </div>

            <div class="col-span-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold mb-4 text-gray-800">Volume Kendaraan</h3>
                <div class="flex gap-4 items-start">
                    <div class="h-64 w-2/3 relative">
                        <canvas id="barChart" data-labels="{{ json_encode($grafikVolume['labels']) }}"
                            data-values="{{ json_encode($grafikVolume['data']) }}">
                        </canvas>
                    </div>

                    <div class="w-1/3 space-y-2">
                        <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase">Total Motor</p>
                            <p class="font-bold text-lg text-blue-500">476</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase">Total Mobil</p>
                            <p class="font-bold text-lg text-indigo-500">313</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase">Total Mini Bus</p>
                            <p class="font-bold text-lg text-indigo-500">145</p>
                        </div>
                        <div class="bg-gray-50 p-2 rounded text-center border border-gray-100">
                            <p class="text-[10px] text-gray-500 uppercase">Total Truk</p>
                            <p class="font-bold text-lg text-indigo-500">98</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold mb-6 text-lg">Rekapitulasi Tarif dan Penerimaan Baru</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs tracking-wider">
                            <th class="p-4 rounded-l-xl">No</th>
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Jenis</th>
                            <th class="p-4 text-center">Jumlah</th>
                            <th class="p-4">Tarif</th>
                            <th class="p-4">Total Penerimaan Lama</th>
                            <th class="p-4 rounded-r-xl">Total Penerimaan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($rekapitulasi as $row)
                        @foreach($row['details'] as $index => $detail)
                        <tr class="bg-white shadow-[0_2px_10px_-3px_rgba(0,0,0,0.07)] rounded-xl overflow-hidden">
                            @if($index === 0)
                            <td class="p-4 font-medium border-l border-t border-b rounded-l-xl text-center"
                                rowspan="{{ count($row['details']) }}">
                                {{ $row['no'] }}
                            </td>
                            <td class="p-4 border-t border-b text-center font-medium"
                                rowspan="{{ count($row['details']) }}">
                                {{ $row['waktu'] }}
                            </td>
                            @endif

                            <td class="p-4 border-t border-b text-gray-600 font-medium italic">
                                {{ $detail['jenis'] }}
                            </td>
                            <td class="p-4 border-t border-b text-center font-bold">
                                {{ number_format($detail['jumlah']) }}
                            </td>
                            <td class="p-4 border-t border-b text-gray-500 italic">
                                Rp. {{ number_format($detail['tarif'], 0, ',', '.') }}
                            </td>
                            <td class="p-4 border-t border-b font-semibold">
                                Rp. {{ number_format($detail['penerimaan_lama'], 0, ',', '.') }}
                            </td>

                            @if($index === 0)
                            <td class="p-4 border-r border-t border-b rounded-r-xl font-bold text-gray-800"
                                rowspan="{{ count($row['details']) }}">
                                Rp. {{ number_format($row['total_penerimaan'], 0, ',', '.') }}
                            </td>
                            @endif
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-gray-200 text-gray-800 font-black">
                            <td class="p-4 rounded-l-xl text-center uppercase italic" colspan="3">Total</td>
                            <td class="p-4 text-center">{{ number_format($totalJumlah) }}</td>
                            <td class="p-4 text-center">-</td>
                            <td class="p-4">Rp. {{ number_format($totalPenerimaanLama, 0, ',', '.') }}</td>
                            <td class="p-4 rounded-r-xl">Rp. {{ number_format($totalPenerimaanBaru, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
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