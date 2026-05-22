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

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <style>
    .flatpickr-calendar {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        border: none;
    }

    .flatpickr-day.selected {
        background: #253D6B !important;
        border-color: #253D6B !important;
    }
    </style>
</head>

<body class="flex bg-[#F5F7FA]">

    @include('admin.template.navbar')

    <main class="main-content ml-64 p-8 w-full">
        <header class="flex justify-end items-center mb-8">
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" class="w-10 h-10 object-contain" alt="Logo Klungkung">
        </header>

        <div class="bg-white rounded-xl shadow-lg p-10 min-h-[600px]">
            <h1 class="text-4xl font-bold text-center text-black mb-10">
                {{ isset($penugasan) ? 'Edit Penugasan' : 'Form Penugasan' }}</h1>

            <!-- Alert Notifikasi -->
            @if(session('success'))
            <div
                class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl flex items-center gap-3">
                <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl flex items-center gap-3">
                <iconify-icon icon="lucide:alert-circle" class="text-xl"></iconify-icon>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl">
                <ul class="list-disc list-inside font-medium text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form
                action="{{ isset($penugasan) ? url('/dashboard-penugasan/update/'.$id) : '/dashboard-penugasan/store' }}"
                method="POST" enctype="multipart/form-data">
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
                                <option value="{{ $id_user }}"
                                    {{ (old('id_user', $penugasan['id_user'] ?? '') == $id_user) ? 'selected' : '' }}>
                                    {{ $user['username'] }}</option>
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
                                <option value="{{ $id_lokasi }}"
                                    {{ (old('id_lokasi', $penugasan['id_lokasi'] ?? '') == $id_lokasi) ? 'selected' : '' }}>
                                    {{ $lokasi['nama_lokasi'] ?? $lokasi['alamat'] ?? 'Lokasi Tanpa Nama' }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input type="text" name="waktu_mulai" id="waktu_mulai" required
                                    class="w-full border border-gray-300 rounded-md p-2 text-sm bg-white cursor-pointer"
                                    placeholder="Pilih Tanggal & Waktu" value="{{ old('waktu_mulai', $penugasan['waktu_mulai'] ?? '') }}">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 text-sm">S/D</span>
                                <input type="text" name="waktu_selesai" id="waktu_selesai" required
                                    class="w-full border border-gray-300 rounded-md p-2 text-sm bg-white cursor-pointer"
                                    placeholder="Pilih Tanggal & Waktu" value="{{ old('waktu_selesai', $penugasan['waktu_selesai'] ?? '') }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Lokasi</label>
                            <textarea name="keterangan"
                                class="w-full border border-gray-300 rounded-md p-2 h-24 outline-none"
                                placeholder="Masukkan detail lokasi...">{{ old('keterangan', $penugasan['keterangan'] ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Objek Survei</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1 group">
                                    <input type="text" id="displayObjek" readonly
                                        class="w-full border border-gray-300 rounded-t-md p-2 bg-gray-50 outline-none text-sm font-semibold text-navy-900 border-b-0"
                                        placeholder="Pilih objek di bawah atau lewat tabel..."
                                        value="{{ old('objek_terpilih', $penugasan['objek_survei'] ?? '') }}">

                                    <select id="dropdownObjek" onchange="syncFromDropdown(this)"
                                        class="w-full border border-gray-300 rounded-b-md p-2 appearance-none outline-none text-sm cursor-pointer bg-white hover:bg-gray-50 transition-colors">
                                        <option value="">+ Tambah Objek Survei...</option>
                                        @if(!empty($objekTarif) && is_array($objekTarif))
                                            @foreach($objekTarif as $id_objek => $objek)
                                                @if(isset($objek['status']) && $objek['status'] == 'Aktif')
                                                    @php $safeValue = strtolower(str_replace(' ', '-', $objek['nama'])); @endphp
                                                    <option value="{{ $safeValue }}" data-nama="{{ $objek['nama'] }}">{{ $objek['nama'] }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>

                                    <div class="absolute right-3 bottom-3 pointer-events-none text-gray-400">
                                        <iconify-icon icon="lucide:chevron-down"></iconify-icon>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="objek_terpilih" id="hiddenObjekInput"
                                value="{{ old('objek_terpilih', $penugasan['objek_survei'] ?? '') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Surat Tugas / SPT
                                (PDF/JPG/PNG)</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-md p-4 flex justify-between items-center bg-gray-50">
                                <span
                                    class="text-gray-400 text-sm italic">{{ isset($penugasan['file_spt']) ? $penugasan['file_spt'] : 'Pilih file...' }}</span>
                                <input type="file" name="surat_spt" class="hidden" id="fileSpt">
                                <label for="fileSpt" class="cursor-pointer">
                                    <iconify-icon icon="lucide:upload" class="text-gray-400 text-xl"></iconify-icon>
                                </label>
                            </div>
                            <small style="color: gray;">Maksimal ukuran file: 2MB.
                                {{ isset($penugasan) ? '(Biarkan kosong jika tidak ingin mengganti SPT)' : '' }}</small>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="submit"
                                class="bg-[#253D6B] text-white px-10 py-2 rounded-full font-bold hover:bg-navy-800 transition-all shadow-lg">
                                {{ isset($penugasan) ? 'Update' : 'Kirim' }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="border border-gray-200 rounded-lg p-4 shadow-sm bg-white">
                            <div class="relative mb-4 flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" id="mapSearch" placeholder="Cari alamat di Google Maps..."
                                        class="w-full border border-gray-300 rounded-full pl-4 pr-10 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                                    <button type="button" onclick="searchLocation()"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 flex items-center">
                                        <iconify-icon icon="lucide:search" class="text-xl"></iconify-icon>
                                    </button>
                                </div>
                                <button type="button" id="locateBtn" title="Dapatkan Lokasi GPS"
                                    class="bg-white border border-gray-300 rounded-full p-2 text-gray-500 hover:text-blue-500 hover:border-blue-500 transition-all flex items-center justify-center shadow-sm">
                                    <iconify-icon icon="lucide:locate-fixed" class="text-xl"></iconify-icon>
                                </button>
                            </div>

                            <div
                                class="w-full h-72 bg-gray-100 rounded-lg overflow-hidden shadow-inner border border-gray-200">
                                <div id="map" class="w-full h-full"></div>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg overflow-hidden text-sm">
                            <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 border-b sticky top-0 z-10">
                                        <tr>
                                            <th class="p-3">No</th>
                                            <th class="p-3">Nama Objek</th>
                                            <th class="p-3">Status</th>
                                            <th class="p-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @if(!empty($objekTarif) && is_array($objekTarif))
                                        @foreach($objekTarif as $id_objek => $objek)
                                        @if(isset($objek['status']) && $objek['status'] == 'Aktif')
                                        @php $safeId = strtolower(str_replace(' ', '-', $objek['nama'])); @endphp
                                        <tr class="border-b"
                                            id="row-{{ $safeId }}">
                                            <td class="p-3 text-center">{{ $no++ }}</td>
                                            <td class="p-3 font-bold">{{ $objek['nama'] }}</td>
                                            <td class="p-3 status-text">-</td>
                                            <td class="p-3 text-center">
                                                <button type="button" onclick="toggleObjek('{{ $objek['nama'] }}', '{{ $safeId }}')"
                                                    class="action-btn text-green-500">
                                                    <iconify-icon icon="lucide:plus-circle" class="text-2xl">
                                                    </iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="4" class="p-3 text-center text-gray-400 italic">Tidak ada data
                                                objek tarif aktif</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </main>
    <script>
    window.LokasiTerdaftar = @json($lokasitikor);

    // Inisialisasi Objek Survei Terpilih (Mode Edit atau Gagal Validasi)
    document.addEventListener('DOMContentLoaded', function() {
        const savedObjects = "{{ old('objek_terpilih', $penugasan['objek_survei'] ?? '') }}";
        if (savedObjects) {
            const objectList = savedObjects.split(',').map(s => s.trim());
            objectList.forEach(obj => {
                if (obj) {
                    // Beri sedikit delay agar script PenugasanUser.js siap
                    setTimeout(() => {
                        // Cari baris tabel yang memiliki nama objek tersebut (case sensitive sesuai label database)
                        const rows = document.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const nameCell = row.querySelector('td.font-bold');
                            if (nameCell && nameCell.innerText.trim() === obj.trim()) {
                                const btn = row.querySelector('button');
                                const statusCell = row.querySelector('.status-text');
                                // Hanya klik jika statusnya belum terpilih
                                if (statusCell && statusCell.innerText.trim() === '-' && btn) {
                                    btn.click();
                                }
                            }
                        });
                    }, 400); // Delay sedikit lebih lama untuk memastikan script JS lain sudah load
                }
            });
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#waktu_mulai", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minDate: "today",
            locale: {
                firstDayOfWeek: 1
            }
        });

        flatpickr("#waktu_selesai", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minDate: "today",
            locale: {
                firstDayOfWeek: 1
            }
        });
    });
    </script>
    <script src="{{ asset('js/navbar.js') }}"></script>
    <script src="{{ asset('js/PenugasanUser.js') }}"></script>

</body>

</html>