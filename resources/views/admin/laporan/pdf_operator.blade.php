<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Uji Petik - Operator</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .logo-container { width: 100%; margin-bottom: 20px; }
        .logo-container table { width: 100%; border: none; }
        .logo-container td { border: none; padding: 0; }
        .header-text { text-align: center; font-weight: bold; }
        .kop-surat { font-size: 14px; }
        .kop-surat-dinas { font-size: 16px; }

        .report-info { margin-bottom: 20px; }
        .report-info table { width: 100%; border: none; }
        .report-info td { border: none; padding: 2px 0; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th, table.main-table td { border: 1px solid #000; padding: 6px; }
        table.main-table th { background-color: #f2f2f2; }

        .summary-box { margin-top: 20px; width: 300px; float: right; }
        .summary-box table { width: 100%; border-collapse: collapse; }
        .summary-box td { padding: 5px; border: 1px solid #ddd; }
        .bg-gray { background-color: #f9f9f9; }
        
        .clearfix { clear: both; }
    </style>
</head>
<body>

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
        } catch (\Exception $e) {}
    @endphp

    <div class="logo-container">
        <table>
            <tr>
                <td width="15%" class="text-center"><img src="{{ $logoKlungkung }}" width="50"></td>
                <td width="70%" class="header-text">
                    <div class="kop-surat">PEMERINTAH KABUPATEN KLUNGKUNG</div>
                    <div class="kop-surat-dinas">DINAS PERHUBUNGAN</div>
                    <div style="font-weight: normal; font-size:10px;">Jalan Raya Watu Klotok, Telp : (0366) 21087</div>
                    <div style="font-weight: normal; font-size:10px;">SEMARAPURA</div>
                </td>
                <td width="15%" class="text-center"><img src="{{ $logoDishub }}" width="50"></td>
            </tr>
        </table>
        <hr style="border: 1px solid black; margin-top:5px;">
    </div>

    <div class="text-center font-bold" style="font-size: 14px; margin-bottom: 20px;">
        LAPORAN HASIL UJI PETIK - OPERATOR
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td width="15%">Lokasi</td>
                <td width="2%">:</td>
                <td>{{ $namaLokasi }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $selectedDate }}</td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>:</td>
                <td>{{ $namaOperator }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Waktu</th>
                <th>Jenis Kendaraan</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Tarif Lama</th>
                <th width="15%">Tarif Baru</th>
                <th width="15%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapitulasi as $row)
                @foreach($row['details'] as $index => $detail)
                    <tr>
                        @if($index === 0)
                            <td class="text-center" rowspan="{{ count($row['details']) }}">{{ $no++ }}</td>
                            <td class="text-center" rowspan="{{ count($row['details']) }}">{{ $row['waktu'] }}</td>
                        @endif
                        <td>{{ $detail['jenis'] }}</td>
                        <td class="text-center">{{ number_format($detail['jumlah']) }}</td>
                        <td class="text-right" style="color: #666;">Rp {{ number_format($detail['tarif_lama'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail['tarif'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($detail['penerimaan'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data survei.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td colspan="6" class="text-center">TOTAL REALISASI</td>
                <td class="text-right">Rp {{ number_format($keuangan['realisasi'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-box">
        <table>
            <tr class="bg-gray">
                <td width="50%">Total Kedatangan</td>
                <td class="text-right font-bold">{{ number_format($totalKedatangan) }} Unit</td>
            </tr>
            <tr>
                <td>Target Harian</td>
                <td class="text-right">Rp {{ number_format($keuangan['target'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Realisasi</td>
                <td class="text-right">Rp {{ number_format($keuangan['realisasi'], 0, ',', '.') }}</td>
            </tr>
            <tr class="font-bold" style="color: #d32f2f;">
                <td>Selisih</td>
                <td class="text-right">Rp {{ number_format($keuangan['target'] - $keuangan['realisasi'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

</body>
</html>