<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Uji Petik</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F5F7FA] min-h-screen flex items-center justify-center p-6">
    <div class="bg-white w-full max-w-md rounded-[30px] shadow-2xl p-10 border border-gray-100">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/logo_dishub.png') }}" class="w-20 h-20 object-contain" alt="Logo">
        </div>
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Lupa Password</h2>
        <p class="text-sm text-gray-500 text-center mb-8">Masukkan email Anda untuk menerima kode OTP reset password.</p>

        @if(session('error'))
            <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Email</label>
                <input type="email" name="email" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                    placeholder="email@contoh.com">
            </div>

            <button type="submit" 
                class="w-full bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-4 rounded-xl transition-all shadow-lg">
                Kirim Kode OTP
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
