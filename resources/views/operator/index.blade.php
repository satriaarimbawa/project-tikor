<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DASHBOARD || OPERATOR</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/css/dash_operator.css'])
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-800">
    <div class="p-4 md:p-6 lg:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-14 h-14">
                <img src="{{ asset('assets/logo_dishub.png') }}" alt="Logo">
            </div>
            <div>
                <h1 class="font-bold text-lg lg:text-xl leading-tight text-slate-900">Uji Petik - {{ $nama_lokasi }}</h1>
                <p class="text-xs lg:text-sm text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200 mb-6 relative">
            <h2 class="font-bold mb-4 text-sm lg:text-base text-slate-900">Ringkasan Harian ( Status : <span
                    class="{{ $isAktif ? 'text-emerald-500' : 'text-red-500' }}">● {{ $isAktif ? 'AKTIF' : 'INAKTIF' }}</span> )</h2>

            <div class="bg-slate-100 rounded-2xl p-4 text-center border border-slate-100">
                <p class="text-slate-500 text-sm">Total Survei : <span
                        class="text-slate-900 font-bold text-lg">{{ $totalSemua }}</span></p>
                <hr class="my-3 border-slate-200">

                <div class="grid grid-cols-2 gap-4">
                    @php
                        $iconConfig = [
                            'motor' => ['icon' => 'fas fa-motorcycle', 'label' => 'Motor'],
                            'minibus' => ['icon' => 'fas fa-car-side', 'label' => 'Mini Bus'],
                            'bus' => ['icon' => 'fas fa-bus', 'label' => 'Bus'],
                            'truk' => ['icon' => 'fas fa-truck', 'label' => 'Truk'],
                            'default' => ['icon' => 'fas fa-car', 'label' => 'Lainnya']
                        ];
                    @endphp

                    @foreach($objekSurvei as $obj)
                        @php 
                            $key = strtolower(str_replace(' ', '', $obj));
                            $conf = $iconConfig[$key] ?? $iconConfig['default'];
                            $label = isset($iconConfig[$key]) ? $conf['label'] : ucfirst($obj);
                        @endphp
                        <div class="flex items-center gap-3 border-r border-slate-200 pr-2 last:border-r-0">
                            <i class="{{ $conf['icon'] }} text-2xl text-slate-700"></i>
                            <div class="text-left">
                                <p class="text-[10px] text-slate-500 leading-none mb-1">{{ $label }}</p>
                                <p class="font-bold text-sm leading-none">{{ $counts[$key] ?? 0 }}</p>
                            </div>
                        </div>
                    @endforeach
                    
                    @if(empty($objekSurvei))
                        <div class="col-span-2 py-2 text-slate-400 text-xs italic">
                            Belum ada objek penugasan aktif
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-4 flex justify-between items-end">
                <div
                    class="{{ $isAktif ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-red-100 text-red-600 border-red-200' }} px-6 py-1 rounded-full text-sm font-bold border shadow-inner">
                    Status : [ ● {{ $isAktif ? 'AKTIF' : 'INAKTIF' }} ]
                </div>
                <div class="flex flex-col gap-2">
                    <button onclick="handleFile()" class="hover:scale-110 transition-transform p-1">
                        <i class="far fa-file-alt text-2xl lg:text-3xl text-slate-600 hover:text-slate-800"></i>
                    </button>
                    <button onclick="handleDownload()" class="hover:scale-110 transition-transform p-1">
                        <i class="fas fa-download text-2xl lg:text-3xl text-slate-600 hover:text-slate-800"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-4 lg:p-6 shadow-inner-custom min-h-[300px] border border-slate-200">
            <h2 class="font-bold mb-4 text-sm lg:text-base px-2 text-slate-900">Riwayat Aktivitas Harian</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm lg:text-base border-separate border-spacing-y-3">
                    <thead>
                        <tr
                            class="bg-slate-200 text-slate-600 uppercase text-[10px] lg:text-[12px] tracking-widest font-semibold">
                            <th class="p-3 lg:p-4 rounded-l-lg">Jam</th>
                            <th class="p-3 lg:p-4 text-center">Jenis Kendaraan</th>
                            <th class="p-3 lg:p-4 rounded-r-lg">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat_kendaraan as $row)
                        <tr
                            class="bg-white shadow-md rounded-xl overflow-hidden transform transition hover:scale-[1.01]">
                            <td class="p-4 rounded-l-xl border-y border-l border-slate-100 font-bold text-slate-900">
                                {{ $row['jam'] ?? '--:--' }}
                            </td>
                            <td class="p-4 text-center border-y border-slate-100 font-medium text-slate-900">
                                {{ $row['kendaraan'] ?? 'Tidak Dikenal' }}
                            </td>
                            <td class="p-4 rounded-r-xl border-y border-r border-slate-100 text-slate-500 text-xs">
                                {{ $row['lokasi'] ?? 'Tidak Dikenal' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-10 text-center text-slate-400 italic bg-slate-50 rounded-xl">
                                Belum ada aktivitas survei hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 bg-[#5A6C8F] shadow-2xl rounded-t-2xl z-50">
            <div class="flex justify-around p-3 text-slate-300 max-w-md mx-auto lg:max-w-lg">
                <button class="flex flex-col items-center text-xs opacity-100 text-white hover:scale-110 transition">
                    <a href="/dashboard-operator" class="fas fa-home text-lg mb-1"></a>Beranda
                </button>
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110">
                    <a href="/dashboard-operator-penugasan" class="fas fa-clipboard-list text-lg mb-1"></a>Penugasan
                </button>
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110">
                    <a href="/dashboard-operator-survei" class="fas fa-poll text-lg mb-1"></a>Survei
                </button>
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110"><a
                        href="/dashboard-operator-profile" class="fas fa-user-circle text-lg mb-1"></a>Profil
                </button>
            </div>
        </div>

        <div class="h-24"></div>


    </div>
    <script src="{{ asset('js/deteksiTikorUser.js') }}"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        startGeofencing(
            "{{ route('check.location.radius') }}", 
            "{{ csrf_token() }}",                  
            "{{ url('/login') }}"                      
        );
    });

    </script>
</body>

</html>a tambahan jika diperlukan
    }

    function handleDownload() {
        alert("Sedang mengunduh laporan ringkasan...");
        // Logika tambahan jika diperlukan
    }
    </script>
</body>

</html>