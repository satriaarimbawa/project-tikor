<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sistem Sedang Gangguan - Sedetik Dishub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #E7EFF6 70%, #FFFFFF 100%);
            color: #1F2937;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .halaman {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }

        .kartu {
            width: 100%;
            max-width: 560px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.04);
            padding: 56px 48px;
            text-align: center;
        }

        .merek {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #253D6B;
            margin-bottom: 28px;
        }

        .ikon {
            width: 72px;
            height: 72px;
            border-radius: 9999px;
            background: #FEF2F2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .judul {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.25;
            color: #111827;
            margin-bottom: 12px;
        }

        .isi {
            font-size: 15px;
            line-height: 1.7;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .keterangan {
            font-size: 13.5px;
            line-height: 1.6;
            color: #64748B;
            margin-bottom: 28px;
        }

        .laporan {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #FFF7ED;
            border: 1px solid #FED7AA;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 28px;
        }

        .laporan-label {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9A3412;
        }

        .laporan-nilai {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #9A3412;
        }

        .laporan-waktu {
            font-size: 12px;
            color: #B45309;
        }

        .tombol {
            display: inline-block;
            background: #253D6B;
            color: #FFFFFF;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 13px 30px;
            border-radius: 12px;
            transition: background 0.15s ease;
        }

        .tombol:hover { background: #1a2e52; }

        .bantuan {
            font-size: 12.5px;
            color: #64748B;
            margin-top: 20px;
        }
    </style>
</head>
<body>
@php
    $kodeLaporan = \App\Support\KodeLaporan::terakhir();
    $waktuWita = (new \DateTime('now', new \DateTimeZone('Asia/Makassar')))->format('H:i');
@endphp
    <main class="halaman">
        <div class="kartu">
            <p class="merek">Sedetik Dishub</p>

            <div class="ikon" aria-hidden="true">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>

            <h1 class="judul">Sistem Sedang Gangguan</h1>
            <p class="isi">Maaf atas gangguan ini. Halaman tidak bisa dimuat.</p>
            <p class="keterangan">Gangguan sudah tercatat otomatis. Tim kami sudah diberi tahu.</p>

            @if ($kodeLaporan)
                <div class="laporan">
                    <span class="laporan-label">Kode Laporan</span>
                    <span class="laporan-nilai">{{ $kodeLaporan }}</span>
                    <span class="laporan-waktu">{{ $waktuWita }} WITA</span>
                </div>
            @endif

            <a class="tombol" href="{{ url()->current() }}">Coba Lagi</a>

            <p class="bantuan">Jika masih gagal, hubungi admin Dishub.</p>
        </div>
    </main>
</body>
</html>
