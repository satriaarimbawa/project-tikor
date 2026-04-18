<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/tambahuser.css') }}?v={{ time() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
         body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(180deg, #E7EFF6 70%, #FFFFFF 100%);
            min-height: 100vh;
        }
        .sidebar-navy {
            background-color: rgba(37, 61, 107, 0.75) !important;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#E7EFF6] to-[#FFFFFF] min-h-screen flex">

    @include('admin.template.navbar')

    <main class="flex-1 ml-64 p-8 flex justify-center items-start pt-20">
    
    <div class="fixed top-6 right-8">
        <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-12 object-contain" alt="Logo">
    </div>

    <div class="bg-white w-full max-w-3xl rounded-[25px] shadow-[0_10px_40px_rgba(0,0,0,0.1)] p-12 relative">
    <h2 class="text-4xl font-bold text-center text-black mb-10">{{ isset($user) ? 'Form Edit User' : 'Form Tambah User' }}</h2>

    @php
        $formAction = isset($user) ? route('user.update', $id) : route('user.store');
    @endphp

    <form action="{{ $formAction }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text" name="username" value="{{ $user['username'] ?? old('username') }}" required
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Username">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" name="email" value="{{ $user['email'] ?? old('email') }}" required
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Email">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role User</label>
            <div class="relative">
                <select name="role_user" required
                    class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 appearance-none focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm text-gray-600">
                    <option value="" disabled {{ !isset($user) ? 'selected' : '' }}>Pilih Role</option>
                    <option value="admin" {{ (isset($user) && $user['role_user'] == 'admin') ? 'selected' : '' }}>Admin</option>
                    <option value="operator" {{ (isset($user) && $user['role_user'] == 'operator') ? 'selected' : '' }}>Operator</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password {{ isset($user) ? '(Kosongkan jika tidak ingin mengubah)' : '' }}</label>
            <input type="password" name="password" {{ isset($user) ? '' : 'required' }}
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Password">
        </div>

        <p class="text-red-600 text-[13px] italic mt-4">
            *Periksa kembali kebenaran data sebelum dikirim
        </p>

        <div class="pt-8">
            <button type="submit" 
                class="w-full bg-[#24AD45] hover:bg-[#1e913a] text-white font-bold py-4 rounded-xl text-2xl transition-all shadow-lg transform active:scale-[0.98]">
                {{ isset($user) ? 'Simpan Perubahan' : 'Simpan User' }}
            </button>
        </div>
    </form>
</div>
</main>

<script src="{{ asset('js/navbar.js') }}"></script>

</body>
</html>