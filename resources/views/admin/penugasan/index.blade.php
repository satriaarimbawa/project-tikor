<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Penugasan</title>


    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>

<body class="flex bg-[#F5F7FA]">

    @include('admin.template.navbar')

    <main class="main-content ml-64 p-8 w-full">
        <header class="flex justify-end items-center mb-8">
            <div class="flex items-center">
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain"
                    alt="Logo Klungkung">
            </div>
        </header>

        <h1 class="text-3xl font-bold text-black mb-6">Data Penugasan</h1>

        <div class="bg-white rounded-xl shadow-lg p-8 min-h-[600px] relative">

            <div class="flex justify-between items-center mb-6">
                <div class="relative group">
                    <a href="/dashboard-penugasan/create">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <iconify-icon icon="lucide:search" class="text-white text-xl"></iconify-icon>
                        </span>
                    </a>
                    <input type="text" placeholder="Cari data penugasan"
                        class="bg-[#253D6B] text-white text-sm rounded-full pl-10 pr-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-white/70 shadow-md">
                </div>

                <a href="/dashboard-penugasan/create"
                    class="bg-[#253D6B] hover:bg-[#1a2e52] text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md transition-all active:scale-95 inline-flex">
                    <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                    <span class="font-semibold text-sm">Tambah Data</span>
                </a>
            </div>

            <div class="overflow-hidden border border-gray-300 rounded-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FDE047] border-b border-gray-300">
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800 w-12 text-center">
                                No</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Username</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Nama lokasi</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Waktu</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">SPT</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Status</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Objek</th>
                            <th class="py-3 px-4 font-semibold text-gray-800 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#FFFBEB]">

                        @foreach ($dataPenugasan as $penugasan)
                        <tr class="border-b border-gray-300">
                            <td class="py-3 px-4 border-r border-gray-300 text-center text-sm">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">{{ $penugasan['nama_operator'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">{{ $penugasan['alamat_lokasi'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">{{ $penugasan['tanggal_rentang'] }}
                                <br>{{ $penugasan['jam_rentang'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm"><a href="{{ asset('uploads/spt/' . $penugasan['no_spt']) }}" 
                                                                                    download="SPT_Penugasan_{{ $penugasan['nama_operator'] }}.png" 
                                                                                    class="text-blue-600 hover:underline font-bold">
                                                                                    Download SPT
                                                                                    </a></td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Aktif</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">Motor</td>
                            <td class="py-3 px-4 flex justify-center gap-2">
                                <button
                                    class="bg-yellow-400 p-1.5 rounded hover:bg-yellow-500 flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit-3" class="text-white text-lg"></iconify-icon>
                                </button>
                                <button
                                    class="bg-red-500 p-1.5 rounded hover:bg-red-600 flex items-center justify-center">
                                    <iconify-icon icon="lucide:trash-2" class="text-white text-lg"></iconify-icon>
                                </button>
                            </td>
                        </tr>
                            @endforeach


                        {{-- @for ($i = 2; $i <= 10; $i++) <tr class="border-b border-gray-300">
                            <td class="py-3 px-4 border-r border-gray-300 text-center h-10"></td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300"></td>
                            <td class="py-3 px-4 flex justify-center gap-2">
                                <button
                                    class="bg-yellow-400 p-1.5 rounded hover:bg-yellow-500 flex items-center justify-center">
                                    <iconify-icon icon="lucide:edit-3" class="text-white text-lg"></iconify-icon>
                                </button>
                                <button
                                    class="bg-red-500 p-1.5 rounded hover:bg-red-600 flex items-center justify-center">
                                    <iconify-icon icon="lucide:trash-2" class="text-white text-lg"></iconify-icon>
                                </button>
                            </td>
                            </tr>
                            @endfor --}}
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center gap-4 mt-8">
                <button
                    class="w-10 h-10 flex items-center justify-center bg-[#E29A81] rounded-full shadow-md hover:bg-[#d18970] transition-all">
                    <iconify-icon icon="lucide:chevron-left" class="text-white text-xl"></iconify-icon>
                </button>
                <button
                    class="w-10 h-10 flex items-center justify-center bg-[#E29A81] rounded-full shadow-md hover:bg-[#d18970] transition-all">
                    <iconify-icon icon="lucide:chevron-right" class="text-white text-xl"></iconify-icon>
                </button>
            </div>
        </div>
    </main>

    <script src="{{ asset('js/navbar.js') }}"></script>
    <script>

    </script>
</body>

</html>