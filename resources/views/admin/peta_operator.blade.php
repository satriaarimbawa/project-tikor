<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Operator - IT Support</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(180deg, #E7EFF6 0%, #F8FAFC 100%);
            min-height: 100vh;
        }

        .sidebar-navy {
            background-color: rgba(37, 61, 107, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        #peta {
            height: 560px;
            border-radius: 20px;
            z-index: 0;
        }

        /* Marker operator */
        .pin {
            width: 18px;
            height: 18px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.45);
        }

        .pin-dalam { background: #10b981; }
        .pin-luar { background: #ef4444; }
        .pin-istirahat { background: #f59e0b; }
        .pin-basi { background: #64748b; }

        /* Titik pos uji */
        .titik {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #253D6B;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.4);
        }

        .baris-terpilih {
            background: #e0f2fe !important;
            border-color: #38bdf8 !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 14px;
        }

        .leaflet-popup-content {
            margin: 12px 14px;
            font-size: 12px;
            line-height: 1.6;
        }
    </style>
</head>

<body class="flex">

    @include('admin.template.navbar')

    <main class="main-content lg:ml-64 p-4 md:p-8 flex-1 w-full max-w-7xl mx-auto">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#253D6B] flex items-center justify-center text-cyan-400 shadow-md">
                    <iconify-icon icon="lucide:map-pinned" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-[#253D6B] tracking-tight">Peta Posisi Operator</h1>
                    <p class="text-xs md:text-sm text-slate-500 font-medium">Posisi terakhir yang dikirim heartbeat geofencing. Alat bantu debug IT Support.</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <span id="badgeStatus" class="px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span id="badgeStatusText">Memuat...</span>
                </span>
                <button type="button" id="btnSegarkan"
                    class="px-4 py-2.5 rounded-xl bg-[#253D6B] hover:bg-[#1a2e52] text-white text-xs font-bold flex items-center gap-2 shadow-md transition active:scale-95">
                    <iconify-icon icon="lucide:refresh-cw" class="text-base"></iconify-icon>
                    Segarkan
                </button>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-14 object-contain drop-shadow" alt="Logo Klungkung">
            </div>
        </div>

        @if($gagalBaca)
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium flex items-start gap-3">
            <iconify-icon icon="lucide:alert-triangle" class="text-lg shrink-0"></iconify-icon>
            <span>Firebase tidak terbaca saat halaman dimuat. Periksa koneksi server dan kredensial service account, lalu tekan Segarkan.</span>
        </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- PETA -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-3">
                    <div id="peta"></div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 px-3 py-4 text-[11px] font-semibold text-slate-600">
                        <span class="flex items-center gap-2"><span class="pin pin-dalam" style="transform:rotate(-45deg)"></span> Dalam radius</span>
                        <span class="flex items-center gap-2"><span class="pin pin-luar" style="transform:rotate(-45deg)"></span> Luar radius</span>
                        <span class="flex items-center gap-2"><span class="pin pin-istirahat" style="transform:rotate(-45deg)"></span> Istirahat</span>
                        <span class="flex items-center gap-2"><span class="pin pin-basi" style="transform:rotate(-45deg)"></span> Denyut basi (&gt; {{ round($umurBasi / 60) }} menit)</span>
                        <span class="flex items-center gap-2"><span class="titik"></span> Pos uji</span>
                        <span class="flex items-center gap-2 ml-auto text-slate-400" id="infoWaktu">-</span>
                    </div>
                </div>

                <!-- Ringkasan -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ada Posisi</p>
                        <p class="text-3xl font-extrabold text-[#253D6B] mt-1" id="jmlAdaPosisi">0</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Luar Radius</p>
                        <p class="text-3xl font-extrabold text-red-500 mt-1" id="jmlLuar">0</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanpa Posisi</p>
                        <p class="text-3xl font-extrabold text-amber-500 mt-1" id="jmlTanpa">0</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Online</p>
                        <p class="text-3xl font-extrabold text-emerald-500 mt-1" id="jmlOnline">0</p>
                    </div>
                </div>
            </div>

            <!-- DAFTAR OPERATOR -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col overflow-hidden" style="max-height:760px">
                    <div class="px-5 py-4 border-b border-slate-100 shrink-0">
                        <h2 class="text-sm font-extrabold text-[#253D6B] uppercase tracking-wider">Operator</h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Klik nama untuk memusatkan peta. Tanpa posisi = denyut tidak sampai.</p>
                    </div>
                    <div id="daftarOperator" class="flex-1 overflow-y-auto p-3 space-y-2">
                        <div class="text-center py-10 text-slate-400 italic text-sm">Memuat data...</div>
                    </div>
                </div>
            </div>

        </div>

        <p class="mt-6 text-[11px] text-slate-400 font-medium leading-relaxed">
            <strong class="text-slate-500">Cara baca.</strong>
            Warna hijau berarti jarak operator ke pos tugasnya masih di dalam radius yang berlaku.
            Merah berarti di luar radius. Kuning berarti operator sedang istirahat.
            Abu-abu berarti denyut terakhirnya sudah lebih lama dari {{ round($umurBasi / 60) }} menit, jadi posisinya tidak boleh dianggap sebagai posisi sekarang.
            Operator tanpa koordinat sama sekali biasanya karena denyutnya ditolak server.
        </p>

    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.PETA_AWAL = {
            operator: @json($operator),
            lokasi: @json($lokasi),
            umurBasi: @json($umurBasi),
            gagalBaca: @json($gagalBaca),
            urlPosisi: "{{ route('peta.operator.posisi') }}",
            intervalDetik: 20
        };
    </script>
    <script src="{{ asset('js/petaOperator.js') }}"></script>

</body>

</html>