<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penetapan Lokasi - Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/daftar-user.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

    <main class="flex-1 lg:ml-64 p-4 md:p-12 min-h-screen" style="background: linear-gradient(180deg, #E7EFF6 0%, #FFFFFF 100%);">
        
        <div class="flex justify-between items-start mb-10">
            <h1 class="text-[32px] font-bold text-[#1e293b] tracking-tight">Penetapan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-auto" alt="Logo Klungkung">
        </div>

        <div class="flex flex-col xl:flex-row gap-10 items-start">
            
            <div class="w-full xl:flex-[3] bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] p-10 border border-gray-50/50">
                <h2 class="text-xl font-bold text-[#1e293b] mb-8 uppercase tracking-widest">Daftar Lokasi</h2>

                <form action="{{ url()->current() }}" method="GET" class="flex flex-col sm:flex-row gap-4 mb-8">
                    <div class="relative flex-1">
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Search" class="w-full pl-12 pr-4 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-2xl focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-all font-medium placeholder:text-gray-300">
                        <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xl"></iconify-icon>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-2 bg-[#FBFBFB] border border-gray-200 rounded-2xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase whitespace-nowrap">Show:</span>
                        <select name="perPage" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold outline-none cursor-pointer appearance-none px-2">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        </select>
                    </div>
                </form>

            <div id="table-container">
                <div class="overflow-x-auto mt-6">
                    <table class="w-full border-separate border-spacing-y-2">
                        <thead class="bg-[#E5E7EB] text-[#1E293B] text-[12px] uppercase font-bold">
                            <tr>
                                <th class="py-3 px-4 rounded-l-lg text-center w-12">No</th>
                                <th class="py-3 px-4 text-left">Nama Lokasi</th>
                                <th class="py-3 px-4 text-left">Koordinat</th>
                                <th class="py-3 px-4 text-left">Target</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 rounded-r-lg text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-[12px] font-bold text-[#1E293B]">
                            @if($daftarLokasi)
                                @foreach($daftarLokasi as $index => $lokasi)
                                @php $key = $lokasi['id']; @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-colors shadow-sm">
                                    <td class="py-4 px-4 text-center border-y border-l border-gray-100 rounded-l-xl">
                                        {{ ($currentPage - 1) * $perPage + ($index + 1) }}
                                    </td>
                                    <td class="py-4 px-4 border-y border-gray-100">{{ $lokasi['nama_lokasi'] ?? '-' }}</td>
                                    <td class="py-4 px-4 border-y border-gray-100 text-gray-400 font-medium italic">
                                        {{ $lokasi['koordinat'] ?? 'Belum diset' }}
                                    </td>
                                    <td class="py-4 px-4 border-y border-gray-100 uppercase">
                                        Rp. {{ number_format($lokasi['target_harian'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-center border-y border-gray-100">
                                        <span class="{{ ($lokasi['status_dinamis'] ?? '') == 'Aktif' ? 'bg-[#BCF0DA] text-[#03543F]' : 'bg-gray-200 text-gray-600' }} px-4 py-1.5 rounded-lg text-[10px] uppercase font-black tracking-tighter">
                                            {{ $lokasi['status_dinamis'] ?? 'Inaktif' }}
                                            
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center border-y border-r border-gray-100 rounded-r-xl">
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ url('/delete-lokasi-tikor/'.$key) }}" method="POST" onsubmit="confirmDelete(event, this, 'Yakin ingin menghapus lokasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-800 hover:text-red-600 transition">
                                                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400 italic bg-white rounded-xl border border-gray-100">
                                        Data lokasi tidak ditemukan.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center items-center gap-4">
                    <a href="{{ $currentPage > 1 ? url()->current().'?page='.($currentPage - 1).'&perPage='.$perPage.'&search='.request('search') : '#' }}" 
                        class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-full shadow-md hover:bg-[#1a2e52] transition-all text-white {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-left" class="text-xl"></iconify-icon>
                    </a>
                    
                    <span class="text-sm font-bold text-gray-400">Page {{ $currentPage }} of {{ $totalPages }}</span>

                    <a href="{{ $currentPage < $totalPages ? url()->current().'?page='.($currentPage + 1).'&perPage='.$perPage.'&search='.request('search') : '#' }}" 
                        class="w-10 h-10 flex items-center justify-center bg-[#253D6B] rounded-full shadow-md hover:bg-[#1a2e52] transition-all text-white {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-right" class="text-xl"></iconify-icon>
                    </a>
                </div>
            </div>
            </div>

            <div class="w-full xl:flex-[2] bg-white rounded-[2rem] shadow-[0_30px_90px_rgba(0,0,0,0.15)] border border-gray-50/50 overflow-hidden flex flex-col">
                <div class="h-64 bg-[#F8FAFC] m-4 rounded-[1.5rem] overflow-hidden border border-gray-100 relative">

                    <div id="map" class="absolute inset-0 flex flex-col items-center justify-center">
                        
                        <div class="w-4 h-1.5 bg-black/10 rounded-full mt-1 blur-[2px]"></div>
                    </div>
                </div>

                <div class="p-8 pt-2 flex-1">
                    <h2 class="text-[12px] font-black text-[#1e293b] mb-6 uppercase tracking-[0.2em]">Form Penetapan Lokasi</h2>
                    <form action="/update-lokasi-tikor" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Nama Lokasi</label>
                            <input type="text" name="nama_lokasi" placeholder="Jl. Diponegoro" class="w-full px-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-sm font-bold text-[#1e293b] placeholder:text-gray-300 transition-all">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Koordinat</label>
                            <div class="relative">
                                <input type="text" name="koordinat" placeholder="-8.53532264617003, 115.4042257919066" class="w-full px-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-[11px] font-medium text-[#1e293b] placeholder:text-gray-300 transition-all">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center">
                                    <iconify-icon icon="lucide:locate-fixed" class="text-gray-400 hover:text-blue-500 cursor-pointer transition-colors"></iconify-icon>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-[#94a3b8] uppercase mb-2 block tracking-widest">Target Harian</label>
                            <div class="relative">
                                <div class="absolute left-5 top-1/2 -translate-y-1/2 flex items-center border-r border-gray-200 pr-3">
                                    <span class="text-sm font-bold text-[#1e293b]">Rp.</span>
                                </div>
                                <input type="text" name="target_harian" placeholder="250.000" class="w-full pl-16 pr-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-sm font-bold text-[#1e293b] placeholder:text-gray-300 transition-all" onkeyup="this.value = formatRupiah(this.value)">
                            </div>
                        </div>
                        <div class="flex items-start gap-2 pt-2">
                            <iconify-icon icon="lucide:info" class="text-red-400 text-sm mt-0.5"></iconify-icon>
                            <p class="text-[10px] text-red-400 font-bold italic leading-tight">Periksa kembali kebenaran data sebelum dikirim</p>
                        </div>
                        <button type="submit" class="w-full bg-[#22C55E] hover:bg-[#16A34A] text-white font-black py-4 rounded-[1.25rem] shadow-[0_12px_30px_-5px_rgba(34,197,94,0.4)] transition-all text-xl mt-4 tracking-tight hover:-translate-y-1 active:scale-[0.98]">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        </main>

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
    function formatRupiah(angka) {
        if (!angka) return '';
        var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

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
</script>
<script>
    // Mengirim data lokasi dari Firebase ke JavaScript
    window.daftarLokasi = @json($daftarLokasi);
</script>
<script src="{{ asset('js/tikor.js') }}"></script>
</body>
</html>