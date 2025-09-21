@extends('layouts.public')

@section('title', 'Tracking Pengajuan Surat')

@section('content')
<div class="max-w-4xl mx-auto py-12">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tracking Pengajuan Surat</h1>
        <p class="mt-2 text-gray-600">Masukkan token tracking untuk melihat status pengajuan Anda</p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <form id="trackingForm" class="space-y-4">
            <div>
                <label for="tracking_token" class="block text-sm font-medium text-gray-700 mb-2">
                    Token Tracking <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-3">
                    <input type="text" 
                           id="tracking_token" 
                           name="tracking_token"
                           class="flex-1 border border-gray-300 rounded-md px-4 py-3 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           placeholder="Contoh: TRK-36064A72"
                           pattern="TRK-[A-Z0-9]{8}"
                           title="Format: TRK-XXXXXXXX"
                           value="{{ $token ?? '' }}"
                           required>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Tracking
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Token tracking diberikan saat Anda mengirim pengajuan surat
                </p>
            </div>
        </form>
    </div>

    <!-- Auto-search if token provided -->
    @if($token)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('trackingForm').dispatchEvent(new Event('submit'));
        });
    </script>
    @endif

    <!-- Loading State -->
    <div id="loadingState" class="hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Mencari pengajuan Anda...</p>
        </div>
    </div>

    <!-- Error State -->
    <div id="errorState" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-medium">Pengajuan Tidak Ditemukan</h3>
                    <p class="text-red-700 text-sm mt-1" id="errorMessage">
                        Token tracking tidak valid atau tidak ditemukan. Pastikan Anda memasukkan token dengan benar.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div id="resultsContainer" class="hidden">
        <!-- Dynamic content will be loaded here -->
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
        <h3 class="text-blue-800 font-medium mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            Informasi Tracking
        </h3>
        <div class="text-blue-700 text-sm space-y-2">
            <p><strong>Status Pengajuan:</strong></p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><span class="font-medium">Pending:</span> Pengajuan sedang dalam antrian review</li>
                <li><span class="font-medium">Processing:</span> Pengajuan sedang diproses oleh staff</li>
                <li><span class="font-medium">Approved:</span> Pengajuan disetujui, surat sedang disiapkan</li>
                <li><span class="font-medium">Completed:</span> Surat sudah selesai dan dapat diambil</li>
                <li><span class="font-medium">Rejected:</span> Pengajuan ditolak dengan alasan tertentu</li>
            </ul>
            <p class="mt-3"><strong>Catatan:</strong> Jika ada masalah dengan tracking, hubungi bagian akademik dengan menyebutkan token tracking Anda.</p>
        </div>
    </div>
</div>

<script>
document.getElementById('trackingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = document.getElementById('tracking_token').value.trim();
    
    if (!token) {
        showError('Token tracking tidak boleh kosong');
        return;
    }
    
    // Show loading
    showLoading();
    
    try {
        const response = await fetch('/tracking/api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tracking_token: token })
        });
        
        const data = await response.json();
        
        if (data.success && data.pengajuan) {
            showResults(data.pengajuan);
        } else {
            showError(data.message || 'Pengajuan tidak ditemukan');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showError('Terjadi kesalahan sistem. Silakan coba lagi.');
    }
});

function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('errorState').classList.add('hidden');
    document.getElementById('resultsContainer').classList.add('hidden');
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorState').classList.remove('hidden');
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('resultsContainer').classList.add('hidden');
}

function showResults(pengajuan) {
    const container = document.getElementById('resultsContainer');
    
    // Format additional data
    let additionalDataHtml = '';
    if (pengajuan.additional_data) {
        try {
            const additionalData = JSON.parse(pengajuan.additional_data);
            
            if (additionalData.orang_tua) {
                additionalDataHtml = `
                    <div class="bg-yellow-50 p-4 rounded-lg mt-4">
                        <h4 class="font-medium text-yellow-800 mb-3">
                            <i class="fas fa-users mr-2"></i>
                            Biodata Orang Tua
                        </h4>
                        <div class="grid md:grid-cols-2 gap-3 text-sm">
                            <div><strong>Nama:</strong> ${additionalData.orang_tua.nama || '-'}</div>
                            <div><strong>Pekerjaan:</strong> ${additionalData.orang_tua.pekerjaan || '-'}</div>
                            <div><strong>Pendidikan:</strong> ${additionalData.orang_tua.pendidikan_terakhir || '-'}</div>
                        </div>
                    </div>
                `;
            }
        } catch (e) {
            console.error('Error parsing additional data:', e);
        }
    }
    
    container.innerHTML = `
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header with Status -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold">Pengajuan Ditemukan</h2>
                        <p class="text-blue-100 text-sm">Token: ${pengajuan.tracking_token}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-sm font-medium ${getStatusClass(pengajuan.status)}">
                            ${getStatusText(pengajuan.status)}
                        </span>
                        <p class="text-blue-100 text-xs mt-1">${formatDate(pengajuan.created_at)}</p>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Pengajuan Info -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">
                            <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                            Informasi Surat
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Jenis Surat:</strong> ${pengajuan.jenis_surat?.nama_jenis || 'N/A'}</div>
                            <div><strong>Kode:</strong> ${pengajuan.jenis_surat?.kode_surat || 'N/A'}</div>
                            <div><strong>Tanggal Pengajuan:</strong> ${formatDate(pengajuan.created_at)}</div>
                        </div>
                    </div>
                    
                    <!-- Mahasiswa Info -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-3">
                            <i class="fas fa-user-graduate mr-2 text-green-600"></i>
                            Data Mahasiswa
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>NIM:</strong> ${pengajuan.nim}</div>
                            <div><strong>Nama:</strong> ${pengajuan.nama_mahasiswa}</div>
                            <div><strong>Program Studi:</strong> ${pengajuan.prodi?.nama_prodi || 'N/A'}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Keperluan -->
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-clipboard-list mr-2 text-purple-600"></i>
                        Keperluan
                    </h3>
                    <div class="bg-gray-50 p-3 rounded border text-sm">
                        ${pengajuan.keperluan}
                    </div>
                </div>
                
                <!-- Additional Data -->
                ${additionalDataHtml}
                
                <!-- Timeline/Next Steps -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-800 mb-2">
                        <i class="fas fa-clock mr-2"></i>
                        Status & Langkah Selanjutnya
                    </h4>
                    <p class="text-blue-700 text-sm">
                        ${getStatusDescription(pengajuan.status)}
                    </p>
                </div>
            </div>
        </div>
    `;
    
    container.classList.remove('hidden');
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('errorState').classList.add('hidden');
}

function getStatusClass(status) {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800', 
        approved: 'bg-green-100 text-green-800',
        completed: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function getStatusText(status) {
    const texts = {
        pending: 'Menunggu Review',
        processing: 'Sedang Diproses',
        approved: 'Disetujui',
        completed: 'Selesai',
        rejected: 'Ditolak'
    };
    return texts[status] || 'Unknown';
}

function getStatusDescription(status) {
    const descriptions = {
        pending: 'Pengajuan Anda sedang dalam antrian review. Staff akan memproses dalam 1-2 hari kerja.',
        processing: 'Pengajuan sedang diproses oleh staff akademik. Mohon tunggu konfirmasi selanjutnya.',
        approved: 'Pengajuan Anda telah disetujui. Surat sedang dalam proses pembuatan.',
        completed: 'Surat sudah selesai dibuat dan dapat diambil di bagian akademik.',
        rejected: 'Pengajuan ditolak. Silakan hubungi bagian akademik untuk informasi lebih lanjut.'
    };
    return descriptions[status] || 'Status tidak diketahui.';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>
@endsection