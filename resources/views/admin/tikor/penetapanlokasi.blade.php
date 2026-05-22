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

    <main class="flex-1 ml-64 p-12 min-h-screen" style="background: linear-gradient(180deg, #E7EFF6 0%, #FFFFFF 100%);">
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-between items-start mb-10">
            <h1 class="text-[32px] font-bold text-[#1e293b] tracking-tight">Penetapan Lokasi</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-12 h-auto" alt="Logo Klungkung">
        </div>

        <div class="flex gap-10 items-start">
            
            <div class="flex-[3] bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] p-10 border border-gray-50/50">
                <h2 class="text-xl font-bold text-[#1e293b] mb-8 uppercase tracking-widest">Daftar Lokasi</h2>

                <div class="relative mb-8">
                    <input type="text" id="searchInput" placeholder="Search" class="w-full pl-12 pr-4 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-2xl focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-all font-medium placeholder:text-gray-300">
                    <iconify-icon icon="lucide:search" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 text-xl"></iconify-icon>
                </div>

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
                            @php $no = 1; @endphp
                            @if($daftarLokasi)
                                @foreach($daftarLokasi as $key => $lokasi)
                                <tr class="bg-white hover:bg-gray-50 transition-colors shadow-sm">
                                    <td class="py-4 px-4 text-center border-y border-l border-gray-100 rounded-l-xl">{{ $no++ }}</td>
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
                                            <form action="{{ url('/delete-lokasi-tikor/'.$key) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
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
            </div>

            <div class="flex-[2] bg-white rounded-[2rem] shadow-[0_30px_90px_rgba(0,0,0,0.15)] border border-gray-50/50 overflow-hidden flex flex-col">
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
                                <input type="text" name="target_harian" placeholder="250.000" class="w-full pl-16 pr-5 py-3.5 bg-[#FBFBFB] border border-gray-200 rounded-xl focus:ring-4 focus:ring-blue-50 focus:border-blue-400 outline-none text-sm font-bold text-[#1e293b] placeholder:text-gray-300 transition-all">
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
<script>
    // Mengirim data lokasi dari Firebase ke JavaScript
    window.daftarLokasi = @json($daftarLokasi);
</script>
<script src="{{ asset('js/tikor.js') }}"></script>
</body>
</html>