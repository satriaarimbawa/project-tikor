<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tambah Penugasan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
</head>

<body class="flex bg-[#F5F7FA]">

    <aside class="sidebar-navy w-64 min-h-screen text-white flex flex-col fixed z-50 shadow-2xl">
        <div class="py-8 flex justify-center items-center">
            <img src="{{ asset('assets/logo_dishub.png') }}"
                class="w-20 h-20 object-contain drop-shadow-xl transition-all duration-300" alt="Logo Dishub">
        </div>

        <nav class="flex-1 space-y-1">
            <a href="/dashboard-admin"
                class="relative flex items-center px-6 py-4 text-white group overflow-hidden transition-all">
                @if(request()->is('dashboard') || request()->is('/'))
                <div class="absolute inset-0" style="background-color: rgba(37, 61, 107, 0.55);"></div>
                @endif
                <div class="relative z-10 flex items-center gap-4 ml-2">
                    <img src="{{ asset('assets/Beranda.png') }}" class="w-6 h-6 mr-4 object-contain" alt="Beranda">
                    <span class="font-bold text">Beranda</span>
                </div>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Objek Survey.png') }}" class="w-5 h-5 mr-4 object-contain"
                    alt="Objek Survey">
                <span>Objek Survey & Tarif</span>
            </a>

            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/Lokasi.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Lokasi">
                <span>Penetapan Lokasi</span>
            </a>

            <a href="/dashboard-penugasan"
                class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <div class="absolute inset-0" style="background-color: rgba(37, 61, 107, 0.55);"></div>
                <img src="{{ asset('assets/Penugasan.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Penugasan">
                <span>Penugasan</span>
            </a>
            <a href="#" class="nav-link flex items-center px-6 py-3 text-sm rounded-r-full transition-all">
                <img src="{{ asset('assets/User.png') }}" class="w-5 h-5 mr-4 object-contain" alt="Daftar User">
                <span>Daftar User</span>
            </a>

            <div class="relative">
                <button onclick="toggleSubMenu()"
                    class="nav-link w-full flex items-center px-6 py-3 text-sm rounded-r-full transition-all focus:outline-none">
                    <img src="{{ asset('assets/Laporan.png') }}" class="w-6 h-6 mr-4 object-contain" alt="Laporan">
                    <span>Laporan</span>
                    <iconify-icon icon="lucide:chevron-down" id="chevron-icon"
                        class="ml-auto transition-transform duration-300"></iconify-icon>
                </button>

                <div id="subMenuLaporan" class="hidden flex flex-col mt-2 space-y-2 mx-2 transition-all">
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Lokasi.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Lokasi">
                        <span>Berdasarkan Lokasi</span>
                    </a>
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Kedatangan.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Kedatangan">
                        <span>Berdasarkan Waktu</span>
                    </a>
                    <a href="#"
                        class="py-2 pl-10 text-[11px] text-white/70 hover:text-white flex items-center gap-4 transition-colors">
                        <img src="{{ asset('assets/Laporan_Operator.png') }}" class="w-6 h-6 object-contain"
                            alt="Laporan Operator">
                        <span>Berdasarkan Operator</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-6 mt-auto flex items-center gap-3" style="background-color: rgba(37, 61, 107, 0.55);">
            <div class="w-5 h-5 flex items-center justify-center">
                <img src="{{ asset('assets/Profil.png') }}" class="w-8 h-8 object-contain" alt="User Profile">
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-xs font-bold leading-none truncate text-white">Rani</p>
                <p class="text-[10px] text-white/50 uppercase tracking-tighter mt-1">Administrator</p>
            </div>
            <button class="text-white/30 hover:text-white transition">
                <iconify-icon icon="lucide:log-out" class="text-lg"></iconify-icon>
            </button>
        </div>
    </aside>


    <main class="main-content ml-64 p-8 w-full">
        <header class="flex justify-end items-center mb-8">
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain" alt="Logo Klungkung">
        </header>

        <div class="bg-white rounded-xl shadow-lg p-10 min-h-[600px]">
            <h1 class="text-4xl font-bold text-center text-black mb-10">Form Penugasan</h1>

            <form action="/dashboard-penugasan/store" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Username</label>
                            <select name="id_user" required
                                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih Operator --</option>
                                @if(!empty($users) && is_array($users))
                                @foreach($users as $id_user => $user)
                                @if(isset($user['role_user']) && $user['role_user'] == 'operator')
                                <option value="{{ $id_user }}">{{ $user['username'] }}</option>
                                @endif
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                            <select name="id_lokasi" required
                                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih Lokasi --</option>
                                @if(!empty($lokasitikor) && is_array($lokasitikor))
                                @foreach($lokasitikor as $id_lokasi => $lokasi)
                                <option value="{{ $id_lokasi }}">{{ $lokasi['alamat'] ?? 'Lokasi Tanpa Nama' }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="datetime-local" name="waktu_mulai" required
                                    class="w-full border border-gray-300 rounded-md p-2 text-sm">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 text-sm">S/D</span>
                                <input type="datetime-local" name="waktu_selesai" required
                                    class="w-full border border-gray-300 rounded-md p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Lokasi</label>
                            <textarea name="keterangan" class="w-full border border-gray-300 rounded-md p-2 h-24 outline-none"
                                placeholder="Masukkan detail lokasi..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Objek Survei</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1 group">
                                    <input type="text" id="displayObjek" readonly
                                        class="w-full border border-gray-300 rounded-t-md p-2 bg-gray-50 outline-none text-sm font-semibold text-navy-900 border-b-0"
                                        placeholder="Pilih objek di bawah atau lewat tabel...">

                                    <select id="dropdownObjek" onchange="syncFromDropdown(this)"
                                        class="w-full border border-gray-300 rounded-b-md p-2 appearance-none outline-none text-sm cursor-pointer bg-white hover:bg-gray-50 transition-colors">
                                        <option value="">+ Tambah Objek Survei...</option>
                                        <option value="motor">Motor</option>
                                        <option value="mobil">Mobil</option>
                                        <option value="truk">Truk</option>
                                        <option value="mini">Mini Bus</option>
                                    </select>

                                    <div class="absolute right-3 bottom-3 pointer-events-none text-gray-400">
                                        <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="objek_terpilih" id="hiddenObjekInput">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Surat Tugas / SPT
                                (PDF/JPG/PNG)</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-md p-4 flex justify-between items-center bg-gray-50">
                                <span class="text-gray-400 text-sm italic">Pilih file...</span>
                                <input type="file" name="surat_spt" class="hidden" id="fileSpt">
                                <label for="fileSpt" class="cursor-pointer">
                                    <iconify-icon icon="lucide:upload" class="text-gray-400 text-xl"></iconify-icon>
                                </label>
                            </div>
                            <small style="color: gray;">Maksimal ukuran file: 2MB.</small>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="submit"
                                class="bg-[#253D6B] text-white px-10 py-2 rounded-full font-bold hover:bg-navy-800 transition-all shadow-lg">
                                Kirim
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="border border-gray-200 rounded-lg p-4 shadow-sm bg-white">
                            <div class="relative mb-4">
                                <input type="text" id="mapSearch" placeholder="Cari alamat di Google Maps..."
                                    class="w-full border border-gray-300 rounded-full pl-4 pr-10 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                                <button onclick="searchLocation()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 flex items-center">
                                    <iconify-icon icon="lucide:search" class="text-xl"></iconify-icon>
                                </button>
                            </div>

                            <div
                                class="w-full h-72 bg-gray-100 rounded-lg overflow-hidden shadow-inner border border-gray-200">
                                <div id="map" class="w-full h-full"></div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg overflow-hidden text-sm">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b">
                                        <tr>
                                            <th class="p-3">No</th>
                                            <th class="p-3">Nama Objek</th>
                                            <th class="p-3">Status</th>
                                            <th class="p-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-b" id="row-motor">
                                            <td class="p-3 text-center">1</td>
                                            <td class="p-3 font-bold">Motor</td>
                                            <td class="p-3 status-text">-</td>
                                            <td class="p-3 text-center">
                                                <button type="button" onclick="toggleObjek('motor')"
                                                    class="action-btn text-green-500">
                                                    <iconify-icon icon="lucide:plus-circle" class="text-2xl">
                                                    </iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="border-b" id="row-mobil">
                                            <td class="p-3 text-center">2</td>
                                            <td class="p-3 font-bold">Mobil</td>
                                            <td class="p-3 status-text">-</td>
                                            <td class="p-3 text-center">
                                                <button type="button" onclick="toggleObjek('mobil')"
                                                    class="action-btn text-green-500">
                                                    <iconify-icon icon="lucide:plus-circle" class="text-2xl">
                                                    </iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="border-b" id="row-truk">
                                            <td class="p-3 text-center">3</td>
                                            <td class="p-3 font-bold">Truk</td>
                                            <td class="p-3 status-text">-</td>
                                            <td class="p-3 text-center">
                                                <button type="button" onclick="toggleObjek('truk')"
                                                    class="action-btn text-green-500">
                                                    <iconify-icon icon="lucide:plus-circle" class="text-2xl">
                                                    </iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr id="row-mini">
                                            <td class="p-3 text-center">4</td>
                                            <td class="p-3 font-bold">Mini Bus</td>
                                            <td class="p-3 status-text">-</td>
                                            <td class="p-3 text-center">
                                                <button type="button" onclick="toggleObjek('mini')"
                                                    class="action-btn text-green-500">
                                                    <iconify-icon icon="lucide:plus-circle" class="text-2xl">
                                                    </iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
            </form>
        </div>
    </main>
<script>
    window.LokasiTerdaftar = @json($lokasitikor);
</script>
<script src="{{ asset('js/PenugasanUser.js') }}"></script>

</body>
</html>