{{-- resources/views/surat/fsi/preview-editable.blade.php --}}
@extends('layouts.app')

@section('title', 'Preview & Edit Surat FSI')

@push('styles')
<style>
/* A4 Container */
.a4-container {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    padding: 15mm 20mm;
    font-family: 'Times New Roman', serif;
    font-size: 11pt;
    line-height: 1.4;
}

/* Editable fields - enhanced for interactivity */
.editable-field {
    background: #fef3c7;
    padding: 2px 6px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid transparent;
    display: inline-block;
    min-width: 50px;
    position: relative;
    transition: all 0.2s;
}

.editable-field:hover {
    background: #fde047;
    border-color: #f59e0b;
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.editable-field.active {
    background: #fbbf24;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3);
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Highlight corresponding input */
.form-control.highlight {
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.3) !important;
    background: #fef3c7 !important;
}

/* Status badges - simplified */
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-signing { background: #fed7aa; color: #9a3412; }
.status-completed { background: #dbeafe; color: #1e40af; }
.status-rejected { background: #fecaca; color: #991b1b; }

/* Cards - simplified */
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.card-header {
    background: #f8fafc;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 12px 12px 0 0;
}

.card-body {
    padding: 1rem;
}

/* Buttons - simplified */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary { background: #3b82f6; color: white; }
.btn-success { background: #10b981; color: white; }
.btn-warning { background: #f59e0b; color: white; }
.btn-danger { background: #ef4444; color: white; }
.btn-outline { background: white; border: 1px solid #d1d5db; color: #6b7280; }

.btn:hover:not(:disabled) {
    opacity: 0.9;
    transform: translateY(-1px);
}

/* Forms - simplified */
.form-control {
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: 0.5rem 0.75rem;
    width: 100%;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6;
}

/* Tooltip for editable fields */
.tooltip-edit {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s;
}

.editable-field:hover .tooltip-edit {
    opacity: 1;
}

/* Loading spinner */
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Print styles */
@media print {
    .no-print { display: none !important; }
    .a4-container { 
        box-shadow: none; 
        margin: 0;
        width: 100%;
        min-height: auto;
    }
    .editable-field {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .a4-container {
        width: 100%;
        min-height: auto;
        padding: 10mm 15mm;
        margin: 0;
    }
}
</style>
@endpush

@php
    // Parse additional data with proper structure
    $parsedAdditionalData = null;
    if (isset($pengajuan->additional_data) && !empty($pengajuan->additional_data)) {
        if (is_string($pengajuan->additional_data)) {
            try {
                $parsedAdditionalData = json_decode($pengajuan->additional_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $parsedAdditionalData = null;
                }
            } catch (\Exception $e) {
                $parsedAdditionalData = null;
            }
        } elseif (is_array($pengajuan->additional_data)) {
            $parsedAdditionalData = $pengajuan->additional_data;
        } elseif (is_object($pengajuan->additional_data)) {
            $parsedAdditionalData = (array) $pengajuan->additional_data;
        }
    }
    
    // Override $additionalData with parsed data
    if ($parsedAdditionalData) {
        $additionalData = $parsedAdditionalData;
    }
@endphp

@section('content')
<div class="bg-gray-50 min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- LEFT PANEL: Edit Controls -->
            <div class="lg:col-span-1 space-y-4">
                <div class="no-print">
                    <!-- Header Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="font-bold text-gray-900">
                                    <i class="fas fa-file-edit text-blue-600 mr-2"></i> 
                                    Edit Surat {{ $pengajuan->jenisSurat->nama_jenis }}
                                </h3>
                                <span class="status-badge status-{{ $pengajuan->status == 'completed' ? 'completed' : ($pengajuan->status == 'sedang_ditandatangani' ? 'signing' : 'approved') }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                                <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                                <div><strong>Token:</strong> {{ $pengajuan->tracking_token }}</div>
                            </div>
                            
                            <!-- Debug Additional Data -->
                            @if(config('app.debug'))
                            <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
                                <strong>Debug Additional Data:</strong>
                                <pre style="max-height: 100px; overflow-y: auto; font-size: 10px;">{{ json_encode($additionalData, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @endif
                            
                            <div class="mt-3">
                                <a href="{{ route('fakultas.surat.index') }}" class="btn btn-outline w-full">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Form -->
                    @if($canEdit)
                    <div class="card">
                        <div class="card-header">
                            <h4 class="font-bold text-gray-900">
                                <i class="fas fa-edit text-yellow-500 mr-2"></i>
                                Data Surat
                            </h4>
                        </div>
                        <div class="card-body space-y-3" style="max-height: 600px; overflow-y: auto;">
                            <!-- Nomor Surat & Tanggal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
                                <input type="text" id="edit_nomor_surat" class="form-control" data-preview="preview_nomor_surat,preview_nomor_surat_kp"
                                       value="{{ $nomorSurat }}" placeholder="P/001/FSI-UNJANI/IX/2024">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
                                <input type="text" id="edit_tanggal_surat" class="form-control" data-preview="preview_tanggal,preview_tempat_tanggal"
                                       value="{{ $tanggalSurat }}" placeholder="25 September 2024">
                            </div>
                            
                            <!-- Data Penandatangan -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-signature text-blue-500 mr-2"></i>
                                Data Penandatangan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" id="edit_ttd_nama" class="form-control" data-preview="preview_ttd_nama,preview_ttd_nama_bottom,preview_ttd_nama_bottom_kp"
                                       value="{{ $penandatangan['nama'] }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat/Golongan</label>
                                <input type="text" id="edit_ttd_pangkat" class="form-control" data-preview="preview_ttd_pangkat"
                                       value="{{ $penandatangan['pangkat'] }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" id="edit_ttd_jabatan" class="form-control" data-preview="preview_ttd_jabatan"
                                       value="{{ $penandatangan['jabatan'] }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NID</label>
                                <input type="text" id="edit_ttd_nid" class="form-control" data-preview="preview_ttd_nid,preview_ttd_nid_kp"
                                       value="{{ $penandatangan['nid'] }}">
                            </div>

                            <!-- Data Mahasiswa -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-user text-green-500 mr-2"></i>
                                Data Mahasiswa
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mahasiswa</label>
                                <input type="text" id="edit_nama_mahasiswa" class="form-control" data-preview="preview_nama_mahasiswa"
                                       value="{{ $pengajuan->nama_mahasiswa }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIM</label>
                                <input type="text" id="edit_nim" class="form-control" data-preview="preview_nim"
                                       value="{{ $pengajuan->nim }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                                <input type="text" id="edit_prodi" class="form-control" data-preview="preview_prodi"
                                       value="{{ $pengajuan->prodi->nama_prodi ?? 'Tidak ada data' }}">
                            </div>

                            <!-- Data Additional -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                                Data Tambahan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="text" id="edit_semester" class="form-control" data-preview="preview_semester"
                                       value="{{ $additionalData['semester'] ?? 'Ganjil' }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                                <input type="text" id="edit_tahun_akademik" class="form-control" data-preview="preview_tahun_akademik"
                                       value="{{ $additionalData['tahun_akademik'] ?? '2024/2025' }}">
                            </div>

                            <!-- Data Orang Tua Section (untuk MA) -->
                            @if($pengajuan->jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']) && is_array($additionalData['orang_tua']))
                            @php
                                $orangTua = $additionalData['orang_tua'];
                            @endphp
                            
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-users text-orange-500 mr-2"></i>
                                Data Orang Tua
                            </h5>
                            
                            @foreach([
                                'nama' => 'Nama Orang Tua',
                                'nama_ayah' => 'Nama Ayah',
                                'nama_ibu' => 'Nama Ibu',
                                'tempat_lahir' => 'Tempat Lahir',
                                'tanggal_lahir' => 'Tanggal Lahir',
                                'pekerjaan' => 'Pekerjaan',
                                'nip' => 'NIP',
                                'pangkat_golongan' => 'Pangkat/Golongan',
                                'instansi' => 'Instansi',
                                'alamat_instansi' => 'Alamat Instansi',
                                'alamat_rumah' => 'Alamat Rumah'
                            ] as $key => $label)
                                @if(isset($orangTua[$key]) && !empty($orangTua[$key]))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                                    @if(in_array($key, ['alamat_instansi', 'alamat_rumah']))
                                        <textarea id="edit_{{ $key }}" class="form-control" rows="2" data-preview="preview_{{ $key }}">{{ $orangTua[$key] }}</textarea>
                                    @else
                                        <input type="text" id="edit_{{ $key }}" class="form-control" data-preview="preview_{{ $key }}"
                                               value="{{ $orangTua[$key] }}">
                                    @endif
                                </div>
                                @endif
                            @endforeach
                            @endif
                            
                            <!-- Data KP Section (untuk KP) -->
                            @if($pengajuan->jenisSurat->kode_surat === 'KP')
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                                Data Kerja Praktek
                            </h5>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sifat Surat</label>
                                <input type="text" id="edit_sifat" class="form-control" data-preview="preview_sifat_kp"
                                       value="Biasa">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran</label>
                                <input type="text" id="edit_lampiran" class="form-control" data-preview="preview_lampiran_kp"
                                       value="-">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                                <input type="text" id="edit_kepada_nama" class="form-control" data-preview="preview_kepada_nama"
                                       value="{{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? '' }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Perusahaan (Baris 1)</label>
                                <textarea id="edit_kepada_alamat_1" class="form-control" rows="2" data-preview="preview_kepada_alamat_1">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Perusahaan (Baris 2)</label>
                                <input type="text" id="edit_kepada_alamat_2" class="form-control" data-preview="preview_kepada_alamat_2"
                                       value="">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode Mulai</label>
                                    <input type="date" id="edit_periode_mulai_date" class="form-control" data-preview="preview_periode_mulai"
                                           value="{{ $additionalData['kerja_praktek']['periode_mulai'] ?? '' }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode Selesai</label>
                                    <input type="date" id="edit_periode_selesai_date" class="form-control" data-preview="preview_periode_selesai"
                                           value="{{ $additionalData['kerja_praktek']['periode_selesai'] ?? '' }}">
                                </div>
                            </div>

                            <!-- Dynamic Mahasiswa Fields untuk KP -->
                            <h6 class="font-bold text-gray-700 mt-3 mb-2">Data Mahasiswa</h6>
                            @if(isset($additionalData['kerja_praktek']['mahasiswa_kp']))
                                @foreach($additionalData['kerja_praktek']['mahasiswa_kp'] as $index => $mhs)
                                <div class="border border-gray-200 rounded p-2 mb-2">
                                    <small class="text-gray-600">Mahasiswa {{ $index + 1 }}</small>
                                    <input type="text" id="edit_mhs_{{ $index }}_nama" class="form-control mt-1" 
                                           placeholder="Nama" value="{{ $mhs['nama'] ?? '' }}" data-preview="preview_mhs_{{ $index }}_nama">
                                    <input type="text" id="edit_mhs_{{ $index }}_nim" class="form-control mt-1" 
                                           placeholder="NIM" value="{{ $mhs['nim'] ?? '' }}" data-preview="preview_mhs_{{ $index }}_nim">
                                    <input type="text" id="edit_mhs_{{ $index }}_prodi" class="form-control mt-1" 
                                           placeholder="Program Studi" value="{{ $mhs['prodi'] ?? '' }}" data-preview="preview_mhs_{{ $index }}_prodi">
                                </div>
                                @endforeach
                            @endif

                            <!-- Add 5 mahasiswa fields for KP even if not filled -->
                            @for($i = (isset($additionalData['kerja_praktek']['mahasiswa_kp']) ? count($additionalData['kerja_praktek']['mahasiswa_kp']) : 0); $i < 5; $i++)
                            <div class="border border-gray-200 rounded p-2 mb-2">
                                <small class="text-gray-600">Mahasiswa {{ $i + 1 }}</small>
                                <input type="text" id="edit_mhs_{{ $i }}_nama" class="form-control mt-1" 
                                       placeholder="Nama" value="" data-preview="preview_mhs_{{ $i }}_nama">
                                <input type="text" id="edit_mhs_{{ $i }}_nim" class="form-control mt-1" 
                                       placeholder="NIM" value="" data-preview="preview_mhs_{{ $i }}_nim">
                                <input type="text" id="edit_mhs_{{ $i }}_prodi" class="form-control mt-1" 
                                       placeholder="Program Studi" value="" data-preview="preview_mhs_{{ $i }}_prodi">
                            </div>
                            @endfor

                            <!-- Additional KP Text Fields -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salam Pembuka</label>
                                <input type="text" id="edit_salam_pembuka" class="form-control" data-preview="preview_salam_pembuka"
                                       value="Dengan hormat,">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Paragraph 1 (Dasar)</label>
                                <textarea id="edit_paragraph_1" class="form-control" rows="2" data-preview="preview_paragraph_1">Dasar : Nota Dinas Ketua Program Studi {{ $pengajuan->prodi->nama_prodi ?? 'Kimia' }} Nomor: ND/373KI-FSI/XI/2024 tanggal {{ date('d F Y') }} perihal Permohonan Surat Pengantar Kerja Praktik (KP).</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Paragraph 2 (Isi)</label>
                                <textarea id="edit_paragraph_2" class="form-control" rows="3" data-preview="preview_paragraph_2">Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan Izin untuk melaksanakan Kerja Praktik pada tanggal {{ $additionalData['kerja_praktek']['periode_mulai'] ?? '14 Juli' }} s.d {{ $additionalData['kerja_praktek']['periode_selesai'] ?? '16 Agustus 2025' }} di Instansi/Perusahaan yang Bapak/Ibu Pimpin kepada mahasiswa sebagai berikut :</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Paragraph 3 (Penutup)</label>
                                <textarea id="edit_paragraph_3" class="form-control" rows="2" data-preview="preview_paragraph_3">Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Penandatangan</label>
                                <input type="text" id="edit_jabatan_penandatangan" class="form-control" data-preview="preview_jabatan_penandatangan"
                                       value="An. Dekan">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Wakil</label>
                                <input type="text" id="edit_jabatan_wakil" class="form-control" data-preview="preview_jabatan_wakil"
                                       value="Wakil Dekan I - FSI">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tembusan 1</label>
                                <input type="text" id="edit_tembusan_1" class="form-control" data-preview="preview_tembusan_1"
                                       value="Dekan FSI (sebagai laporan)">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tembusan 2</label>
                                <input type="text" id="edit_tembusan_2" class="form-control" data-preview="preview_tembusan_2"
                                       value="Ketua Program Studi {{ $pengajuan->prodi->nama_prodi ?? 'Kimia' }} FSI UNJANI">
                            </div>
                            @endif
                            
                            <!-- Other Additional fields -->
                            @if(isset($additionalData['keperluan']) && !empty($additionalData['keperluan']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                                <textarea id="edit_keperluan" class="form-control" rows="2">{{ $additionalData['keperluan'] }}</textarea>
                            </div>
                            @endif
                            
                            @if(isset($additionalData['alamat']) && !empty($additionalData['alamat']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <textarea id="edit_alamat" class="form-control" rows="2">{{ $additionalData['alamat'] }}</textarea>
                            </div>
                            @endif
                            
                            <button onclick="saveAllChanges()" class="btn btn-success w-full mt-4" id="saveBtn">
                                <span class="btn-text">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </span>
                                <span class="loading-text hidden">
                                    <div class="loading-spinner mr-2"></div>Menyimpan...
                                </span>
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="font-bold text-gray-900">
                                <i class="fas fa-cogs text-purple-500 mr-2"></i>
                                Aksi
                            </h4>
                        </div>
                        <div class="card-body space-y-2">
                            @if($canPrint && $pengajuan->status != 'sedang_ditandatangani')
                            <button onclick="printForSignature()" class="btn btn-warning w-full">
                                <i class="fas fa-print mr-2"></i>Cetak untuk TTD Fisik
                            </button>
                            @endif
                            
                            @if($pengajuan->status == 'sedang_ditandatangani')
                            <div class="alert alert-info">
                                <strong>Status:</strong> Sedang proses TTD fisik
                                @if($pengajuan->printed_at)
                                    <br><small>Dicetak: {{ $pengajuan->printed_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                            
                            <button onclick="printForSignature()" class="btn btn-outline w-full">
                                <i class="fas fa-print mr-2"></i>Cetak Ulang
                            </button>
                            @endif
                            
                            @if($canEdit)
                            <button onclick="rejectSurat()" class="btn btn-danger w-full">
                                <i class="fas fa-times mr-2"></i>Tolak Surat
                            </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Upload Signed Document -->
                    @if($canUploadSigned)
                    <div class="card border-2 border-green-300">
                        <div class="card-header bg-green-50">
                            <h4 class="font-bold text-green-800">
                                <i class="fas fa-upload mr-2"></i>Upload Surat Ter-TTD
                            </h4>
                        </div>
                        <div class="card-body space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Link Surat Final <span class="text-red-500">*</span>
                                </label>
                                <input type="url" id="signed_url" class="form-control" 
                                       placeholder="https://drive.google.com/file/d/...">
                                <small class="text-gray-500 text-xs">
                                    Upload surat yang sudah ditandatangani ke cloud storage dan masukkan link-nya di sini
                                </small>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                <textarea id="signed_notes" class="form-control" rows="2" 
                                          placeholder="Catatan tambahan..."></textarea>
                            </div>
                            
                            <button onclick="uploadSignedDocument()" class="btn btn-success w-full" id="uploadBtn">
                                <span class="btn-text">
                                    <i class="fas fa-check-circle mr-2"></i>Selesaikan Surat
                                </span>
                                <span class="loading-text hidden">
                                    <div class="loading-spinner mr-2"></div>Menyelesaikan...
                                </span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT PANEL: A4 Preview -->
            <div class="lg:col-span-3">
                <div class="card">
                    <div class="card-header no-print">
                        <h3 class="font-bold text-gray-900">
                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                            Preview Surat {{ $pengajuan->jenisSurat->nama_jenis }}
                        </h3>
                        <small class="text-gray-500">Klik field kuning untuk edit langsung</small>
                    </div>
                    
                    <div class="p-2 bg-gray-50">
                        <div class="a4-container">
                            <!-- KOP SURAT -->
                            <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px;">
                                <tr>
                                    <td style="width: 15%; text-align: center; vertical-align: middle;">
                                        <img src="{{ asset('images/logo-ykep.png') }}" style="width: 50px; height: 50px;" alt="Logo YKEP" onerror="this.style.display='none'">
                                    </td>
                                    <td style="width: 70%; text-align: center; font-weight: bold; line-height: 1.2; vertical-align: middle;">
                                        <div style="font-size: 12pt;">YAYASAN KARTIKA EKA PAKSI</div>
                                        <div style="font-size: 12pt;">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                                        <div style="font-size: 12pt;">FAKULTAS SAINS DAN INFORMATIKA</div>
                                        <div style="font-size: 12pt; font-weight: bold;">(FSI)</div>
                                        <div style="font-size: 9pt; font-weight: normal;">
                                            Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6650646
                                        </div>
                                    </td>
                                    <td style="width: 15%; text-align: center; vertical-align: middle;">
                                        <img src="{{ asset('images/logo-unjani.png') }}" style="width: 55px; height: 55px;" alt="Logo UNJANI" onerror="this.style.display='none'">
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- DYNAMIC TITLE based on Jenis Surat -->
                            <div style="text-align: center; margin: 20px 0;">
                                @switch($pengajuan->jenisSurat->kode_surat)
                                    @case('MA')
                                        <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
                                            SURAT KETERANGAN MAHASISWA AKTIF
                                        </h3>
                                        @break
                                    @case('KP')
                                        <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
                                            SURAT PERMOHONAN IZIN KERJA PRAKTIK
                                        </h3>
                                        @break
                                    @case('TA')
                                        <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
                                            SURAT PERMOHONAN IZIN PENELITIAN TUGAS AKHIR
                                        </h3>
                                        @break
                                    @default
                                        <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
                                            SURAT KETERANGAN
                                        </h3>
                                @endswitch
                                
                                <div style="margin-top: 8px; font-size: 12pt; font-weight: bold;">
                                    NOMOR: <span id="preview_nomor_surat" class="editable-field" data-input="edit_nomor_surat">{{ $nomorSurat }}<span class="tooltip-edit">Klik untuk edit</span></span>
                                </div>
                            </div>
                            
                            <!-- DYNAMIC CONTENT based on Jenis Surat -->
                            <div id="dynamic-content">
                                @switch($pengajuan->jenisSurat->kode_surat)
                                    @case('MA')
                                        @include('surat.templates.ma-content')
                                        @break
                                    @case('KP')
                                        @include('surat.templates.kp-preview', [
                                            'mahasiswa' => $additionalData['kerja_praktek']['mahasiswa_kp'] ?? []
                                        ])
                                        @break
                                    @case('TA')
                                        <div style="text-align: center; color: #666; padding: 40px;">
                                            Template untuk Tugas Akhir belum tersedia
                                        </div>
                                        @break
                                    @default
                                        <div style="text-align: center; color: #666; padding: 40px;">
                                            Template untuk {{ $pengajuan->jenisSurat->nama_jenis }} belum tersedia
                                        </div>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Interactive preview - click to focus input
    document.querySelectorAll('.editable-field').forEach(field => {
        field.addEventListener('click', function() {
            const inputId = this.getAttribute('data-input');
            if (!inputId) return;
            
            const inputEl = document.getElementById(inputId);
            if (!inputEl) return;
            
            // Clear all active states
            document.querySelectorAll('.editable-field').forEach(f => f.classList.remove('active'));
            document.querySelectorAll('.form-control').forEach(f => f.classList.remove('highlight'));
            
            // Add active state to clicked field
            this.classList.add('active');
            inputEl.classList.add('highlight');
            
            // Scroll to and focus input
            inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                inputEl.focus();
                inputEl.select();
            }, 300);
        });
    });
    
    // Real-time preview updates with data-preview attribute
    document.querySelectorAll('.form-control[data-preview]').forEach(input => {
        input.addEventListener('input', function() {
            const previewIds = this.getAttribute('data-preview').split(',');
            
            previewIds.forEach(previewId => {
                const previewEl = document.getElementById(previewId);
                if (previewEl) {
                    // Special handling for nama_mahasiswa to uppercase
                    if (previewId === 'preview_nama_mahasiswa') {
                        previewEl.childNodes[0].textContent = this.value.toUpperCase();
                    } else {
                        previewEl.childNodes[0].textContent = this.value || '-';
                    }
                }
            });
        });
        
        // Clear highlight when input loses focus
        input.addEventListener('blur', function() {
            setTimeout(() => {
                this.classList.remove('highlight');
                document.querySelectorAll('.editable-field').forEach(f => f.classList.remove('active'));
            }, 200);
        });
    });
    
    // Initialize all data on load with proper parsing
    @php
        $allAdditionalData = json_encode($additionalData ?? []);
    @endphp
    
    const additionalData = {!! $allAdditionalData !!};
    console.log('Additional Data Loaded:', additionalData);
    
    // Set values for orang_tua fields dynamically (for MA)
    if (additionalData.orang_tua && typeof additionalData.orang_tua === 'object') {
        const orangTuaFields = [
            'nama', 'nama_ayah', 'nama_ibu', 'tempat_lahir', 'tanggal_lahir',
            'pekerjaan', 'nip', 'pangkat_golongan', 'instansi', 
            'alamat_instansi', 'alamat_rumah'
        ];
        
        orangTuaFields.forEach(field => {
            if (additionalData.orang_tua[field]) {
                const inputEl = document.getElementById(`edit_${field}`);
                if (inputEl) {
                    inputEl.value = additionalData.orang_tua[field];
                }
            }
        });
    }
    
    // Set values for KP fields dynamically
    if (additionalData.kerja_praktek && typeof additionalData.kerja_praktek === 'object') {
        const kpData = additionalData.kerja_praktek;
        
        // Set basic KP fields
        if (kpData.nama_perusahaan) {
            const namaPerusahaanEl = document.getElementById('edit_kepada_nama');
            if (namaPerusahaanEl) namaPerusahaanEl.value = kpData.nama_perusahaan;
        }
        
        if (kpData.alamat_perusahaan) {
            const alamatEl = document.getElementById('edit_kepada_alamat_1');
            if (alamatEl) alamatEl.value = kpData.alamat_perusahaan;
        }
        
        if (kpData.periode_mulai) {
            const mulaiEl = document.getElementById('edit_periode_mulai_date');
            if (mulaiEl) mulaiEl.value = kpData.periode_mulai;
        }
        
        if (kpData.periode_selesai) {
            const selesaiEl = document.getElementById('edit_periode_selesai_date');
            if (selesaiEl) selesaiEl.value = kpData.periode_selesai;
        }
        
        // Set mahasiswa data
        if (kpData.mahasiswa_kp && Array.isArray(kpData.mahasiswa_kp)) {
            kpData.mahasiswa_kp.forEach((mhs, index) => {
                const namaEl = document.getElementById(`edit_mhs_${index}_nama`);
                const nimEl = document.getElementById(`edit_mhs_${index}_nim`);
                const prodiEl = document.getElementById(`edit_mhs_${index}_prodi`);
                
                if (namaEl) namaEl.value = mhs.nama || '';
                if (nimEl) nimEl.value = mhs.nim || '';
                if (prodiEl) prodiEl.value = mhs.prodi || '';
            });
        }
    }
    
    // Set other additional fields
    if (additionalData.keperluan) {
        const keperluanInput = document.getElementById('edit_keperluan');
        if (keperluanInput) keperluanInput.value = additionalData.keperluan;
    }
    
    if (additionalData.alamat) {
        const alamatInput = document.getElementById('edit_alamat');
        if (alamatInput) alamatInput.value = additionalData.alamat;
    }
    
    // Ensure semester and tahun_akademik have default values
    const semesterInput = document.getElementById('edit_semester');
    const tahunInput = document.getElementById('edit_tahun_akademik');
    
    if (semesterInput && !semesterInput.value) {
        semesterInput.value = 'Ganjil';
        const previewEl = document.getElementById('preview_semester');
        if (previewEl) previewEl.childNodes[0].textContent = 'Ganjil';
    }
    
    if (tahunInput && !tahunInput.value) {
        tahunInput.value = '2024/2025';
        const previewEl = document.getElementById('preview_tahun_akademik');
        if (previewEl) previewEl.childNodes[0].textContent = '2024/2025';
    }
});

function saveAllChanges() {
    const saveBtn = document.getElementById('saveBtn');
    const btnText = saveBtn.querySelector('.btn-text');
    const loadingText = saveBtn.querySelector('.loading-text');
    
    saveBtn.disabled = true;
    btnText.classList.add('hidden');
    loadingText.classList.remove('hidden');
    
    // Collect orang_tua data dynamically (for MA)
    const orangTuaData = {};
    const orangTuaFields = [
        'nama', 'nama_ayah', 'nama_ibu', 'tempat_lahir', 'tanggal_lahir',
        'pekerjaan', 'nip', 'pangkat_golongan', 'instansi', 
        'alamat_instansi', 'alamat_rumah'
    ];
    
    orangTuaFields.forEach(field => {
        const inputEl = document.getElementById(`edit_${field}`);
        if (inputEl && inputEl.value) {
            orangTuaData[field] = inputEl.value;
        }
    });
    
    // Collect KP data dynamically (for KP)
    const kpData = {};
    if (document.getElementById('edit_kepada_nama')) {
        kpData.nama_perusahaan = document.getElementById('edit_kepada_nama')?.value || '';
        kpData.alamat_perusahaan = document.getElementById('edit_kepada_alamat_1')?.value || '';
        kpData.periode_mulai = document.getElementById('edit_periode_mulai_date')?.value || '';
        kpData.periode_selesai = document.getElementById('edit_periode_selesai_date')?.value || '';
        
        // Collect mahasiswa data for KP
        kpData.mahasiswa_kp = [];
        for (let i = 0; i < 5; i++) {
            const namaEl = document.getElementById(`edit_mhs_${i}_nama`);
            const nimEl = document.getElementById(`edit_mhs_${i}_nim`);
            const prodiEl = document.getElementById(`edit_mhs_${i}_prodi`);
            
            if (namaEl && namaEl.value.trim()) {
                kpData.mahasiswa_kp.push({
                    nama: namaEl.value.trim(),
                    nim: nimEl?.value.trim() || '',
                    prodi: prodiEl?.value.trim() || ''
                });
            }
        }
    }
    
    const data = {
        nomor_surat: document.getElementById('edit_nomor_surat')?.value || '',
        tanggal_surat: document.getElementById('edit_tanggal_surat')?.value || '',
        penandatangan: {
            nama: document.getElementById('edit_ttd_nama')?.value || '',
            pangkat: document.getElementById('edit_ttd_pangkat')?.value || '',
            jabatan: document.getElementById('edit_ttd_jabatan')?.value || '',
            nid: document.getElementById('edit_ttd_nid')?.value || ''
        },
        mahasiswa: {
            nama_mahasiswa: document.getElementById('edit_nama_mahasiswa')?.value || '',
            nim: document.getElementById('edit_nim')?.value || '',
            prodi: document.getElementById('edit_prodi')?.value || ''
        },
        additional_data: {
            semester: document.getElementById('edit_semester')?.value || 'Ganjil',
            tahun_akademik: document.getElementById('edit_tahun_akademik')?.value || '2024/2025',
            orang_tua: orangTuaData,
            kerja_praktek: kpData,
            keperluan: document.getElementById('edit_keperluan')?.value || '',
            alamat: document.getElementById('edit_alamat')?.value || ''
        }
    };
    
    console.log('Saving data:', data);
    
    fetch(`{{ route('fakultas.surat.fsi.save-edits', $pengajuan->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        saveBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        
        if (result.success) {
            // Flash success indication
            saveBtn.style.background = '#10b981';
            setTimeout(() => {
                saveBtn.style.background = '';
            }, 2000);
            alert('Perubahan berhasil disimpan!');
        } else {
            alert(result.message || 'Gagal menyimpan perubahan');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        alert('Terjadi kesalahan: ' + error.message);
    });
}

function printForSignature() {
    if (confirm('Print surat dan ubah status menjadi "Sedang Ditandatangani"?\n\nPastikan Anda sudah menyimpan perubahan terlebih dahulu.')) {
        window.location.href = `{{ route('fakultas.surat.fsi.print', $pengajuan->id) }}`;
    }
}

function uploadSignedDocument() {
    const url = document.getElementById('signed_url').value.trim();
    const notes = document.getElementById('signed_notes').value.trim();
    
    if (!url) {
        alert('Masukkan link surat yang sudah ditandatangani');
        document.getElementById('signed_url').focus();
        return;
    }
    
    if (!url.startsWith('http')) {
        alert('Link harus berupa URL valid (dimulai dengan http:// atau https://)');
        document.getElementById('signed_url').focus();
        return;
    }
    
    if (confirm('Selesaikan surat dan kirim link ke mahasiswa?\n\nPastikan link dapat diakses oleh mahasiswa.')) {
        const uploadBtn = document.getElementById('uploadBtn');
        const btnText = uploadBtn.querySelector('.btn-text');
        const loadingText = uploadBtn.querySelector('.loading-text');
        
        uploadBtn.disabled = true;
        btnText.classList.add('hidden');
        loadingText.classList.remove('hidden');
        
        fetch(`{{ route('fakultas.surat.fsi.upload-signed', $pengajuan->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                signed_url: url,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(result => {
            uploadBtn.disabled = false;
            btnText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            
            if (result.success) {
                alert('Surat berhasil diselesaikan!');
                setTimeout(() => {
                    window.location.href = '{{ route("fakultas.surat.index") }}';
                }, 1000);
            } else {
                alert(result.message || 'Gagal menyelesaikan surat');
            }
        })
        .catch(error => {
            uploadBtn.disabled = false;
            btnText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
}

function rejectSurat() {
    const reason = prompt('Alasan penolakan (wajib):');
    if (!reason || reason.trim() === '') {
        alert('Alasan penolakan harus diisi');
        return;
    }
    
    if (confirm(`Tolak surat dengan alasan: "${reason}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
        fetch(`{{ route('fakultas.surat.fsi.reject', $pengajuan->id) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Surat berhasil ditolak');
                setTimeout(() => {
                    window.location.href = '{{ route("fakultas.surat.index") }}';
                }, 1000);
            } else {
                alert(result.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
}
</script>
@endpush    