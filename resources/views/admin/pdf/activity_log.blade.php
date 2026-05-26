<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #253D6B; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #253D6B; margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 14px; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 3px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background-color: #253D6B; color: white; padding: 10px; text-align: left; text-transform: uppercase; font-size: 10px; }
        table.data td { padding: 8px 10px; border-bottom: 1px solid #eee; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .badge-login { background-color: #d1fae5; color: #065f46; }
        .badge-logout { background-color: #f1f5f9; color: #334155; }
        .badge-violation { background-color: #fee2e2; color: #991b1b; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; color: #aaa; font-size: 10px; border-top: 1px solid #eee; padding-top: 5px; }
        .time { font-family: monospace; color: #3b82f6; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Log Aktivitas Operator</h1>
        <p>Laporan Kehadiran & Aktivitas Harian</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="15%">Tanggal Laporan</td>
                <td width="2%">:</td>
                <td><strong>{{ $date }}</strong></td>
            </tr>
            <tr>
                <td>Dicetak Pada</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="15%">Waktu</th>
                <th width="20%">Operator</th>
                <th width="50%">Aktivitas / Pesan</th>
                <th width="15%" style="text-align: center;">Tipe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="time">{{ \Carbon\Carbon::parse($log['timestamp'])->format('H:i:s') }}</td>
                <td><strong>{{ $log['username'] }}</strong></td>
                <td>{!! strip_tags($log['message']) !!}</td>
                <td style="text-align: center;">
                    @php
                        $badgeClass = match($log['type']) {
                            'login' => 'badge-login',
                            'logout' => 'badge-logout',
                            'violation' => 'badge-violation',
                            default => ''
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $log['type'] }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 30px; color: #999; font-style: italic;">
                    Tidak ada aktivitas yang tercatat untuk tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Uji Petik - {{ date('Y') }}
    </div>
</body>
</html>
