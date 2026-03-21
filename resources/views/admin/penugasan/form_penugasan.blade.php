
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PENUGASAN</title>
</head>
<body>
    <!-- resources/views/admin/penugasan_create.blade.php -->

<h2>Form Tambah Penugasan Operator</h2>

<!-- Tampilkan Pesan Error Validasi Jika Ada -->
@if ($errors->any())
    <div style="color: red; background-color: #fdd; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div style="color: red; margin-bottom: 10px;">{{ session('error') }}</div>
@endif

<!-- PERHATIAN: enctype="multipart/form-data" WAJIB ada agar bisa upload file/gambar -->
<form action="/dashboard-penugasan/store" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
    @csrf

    <!-- 1. Dropdown Pilih Lokasi (Diambil otomatis dari Firebase) -->
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Pilih Lokasi Cabang / Titik Absen:</label>
        <select name="id_lokasi" required style="width: 100%; padding: 8px;">
            <option value="">-- Pilih Lokasi --</option>
            @if(!empty($lokasitikor) && is_array($lokasitikor))
                @foreach($lokasitikor as $id_lokasi => $lokasi)
                    <!-- Menampilkan nama alamat lokasi sebagai pilihan -->
                    <option value="{{ $id_lokasi }}">{{ $lokasi['alamat'] ?? 'Lokasi Tanpa Nama' }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <!-- 2. Dropdown Pilih Operator -->
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Pilih Operator yang Ditugaskan:</label>
        <select name="id_user" required style="width: 100%; padding: 8px;">
            <option value="">-- Pilih Operator --</option>
            @if(!empty($users) && is_array($users))
                @foreach($users as $id_user => $user)
                    <!-- Logika Filter: Hanya tampilkan user yang role-nya 'operator' -->
                    @if(isset($user['role_user']) && $user['role_user'] == 'operator')
                        <option value="{{ $user['username'] }}">{{ $user['username'] }} ({{ $user['email'] ?? '-' }})</option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>

    <!-- 3. Input Waktu Mulai (Menggunakan datetime-local agar bisa pilih tanggal & jam sekaligus) -->
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Waktu Mulai Penugasan:</label>
        <input type="datetime-local" name="waktu_mulai" required style="width: 100%; padding: 8px;">
    </div>

    <!-- 4. Input Waktu Selesai -->
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Waktu Selesai Penugasan:</label>
        <input type="datetime-local" name="waktu_selesai" required style="width: 100%; padding: 8px;">
    </div>

    <!-- 5. Upload File Surat Tugas / SPT -->
    <div style="margin-bottom: 20px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Upload Surat Tugas / SPT (PDF/JPG/PNG):</label>
        <input type="file" name="surat_spt" accept=".pdf,.jpg,.jpeg,.png" required style="width: 100%; padding: 8px; border: 1px solid #ccc;">
        <small style="color: gray;">Maksimal ukuran file: 2MB.</small>
    </div>

    <!-- Tombol Simpan -->
    <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold;">
        Simpan Penugasan
    </button>
</form>

<a href="/logout">logout</a>
</body>
</html>