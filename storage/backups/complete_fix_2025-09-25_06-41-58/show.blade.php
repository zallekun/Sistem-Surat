<!-- staff.pengajuan.show -->

@php
    use App\Helpers\StatusHelper;
    $statusColor = StatusHelper::getPengajuanStatusColor($pengajuan->status);
    $statusLabel = StatusHelper::getPengajuanStatusLabel($pengajuan->status);
@endphp

@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Detail Pengajuan Surat</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                
                <!-- Basic Info -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi Pengajuan
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Token Tracking:</strong> 
                                <span class="font-mono bg-blue-100 px-2 py-1 rounded">{{ $pengajuan->tracking_token }}</span>
                            </div>
                            <div><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                            <div><strong>Jenis Surat:</strong> 
                                <span class="font-medium">{{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</span>
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded ml-1">{{ $pengajuan->jenisSurat->kode_surat ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-800 mb-3">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Data Mahasiswa
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                            <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                            <div><strong>Program Studi:</strong> {{ $pengajuan->prodi->nama_prodi ?? 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $pengajuan->email }}</div>
                            <div><strong>Phone:</strong> {{ $pengajuan->phone }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Keperluan -->
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Keperluan Surat
                    </h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700">{{ $pengajuan->keperluan }}</p>
                    </div>
                </div>
                
                <!-- Additional Data berdasarkan Jenis Surat -->
                @if($pengajuan->additional_data)
                    @php
                        $additionalData = $pengajuan->additional_data;
                        $jenisSurat = $pengajuan->jenisSurat;
                    @endphp
                    
                    
                            
                            {{-- SURAT MAHASISWA AKTIF --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'MA')
                                <!-- Data Akademik -->
                                @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']))
                                    <div class="bg-green-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-green-800 mb-3">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Data Akademik
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            @if($additionalData['semester'] ?? false)
                                                <div><strong>Semester:</strong> {{ $additionalData['semester'] }}</div>
                                            @endif
                                            @if($additionalData['tahun_akademik'] ?? false)
                                                <div><strong>Tahun Akademik:</strong> {{ $additionalData['tahun_akademik'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($additionalData && is_array($additionalData))
                        <div class="mb-8">
                            <h3 class="font-semibold text-gray-800 mb-3">
                                <i class="fas fa-list-alt mr-2"></i>
                                Data Tambahan - {{ $jenisSurat->nama_jenis ?? 'Unknown' }}
                            </h3>

                                <!-- Biodata Orang Tua -->
                                @if(isset($additionalData['orang_tua']))
                                    <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-yellow-800 mb-3">
                                            <i class="fas fa-users mr-2"></i>
                                            Biodata Orang Tua
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Nama:</strong> {{ $additionalData['orang_tua']['nama'] ?? '-' }}</div>
                                            <div><strong>Tempat Lahir:</strong> {{ $additionalData['orang_tua']['tempat_lahir'] ?? '-' }}</div>
                                            <div><strong>Tanggal Lahir:</strong> {{ $additionalData['orang_tua']['tanggal_lahir'] ?? '-' }}</div>
                                            <div><strong>Pekerjaan:</strong> {{ $additionalData['orang_tua']['pekerjaan'] ?? '-' }}</div>
                                            <div><strong>NIP:</strong> {{ $additionalData['orang_tua']['nip'] ?? '-' }}</div>
                                            <div><strong>Jabatan:</strong> {{ $additionalData['orang_tua']['jabatan'] ?? '-' }}</div>
                                            <div><strong>Pangkat/Golongan:</strong> {{ $additionalData['orang_tua']['pangkat_golongan'] ?? '-' }}</div>
                                            <div><strong>Pendidikan Terakhir:</strong> {{ $additionalData['orang_tua']['pendidikan_terakhir'] ?? '-' }}</div>
                                        </div>
                                        
                                        @if($additionalData['orang_tua']['alamat_instansi'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Instansi:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['orang_tua']['alamat_instansi'] }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($additionalData['orang_tua']['alamat_rumah'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Rumah:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['orang_tua']['alamat_rumah'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT KERJA PRAKTEK --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'KP')
                                @if(isset($additionalData['kerja_praktek']))
                                    <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-blue-800 mb-3">
                                            <i class="fas fa-briefcase mr-2"></i>
                                            Data Kerja Praktek
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Nama Perusahaan:</strong> {{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? '-' }}</div>
                                            <div><strong>Bidang Kerja:</strong> {{ $additionalData['kerja_praktek']['bidang_kerja'] ?? '-' }}</div>
                                            <div><strong>Periode Mulai:</strong> {{ $additionalData['kerja_praktek']['periode_mulai'] ?? '-' }}</div>
                                            <div><strong>Periode Selesai:</strong> {{ $additionalData['kerja_praktek']['periode_selesai'] ?? '-' }}</div>
                                        </div>
                                        
                                        @if($additionalData['kerja_praktek']['alamat_perusahaan'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Perusahaan:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT TUGAS AKHIR --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'TA')
                                @if(isset($additionalData['tugas_akhir']))
                                    <div class="bg-purple-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-purple-800 mb-3">
                                            <i class="fas fa-book mr-2"></i>
                                            Data Tugas Akhir
                                        </h4>
                                        
                                        @if($additionalData['tugas_akhir']['judul_ta'] ?? false)
                                            <div class="mb-3">
                                                <strong>Judul Tugas Akhir:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['tugas_akhir']['judul_ta'] }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Dosen Pembimbing 1:</strong> {{ $additionalData['tugas_akhir']['dosen_pembimbing1'] ?? '-' }}</div>
                                            <div><strong>Dosen Pembimbing 2:</strong> {{ $additionalData['tugas_akhir']['dosen_pembimbing2'] ?? '-' }}</div>
                                            <div><strong>Lokasi Penelitian:</strong> {{ $additionalData['tugas_akhir']['lokasi_penelitian'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT KETERANGAN --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'SKM')
                                @if(isset($additionalData['keterangan_khusus']))
                                    <div class="bg-orange-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-orange-800 mb-3">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            Keterangan Khusus
                                        </h4>
                                        <div class="p-2 bg-white rounded border">
                                            {{ $additionalData['keterangan_khusus'] }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                @else
                    <div class="mb-8">
                        <div class="bg-gray-100 p-4 rounded-lg text-center">
                            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-600">Tidak ada data tambahan untuk pengajuan ini.</p>
                        </div>
                    </div>
                @endif
                
                <!-- Actions -->
                <!-- Actions -->
<div class="flex justify-between items-center pt-6 border-t border-gray-200">
    <a href="{{ route('staff.pengajuan.index') }}" 
       style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #6b7280; color: white; font-size: 0.875rem; font-weight: 500; border-radius: 6px; text-decoration: none;"
       onmouseover="this.style.backgroundColor='#4b5563'" 
       onmouseout="this.style.backgroundColor='#6b7280'">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>
    
    <div class="flex space-x-3">
        @if($pengajuan->status === 'pending' && auth()->user()->hasRole(['staff_prodi', 'kaprodi']))
            <!-- Tombol Reject -->
            <!-- Tombol Approve dengan inline style yang diperbaiki -->
<!-- Tombol Approve dengan inline style yang diperbaiki -->
<button onclick="showApproveConfirm()" 
        style="display: inline-flex; 
               align-items: center; 
               padding: 8px 16px; 
               background-color: #16a34a !important; 
               color: white !important; 
               font-size: 0.875rem; 
               font-weight: 500; 
               border-radius: 6px; 
               border: none !important; 
               cursor: pointer !important;
               opacity: 1;
               min-width: 100px !important;"
        onmouseover="this.style.backgroundColor='#15803d'" 
        onmouseout="this.style.backgroundColor='#16a34a'">
    <i class="fas fa-check mr-2" style="color: white !important;"></i>
    Setujui
</button>

<!-- Tombol Reject dengan inline style yang diperbaiki -->
<button onclick="showRejectModal()" 
        style="display: inline-flex; 
               align-items: center; 
               padding: 8px 16px; 
               background-color: #dc2626 !important; 
               color: white !important; 
               font-size: 0.875rem; 
               font-weight: 500; 
               border-radius: 6px; 
               border: none !important; 
               cursor: pointer !important;
               opacity: 1;
               min-width: 100px !important;"
        onmouseover="this.style.backgroundColor='#b91c1c'" 
        onmouseout="this.style.backgroundColor='#dc2626'">
    <i class="fas fa-times mr-2" style="color: white !important;"></i>
    Tolak
</button>
            
        @elseif(in_array($pengajuan->status, ['processed', 'approved_prodi']))
            <span style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #dbeafe; color: #1e40af; font-size: 0.875rem; font-weight: 500; border-radius: 6px;">
                <i class="fas fa-check-circle mr-2"></i>
                Sudah Disetujui Prodi
            </span>
            
        @elseif($pengajuan->status === 'approved_prodi_direct_fakultas')
            <span style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #e0e7ff; color: #3730a3; font-size: 0.875rem; font-weight: 500; border-radius: 6px;">
                <i class="fas fa-paper-plane mr-2"></i>
                Diteruskan ke Fakultas
            </span>
            
        @elseif(in_array($pengajuan->status, ['rejected', 'rejected_prodi']))
            <span style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #fee2e2; color: #991b1b; font-size: 0.875rem; font-weight: 500; border-radius: 6px;">
                <i class="fas fa-times-circle mr-2"></i>
                Ditolak: {{ $pengajuan->rejection_reason_prodi ?? $pengajuan->rejection_reason ?? 'Tidak memenuhi syarat' }}
            </span>
            
        @elseif($pengajuan->status === 'approved_fakultas')
            <span style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #dcfce7; color: #166534; font-size: 0.875rem; font-weight: 500; border-radius: 6px;">
                <i class="fas fa-check-double mr-2"></i>
                Disetujui Fakultas
            </span>
            
        @elseif($pengajuan->status === 'surat_generated')
            <span style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #f3e8ff; color: #6b21a8; font-size: 0.875rem; font-weight: 500; border-radius: 6px;">
                <i class="fas fa-file-alt mr-2"></i>
                Surat Sudah Dibuat
            </span>
        @endif
    </div>
</div>
            </div>
        </div>
    </div>

<!-- Modal Konfirmasi Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[1045]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto" 
         onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white rounded-t-xl flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-semibold text-gray-900">Konfirmasi Persetujuan</h3>
                    <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <button onclick="closeApproveModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <div class="mb-6">
                <p class="text-gray-700 mb-4 leading-relaxed">
                    Apakah Anda yakin ingin menyetujui pengajuan surat ini? 
                    <span class="font-medium text-gray-900">Pengajuan akan diteruskan ke fakultas</span> untuk proses selanjutnya.
                </p>
                
                <!-- Alert Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-blue-800">
                                Setelah disetujui, pengajuan akan masuk ke sistem fakultas untuk review lebih lanjut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detail Pengajuan -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Detail Pengajuan:</h4>
                
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-600 min-w-0 flex-1">Mahasiswa:</span>
                        <span class="text-sm text-gray-900 text-right ml-4">{{ $pengajuan->nama_mahasiswa }} <br><span class="text-xs text-gray-500">({{ $pengajuan->nim }})</span></span>
                    </div>
                    
                    <div class="flex justify-between items-start">
                        <span class="text-sm font-medium text-gray-600 min-w-0 flex-1">Jenis Surat:</span>
                        <span class="text-sm text-gray-900 text-right ml-4">{{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="pt-2 border-t border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-medium text-gray-600">Keperluan:</span>
                        </div>
                        <div class="bg-white rounded border px-3 py-2">
                            <p class="text-sm text-gray-900 leading-relaxed">{{ Str::limit($pengajuan->keperluan, 150) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 rounded-b-xl flex flex-col sm:flex-row justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeApproveModal()" 
                    class="order-2 sm:order-1 px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Batal
            </button>
            <button onclick="processPengajuan('approve')" 
                    class="order-1 sm:order-2 px-6 py-3 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

<!-- Modal Reject dengan Alasan -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[1045]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto" 
         onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white rounded-t-xl flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-semibold text-gray-900">Tolak Pengajuan</h3>
                    <p class="text-sm text-gray-500 mt-1">Berikan alasan penolakan yang jelas</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-2 hover:bg-gray-100 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <!-- Alert Warning -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">
                            <span class="font-medium">Perhatian:</span> Alasan penolakan akan dikirimkan ke mahasiswa dan tidak dapat diubah.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Detail Pengajuan -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Detail Pengajuan:</h4>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Mahasiswa:</span>
                        <span class="text-sm text-gray-900">{{ $pengajuan->nama_mahasiswa }} ({{ $pengajuan->nim }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Jenis Surat:</span>
                        <span class="text-sm text-gray-900">{{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Form Alasan -->
            <div class="space-y-4">
                <div>
                    <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejectionReason" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 resize-none text-sm"
                              rows="4"
                              placeholder="Contoh: Dokumen pendukung tidak lengkap, data tidak sesuai dengan persyaratan, format penulisan tidak benar, dll..."
                              required></textarea>
                    <div class="mt-2 flex items-start">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-xs text-gray-500">Alasan ini akan dikirimkan kepada mahasiswa melalui email dan dapat dilihat di sistem tracking.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 rounded-b-xl flex flex-col sm:flex-row justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeRejectModal()" 
                    class="order-2 sm:order-1 px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Batal
            </button>
            <button onclick="processPengajuan('reject')" 
                    class="order-1 sm:order-2 px-6 py-3 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 18M5.636 5.636L6 6"></path>
                </svg>
                Tolak Pengajuan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
console.log('Script loaded successfully');

// Prevent body scroll when modal is open
function disableBodyScroll() {
    document.body.style.overflow = 'hidden';
    document.body.style.paddingRight = getScrollbarWidth() + 'px';
}

function enableBodyScroll() {
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

function getScrollbarWidth() {
    const outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.width = '100px';
    outer.style.msOverflowStyle = 'scrollbar';
    document.body.appendChild(outer);
    
    const widthNoScroll = outer.offsetWidth;
    outer.style.overflow = 'scroll';
    
    const inner = document.createElement('div');
    inner.style.width = '100%';
    outer.appendChild(inner);
    
    const widthWithScroll = inner.offsetWidth;
    outer.parentNode.removeChild(outer);
    
    return widthNoScroll - widthWithScroll;
}

// Auto-fix button styles on page load
(function() {
    'use strict';
    
    function fixButtonStyles() {
        console.log('Fixing button styles...');
        
        const approveBtn = document.querySelector('button[onclick*="showApprove"]');
        if (approveBtn) {
            approveBtn.style.cssText = `
                background-color: #16a34a !important;
                color: white !important;
                opacity: 1;
                display: inline-flex;
                align-items: center;
                padding: 8px 16px;
                border-radius: 6px;
                border: none !important;
                cursor: pointer !important;
                min-width: 100px !important;
                font-size: 14px !important;
                font-weight: 500;
            `;
        }
        
        const rejectBtn = document.querySelector('button[onclick*="showReject"]');
        if (rejectBtn) {
            rejectBtn.style.cssText = `
                background-color: #dc2626 !important;
                color: white !important;
                opacity: 1;
                display: inline-flex;
                align-items: center;
                padding: 8px 16px;
                border-radius: 6px;
                border: none !important;
                cursor: pointer !important;
                min-width: 100px !important;
                font-size: 14px !important;
                font-weight: 500;
            `;
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixButtonStyles);
    } else {
        fixButtonStyles();
    }
    
    setTimeout(fixButtonStyles, 100);
})();

// Modal functions with body scroll prevention
function showApproveConfirm() {
    console.log('Show approve modal');
    disableBodyScroll();
    const modal = document.getElementById('approveModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Focus management
    setTimeout(() => {
        const firstButton = modal.querySelector('button[onclick="processPengajuan(\'approve\')"]');
        if (firstButton) firstButton.focus();
    }, 100);
}

function closeApproveModal() {
    enableBodyScroll();
    console.log('Close approve modal');
    enableBodyScroll();
    const modal = document.getElementById('approveModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showRejectModal() {
    console.log('Show reject modal');
    disableBodyScroll();
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Focus on textarea
    setTimeout(() => {
        const textarea = modal.querySelector('#rejectionReason');
        if (textarea) textarea.focus();
    }, 100);
}

function closeRejectModal() {
    enableBodyScroll();
    console.log('Close reject modal');
    enableBodyScroll();
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('rejectionReason').value = '';
}

// Close modal on backdrop click
document.getElementById('approveModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeApproveModal();
});

document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('approveModal').classList.contains('hidden')) {
            closeApproveModal();
        }
        if (!document.getElementById('rejectModal').classList.contains('hidden')) {
            closeRejectModal();
        }
    }
});

// Process function (keeping the existing one)
function processPengajuan(action) {
    console.log('Processing:', action);
    
    let data = { action: action };
    
    if (action === 'reject') {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Alasan penolakan harus diisi!');
            return;
        }
        data.rejection_reason = reason;
    }
    
    const approveBtn = document.querySelector('button[onclick="processPengajuan(\'approve\')"]');
    const rejectBtn = document.querySelector('button[onclick="processPengajuan(\'reject\')"]');
    const button = action === 'approve' ? approveBtn : rejectBtn;
    
    if (button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
        
        fetch('/staff/pengajuan/{{ $pengajuan->id }}/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Result:', result);
            
            if (result.success) {
                alert(result.message);
                window.location.href = '{{ route("staff.pengajuan.index") }}';
            } else {
                alert('Error: ' + (result.message || 'Unknown error'));
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    // Close modals and enable scroll
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}
</script>
@endpush

<!-- Enhanced Styles -->
@push('styles')
<style>
/* MODAL STYLES - CLEANED */
#approveModal, 
#rejectModal {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Modal animations */
#approveModal.flex > div,
#rejectModal.flex > div {
    animation: modalSlideIn 0.3s ease-out forwards;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Button states */
button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Loading spinner */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Scrollbar styling */
.modal-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.modal-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.modal-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

/* Mobile responsive */
@media (max-width: 640px) {
    #approveModal > div,
    #rejectModal > div {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-footer button {
        width: 100%;
    }
}

/* Action buttons */
.action-button {
    opacity: 1;
    visibility: visible;
    position: relative;
}
</style>
@endpush