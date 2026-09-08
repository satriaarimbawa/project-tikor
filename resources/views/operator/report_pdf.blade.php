<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uji Petik Kendaraan</title>
    <style>
        @page { margin: 1cm 1cm 0.5cm 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; margin: 0; padding: 0; line-height: 1.2; }
        
        /* Header Styling */
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 3px; margin-bottom: 5px; }
        .header table { width: 100%; border: none; border-collapse: collapse; }
        .header td { vertical-align: middle; border: none; }
        .header .logo { width: 80px; }
        .header .title { text-align: center; }
        .header .balinese { font-size: 14pt; margin-bottom: -5px; }
        .header h1 { font-size: 14pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header h2 { font-size: 12pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header p { font-size: 8pt; margin: 2px 0; }

        /* Report Title */
        .report-title { text-align: center; margin: 5px 0; }
        .report-title h3 { font-size: 12pt; margin: 0; text-transform: uppercase; font-weight: bold; }
        .report-title .lokasi { font-size: 10pt; margin-top: 3px; text-transform: uppercase; }

        /* Identity Section */
        .identity { width: 100%; margin-bottom: 15px; }
        .identity table { width: 100%; border: none; }
        .identity td { vertical-align: top; border: none; padding: 1px 0; }

        /* Table Styling */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 2px 4px; text-align: center; }
        table.data-table th { background-color: #e9e9e9; font-weight: bold; text-transform: uppercase; font-size: 8pt; }
        table.data-table td { font-size: 8pt; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* Summary Title */
        .section-title { font-weight: bold; margin-bottom: 3px; text-transform: uppercase; font-size: 9pt; }

        /* Footer / Signature */
        .signature-container { position: absolute; bottom: 45px; right: 0; width: 250px; text-align: center; }
        .signature-space { height: 50px; }
        
        .disclaimer { position: absolute; bottom: 0; left: 0; right: 0; font-size: 8pt; font-style: italic; border-top: 1px solid #ccc; padding-top: 3px; }
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

    <div class="header">
        <table>
            <tr>
                <td class="logo" style="text-align: left; width: 100px;">
                    <img src="{{ $logoKlungkung }}" width="70" alt="Logo Klungkung">
                </td>
                <td class="title">
                    <h1>Pemerintah Kabupaten Klungkung</h1>
                    <h2>Dinas Perhubungan</h2>
                    <p>Jalan Raya Watu Klotok, Telp : (0366) 21087, SEMARAPURA</p>
                </td>
                <td class="logo" style="text-align: right; width: 100px;">
                    <img src="{{ $logoDishub }}" width="70" alt="Logo Dishub">
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        <h3>LAPORAN HASIL UJI PETIK KENDARAAN</h3>
        <div class="lokasi">LOKASI: {{ $nama_lokasi }}</div>
    </div>

    <div class="identity">
        <table>
            <tr>
                <td width="50%">
                    <table style="width: auto;">
                        <tr>
                            <td width="80">Arah</td>
                            <td width="10">:</td>
                            <td>{{ $arah }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>{{ $tanggal }}</td>
                        </tr>
                    </table>
                </td>
                <td width="50%" style="text-align: right;">
                    <table style="width: auto; margin-left: auto;">
                        <tr>
                            <td width="80" style="text-align: left;">Surveyor</td>
                            <td width="10">:</td>
                            <td style="text-align: left;">{{ $surveyor }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">A. RINCIAN VOLUME KENDARAAN PER JAM</div>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2">JENIS KENDARAAN</th>
                <th colspan="{{ count($hourlyData) }}" >JAM (WITA)</th>
            </tr>
            <tr>
                @foreach($hourlyData as $label => $counts)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($objekSurvei as $obj)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ $obj }}</td>
                @foreach($hourlyData as $label => $counts)
                    @php 
                        $key = strtolower(str_replace(' ', '', $obj));
                        $count = $counts[$key] ?? 0;
                    @endphp
                    <td>{{ $count }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">B. REKAPITULASI PENERIMAAN</div>
    <table class="data-table" style="width: auto; font-size: 7.5pt;">
        <thead>
            <tr>
                <th width="25">NO</th>
                <th>JENIS KENDARAAN</th>
                <th width="60">JUMLAH</th>
                <th width="80">TARIF LAMA</th>
                <th width="90">TOTAL LAMA</th>
                <th width="80">TARIF BARU</th>
                <th width="90">TOTAL BARU</th>
            </tr>
        </thead>
        <tbody>
            @php $noSummary = 1; @endphp
            @foreach($summaryData as $key => $data)
            <tr>
                <td>{{ $noSummary++ }}</td>
                <td class="text-left">{{ $data['nama'] }}</td>
                <td>{{ number_format($data['jumlah']) }}</td>
                <td class="text-right" style="color: #666;">{{ number_format($data['tarif_lama'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #666;">{{ number_format($data['total_lama'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['tarif'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($data['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="font-bold" style="background-color: #f2f2f2;">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN</td>
                <td style="background-color: #e9e9e9;"></td>
                <td class="text-right" style="color: #666;">Rp {{ number_format($totalSeluruhLama, 0, ',', '.') }}</td>
                <td style="background-color: #e9e9e9;"></td>
                <td class="text-right">Rp {{ number_format($totalSeluruh, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-container">
        <p>Semarapura, {{ $tanggal }}</p>
        <p>Petugas Surveyor,</p>
        <div class="signature-space"></div>
        <p>( {{ $surveyor }} )</p>
    </div>

    <div class="disclaimer">
        * Laporan ini dicetak secara otomatis melalui Sistem Digitalisasi Uji Petik Dinas Perhubungan Kabupaten Klungkung.
    </div>
    </body>
    </html>
