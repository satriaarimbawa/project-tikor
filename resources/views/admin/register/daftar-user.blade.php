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
    <link rel="stylesheet" href="{{ asset('css/daftar-user.css') }}?v={{ time() }}">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

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

<body class="flex">

    @include('admin.template.navbar')

    <main class="main-content lg:ml-64 p-4 md:p-10 flex-1">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-[#253D6B] tracking-tight">Daftar User</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-16 object-contain" alt="Logo Klungkung">
        </div>

        <div class="card-figma">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <form action="/daftar-user" method="GET" class="flex flex-col md:flex-row gap-4 w-full md:w-auto flex-1">
                    <div class="relative flex-1 max-w-sm">
                        <button type="submit" class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <iconify-icon icon="lucide:search" class="text-white/50 text-xl"></iconify-icon>
                        </button>
                        <input type="text" name="search" value="{{ $searchTerm ?? '' }}" placeholder="Cari data user"
                            class="w-full bg-[#253D6B] text-white text-sm rounded-full py-2.5 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder:text-white/50">
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#253D6B] uppercase whitespace-nowrap">Tampilkan:</span>
                        <select name="perPage" onchange="this.form.submit()" class="bg-[#253D6B] text-white text-xs rounded-full py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer appearance-none text-center min-w-[80px]">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        </select>
                    </div>
                </form>

                <a href="{{ route('user.create') }}"
                    class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white text-sm font-bold py-2.5 px-6 rounded-full flex items-center gap-2 transition-all shadow-lg whitespace-nowrap">
                    <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                    Tambah Data
                </a>
            </div>

            <div id="table-container">
                <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                    <table class="w-full text-left bg-white">
                        <thead class="bg-[#F8FAFC] text-gray-500 text-xs font-bold uppercase border-b border-gray-100">
                            <tr>
                                <th class="w-16">No</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                            @php $uid = $user['id']; @endphp
                            <tr class="table-row-hover">
                                <td class="text-center font-medium text-gray-500">
                                    {{ ($currentPage - 1) * $perPage + ($index + 1) }}
                                </td>
                                <td class="font-bold text-[#253D6B]">{{ $user['username'] ?? 'No Name' }}</td>
                                <td class="text-gray-600">{{ $user['email'] ?? '-' }}</td>
                                <td>
                                    <span
                                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase">
                                        {{ $user['role_user'] ?? 'No Role' }}
                                    </span>
                                </td>
                                <td class="flex justify-center gap-2">
                                    <a href="{{ route('user.edit', $uid) }}"
                                        class="w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md flex items-center justify-center transition-colors">
                                        <iconify-icon icon="lucide:edit-3"></iconify-icon>
                                    </a>
                                    <form action="{{ route('user.destroy', $uid) }}" method="POST"
                                        onsubmit="confirmDelete(event, this, 'Apakah Anda yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center justify-center transition-colors">
                                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-gray-500 italic">Data tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-8 gap-4 items-center">
                    <a href="{{ $currentPage > 1 ? url('/daftar-user?page='.($currentPage - 1).'&search='.$searchTerm.'&perPage='.$perPage) : '#' }}"
                        class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-left" class="text-xl"></iconify-icon>
                    </a>
                    
                    <span class="text-sm font-bold text-gray-600">Halaman {{ $currentPage }} dari {{ $totalPages }}</span>

                    <a href="{{ $currentPage < $totalPages ? url('/daftar-user?page='.($currentPage + 1).'&search='.$searchTerm.'&perPage='.$perPage) : '#' }}"
                        class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-right" class="text-xl"></iconify-icon>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset("js/navbar.js") }}"></script>

    <!-- Modal Success -->
    <div id="successModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0F172A]/40 backdrop-blur-md">
        <div class="bg-white w-full max-w-sm rounded-[35px] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="successModalContent">
            <div class="relative p-8 text-center">
                <!-- Decorative Background -->
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-green-400 to-emerald-500 opacity-10 rounded-b-[50px]"></div>
                
                <!-- Icon Area -->
                <div class="relative mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <iconify-icon icon="lucide:check-circle" class="text-5xl text-green-600 animate-bounce"></iconify-icon>
                </div>

                <!-- Text Area -->
                <h3 class="text-2xl font-black text-[#253D6B] mb-2 tracking-tight">Berhasil!</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    {{ session('success') }}
                </p>

                <!-- Button Area -->
                <button onclick="hideSuccessModal()" class="w-full bg-[#253D6B] hover:bg-[#1a2e52] text-white font-bold py-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    Oke, Mengerti
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showSuccessModal();
            @endif
        });

        function showSuccessModal() {
            const modal = document.getElementById('successModal');
            const content = document.getElementById('successModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function hideSuccessModal() {
            const content = document.getElementById('successModalContent');
            const modal = document.getElementById('successModal');
            content.classList.add('scale-95', 'opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</body>

</html>