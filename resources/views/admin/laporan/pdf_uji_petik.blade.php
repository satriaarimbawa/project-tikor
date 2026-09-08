<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Uji Petik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .header-title { font-size: 12px; margin-bottom: 5px; }
        .page-break { page-break-after: always; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        
        .logo-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo-container table {
            border: none;
            margin-top: 0;
        }
        .logo-container th, .logo-container td {
            border: none;
        }
        
        .header-text {
            text-align: center;
            font-weight: bold;
        }
        .kop-surat {
            font-size: 14px;
        }
        .kop-surat-dinas {
            font-size: 16px;
        }
    </style>
</head>
<body>

    <!-- HEADER TEMPLATE -->
    @php
        $logoKlungkung = '';
        $logoDishub = '';
        
        try {
            $pathKlungkung = public_path('assets/Logo_Klungkung.png');
            if (file_exists($pathKlungkung)) {
                $type = pathinfo($pathKlungkung, PATHINFO_EXTENSION);
                $data = file_get_contents($pathKlungkung);
                $logoKlungkung = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            $pathDishub = public_path('assets/logo_dishub.png');
            if (file_exists($pathDishub)) {
                $type = pathinfo($pathDishub, PATHINFO_EXTENSION);
                $data = file_get_contents($pathDishub);
                $logoDishub = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        } catch (\Exception $e) {
            // Log or handle error if needed
        }

        $headerHtml = '
        <div class="logo-container">
            <table>
                <tr>
                    <td width="15%" class="text-center">
                        <img src="' . $logoKlungkung . '" width="50" alt="">
                    </td>
                    <td width="70%" class="header-text">
                        <div class="kop-surat">PEMERINTAH KABUPATEN KLUNGKUNG</div>
                        <div class="kop-surat-dinas">DINAS PERHUBUNGAN</div>
                        <div style="font-weight: normal; font-size:10px;">Jalan Raya Watu Klotok, Telp : (0366) 21087</div>
                        <div style="font-weight: normal; font-size:10px;">SEMARAPURA</div>
                    </td>
                    <td width="15%" class="text-center">
                        <img src="' . $logoDishub . '" width="50" alt="">
                    </td>
                </tr>
            </table>
            <hr style="border: 1px solid black; margin-top:5px; margin-bottom: 15px;">
        </div>';
    @endphp

    <!-- ================= HALAMAN 1: REKAPITULASI (GABUNGAN) ================= -->
    {!! $headerHtml !!}
    <div class="text-center header-title font-bold">REKAPITULASI UJI PETIK KENDARAAN GABUNGAN</div>
    <div class="text-center header-title font-bold" style="margin-bottom: 20px;">
        DI LOKASI PARKIR 
        @php
            $lokasiNames = [];
            foreach($lokasiRef as $loc) { $lokasiNames[] = strtoupper($loc['nama_lokasi'] ?? $loc['alamat'] ?? 'TANPA NAMA'); }
            echo implode(' DAN ', $lokasiNames);
        @endphp
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Tanggal</th>
                <th colspan="{{ count($objekNames) }}">Jumlah Kendaraan</th>
                <th colspan="{{ count($objekNames) + 1 }}">Total Penerimaan (Rp)</th>
            </tr>
            <tr>
                @foreach($objekNames as $key => $nama)
                    <th>{{ strtoupper($nama) }}</th>
                @endforeach
                
                @foreach($objekNames as $key => $nama)
                    <th>{{ strtoupper($nama) }}</th>
                @endforeach
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $n = 1;
                $totVolGab = [];
                $totPenGab = [];
                $grandTotPenGab = 0;
                foreach($objekNames as $key => $nama) {
                    $totVolGab[$key] = 0;
                    $totPenGab[$key] = 0;
                }
            @endphp
            @foreach($dates as $date)
                <tr>
                    <td class="text-center">{{ $n++ }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                    
                    @foreach($objekNames as $key => $nama)
                        @php 
                            $v = $gabungan[$date]['volume'][$key]; 
                            $totVolGab[$key] += $v;
                        @endphp
                        <td class="text-center">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    
                    @php $harianTotal = 0; @endphp
                    @foreach($objekNames as $key => $nama)
                        @php 
                            $p = $gabungan[$date]['penerimaan'][$key]; 
                            $totPenGab[$key] += $p;
                            $harianTotal += $p;
                        @endphp
                        <td class="text-right">{{ $p > 0 ? number_format($p, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    
                    @php $grandTotPenGab += $harianTotal; @endphp
                    <td class="text-right font-bold">{{ $harianTotal > 0 ? number_format($harianTotal, 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold bg-gray-100">
                <td colspan="2" class="text-center">TOTAL</td>
                @foreach($objekNames as $key => $nama)
                    <td class="text-center">{{ $totVolGab[$key] > 0 ? number_format($totVolGab[$key], 0, ',', '.') : '-' }}</td>
                @endforeach
                @foreach($objekNames as $key => $nama)
                    <td class="text-right">{{ $totPenGab[$key] > 0 ? number_format($totPenGab[$key], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td class="text-right">{{ $grandTotPenGab > 0 ? number_format($grandTotPenGab, 0, ',', '.') : '-' }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="page-break"></div>

    <!-- ================= HALAMAN 3+: REKAPITULASI (PER LOKASI) ================= -->
    @foreach($lokasiRef as $locId => $loc)
        {!! $headerHtml !!}
        <div class="text-center header-title font-bold">REKAPITULASI UJI PETIK KENDARAAN</div>
        <div class="text-center header-title font-bold" style="margin-bottom: 20px;">
            DI LOKASI PARKIR {{ strtoupper($loc['nama_lokasi'] ?? $loc['alamat'] ?? 'TANPA NAMA') }}
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Tanggal</th>
                    <th colspan="{{ count($objekNames) }}">Jumlah Kendaraan</th>
                    <th colspan="{{ count($objekNames) + 1 }}">Total Penerimaan (Rp)</th>
                </tr>
                <tr>
                    @foreach($objekNames as $key => $nama)
                        <th>{{ strtoupper($nama) }}</th>
                    @endforeach
                    
                    @foreach($objekNames as $key => $nama)
                        <th>{{ strtoupper($nama) }}</th>
                    @endforeach
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $n = 1;
                    $totVolLoc = [];
                    $totPenLoc = [];
                    $grandTotPenLoc = 0;
                    foreach($objekNames as $key => $nama) {
                        $totVolLoc[$key] = 0;
                        $totPenLoc[$key] = 0;
                    }
                @endphp
                @foreach($dates as $date)
                    <tr>
                        <td class="text-center">{{ $n++ }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                        
                        @foreach($objekNames as $key => $nama)
                            @php 
                                $v = $dataHarian[$date][$locId]['volume'][$key]; 
                                $totVolLoc[$key] += $v;
                            @endphp
                            <td class="text-center">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                        @endforeach
                        
                        @php $harianTotalLoc = 0; @endphp
                        @foreach($objekNames as $key => $nama)
                            @php 
                                $p = $dataHarian[$date][$locId]['penerimaan'][$key]; 
                                $totPenLoc[$key] += $p;
                                $harianTotalLoc += $p;
                            @endphp
                            <td class="text-right">{{ $p > 0 ? number_format($p, 0, ',', '.') : '-' }}</td>
                        @endforeach
                        
                        @php $grandTotPenLoc += $harianTotalLoc; @endphp
                        <td class="text-right font-bold">{{ $harianTotalLoc > 0 ? number_format($harianTotalLoc, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-100">
                    <td colspan="2" class="text-center">TOTAL</td>
                    @foreach($objekNames as $key => $nama)
                        <td class="text-center">{{ $totVolLoc[$key] > 0 ? number_format($totVolLoc[$key], 0, ',', '.') : '-' }}</td>
                    @endforeach
                    @foreach($objekNames as $key => $nama)
                        <td class="text-right">{{ $totPenLoc[$key] > 0 ? number_format($totPenLoc[$key], 0, ',', '.') : '-' }}</td>
                    @endforeach
                    <td class="text-right">{{ $grandTotPenLoc > 0 ? number_format($grandTotPenLoc, 0, ',', '.') : '-' }}</td>
                </tr>
            </tfoot>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>