<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uji Petik Kendaraan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; }
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; border: none; }
        .header .logo { width: 80px; }
        .header .title { text-align: center; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin: 5px 0; text-transform: uppercase; }
        .header p { font-size: 10px; margin: 2px 0; }

        .report-title { text-align: center; margin-bottom: 20px; }
        .report-title h3 { font-size: 14px; margin: 0; text-transform: uppercase; text-decoration: underline; }
        .report-title h4 { font-size: 13px; margin: 5px 0; text-transform: uppercase; }

        .identity { width: 100%; margin-bottom: 15px; }
        .identity table { width: 100%; border: none; }
        .identity td { width: 33%; vertical-align: top; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px; text-align: center; word-wrap: break-word; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; font-size: 10px; }

        .summary-title { font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        
        .footer { margin-top: 30px; }
        .catatan { font-style: italic; border: 1px solid #ccc; padding: 10px; min-height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo" style="text-align: left;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b3/Lambang_Kabupaten_Klungkung.png" width="70" alt="Logo Klungkung">
                </td>
                <td class="title">
                    <h1>Pemerintah Kabupaten Klungkung</h1>
                    <h2>Dinas Perhubungan</h2>
                    <p>Jalan Raya Watu Klotok, Telp : (0366) 21087</p>
                    <p>SEMARAPURA</p>
                </td>
                <td class="logo" style="text-align: right;">
                    <img src="https://dishub.klungkungkab.go.id/img/logo-dishub.png" width="70" alt="Logo Dishub">
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h3>Uji Petik Kendaraan</h3>
        <h4>{{ $nama_lokasi }}</h4>
    </div>

    <div class="identity">
        <table>
            <tr>
                <td>Arah : ...................................</td>
                <td style="text-align: center;">Tanggal : {{ $tanggal }}</td>
                <td style="text-align: right;">Surveyor : {{ $surveyor }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-title">Rincian Per Jam:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 100px;">Waktu</th>
                @foreach($objekSurvei as $obj)
                    <th>{{ $obj }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($hourlyData as $label => $counts)
            <tr>
                <td>{{ $label }}</td>
                @foreach($objekSurvei as $obj)
                    @php $key = strtolower(str_replace(' ', '', $obj)); @endphp
                    <td>{{ $counts[$key] ?? 0 }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-title">Rekapitulasi Jumlah Kendaraan dan Tarif:</div>
    <table class="data-table" style="width: 70%; margin-left: 0;">
        <thead>
            <tr>
                <th style="width: 40px;">No.</th>
                <th>Jenis Kendaraan</th>
                <th>Jumlah Kendaraan</th>
                <th>Tarif</th>
                <th>Total Penerimaan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($summaryData as $key => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td style="text-align: left;">{{ $data['nama'] }}</td>
                <td>{{ $data['jumlah'] }}</td>
                <td>Rp. {{ number_format($data['tarif'], 0, ',', '.') }}</td>
                <td>Rp. {{ number_format($data['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="4" style="text-align: right;">TOTAL</td>
                <td>Rp. {{ number_format($totalSeluruh, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Catatan/Keterangan:</strong></p>
        <div class="catatan"></div>
        <p style="font-size: 9px; margin-top: 10px;">* Dicetak secara otomatis oleh Sistem Digitalisasi Uji Petik Dishub Klungkung</p>
    </div>
</body>
</html>
