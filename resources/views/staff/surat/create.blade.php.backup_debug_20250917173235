@extends('layouts.app')

@section('title', 'Buat Surat Baru')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Buat Surat Baru</h1>

                @livewire('create-surat-form')

            </div>
        </div>
    </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('scripts')
<script>
function setActionType(type) {
    document.getElementById('action_type').value = type;
    return true;
}

function confirmAndSubmit() {
    // Using SweetAlert2 if available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi Pengiriman',
            html: '<p>Apakah Anda yakin ingin mengirim surat ini ke Kaprodi?</p>' +
                  '<p class="text-sm text-gray-600 mt-2">Surat yang sudah dikirim tidak dapat diedit kembali.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                setActionType('submit');
                document.getElementById('create-surat-form').submit();
            }
        });
    } else {
        // Fallback to native confirm
        const message = 'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi?\n\n' +
                       '⚠️ Perhatian:\n' +
                       'Surat yang sudah dikirim tidak dapat diedit kembali.';
        
        if (confirm(message)) {
            setActionType('submit');
            document.getElementById('create-surat-form').submit();
        }
    }
}

// Optional: Add form validation before submit
document.getElementById('create-surat-form').addEventListener('submit', function(e) {
    const actionType = document.getElementById('action_type').value;
    
    // You can add custom validation here
    console.log('Submitting form with action:', actionType);
});
</script>
@endpush

@push('scripts')
<script>
function setActionType(type) {
    document.getElementById('action_type').value = type;
    console.log('Action type set to:', type);
    return true;
}

function confirmSubmit() {
    // Check if SweetAlert2 is available
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi Pengiriman',
            html: '<div class="text-left">' +
                  '<p class="mb-2">Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?</p>' +
                  '<div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-4">' +
                  '<p class="text-sm text-yellow-700">' +
                  '<strong>Perhatian:</strong> Surat yang sudah dikirim tidak dapat diedit kembali.' +
                  '</p>' +
                  '</div>' +
                  '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                setActionType('submit');
                // Show loading
                Swal.fire({
                    title: 'Mengirim...',
                    text: 'Surat sedang dikirim ke Kaprodi',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                document.getElementById('create-surat-form').submit();
            }
        });
    } else {
        // Fallback to native confirm
        if (confirm('Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\n\nPerhatian: Surat yang sudah dikirim tidak dapat diedit kembali.')) {
            setActionType('submit');
            document.getElementById('create-surat-form').submit();
        }
    }
}

// Add form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-surat-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const actionType = document.getElementById('action_type').value;
            console.log('Submitting with action:', actionType);
            
            // Basic validation
            const perihal = document.querySelector('[name="perihal"]');
            if (perihal && !perihal.value.trim()) {
                e.preventDefault();
                alert('Perihal harus diisi!');
                return false;
            }
        });
    }
});
</script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush