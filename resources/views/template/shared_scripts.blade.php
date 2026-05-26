<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /**
     * Helper untuk konfirmasi hapus data
     */
    function confirmDelete(event, form, message = "Apakah Anda yakin ingin menghapus data ini?") {
        event.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#253D6B',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            borderRadius: '20px'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    /**
     * Helper untuk konfirmasi aksi umum
     */
    function confirmAction(title, message, icon = 'question') {
        return Swal.fire({
            title: title,
            text: message,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#253D6B',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        });
    }

    /**
     * Helper untuk alert informasi/error
     */
    function showAlert(title, message, icon = 'info') {
        return Swal.fire({
            title: title,
            text: message,
            icon: icon,
            confirmButtonColor: '#253D6B'
        });
    }

    // Auto-display success/error messages if session exists
    document.addEventListener('DOMContentLoaded', function() {
        if (window.suppressSessionAlerts) return;

        @if(session('success'))
            showAlert('Berhasil', "{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showAlert('Gagal', "{{ session('error') }}", 'error');
        @endif
        @if($errors->any())
            showAlert('Peringatan', "{!! implode('<br>', $errors->all()) !!}", 'warning');
        @endif
    });
</script>
