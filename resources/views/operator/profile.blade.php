<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profil - Operator</title>
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
                <p class="text-xs lg:text-sm text-slate-500">Profil Operator | {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- berisi data sementara pada value, dan berisi alert -->

        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-md p-6 mt-4">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-600 mb-1">Username</label>
                <input type="text" value="{{ $user['username'] ?? '-' }}"
                    class="w-full bg-slate-100 border-none rounded-lg px-4 py-3 text-slate-500 focus:ring-2 focus:ring-blue-500 outline-none"
                    readonly>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                <input type="email" value="{{ $user['email'] ?? '-' }}"
                    class="w-full bg-slate-100 border-none rounded-lg px-4 py-3 text-slate-500 focus:ring-2 focus:ring-blue-500 outline-none"
                    readonly>
            </div>

            <div class="flex flex-col gap-4 mt-8">
                <button id="btn-istirahat" onclick="toggleIstirahat()"
                    style="background-color: {{ ($user['status_istirahat'] ?? false) ? '#0ea5e9' : '#f59e0b' }} !important;"
                    class="w-full text-white flex items-center justify-center gap-3 py-4 rounded-2xl font-bold shadow-lg transition-all active:scale-95">
                    <i class="fas {{ ($user['status_istirahat'] ?? false) ? 'fa-play' : 'fa-coffee' }}"></i>
                    <span id="text-istirahat">{{ ($user['status_istirahat'] ?? false) ? 'Selesai Istirahat' : 'Mulai Istirahat' }}</span>
                </button>

                <div class="flex justify-center">
                    <a href="{{ url('/logout') }}" class="text-red-500 font-bold text-sm flex items-center gap-2 hover:underline">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout dari Sistem</span>
                    </a>
                </div>
            </div>
        </div>

        <script>
            function toggleIstirahat() {
                const btn = document.getElementById('btn-istirahat');
                const text = document.getElementById('text-istirahat');
                
                btn.disabled = true;
                btn.style.opacity = '0.5';

                fetch("{{ route('operator.toggle-istirahat') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    }
                })
                .then(async response => {
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`Error ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.status_istirahat) {
                            btn.style.backgroundColor = '#0ea5e9'; // Blue
                            btn.innerHTML = '<i class="fas fa-play"></i><span>Selesai Istirahat</span>';
                            alert("Anda sedang ISTIRAHAT. Aturan radius dinonaktifkan sementara.");
                        } else {
                            btn.style.backgroundColor = '#f59e0b'; // Amber
                            btn.innerHTML = '<i class="fas fa-coffee"></i><span>Mulai Istirahat</span>';
                            alert("Istirahat selesai. Selamat bertugas kembali!");
                        }
                    } else {
                        alert("Gagal mengubah status. Silakan coba lagi.");
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert("Koneksi terganggu. Silakan cek sinyal internet Anda.");
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            }
        </script>





        <div class="fixed bottom-0 left-0 right-0 bg-[#5A6C8F] shadow-2xl rounded-t-2xl z-50">
            <div class="flex justify-around p-3 text-slate-300 max-w-md mx-auto lg:max-w-lg">
                <button
                    class="flex flex-col items-center text-xs opacity-60 hover:opacity-100 transition hover:scale-110">
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
            "{{ route('login') }}"                        // URL Redirect jika logout
        );
    });
    </script>
</body>

</html>
