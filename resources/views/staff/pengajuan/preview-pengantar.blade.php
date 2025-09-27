{{-- resources/views/staff/pengajuan/preview-pengantar.blade.php - FIXED VERSION --}}
@extends('layouts.app')

@section('title', 'Generate Surat Pengantar')

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
    font-size: 12pt;
    line-height: 1.5;
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

/* Table styles */
.nota-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.nota-table th {
    background-color: #ffff99;
    border: 1px solid #000;
    padding: 8px;
    font-weight: bold;
    text-align: center;
}

.nota-table td {
    border: 1px solid #000;
    padding: 8px;
    text-align: left;
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
    z-index: 1000;
}

.editable-field:hover .tooltip-edit {
    opacity: 1;
}

/* Cards */
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

/* Buttons */
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
    transition: all 0.2s;
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

/* Forms */
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
    .tooltip-edit {
        display: none !important;
    }
}
</style>
@endpush

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
                                    Generate Surat Pengantar
                                </h3>
                                <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ $pengajuan->jenisSurat->kode_surat }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                                <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                                <div><strong>Prodi:</strong> {{ $pengajuan->prodi->nama_prodi }}</div>
                                <div><strong>Token:</strong> {{ $pengajuan->tracking_token }}</div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="{{ route('staff.pengajuan.show', $pengajuan->id) }}" class="btn btn-outline w-full">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Form -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="font-bold text-gray-900">
                                <i class="fas fa-edit text-yellow-500 mr-2"></i>
                                Data Nota Dinas
                            </h4>
                        </div>
                        <div class="card-body space-y-3" style="max-height: 600px; overflow-y: auto;">
                            <!-- Nomor Surat -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Nota Dinas</label>
                                <input type="text" id="edit_nomor_nota" class="form-control" data-preview="preview_nomor_nota"
                                       value="{{ $nomorSurat }}" 
                                       placeholder="ND-70/SI-F.SI/VII/2025">
                            </div>
                            
                            <!-- Kepada/Dari/Perihal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kepada</label>
                                <input type="text" id="edit_kepada" class="form-control" data-preview="preview_kepada"
                                       value="Wakil Dekan I FSI">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                                <input type="text" id="edit_dari" class="form-control" data-preview="preview_dari"
                                       value="Ketua Program Studi {{ $pengajuan->prodi->nama_prodi }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Perihal</label>
                                <input type="text" id="edit_perihal" class="form-control" data-preview="preview_perihal"
                                       value="Permohonan Pengantar Kerja Praktik">
                            </div>

                            <!-- Dasar -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-list text-blue-500 mr-2"></i>
                                Dasar
                            </h5>
                            
                            <ol style="list-style-type: lower-alpha; padding-left: 20px; margin: 0;">
                                <li style="margin-bottom: 12px;">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dasar a</label>
                                    <textarea id="edit_dasar_a" class="form-control" rows="2" data-preview="preview_dasar_a">Program Kerja Prodi {{ $pengajuan->prodi->nama_prodi }} 2025, tentang Kalender Kegiatan Akademik Unjani TA. 2024/2025.</textarea>
                                </li>
                                
                                <li style="margin-bottom: 12px;">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dasar b</label>
                                    <textarea id="edit_dasar_b" class="form-control" rows="2" data-preview="preview_dasar_b">Form pengajuan Pembuatan surat pengantar ke instansi/lembaga/perusahaan dari mahasiswa.</textarea>
                                </li>
                                
                                <li style="margin-bottom: 12px;">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dasar c</label>
                                    <textarea id="edit_dasar_c" class="form-control" rows="2" data-preview="preview_dasar_c">Memperhatikan saran, arahan dan pandangan dari pimpinan jurusan, berkaitan dengan kegiatan :</textarea>
                                </li>
                            </ol>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kegiatan (Bold)</label>
                                <input type="text" id="edit_kegiatan" class="form-control" data-preview="preview_kegiatan"
                                       value="Pengumpulan data dan survey guna memenuhi tugas besar mata kuliah Kerja Praktik">
                            </div>

                            <!-- Data Mahasiswa - DYNAMIC SECTION -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-users text-green-500 mr-2"></i>
                                Daftar Mahasiswa
                                <button type="button" onclick="addMahasiswaRow()" class="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded">
                                    <i class="fas fa-plus mr-1"></i>Tambah
                                </button>
                            </h5>

                            <div id="mahasiswa-container">
                                @php
                                    $mahasiswaList = [];
                                    // Get existing mahasiswa data
                                    if (isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp'])) {
                                        $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'];
                                    }
                                    
                                    // If empty, use current pengajuan data as default
                                    if (empty($mahasiswaList) || (count($mahasiswaList) == 1 && empty($mahasiswaList[0]['nama']))) {
                                        $mahasiswaList = [
                                            [
                                                'nim' => $pengajuan->nim,
                                                'nama' => $pengajuan->nama_mahasiswa
                                            ]
                                        ];
                                    }
                                @endphp

                                @foreach($mahasiswaList as $index => $mhs)
                                <div class="mahasiswa-row border border-gray-200 rounded p-3 mb-2" data-index="{{ $index }}">
                                    <div class="flex justify-between items-center mb-2">
                                        <small class="text-gray-600 font-medium">Mahasiswa {{ $index + 1 }}</small>
                                        @if($index > 0)
                                        <button type="button" onclick="removeMahasiswaRow({{ $index }})" class="text-red-500 text-xs">
                                            <i class="fas fa-times"></i> Hapus
                                        </button>
                                        @endif
                                    </div>
                                    <div class="space-y-2">
                                        <input type="text" id="edit_mhs_{{ $index }}_nim" class="form-control" 
                                               placeholder="NIM" 
                                               value="{{ $mhs['nim'] ?? '' }}" 
                                               data-preview="preview_mhs_{{ $index }}_nim">
                                        <input type="text" id="edit_mhs_{{ $index }}_nama" class="form-control" 
                                               placeholder="Nama Lengkap" 
                                               value="{{ $mhs['nama'] ?? '' }}" 
                                               data-preview="preview_mhs_{{ $index }}_nama">
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Instansi Tujuan -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-building text-purple-500 mr-2"></i>
                                Instansi Tujuan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Instansi</label>
                                <input type="text" id="edit_nama_instansi" class="form-control" data-preview="preview_nama_instansi"
                                       value="{{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Badan Kesatuan Bangsa dan Politik Kota Cimahi (Bakesbangpol Cimahi)' }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Instansi</label>
                                <textarea id="edit_alamat_instansi" class="form-control" rows="3" data-preview="preview_alamat_instansi">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Gedung C, Jl. Raden Demang Hardjakusumah Lantai 1, Cibabat, Kec. Cimahi Utara, Kota Cimahi, Jawa Barat 40513' }}</textarea>
                            </div>

                            <!-- Penandatangan -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-signature text-orange-500 mr-2"></i>
                                Data Penandatangan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat, Tanggal</label>
                                <input type="text" id="edit_tempat_tanggal" class="form-control" data-preview="preview_tempat_tanggal"
                                       value="Cimahi, {{ $tanggalSurat }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan TTD</label>
                                <input type="text" id="edit_jabatan_ttd" class="form-control" data-preview="preview_jabatan_ttd"
                                       value="Ketua Prodi">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit TTD</label>
                                <input type="text" id="edit_unit_ttd" class="form-control" data-preview="preview_unit_ttd"
                                       value="{{ $pengajuan->prodi->nama_prodi }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penandatangan</label>
                                <input type="text" id="edit_nama_ttd" class="form-control" data-preview="preview_nama_ttd"
                                       value="{{ $kaprodi->nama ?? 'Taebir Hendro Pudjiantoro, S.Si., M.T' }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NID</label>
                                <input type="text" id="edit_nid_ttd" class="form-control" data-preview="preview_nid_ttd"
                                       value="{{ $kaprodi->nip ?? '412166969' }}">
                            </div>

                            <!-- Upload TTD -->
                            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                <h6 class="font-bold text-yellow-800 mb-2">
                                    <i class="fas fa-upload mr-2"></i>Upload Tanda Tangan Kaprodi
                                </h6>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        File Gambar TTD <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" id="ttd_kaprodi_file" accept="image/*" 
                                           class="form-control" required>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Format: PNG/JPG, Maks. 2MB, Background transparan disarankan
                                    </p>
                                    
                                    <!-- Preview -->
                                    <div id="ttd_preview" class="mt-3 hidden">
                                        <img id="ttd_preview_img" class="max-h-32 border rounded" alt="Preview TTD">
                                    </div>
                                    
                                    <!-- Hidden input untuk base64 -->
                                    <input type="hidden" id="ttd_kaprodi_image" name="ttd_kaprodi_image">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="font-bold text-gray-900">
                                <i class="fas fa-cogs text-purple-500 mr-2"></i>
                                Aksi
                            </h4>
                        </div>
                        <div class="card-body space-y-2">
                            <button onclick="generateSuratPengantar()" class="btn btn-success w-full" id="generateBtn">
                                <span class="btn-text">
                                    <i class="fas fa-file-pdf mr-2"></i>Generate Surat Pengantar
                                </span>
                                <span class="loading-text hidden">
                                    <div class="loading-spinner mr-2"></div>Generating...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: A4 Preview - FIXED TEMPLATE -->
            <div class="lg:col-span-3">
                <div class="card">
                    <div class="card-header no-print">
                        <h3 class="font-bold text-gray-900">
                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                            Preview Nota Dinas {{ $pengajuan->jenisSurat->nama_jenis }}
                        </h3>
                        <small class="text-gray-500">Klik field kuning untuk edit langsung</small>
                    </div>
                    
                    <div class="p-2 bg-gray-50">
                        <div class="a4-container">
                            <!-- KOP SURAT -->
                            <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px;">
                                <tr>
                                    <td style="width: 15%; text-align: center; vertical-align: middle;">
                                        <img src="{{ asset('images/logo-ykep.png') }}" style="width: 60px; height: 60px;" alt="Logo YKEP">
                                    </td>
                                    <td style="width: 70%; text-align: center; font-weight: bold; line-height: 1.3; vertical-align: middle;">
                                        <div style="font-size: 11pt;">YAYASAN KARTIKA EKA PAKSI</div>
                                        <div style="font-size: 12pt;">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                                        <div style="font-size: 13pt;">FAKULTAS SAINS DAN INFORMATIKA</div>
                                        <div style="font-size: 14pt; font-weight: bold;">PROGRAM STUDI S1 {{ strtoupper($pengajuan->prodi->nama_prodi) }}</div>
                                        <div style="font-size: 9pt; font-weight: normal;">
                                            Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO. BOX 148 Telp. (022) 6631556 Fax. (022) 6631556
                                        </div>
                                    </td>
                                    <td style="width: 15%; text-align: center; vertical-align: middle;">
                                        <img src="{{ asset('images/logo-unjani.png') }}" style="width: 65px; height: 65px;" alt="Logo UNJANI">
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- JUDUL -->
                            <div style="text-align: center; margin: 20px 0;">
                                <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">NOTA DINAS</h3>
                                <div style="margin-top: 8px;">Nomor : <span id="preview_nomor_nota" class="editable-field" data-input="edit_nomor_nota">{{ $nomorSurat }}<span class="tooltip-edit">Klik untuk edit</span></span></div>
                            </div>
                            
                            <!-- METADATA -->
                            <div style="margin: 20px 0;">
                                <table style="border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 80px; padding: 2px 0;">Kepada</td>
                                        <td style="width: 20px; text-align: center; padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;"><span id="preview_kepada" class="editable-field" data-input="edit_kepada">Wakil Dekan I FSI<span class="tooltip-edit">Klik untuk edit</span></span></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Dari</td>
                                        <td style="text-align: center; padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;"><span id="preview_dari" class="editable-field" data-input="edit_dari">Ketua Program Studi {{ $pengajuan->prodi->nama_prodi }}<span class="tooltip-edit">Klik untuk edit</span></span></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Perihal</td>
                                        <td style="text-align: center; padding: 2px 0;">:</td>
                                        <td style="padding: 2px 0;"><span id="preview_perihal" class="editable-field" data-input="edit_perihal">Permohonan Pengantar Kerja Praktik<span class="tooltip-edit">Klik untuk edit</span></span></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- CONTENT -->
                            <div style="text-align: justify; margin: 15px 0;">
                                <ol style="margin-left: 0px; padding-left: 20px;">
                                    <li style="margin-bottom: 15px;">
                                        <strong>1. Dasar :</strong>
                                        <ol type="a" style="margin-left: 20px; margin-top: 8px; padding-left: 0px; list-style-type: lower-alpha;">
                                            <li style="margin-bottom: 8px; text-align: justify;">
                                                <span id="preview_dasar_a" class="editable-field" data-input="edit_dasar_a">Program Kerja Prodi {{ $pengajuan->prodi->nama_prodi }} 2025, tentang Kalender Kegiatan Akademik Unjani TA. 2024/2025.<span class="tooltip-edit">Klik untuk edit</span></span>
                                            </li>
                                            <li style="margin-bottom: 8px; text-align: justify;">
                                                <span id="preview_dasar_b" class="editable-field" data-input="edit_dasar_b">Form pengajuan Pembuatan surat pengantar ke instansi/lembaga/perusahaan dari mahasiswa.<span class="tooltip-edit">Klik untuk edit</span></span>
                                            </li>
                                            <li style="margin-bottom: 8px; text-align: justify;">
                                                <span id="preview_dasar_c" class="editable-field" data-input="edit_dasar_c">Memperhatikan saran, arahan dan pandangan dari pimpinan jurusan, berkaitan dengan kegiatan :<span class="tooltip-edit">Klik untuk edit</span></span><br>
                                                <strong><span id="preview_kegiatan" class="editable-field" data-input="edit_kegiatan">Pengumpulan data dan survey guna memenuhi tugas besar mata kuliah Kerja Praktik<span class="tooltip-edit">Klik untuk edit</span></span></strong>
                                            </li>
                                        </ol>
                                    </li>
                                    
                                    <li style="margin-bottom: 15px; text-align: justify;">
                                        2. Atas dasar tersebut di atas, dengan ini kami sampaikan permohonan kepada Wakil Dekan I, untuk berkenan kiranya menerbitkan surat pengantar bagi mahasiswa yang tersebut dalam lampiran, yang ditujukan kepada :
                                    </li>
                                </ol>
                                
                                <!-- DYNAMIC TABLE -->
                                <table class="nota-table" id="mahasiswa-table-preview">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%;">No</th>
                                            <th style="width: 25%;">N I M</th>
                                            <th style="width: 65%;">NAMA LENGKAP</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mahasiswa-table-body-preview">
                                        <!-- Dynamic content will be populated by JavaScript -->
                                    </tbody>
                                </table>
                                
                                <div style="margin: 20px 0; font-weight: bold;">
                                    <span id="preview_nama_instansi" class="editable-field" data-input="edit_nama_instansi">{{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Badan Kesatuan Bangsa dan Politik Kota Cimahi (Bakesbangpol Cimahi)' }}<span class="tooltip-edit">Klik untuk edit</span></span>
                                </div>
                                <div style="margin: 5px 0;">
                                    <span id="preview_alamat_instansi" class="editable-field" data-input="edit_alamat_instansi">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Gedung C, Jl. Raden Demang Hardjakusumah Lantai 1, Cibabat, Kec. Cimahi Utara, Kota Cimahi, Jawa Barat 40513' }}<span class="tooltip-edit">Klik untuk edit</span></span>
                                </div>
                                
                                <ol start="3" style="margin-left: 20px;">
                                    <li>Demikian surat permohonan ini kami sampaikan, atas perhatian dan Kerjasamanya, diucapkan terima kasih.</li>
                                </ol>
                            </div>
                            
                            <!-- SIGNATURE -->
                            <div style="margin-top: 40px; text-align: right;">
                                <div style="display: inline-block; text-align: center; min-width: 200px;">
                                    <p><span id="preview_tempat_tanggal" class="editable-field" data-input="edit_tempat_tanggal">Cimahi, {{ $tanggalSurat }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
                                    <p style="font-weight: bold; text-decoration: underline;"><span id="preview_jabatan_ttd" class="editable-field" data-input="edit_jabatan_ttd">Ketua Prodi<span class="tooltip-edit">Klik untuk edit</span></span></p>
                                    <p><span id="preview_unit_ttd" class="editable-field" data-input="edit_unit_ttd">{{ $pengajuan->prodi->nama_prodi }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
                                    <div style="height: 60px; margin: 10px 0;" id="ttd-preview-space">
                                        <!-- TTD will appear here after upload -->
                                    </div>
                                    <p style="text-decoration: underline; font-weight: bold;"><span id="preview_nama_ttd" class="editable-field" data-input="edit_nama_ttd">{{ $kaprodi->nama ?? 'Taebir Hendro Pudjiantoro, S.Si., M.T' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
                                    <p>NID. <span id="preview_nid_ttd" class="editable-field" data-input="edit_nid_ttd">{{ $kaprodi->nip ?? '412166969' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
                                </div>
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
// Counter untuk mahasiswa rows
let mahasiswaCount = {{ count($mahasiswaList ?? []) }};

// Function untuk menambah row mahasiswa
function addMahasiswaRow() {
    const container = document.getElementById('mahasiswa-container');
    const newIndex = mahasiswaCount;
    
    const newRow = document.createElement('div');
    newRow.className = 'mahasiswa-row border border-gray-200 rounded p-3 mb-2';
    newRow.setAttribute('data-index', newIndex);
    
    newRow.innerHTML = `
        <div class="flex justify-between items-center mb-2">
            <small class="text-gray-600 font-medium">Mahasiswa ${newIndex + 1}</small>
            <button type="button" onclick="removeMahasiswaRow(${newIndex})" class="text-red-500 text-xs">
                <i class="fas fa-times"></i> Hapus
            </button>
        </div>
        <div class="space-y-2">
            <input type="text" id="edit_mhs_${newIndex}_nim" class="form-control" 
                   placeholder="NIM" 
                   data-preview="preview_mhs_${newIndex}_nim">
            <input type="text" id="edit_mhs_${newIndex}_nama" class="form-control" 
                   placeholder="Nama Lengkap" 
                   data-preview="preview_mhs_${newIndex}_nama">
        </div>
    `;
    
    container.appendChild(newRow);
    
    // Add event listeners untuk real-time update
    const namaInput = document.getElementById(`edit_mhs_${newIndex}_nama`);
    const nimInput = document.getElementById(`edit_mhs_${newIndex}_nim`);
    
    [namaInput, nimInput].forEach(input => {
        input.addEventListener('input', function() {
            updateMahasiswaTable();
            updatePreviewField(this);
        });
    });
    
    mahasiswaCount++;
    updateMahasiswaTable();
}

// Function untuk menghapus row mahasiswa
function removeMahasiswaRow(index) {
    const row = document.querySelector(`.mahasiswa-row[data-index="${index}"]`);
    if (row) {
        row.remove();
        updateMahasiswaTable();
        reorderMahasiswaRows();
    }
}

// Function untuk reorder nomor mahasiswa setelah hapus
function reorderMahasiswaRows() {
    const rows = document.querySelectorAll('.mahasiswa-row');
    rows.forEach((row, index) => {
        const label = row.querySelector('small');
        if (label) {
            label.textContent = `Mahasiswa ${index + 1}`;
        }
        row.setAttribute('data-index', index);
    });
}

// Function untuk update tabel mahasiswa di preview
function updateMahasiswaTable() {
    const tableBody = document.getElementById('mahasiswa-table-body-preview');
    if (!tableBody) return;
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Get all mahasiswa data
    const mahasiswaRows = document.querySelectorAll('.mahasiswa-row');
    
    mahasiswaRows.forEach((row, index) => {
        const dataIndex = row.getAttribute('data-index');
        const namaInput = document.getElementById(`edit_mhs_${dataIndex}_nama`);
        const nimInput = document.getElementById(`edit_mhs_${dataIndex}_nim`);
        
        const nama = namaInput ? namaInput.value : '';
        const nim = nimInput ? nimInput.value : '';
        
        if (nama.trim() || nim.trim()) {
            const tableRow = document.createElement('tr');
            tableRow.innerHTML = `
                <td style="text-align: center; border: 1px solid #000; padding: 8px;">${index + 1}.</td>
                <td style="text-align: center; border: 1px solid #000; padding: 8px;">
                    <span id="preview_mhs_${dataIndex}_nim" class="editable-field" data-input="edit_mhs_${dataIndex}_nim">${nim || '-'}<span class="tooltip-edit">Klik untuk edit</span></span>
                </td>
                <td style="border: 1px solid #000; padding: 8px;">
                    <span id="preview_mhs_${dataIndex}_nama" class="editable-field" data-input="edit_mhs_${dataIndex}_nama">${nama || '-'}<span class="tooltip-edit">Klik untuk edit</span></span>
                </td>
            `;
            tableBody.appendChild(tableRow);
        }
    });
    
    // Re-attach click events to new editable fields
    attachEditableFieldEvents();
}

// Function untuk attach events ke editable fields yang baru
function attachEditableFieldEvents() {
    document.querySelectorAll('.editable-field').forEach(field => {
        field.removeEventListener('click', handleEditableClick);
        field.addEventListener('click', handleEditableClick);
    });
}

// Handler untuk editable field clicks
function handleEditableClick() {
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
}

// Function untuk update preview field
function updatePreviewField(input) {
    const previewIds = input.getAttribute('data-preview');
    if (!previewIds) return;
    
    previewIds.split(',').forEach(previewId => {
        const previewEl = document.getElementById(previewId);
        if (previewEl && previewEl.childNodes[0]) {
            previewEl.childNodes[0].textContent = input.value || '-';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mahasiswa table
    updateMahasiswaTable();
    
    // Attach events ke existing inputs
    document.querySelectorAll('.mahasiswa-row').forEach((row, index) => {
        const dataIndex = row.getAttribute('data-index');
        const namaInput = document.getElementById(`edit_mhs_${dataIndex}_nama`);
        const nimInput = document.getElementById(`edit_mhs_${dataIndex}_nim`);
        
        [namaInput, nimInput].forEach(input => {
            if (input) {
                input.addEventListener('input', function() {
                    updateMahasiswaTable();
                    updatePreviewField(this);
                });
            }
        });
    });
    
    // Interactive preview - click to focus input
    attachEditableFieldEvents();
    
    // Real-time preview updates
    document.querySelectorAll('.form-control[data-preview]').forEach(input => {
        input.addEventListener('input', function() {
            updatePreviewField(this);
        });
        
        input.addEventListener('blur', function() {
            setTimeout(() => {
                this.classList.remove('highlight');
                document.querySelectorAll('.editable-field').forEach(f => f.classList.remove('active'));
            }, 200);
        });
    });

    // TTD Upload Handler
    document.getElementById('ttd_kaprodi_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) return;
        
        // Validasi ukuran (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            this.value = '';
            return;
        }
        
        // Validasi tipe
        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar!');
            this.value = '';
            return;
        }
        
        // Convert to base64
        const reader = new FileReader();
        reader.onload = function(event) {
            const base64 = event.target.result;
            document.getElementById('ttd_kaprodi_image').value = base64;
            
            // Show preview in form
            document.getElementById('ttd_preview').classList.remove('hidden');
            document.getElementById('ttd_preview_img').src = base64;
            
            // Show preview in nota dinas
            const ttdSpace = document.getElementById('ttd-preview-space');
            ttdSpace.innerHTML = `<img src="${base64}" style="max-width: 150px; max-height: 60px;" alt="TTD Preview">`;
        };
        reader.readAsDataURL(file);
    });
});

function generateSuratPengantar() {
    const generateBtn = document.getElementById('generateBtn');
    const btnText = generateBtn.querySelector('.btn-text');
    const loadingText = generateBtn.querySelector('.loading-text');
    
    // Validasi TTD
    const ttdImage = document.getElementById('ttd_kaprodi_image').value;
    if (!ttdImage) {
        alert('Tanda tangan Kaprodi harus diupload!');
        document.getElementById('ttd_kaprodi_file').focus();
        return;
    }

    // Validasi nomor surat
    const nomorSurat = document.getElementById('edit_nomor_nota').value.trim();
    if (!nomorSurat) {
        alert('Nomor nota dinas harus diisi!');
        document.getElementById('edit_nomor_nota').focus();
        return;
    }
    
    // Collect mahasiswa data from dynamic table
    const mahasiswaData = [];
    document.querySelectorAll('.mahasiswa-row').forEach((row, index) => {
        const dataIndex = row.getAttribute('data-index');
        const namaInput = document.getElementById(`edit_mhs_${dataIndex}_nama`);
        const nimInput = document.getElementById(`edit_mhs_${dataIndex}_nim`);
        
        if (namaInput && nimInput) {
            const nama = namaInput.value.trim();
            const nim = nimInput.value.trim();
            
            if (nama || nim) {
                mahasiswaData.push({ nama, nim });
            }
        }
    });
    
    // Validasi minimal 1 mahasiswa
    if (mahasiswaData.length === 0) {
        alert('Minimal harus ada 1 data mahasiswa!');
        return;
    }
    
    // Collect all form data sesuai template SI
    const suratData = {
        nomor_nota: nomorSurat,
        kepada: document.getElementById('edit_kepada').value,
        dari: document.getElementById('edit_dari').value,
        perihal: document.getElementById('edit_perihal').value,
        dasar_a: document.getElementById('edit_dasar_a').value,
        dasar_b: document.getElementById('edit_dasar_b').value,
        dasar_c: document.getElementById('edit_dasar_c').value,
        kegiatan: document.getElementById('edit_kegiatan').value,
        mahasiswa_list: mahasiswaData, // Dynamic mahasiswa data
        nama_instansi: document.getElementById('edit_nama_instansi').value,
        alamat_instansi: document.getElementById('edit_alamat_instansi').value,
        tempat_tanggal: document.getElementById('edit_tempat_tanggal').value,
        jabatan_ttd: document.getElementById('edit_jabatan_ttd').value,
        unit_ttd: document.getElementById('edit_unit_ttd').value,
        nama_ttd: document.getElementById('edit_nama_ttd').value,
        nid_ttd: document.getElementById('edit_nid_ttd').value
    };

    const requestData = {
        surat_pengantar_nomor: nomorSurat,
        nota_dinas_number: nomorSurat,
        ttd_kaprodi_image: ttdImage,
        surat_data: suratData
    };
    
    // Disable button
    generateBtn.disabled = true;
    btnText.classList.add('hidden');
    loadingText.classList.remove('hidden');
    
    // Submit via AJAX
    fetch('{{ route("staff.pengajuan.pengantar.store", $pengajuan->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        generateBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        
        if (result.success) {
            alert(result.message);
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        generateBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        alert('Terjadi kesalahan jaringan');
    });
}
</script>
@endpush