<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
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

    <main class="flex-1 lg:ml-64 p-8 flex justify-center items-start pt-20">
    
    <div class="fixed top-6 right-8">
        <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-12 object-contain" alt="Logo">
    </div>

    <div class="bg-white w-full max-w-3xl rounded-[25px] shadow-[0_10px_40px_rgba(0,0,0,0.1)] p-12 relative">
    <div class="absolute top-8 left-8">
        <a href="/daftar-user" class="flex items-center gap-2 text-gray-500 hover:text-[#253D6B] transition-colors group">
            <iconify-icon icon="lucide:arrow-left" class="text-2xl group-hover:-translate-x-1 transition-transform"></iconify-icon>
            <span class="font-medium">Kembali</span>
        </a>
    </div>
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
            @error('username')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" name="email" value="{{ $user['email'] ?? old('email') }}" required
                class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm"
                placeholder="Masukkan Email">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
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
            @error('role_user')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password {{ isset($user) ? '(Kosongkan jika tidak ingin mengubah)' : '' }}</label>
            <div class="relative">
                <input type="password" name="password" id="passwordInput" {{ isset($user) ? '' : 'required' }}
                    class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none transition-all shadow-sm pr-12"
                    placeholder="Masukkan Password">
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <iconify-icon icon="lucide:eye" id="eyeIcon" class="text-xl"></iconify-icon>
                </button>
            </div>
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

            <!-- Password Checklist -->
            <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200 hidden transition-all duration-300" id="passwordChecklist">
                <p class="text-[13px] font-semibold text-gray-700 mb-2">Password harus memiliki:</p>
                <ul class="text-xs space-y-1.5">
                    <li id="req-length" class="text-red-500 flex items-center gap-2"><iconify-icon icon="lucide:circle" class="w-3 h-3 transition-colors duration-300"></iconify-icon> Minimal 6 karakter</li>
                    <li id="req-upper" class="text-red-500 flex items-center gap-2"><iconify-icon icon="lucide:circle" class="w-3 h-3 transition-colors duration-300"></iconify-icon> Huruf besar</li>
                    <li id="req-number" class="text-red-500 flex items-center gap-2"><iconify-icon icon="lucide:circle" class="w-3 h-3 transition-colors duration-300"></iconify-icon> Angka</li>
                    <li id="req-symbol" class="text-red-500 flex items-center gap-2"><iconify-icon icon="lucide:circle" class="w-3 h-3 transition-colors duration-300"></iconify-icon> Karakter unik/simbol</li>
                </ul>
            </div>
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

<!-- Modal Duplicate User -->
<div id="duplicateModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0F172A]/40 backdrop-blur-md">
    <div class="bg-white w-full max-w-sm rounded-[35px] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="duplicateModalContent">
        <div class="relative p-8 text-center">
            <!-- Decorative Background -->
            <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-red-400 to-orange-500 opacity-10 rounded-b-[50px]"></div>
            
            <!-- Icon Area -->
            <div class="relative mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                <iconify-icon icon="lucide:user-x" class="text-5xl text-red-600 animate-pulse"></iconify-icon>
            </div>

            <!-- Text Area -->
            <h3 class="text-2xl font-black text-[#253D6B] mb-2 tracking-tight">Peringatan!</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-8" id="duplicateMessage">
                {{ session('duplicate_user') }}
            </p>

            <!-- Button Area -->
            <button onclick="hideDuplicateModal()" class="w-full bg-[#253D6B] hover:bg-[#1a2e52] text-white font-bold py-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                Saya Mengerti
                <iconify-icon icon="lucide:check-circle" class="text-xl text-yellow-400"></iconify-icon>
            </button>
        </div>
    </div>
</div>
</main>

<script src="{{ asset('js/navbar.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const checklist = document.getElementById('passwordChecklist');
        
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqNumber = document.getElementById('req-number');
        const reqSymbol = document.getElementById('req-symbol');

        if (passwordInput && togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Ganti Icon
                if (type === 'password') {
                    eyeIcon.setAttribute('icon', 'lucide:eye');
                } else {
                    eyeIcon.setAttribute('icon', 'lucide:eye-off');
                }
            });

            passwordInput.addEventListener('input', function() {
                const val = this.value;
                
                // Tampilkan checklist saat mulai mengetik
                if (val.length > 0) {
                    checklist.classList.remove('hidden');
                } else {
                    checklist.classList.add('hidden');
                }

                // Periksa Panjang
                updateCheck(reqLength, val.length >= 6);
                
                // Periksa Huruf Besar
                updateCheck(reqUpper, /[A-Z]/.test(val));
                
                // Periksa Angka
                updateCheck(reqNumber, /[0-9]/.test(val));
                
                // Periksa Simbol
                updateCheck(reqSymbol, /[^A-Za-z0-9]/.test(val));
            });
        }

        function updateCheck(element, isValid) {
            const icon = element.querySelector('iconify-icon');
            if (isValid) {
                element.classList.remove('text-red-500');
                element.classList.add('text-green-600', 'font-medium');
                icon.setAttribute('icon', 'lucide:check-circle-2');
                icon.classList.remove('text-red-500');
                icon.classList.add('text-green-600');
            } else {
                element.classList.remove('text-green-600', 'font-medium');
                element.classList.add('text-red-500');
                icon.setAttribute('icon', 'lucide:circle');
                icon.classList.remove('text-green-600');
                icon.classList.add('text-red-500');
            }
        }

        // Tampilkan modal jika ada session duplicate_user
        @if(session('duplicate_user'))
            showDuplicateModal();
        @endif
    });

    function showDuplicateModal() {
        const modal = document.getElementById('duplicateModal');
        const content = document.getElementById('duplicateModalContent');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function hideDuplicateModal() {
        const content = document.getElementById('duplicateModalContent');
        const modal = document.getElementById('duplicateModal');
        content.classList.add('scale-95', 'opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>

</body>
</html>