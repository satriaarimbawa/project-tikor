@php
$totalSemua = collect($riwayat)->count();
@endphp


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
                <h1 class="font-bold text-lg lg:text-xl leading-tight text-slate-900">Uji Petik - {{ $namaLokasi }}
                </h1>
                <p class="text-xs lg:text-sm text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 lg:p-8 shadow-sm border border-slate-200 mb-6 relative">
            <h2 class="font-bold mb-4 text-sm lg:text-base text-slate-900">Ringkasan Harian ( Status : <span
                    class="text-emerald-500">● AKTIF</span> )</h2>

            <div class="bg-slate-100 rounded-2xl p-4 text-center border border-slate-100">
                <p class="text-slate-500 text-sm">Total Survei Kendaraan : <span
                        class="text-slate-900 font-bold text-lg"
                        id="total-survei">{{ $dataSurvei['total_survei'] ?? 0 }}</span></p>
                <hr class="my-3 border-slate-200">

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 border-r border-slate-200 pr-2">
                        <i class="fas fa-motorcycle text-2xl text-slate-700"></i>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Motor</p>
                            <p id="count-motor" class="font-bold">{{ $dataSurvei['motor'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-car text-2xl text-slate-700"></i>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Mini Bus</p>
                            <p id="count-minibus" class="font-bold">{{ $dataSurvei['minibus'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-r border-slate-200 pr-2">
                        <i class="fas fa-bus text-2xl text-slate-700"></i>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Bus</p>
                            <p id="count-bus" class="font-bold">{{ $dataSurvei['bus'] ?? 0 }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-truck text-2xl text-slate-700"></i>
                        <div class="text-left">
                            <p class="text-xs text-slate-500">Truk</p>
                            <p id="count-truk" class="font-bold">{{ $dataSurvei['truk'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- tombol ini berisi alert -->
            <div class="mt-4 flex justify-between items-center">
                <button type="button" onclick="alert('Laporan dikirim!')" style="background-color: #D9D9D9;"
                    class="flex-1 max-w-xs hover:bg-gray-300 py-2 px-6 rounded-2xl flex items-center justify-center gap-4 shadow-sm border border-gray-200 transition-all active:scale-95 group">

                    <div class="p-2 rounded-xl border-2 border-red-500">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                    </div>

                    <span class="font-bold text-slate-700 text-xl">Lapor</span>
                </button>

                <div class="flex flex-col gap-2 ml-4">
                    <button class="hover:scale-110 transition-transform">
                        <i class="far fa-file-alt text-2xl text-slate-600"></i>
                    </button>
                    <button class="hover:scale-110 transition-transform">
                        <i class="fas fa-download text-2xl text-slate-600"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 mb-28">
            <h2 class="font-bold mb-6 text-sm text-slate-900 px-2">Pilih Jenis Kendaraan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">


                <button onclick="hitungKendaraan('motor')"
                    style="background: linear-gradient(to right, #fdba74, #ffedd5);"
                    class="flex items-center p-4 rounded-2xl shadow-md hover:scale-105 hover:shadow-xl transition-all active:scale-95 w-full">

                    <div class="bg-orange-500 w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-motorcycle text-white text-xl"></i>
                    </div>

                    <span class="ml-4 font-bold text-slate-800 text-lg">Motor</span>
                </button>


                <button onclick="hitungKendaraan('minibus')"
                    style="background: linear-gradient(to right, #fde047, #fef9c3);"
                    class="flex items-center p-4 rounded-2xl shadow-md hover:scale-105 hover:shadow-xl transition-all active:scale-95 w-full">

                    <div class="bg-yellow-500 w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-car-side text-white text-xl"></i>
                    </div>

                    <span class="ml-4 font-bold text-slate-800 text-lg">Mini Bus</span>
                </button>

                <button onclick="hitungKendaraan('bus')"
                    style="background: linear-gradient(to right, #93c5fd, #dbeafe);"
                    class="flex items-center p-4 rounded-2xl shadow-md hover:scale-105 hover:shadow-xl transition-all active:scale-95 w-full">

                    <div class="bg-blue-500 w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-bus text-white text-xl"></i>
                    </div>

                    <span class="ml-4 font-bold text-slate-800 text-lg">Bus</span>
                </button>


                <button onclick="hitungKendaraan('truk')"
                    style="background: linear-gradient(to right, #c4b5fd, #ede9fe);"
                    class="flex items-center p-4 rounded-2xl shadow-md hover:scale-105 hover:shadow-xl transition-all active:scale-95 w-full">

                    <div class="bg-purple-500 w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-truck text-white text-xl"></i>
                    </div>

                    <span class="ml-4 font-bold text-slate-800 text-lg">Truk</span>
                </button>

            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 bg-[#5A6C8F] shadow-2xl rounded-t-2xl z-50">
            <div class="flex justify-around p-3 text-slate-300 max-w-md mx-auto">
                <button class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition">
                    <a href="/dashboard-operator"><i class="fas fa-home text-lg mb-1"></i></a>Beranda
                </button>
                <a href="/dashboard-operator-penugasan"
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition">
                    <i class="fas fa-clipboard-list text-lg mb-1"></i>Penugasan
                </a>
                <button class="flex flex-col items-center text-xs text-white transition">
                    <i class="fas fa-poll text-lg mb-1"></i>Survei
                </button>
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110"><a
                        href="/dashboard-operator-profile" class="fas fa-user-circle text-lg mb-1"></a>Profil
                </button>
            </div>
        </div>


    <script src="{{ asset('js/deteksiTikorUser.js') }}"></script>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            startGeofencing(
                "{{ route('check.location.radius') }}", // URL Route
                "{{ csrf_token() }}",                  // Token Keamanan
                "{{ url('/') }}"                        // URL Redirect jika logout
            );
        });
        function hitungKendaraan(jenis) {
            // 1. Ambil elemen angka di UI
            let elKendaran = document.getElementById('count-' + jenis);
            let elTotal = document.getElementById('total-survei');

            // 2. Tambah angka secara lokal (UI)
            let currentCount = parseInt(elKendaran.innerText) + 1;
            elKendaran.innerText = currentCount;
            elTotal.innerText = parseInt(elTotal.innerText) + 1;

            // 3. Kirim ke Laravel via AJAX
            fetch("{{ route('simpan.hitung.kendaraan') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        jenis_kendaraan: jenis,
                        // id_lokasi tidak wajib dikirim jika sudah ada di session Laravel
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Berhasil simpan ke Firebase:", data);
                })
                .catch(error => console.error("Gagal kirim:", error));
        }
        </script>
</body>

</html>