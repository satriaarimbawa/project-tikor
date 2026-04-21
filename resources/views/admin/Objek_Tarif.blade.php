<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objek & Tarif - Dishub</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
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
    </style>
</head>
<body class="flex">

    @include('admin.template.navbar')

    <main class="ml-64 p-10 w-full min-h-screen">
        <header class="flex justify-between items-center mb-10">
            <h1 class="text-[28px] font-extrabold text-[#2D3748] tracking-tight">Objek & Tarif</h1>
            <img src="{{ asset('assets/Logo_Klungkung.png') }}" alt="Logo Klungkung" class="w-12 h-12 object-contain">
        </header>

        <div class="grid grid-cols-12 gap-8 items-start">
            
            <div class="col-span-12 lg:col-span-7 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Daftar Objek & Tarif</h2>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-300 text-[10px]"></i>
                        </span>
                        <input type="text" placeholder="Search" class="pl-9 pr-4 py-2 bg-[#F7FAFC] border border-gray-100 rounded-xl text-xs outline-none focus:ring-1 focus:ring-blue-100 w-44">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-[#F7FAFC]">
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">No</th>
                                <th class="py-4 px-4 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase text-left">Nama Objek</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Tarif</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Status</th>
                                <th class="py-4 px-2 border border-[#E6E6E6] text-[10px] font-black text-gray-900 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $index => $item)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-2 border border-gray-100 text-center text-xs font-bold text-gray-400">{{ $index + 1 }}</td>
                                <td class="py-4 px-4 border border-gray-100 text-[13px] font-bold text-gray-700">{{ $item['nama'] }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center font-black text-[13px] text-blue-600">Rp. {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                <td class="py-4 px-2 border border-gray-100 text-center">
                                    <span class="{{ $item['status'] == 'Aktif' ? 'bg-[#EBFFFF] text-[#38B2AC]' : 'bg-gray-100 text-gray-400' }} px-3 py-1 rounded-md text-[9px] font-black uppercase">{{ $item['status'] }}</span>
                                </td>
                                <td class="py-4 px-2 border border-gray-100 text-center text-gray-300">
                                    <button onclick="editData('{{ $item['id'] }}', '{{ $item['nama'] }}', '{{ $item['harga'] }}', '{{ $item['status'] }}', '{{ $item['keterangan'] }}')" class="hover:text-gray-900 mr-2"><i class="fas fa-pencil-alt text-[10px]"></i></button>
                                    <form action="{{ route('objek-tarif.destroy', $item['id']) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:text-red-500"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400 text-xs">Belum ada data objek & tarif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-5 bg-white border border-gray-100 shadow-sm rounded-[30px] p-8 sticky top-5">
                <div class="flex flex-col items-center mb-8">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-4">Icon Objek</p>
                    <div class="w-32 h-32 bg-[#D9D9D9] rounded-[25px] flex items-center justify-center border-2 border-dashed border-[#E2E8F0]">
                        <img src="{{ asset('assets/motor.png') }}" class="w-20 h-20 object-contain filter invert brightness-0 opacity-80">
                    </div>
                    <button class="mt-4 text-[10px] font-black text-blue-500 uppercase tracking-widest">Ganti Icon</button>
                </div>

                <h3 id="form-title" class="text-sm font-black text-gray-800 uppercase mb-6">Form Objek & Tarif</h3>

                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 text-xs" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
                @endif

                <form id="objek-form" action="{{ route('objek-tarif.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div id="method-field"></div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Nama Objek</label>
                        <input type="text" name="nama" id="nama" required class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-bold text-gray-700 outline-none focus:border-blue-200">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Tarif (Rp)</label>
                        <input type="number" name="harga" id="harga" required class="w-full px-5 py-4 bg-[#F7FAFC] border border-transparent rounded-2xl font-black text-blue-600 outline-none focus:border-blue-200">
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
        function editData(id, nama, harga, status, keterangan) {
            document.getElementById('form-title').innerText = 'Edit Objek & Tarif';
            document.getElementById('objek-form').action = `/Objek_Tarif/update/${id}`;
            document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('nama').value = nama;
            document.getElementById('harga').value = harga;
            document.getElementById('status').value = status;
            document.getElementById('keterangan').value = keterangan;
            
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
                const text = row.innerText.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    hasResults = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle empty state if needed
            const emptyRow = document.getElementById('no-results-row');
            if (!hasResults && searchTerm !== '') {
                if (!emptyRow) {
                    const tr = document.createElement('tr');
                    tr.id = 'no-results-row';
                    tr.innerHTML = `<td colspan="5" class="py-4 text-center text-gray-400 text-xs">Pencarian "${searchTerm}" tidak ditemukan.</td>`;
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
</body>
</html>
