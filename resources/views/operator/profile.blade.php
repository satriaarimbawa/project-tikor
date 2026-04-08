@php
//Simulasi database
$riwayat = [
(object)['jam' => '10.00', 'kendaraan' => 'Motor', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.20', 'kendaraan' => 'Mobil', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.21', 'kendaraan' => 'Mobil', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.22', 'kendaraan' => 'Mobil', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.23', 'kendaraan' => 'Truk', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.23', 'kendaraan' => 'Truk', 'lokasi' => 'Terminal Galiran'],
(object)['jam' => '10.24', 'kendaraan' => 'MiniBus', 'lokasi' => 'Terminal Galiran'],
];


$totalMotor = collect($riwayat)->where('kendaraan', 'Motor')->count();
$totalMobil = collect($riwayat)->where('kendaraan', 'Mobil')->count();
$totalTruk = collect($riwayat)->where('kendaraan', 'Truk')->count();
$totalMiniBus = collect($riwayat)->where('kendaraan', 'MiniBus')->count();
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
                <h1 class="font-bold text-lg lg:text-xl leading-tight text-slate-900">Uji Petik - Terminal Galiran</h1>
                <p class="text-xs lg:text-sm text-slate-500">Shift Pagi | 18 Maret 2026</p>
            </div>
        </div>

        <!-- berisi data sementara pada value, dan berisi alert -->

        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-md p-6 mt-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-600 mb-1">Username</label>
                <input type="text" value="Alexander Lorezpierrez"
                    class="w-full bg-slate-100 border-none rounded-lg px-4 py-3 text-slate-500 focus:ring-2 focus:ring-blue-500 outline-none"
                    readonly>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                <input type="email" value="pierrez@gmail.com"
                    class="w-full bg-slate-100 border-none rounded-lg px-4 py-3 text-slate-500 focus:ring-2 focus:ring-blue-500 outline-none"
                    readonly>
            </div>

            <div class="flex justify-between items-center gap-4">
                <button onclick="alert('Logout Berhasil')"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 active:scale-95">
                    Logout
                </button>

                <button onclick="alert('Ajukan Perubahan Data')"
                    class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-slate-800 font-bold py-3 px-4 rounded-lg transition duration-200 active:scale-95 text-sm leading-tight">
                    Ajukan Perubahan Data
                </button>
            </div>
        </div>





        <div class="fixed bottom-0 left-0 right-0 bg-[#5A6C8F] shadow-2xl rounded-t-2xl z-50">
            <div class="flex justify-around p-3 text-slate-300 max-w-md mx-auto lg:max-w-lg">
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110n">
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
                <button class="flex flex-col items-center text-xs opacity-100 text-white hover:scale-110 transition"><a
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
            "{{ route('check.location.radius') }}", // URL Route
            "{{ csrf_token() }}",                  // Token Keamanan
            "{{ url('/') }}"                        // URL Redirect jika logout
        );
    });

</body>

</html>