<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/daftar-user.css') }}?v={{ time() }}">
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
<body class="flex">

    @include('admin.template.navbar')

        <main class="main-content">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-extrabold text-[#253D6B] tracking-tight">Daftar User</h1>
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-16 object-contain" alt="Logo Klungkung">
             </div>

    <div class="card-figma">
        <div class="flex justify-between items-center mb-6">
            <div class="relative w-1/3">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <iconify-icon icon="lucide:search" class="text-white/50 text-xl"></iconify-icon>
                </span>
                <input type="text" placeholder="Cari data user" 
                    class="w-full bg-[#253D6B] text-white text-sm rounded-full py-2.5 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder:text-white/50">
            </div>
            
            <a href="{{ route('user.create') }}" class="bg-[#253D6B] hover:bg-[#1a2c4d] text-white text-sm font-bold py-2.5 px-6 rounded-full flex items-center gap-2 transition-all shadow-lg">
                <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                Tambah Data
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $uid => $user)
                    <tr class="table-row-hover">
                        <td class="text-center font-medium text-gray-500">{{ $loop->iteration }}</td>
                        <td class="font-bold text-[#253D6B]">{{ $user['username'] ?? 'No Name' }}</td>
                        <td class="text-gray-600">{{ $user['email'] ?? '-' }}</td>
                        <td>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase">
                                {{ $user['role_user'] ?? 'No Role' }}
                            </span>
                        </td>
                        <td class="flex justify-center gap-2">
                            <a href="{{ route('user.edit', $uid) }}" class="w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md flex items-center justify-center transition-colors">
                                <iconify-icon icon="lucide:edit-3"></iconify-icon>
                            </a>
                            <form action="{{ route('user.destroy', $uid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center justify-center transition-colors">
                                    <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8 gap-4">
            <button class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition">
                <iconify-icon icon="lucide:chevron-left" class="text-xl"></iconify-icon>
            </button>
            <button class="w-10 h-10 rounded-full bg-[#D99D81] text-white flex items-center justify-center hover:opacity-80 transition">
                <iconify-icon icon="lucide:chevron-right" class="text-xl"></iconify-icon>
            </button>
        </div>
    </div>
</main>


<script src="{{ asset("js/navbar.js") }}"></script>
</body>
</html>