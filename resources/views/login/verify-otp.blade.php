<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Uji Petik</title>
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
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Verifikasi OTP</h2>
        <p class="text-sm text-gray-500 text-center mb-8">Kode OTP telah dikirim ke: <br><strong>{{ session('reset_email') }}</strong></p>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl text-sm mb-6 border border-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('password.verify') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kode OTP (6 Digit)</label>
                <input type="text" name="otp" required maxlength="6"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-center text-2xl font-bold tracking-[1em] focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                    placeholder="000000">
            </div>

            <button type="submit" 
                class="w-full bg-[#253D6B] hover:bg-[#1a2c4d] text-white font-bold py-4 rounded-xl transition-all shadow-lg">
                Verifikasi OTP
            </button>
        </form>

        <div class="mt-8 text-center text-sm">
            Tidak menerima kode? <br>
            <form action="{{ route('password.email') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <button type="submit" class="font-semibold text-blue-600 hover:text-blue-800">Kirim ulang kode</button>
            </form>
        </div>
    </div>
</body>
</html>
