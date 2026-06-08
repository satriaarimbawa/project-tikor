<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objek & Tarif - Dishub</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/objek_tarif.css') }}?v={{ time() }}">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(180deg, #E7EFF6 70%, #FFFFFF 100%);
            min-height: 100vh;
        }
        .sidebar-navy {
            background-color: rgba(37, 61, 107, 0.75) !important;
            backdrop-filter: blur(10px);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="flex">

    @include('admin.template.navbar')

    <main class="lg:ml-64 p-4 md:p-10 flex-1 min-w-0 overflow-x-hidden min-h-screen">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-[28px] font-extrabold text-[#2D3748] tracking-tight">Objek & Tarif</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" alt="Logo Klungkung" class="w-12 h-12 object-contain">
        </header>

        <div class="grid grid-cols-12 gap-8 items-start">
            
            <div class="col-span-12 xl:col-span-7 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8">
                    <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Daftar Objek & Tarif</h2>
                    
                    <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-4">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-300 text-[10px]"></i>
                            </span>
                            <input type="text" name="search" id="search-input" value="{{ request('search') }}" placeholder="Search" class="pl-9 pr-4 py-2 bg-[#F7FAFC] border border-gray-100 rounded-xl text-xs outline-none focus:ring-1 focus:ring-blue-100 w-44">
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-black text-gray-400 uppercase whitespace-nowrap">Show:</span>
                            <select name="perPage" onchange="this.form.submit()" class="bg-[#F7FAFC] border border-gray-100 rounded-lg py-1.5 px-2 text-[10px] font-bold outline-none focus:ring-1 focus:ring-blue-100 cursor-pointer">
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#F7FAFC]">
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">No</th>
                                <th class="py-4 px-4 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase text-left">Nama Objek</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Tarif Lama</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Tarif Baru</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Status</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            @forelse($data as $index => $item)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-2 border border-gray-100 text-center text-xs font-bold text-gray-400">
                                    {{ ($currentPage - 1) * $perPage + ($index + 1) }}
                                </td>
                                <td class="py-4 px-4 border border-gray-100 text-[13px] font-bold text-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden border border-gray-50 p-1">
                                            <img src="{{ $item['icon_url'] }}" class="w-full h-full object-contain">
                                        </div>
                                        <span>{{ $item['nama'] }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-2 border border-gray-100 text-center font-bold text-[13px] text-gray-400">Rp. {{ number_format($item['tarif_lama'] ?? 0, 0, ',', '.') }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center font-black text-[13px] text-blue-600">Rp. {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center">
                                    <span class="{{ $item['status'] == 'Aktif' ? 'bg-[#EBFFFF] text-[#38B2AC]' : 'bg-gray-100 text-gray-400' }} px-3 py-1 rounded-md text-[9px] font-black uppercase">{{ $item['status'] }}</span>
                                </td>
                                <td class="py-4 px-2 border border-gray-100 text-center text-gray-300">
                                    <button onclick="editData('{{ $item['id'] }}', '{{ $item['nama'] }}', '{{ $item['harga'] }}', '{{ $item['status'] }}', '{{ $item['keterangan'] }}', '{{ $item['icon_url'] }}')" class="hover:text-gray-900 mr-2"><i class="fas fa-pencil-alt text-[10px]"></i></button>
                                    <form action="{{ route('objek-tarif.destroy', $item['id']) }}" method="POST" class="inline" onsubmit="confirmDelete(event, this, 'Apakah Anda yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:text-red-500"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-row">
                                <td colspan="6" class="py-4 text-center text-gray-400 text-xs">Belum ada data objek & tarif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex justify-center items-center gap-4">
                    <a href="{{ $currentPage > 1 ? url()->current().'?page='.($currentPage - 1).'&perPage='.$perPage.'&search='.request('search') : '#' }}" 
                        class="w-8 h-8 flex items-center justify-center bg-[#253D6B] rounded-lg shadow-sm hover:bg-[#1a2e52] transition-all text-white {{ $currentPage <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-left" class="text-sm"></iconify-icon>
                    </a>
                    
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Page {{ $currentPage }} of {{ $totalPages }}</span>

                    <a href="{{ $currentPage < $totalPages ? url()->current().'?page='.($currentPage + 1).'&perPage='.$perPage.'&search='.request('search') : '#' }}" 
                        class="w-8 h-8 flex items-center justify-center bg-[#253D6B] rounded-lg shadow-sm hover:bg-[#1a2e52] transition-all text-white {{ $currentPage >= $totalPages ? 'opacity-30 cursor-not-allowed' : '' }}">
                        <iconify-icon icon="lucide:chevron-right" class="text-sm"></iconify-icon>
                    </a>
                </div>
            </div>

            <div class="col-span-12 xl:col-span-5 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8 sticky top-5 text-center">
                <div class="flex flex-col items-center mb-8">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-4">Icon Objek</p>
                    <div id="icon-preview-container" class="w-32 h-32 bg-[#F7FAFC] rounded-[25px] flex items-center justify-center border-2 border-dashed border-[#E2E8F0] overflow-hidden">
                        <img id="icon-preview" src="{{ asset('assets/Motor.png') }}" class="w-20 h-20 object-contain filter invert brightness-0 opacity-10">
                    </div>
                    <button type="button" onclick="document.getElementById('icon-input').click()" class="mt-4 text-[10px] font-black text-blue-500 uppercase tracking-widest hover:text-blue-600 transition-colors">Pilih Gambar</button>
                    <p class="text-[9px] text-gray-400 mt-1 italic">*Format: JPG, PNG, Max 1MB</p>
                </div>

                <h3 id="form-title" class="text-sm font-black text-gray-800 uppercase mb-6 text-left">Form Objek & Tarif</h3>

                <form id="objek-form" action="{{ route('objek-tarif.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-left">
                    @csrf
                    <div id="method-field"></div>
                    
                    <!-- Hidden File Input -->
                    <input type="file" name="icon" id="icon-input" class="hidden" accept="image/*" onchange="previewImage(this)">
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Nama Objek</label>
                        <input type="text" name="nama" id="nama" required class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-bold text-gray-700 outline-none focus:border-blue-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Tarif (Rp)</label>
                        <input type="text" name="harga" id="harga" required class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-black text-blue-600 outline-none focus:border-blue-200" onkeyup="this.value = formatRupiah(this.value)">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Status</label>
                        <select name="status" id="status" required class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-bold text-gray-700 outline-none focus:border-blue-200">
                            <option value="Aktif">Aktif</option>
                            <option value="Inaktif">Inaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl text-sm text-gray-600 outline-none focus:border-blue-200"></textarea>
                    </div>
                    <div class="pt-4 text-center">
                        <button type="submit" id="submit-btn" class="w-full bg-[#24B445] hover:bg-[#1f9d3a] text-white font-black py-5 rounded-2xl shadow-lg uppercase text-xs tracking-widest transition-all">
                            Simpan Objek
                        </button>
                        <button type="button" id="cancel-btn" onclick="resetForm()" class="hidden w-full mt-2 bg-gray-200 hover:bg-gray-300 text-gray-600 font-black py-3 rounded-2xl uppercase text-[10px] tracking-widest transition-all">
                            Batal Edit
                        </button>
                        <p class="text-[9px] text-red-500 mt-4 italic font-medium">*Periksa kembali kebenaran data sebelum dikirim</p>
                    </div>
                </form>
            </div>
        </div>
    </main>

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

        function previewImage(input) {
            const preview = document.getElementById('icon-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('filter', 'invert', 'brightness-0', 'opacity-10');
                    preview.classList.add('w-full', 'h-full', 'object-cover');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function editData(id, nama, harga, status, keterangan, iconUrl) {
            document.getElementById('form-title').innerText = 'Edit Objek & Tarif';
            document.getElementById('objek-form').action = `/Objek_Tarif/update/${id}`;
            document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('nama').value = nama;
            document.getElementById('harga').value = formatRupiah(harga);
            document.getElementById('status').value = status;
            document.getElementById('keterangan').value = keterangan;
            
            const preview = document.getElementById('icon-preview');
            if (iconUrl && iconUrl !== 'null' && iconUrl !== '') {
                preview.src = iconUrl;
                preview.classList.remove('filter', 'invert', 'brightness-0', 'opacity-10');
                preview.classList.add('w-full', 'h-full', 'object-cover');
            } else {
                preview.src = "{{ asset('assets/Motor.png') }}";
                preview.classList.add('filter', 'invert', 'brightness-0', 'opacity-10');
                preview.classList.remove('w-full', 'h-full', 'object-cover');
            }

            document.getElementById('submit-btn').innerText = 'Update Objek';
            document.getElementById('submit-btn').classList.replace('bg-[#24B445]', 'bg-blue-600');
            document.getElementById('submit-btn').classList.replace('hover:bg-[#1f9d3a]', 'hover:bg-blue-700');
            
            document.getElementById('cancel-btn').classList.remove('hidden');
        }

        function resetForm() {
            document.getElementById('form-title').innerText = 'Form Objek & Tarif';
            document.getElementById('objek-form').action = "{{ route('objek-tarif.store') }}";
            document.getElementById('method-field').innerHTML = '';
            
            document.getElementById('nama').value = '';
            document.getElementById('harga').value = '';
            document.getElementById('status').value = 'Aktif';
            document.getElementById('keterangan').value = '';
            document.getElementById('icon-input').value = '';
            
            const preview = document.getElementById('icon-preview');
            preview.src = "{{ asset('assets/Motor.png') }}";
            preview.classList.add('filter', 'invert', 'brightness-0', 'opacity-10');
            preview.classList.remove('w-full', 'h-full', 'object-cover');

            document.getElementById('submit-btn').innerText = 'Simpan Objek';
            document.getElementById('submit-btn').classList.replace('bg-blue-600', 'bg-[#24B445]');
            document.getElementById('submit-btn').classList.replace('hover:bg-blue-700', 'hover:bg-[#1f9d3a]');
            
            document.getElementById('cancel-btn').classList.add('hidden');
        }

        // Fitur Live Search
        document.getElementById('search-input').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#table-body tr');
            let hasResults = false;

            tableRows.forEach(row => {
                if (row.id === 'empty-row' || row.id === 'no-results-row') return;
                const text = row.innerText.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    hasResults = true;
                } else {
                    row.style.display = 'none';
                }
            });

            const emptyRow = document.getElementById('no-results-row');
            if (!hasResults && searchTerm !== '') {
                if (!emptyRow) {
                    const tr = document.createElement('tr');
                    tr.id = 'no-results-row';
                    tr.innerHTML = `<td colspan="6" class="py-4 text-center text-gray-400 text-xs">Pencarian "${searchTerm}" tidak ditemukan.</td>`;
                    document.getElementById('table-body').appendChild(tr);
                } else {
                    emptyRow.querySelector('td').innerText = `Pencarian "${searchTerm}" tidak ditemukan.`;
                    emptyRow.style.display = '';
                }
            } else if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        });
    </script>
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

    <!-- Modal Duplicate -->
    <div id="duplicateModal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0F172A]/40 backdrop-blur-md">
        <div class="bg-white w-full max-w-sm rounded-[35px] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="duplicateModalContent">
            <div class="relative p-8 text-center">
                <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-br from-red-400 to-orange-500 opacity-10 rounded-b-[50px]"></div>
                <div class="relative mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                    <iconify-icon icon="lucide:alert-circle" class="text-5xl text-red-600 animate-pulse"></iconify-icon>
                </div>
                <h3 class="text-2xl font-black text-[#253D6B] mb-2 tracking-tight">Peringatan!</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    {{ session('duplicate') }}
                </p>
                <button onclick="hideDuplicateModal()" class="w-full bg-[#253D6B] hover:bg-[#1a2e52] text-white font-bold py-4 rounded-2xl transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2">
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
            @if(session('duplicate'))
                showDuplicateModal();
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
