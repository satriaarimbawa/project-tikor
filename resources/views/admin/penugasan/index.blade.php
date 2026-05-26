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

    <main class="main-content lg:ml-64 p-4 md:p-8 w-full">
        <header class="flex justify-end items-center mb-8">
            <div class="flex items-center">
                <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain"
                    alt="Logo Klungkung">
            </div>
        </header>

        <h1 class="text-3xl font-bold text-black mb-6">Data Penugasan</h1>

        <div class="bg-white rounded-xl shadow-lg p-8 min-h-[600px] relative">

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <form action="/dashboard-penugasan" method="GET" class="flex flex-col md:flex-row gap-4 w-full md:w-auto flex-1">
                    <div class="relative flex-1 max-w-sm">
                        <button type="submit" class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <iconify-icon icon="lucide:search" class="text-white text-xl"></iconify-icon>
                        </button>
                        <input type="text" name="search" id="searchInputPenugasan" value="{{ $searchTerm ?? '' }}" placeholder="Cari data penugasan"
                            class="bg-[#253D6B] text-white text-sm rounded-full pl-10 pr-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-white/70 shadow-md">
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#253D6B] uppercase whitespace-nowrap">Tampilkan:</span>
                        <select name="perPage" onchange="this.form.submit()" class="bg-[#253D6B] text-white text-xs rounded-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400 cursor-pointer appearance-none text-center min-w-[80px]">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        </select>
                    </div>
                </form>

                <a href="/dashboard-penugasan/create"
                    class="bg-[#253D6B] hover:bg-[#1a2e52] text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-md transition-all active:scale-95 inline-flex whitespace-nowrap">
                    <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                    <span class="font-semibold text-sm">Tambah Data</span>
                </a>
            </div>

            <div class="overflow-hidden border border-gray-300 rounded-sm">
                <table class="w-full text-left border-collapse" id="penugasanTable">
                    <thead>
                        <tr class="bg-[#FDE047] border-b border-gray-300">
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800 w-12 text-center">
                                No</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Username</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Nama lokasi</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Waktu</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800 text-center">SPT</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800 text-center">Status</th>
                            <th class="py-3 px-4 border-r border-gray-300 font-semibold text-gray-800">Objek</th>
                            <th class="py-3 px-4 font-semibold text-gray-800 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#FFFBEB]">
                        @forelse ($dataPenugasan as $index => $penugasan)
                        <tr class="border-b border-gray-300 data-row">
                            <td class="py-3 px-4 border-r border-gray-300 text-center text-sm">
                                {{ ($currentPage - 1) * $perPage + ($index + 1) }}
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm search-target">{{ $penugasan['nama_operator'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm search-target">{{ $penugasan['nama_lokasi'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm">{{ $penugasan['tanggal_rentang'] }}
                                <br>{{ $penugasan['jam_rentang'] }}</td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm text-center">
                                @if($penugasan['file_spt'] !== '-')
                                <button onclick="previewSPT('{{ asset('uploads/spt/' . $penugasan['file_spt']) }}', '{{ $penugasan['file_spt'] }}')" 
                                   class="inline-flex items-center justify-center w-10 h-10 bg-blue-50 text-blue-600 rounded-full hover:bg-blue-100 hover:text-blue-800 transition-all duration-200 shadow-sm" title="Preview SPT">
                                   <iconify-icon icon="lucide:eye" class="text-xl"></iconify-icon>
                                </button>
                                @else
                                <span class="text-gray-400">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm text-center">
                                @if(strtolower($penugasan['status'] ?? 'aktif') === 'aktif')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-bold uppercase border border-green-200 search-target">Aktif</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold uppercase border border-red-200 search-target">Inaktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-r border-gray-300 text-sm italic search-target">{{ $penugasan['objek_survei'] }}</td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center gap-2">
                                    @if(($penugasan['status'] ?? 'aktif') === 'inaktif')
                                    <form action="{{ route('penugasan.reset', $penugasan['id']) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-500 p-1.5 rounded hover:bg-blue-600 flex items-center justify-center transition-colors shadow-md" title="Aktifkan Kembali">
                                            <iconify-icon icon="lucide:refresh-cw" class="text-white text-lg"></iconify-icon>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ url('/dashboard-penugasan/edit/'.$penugasan['id']) }}" class="bg-yellow-400 p-1.5 rounded hover:bg-yellow-500 flex items-center justify-center transition-colors shadow-md">
                                        <iconify-icon icon="lucide:edit-3" class="text-white text-lg"></iconify-icon>
                                    </a>
                                    <form action="{{ url('/delete-penugasan/'.$penugasan['id']) }}" method="POST" onsubmit="confirmDelete(event, this, 'Yakin ingin menghapus penugasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 p-1.5 rounded hover:bg-red-600 flex items-center justify-center transition-colors shadow-md">
                                            <iconify-icon icon="lucide:trash-2" class="text-white text-lg"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-500 italic">Tidak ada data penugasan.</td>
                        </tr>
                        @endforelse
                        <tr id="noResultsRow" class="hidden">
                            <td colspan="8" class="py-10 text-center text-gray-500 italic">Data tidak ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-center items-center gap-4 mt-8">
                <a href="{{ $currentPage > 1 ? url('/dashboard-penugasan?page='.($currentPage - 1).'&search='.$searchTerm.'&perPage='.$perPage) : '#' }}" 
                    class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-full shadow-md hover:bg-[#1a2e52] transition-all {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                    <iconify-icon icon="lucide:chevron-left" class="text-white text-xl"></iconify-icon>
                </a>
                
                <span class="text-sm font-bold text-gray-600">Halaman {{ $currentPage }} dari {{ $totalPages }}</span>

                <a href="{{ $currentPage < $totalPages ? url('/dashboard-penugasan?page='.($currentPage + 1).'&search='.$searchTerm.'&perPage='.$perPage) : '#' }}" 
                    class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-full shadow-md hover:bg-[#1a2e52] transition-all {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : '' }}">
                    <iconify-icon icon="lucide:chevron-right" class="text-white text-xl"></iconify-icon>
                </a>
            </div>
        </div>
    </main>

    <!-- Modal Preview SPT -->
    <div id="sptModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div onclick="closeSPTModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b">
                        <h3 class="text-lg font-bold text-gray-900">Preview Surat Tugas / SPT</h3>
                        <button onclick="closeSPTModal()" class="text-gray-400 hover:text-gray-600">
                            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                        </button>
                    </div>
                    <div id="previewContainer" class="w-full h-[60vh] flex items-center justify-center bg-gray-50 rounded-xl overflow-hidden border">
                        <!-- Content via JS -->
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex flex-col sm:flex-row-reverse gap-3">
                    <a id="downloadBtn" href="#" download class="w-full inline-flex justify-center rounded-full px-6 py-2 bg-[#253D6B] text-white font-bold sm:ml-3 sm:w-auto sm:text-sm">
                        Download SPT
                    </a>
                    <button type="button" onclick="closeSPTModal()" class="w-full inline-flex justify-center rounded-full px-6 py-2 bg-white text-gray-700 border sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/navbar.js') }}"></script>

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

    <!-- Modal Error/Duplicate -->
    <div id="errorModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0F172A]/40 backdrop-blur-md">
        <div class="bg-white w-full max-w-sm rounded-[35px] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="errorModalContent">
            <div class="relative p-8 text-center">
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-red-400 to-orange-500 opacity-10 rounded-b-[50px]"></div>
                <div class="relative mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <iconify-icon icon="lucide:alert-circle" class="text-5xl text-red-600 animate-pulse"></iconify-icon>
                </div>
                <h3 class="text-2xl font-black text-[#253D6B] mb-2 tracking-tight">Peringatan!</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    @if(session('error'))
                        {{ session('error') }}
                    @elseif($errors->any())
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    @endif
                </p>
                <button onclick="hideErrorModal()" class="w-full bg-[#253D6B] hover:bg-[#1a2e52] text-white font-bold py-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showSuccessModal();
            @endif
            @if(session('error') || $errors->any())
                showErrorModal();
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

        function showErrorModal() {
            const modal = document.getElementById('errorModal');
            const content = document.getElementById('errorModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function hideErrorModal() {
            const content = document.getElementById('errorModalContent');
            const modal = document.getElementById('errorModal');
            content.classList.add('scale-95', 'opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function previewSPT(fileUrl, fileName) {
            const modal = document.getElementById('sptModal');
            const container = document.getElementById('previewContainer');
            const downloadBtn = document.getElementById('downloadBtn');
            const fileExt = fileName.split('.').pop().toLowerCase();
            downloadBtn.href = fileUrl;
            container.innerHTML = '';
            
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                const img = document.createElement('img');
                img.src = fileUrl;
                img.className = 'max-w-full max-h-full object-contain';
                container.appendChild(img);
            } else if (fileExt === 'pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = fileUrl + '#toolbar=0';
                iframe.className = 'w-full h-full border-none';
                container.appendChild(iframe);
            } else {
                container.innerHTML = '<p class="p-10 text-gray-500 text-center">Preview tidak tersedia untuk format ini.</p>';
            }
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSPTModal() {
            document.getElementById('sptModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Pencarian sekarang ditangani oleh server (Server-side Search)
        });
    </script>
</body>
</html>
