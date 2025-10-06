


<?php $__env->startSection('title', 'Preview & Edit Surat FSI'); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php
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
?>

<?php $__env->startSection('content'); ?>
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
                                    Edit Surat <?php echo e($pengajuan->jenisSurat->nama_jenis); ?>

                                </h3>
                                <span class="status-badge status-<?php echo e($pengajuan->status == 'completed' ? 'completed' : ($pengajuan->status == 'sedang_ditandatangani' ? 'signing' : 'approved')); ?>">
                                    <?php echo e($pengajuan->status_label); ?>

                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 space-y-1">
                                <div><strong>NIM:</strong> <?php echo e($pengajuan->nim); ?></div>
                                <div><strong>Nama:</strong> <?php echo e($pengajuan->nama_mahasiswa); ?></div>
                                <div><strong>Token:</strong> <?php echo e($pengajuan->tracking_token); ?></div>
                            </div>
                            
                            <!-- Debug Additional Data
                            <?php if(config('app.debug')): ?>
                            <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
                                <strong>Debug Additional Data:</strong>
                                <pre style="max-height: 100px; overflow-y: auto; font-size: 10px;"><?php echo e(json_encode($additionalData, JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                            <?php endif; ?> -->
                            
                            <div class="mt-3">
                                <a href="<?php echo e(route('fakultas.surat.index')); ?>" class="btn btn-outline w-full">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Form -->
                    <?php if($canEdit): ?>
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
                                       value="<?php echo e($nomorSurat); ?>" placeholder="P/001/FSI-UNJANI/IX/2024">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Surat</label>
                                <input type="text" id="edit_tanggal_surat" class="form-control" data-preview="preview_tanggal,preview_tempat_tanggal"
                                       value="<?php echo e($tanggalSurat); ?>" placeholder="25 September 2024">
                            </div>
                            
                            <!-- Data Penandatangan -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-signature text-blue-500 mr-2"></i>
                                Data Penandatangan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" id="edit_ttd_nama" class="form-control" data-preview="preview_ttd_nama,preview_ttd_nama_bottom,preview_ttd_nama_bottom_kp"
                                       value="<?php echo e($penandatangan['nama']); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat/Golongan</label>
                                <input type="text" id="edit_ttd_pangkat" class="form-control" data-preview="preview_ttd_pangkat"
                                       value="<?php echo e($penandatangan['pangkat']); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" id="edit_ttd_jabatan" class="form-control" data-preview="preview_ttd_jabatan"
                                       value="<?php echo e($penandatangan['jabatan']); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NID</label>
                                <input type="text" id="edit_ttd_nid" class="form-control" data-preview="preview_ttd_nid,preview_ttd_nid_kp"
                                       value="<?php echo e($penandatangan['nid']); ?>">
                            </div>

                            <!-- Data Mahasiswa -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-user text-green-500 mr-2"></i>
                                Data Mahasiswa
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mahasiswa</label>
                                <input type="text" id="edit_nama_mahasiswa" class="form-control" data-preview="preview_nama_mahasiswa"
                                       value="<?php echo e($pengajuan->nama_mahasiswa); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIM</label>
                                <input type="text" id="edit_nim" class="form-control" data-preview="preview_nim"
                                       value="<?php echo e($pengajuan->nim); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                                <input type="text" id="edit_prodi" class="form-control" data-preview="preview_prodi"
                                       value="<?php echo e($pengajuan->prodi->nama_prodi ?? 'Tidak ada data'); ?>">
                            </div>

                            <!-- Data Additional -->
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                                Data Tambahan
                            </h5>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="text" id="edit_semester" class="form-control" data-preview="preview_semester"
                                       value="<?php echo e($additionalData['semester'] ?? 'Ganjil'); ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                                <input type="text" id="edit_tahun_akademik" class="form-control" data-preview="preview_tahun_akademik"
                                       value="<?php echo e($additionalData['tahun_akademik'] ?? '2024/2025'); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Mahasiswa</label>
                                <select id="edit_status_mahasiswa" class="form-control" data-preview="preview_status_mahasiswa">
                                    <option value="Aktif" <?php echo e(($additionalData['status_mahasiswa'] ?? 'Aktif') == 'Aktif' ? 'selected' : ''); ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?php echo e(($additionalData['status_mahasiswa'] ?? '') == 'Tidak Aktif' ? 'selected' : ''); ?>>Tidak Aktif</option>
                                    <option value="Cuti" <?php echo e(($additionalData['status_mahasiswa'] ?? '') == 'Cuti' ? 'selected' : ''); ?>>Cuti</option>
                                    <option value="Non-Aktif" <?php echo e(($additionalData['status_mahasiswa'] ?? '') == 'Non-Aktif' ? 'selected' : ''); ?>>Non-Aktif</option>
                                </select>
                            </div>

                            <!-- Data Orang Tua Section (untuk MA) -->
                            <?php if($pengajuan->jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']) && is_array($additionalData['orang_tua'])): ?>
                            <?php
                                $orangTua = $additionalData['orang_tua'];
                            ?>
                            
                            <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                <i class="fas fa-users text-orange-500 mr-2"></i>
                                Data Orang Tua
                            </h5>
                            
                            <?php $__currentLoopData = [
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
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($orangTua[$key]) && !empty($orangTua[$key])): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e($label); ?></label>
                                    <?php if(in_array($key, ['alamat_instansi', 'alamat_rumah'])): ?>
                                        <textarea id="edit_<?php echo e($key); ?>" class="form-control" rows="2" data-preview="preview_<?php echo e($key); ?>"><?php echo e($orangTua[$key]); ?></textarea>
                                    <?php else: ?>
                                        <input type="text" id="edit_<?php echo e($key); ?>" class="form-control" data-preview="preview_<?php echo e($key); ?>"
                                               value="<?php echo e($orangTua[$key]); ?>">
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            
                            <!-- Data KP Section (untuk KP) -->
                            <?php if($pengajuan->jenisSurat->kode_surat === 'KP'): ?>
                                <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                    <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                                    Data Kerja Praktik
                                </h5>

                                <!-- Basic KP Fields -->
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sifat Surat</label>
                                    <input type="text" id="edit_sifat" class="form-control" data-preview="preview_sifat_kp"
                                        value="Biasa">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran</label>
                                    <input type="text" id="edit_lampiran" class="form-control" data-preview="preview_lampiran_kp"
                                        value="-">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Penerima</label>
                                    <input type="text" id="edit_kepada_jabatan" class="form-control" data-preview="preview_kepada_jabatan"
                                        value="Bapak/Ibu Pimpinan">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                                    <input type="text" id="edit_kepada_nama" class="form-control" data-preview="preview_kepada_nama"
                                        value="<?php echo e($additionalData['kerja_praktek']['nama_perusahaan'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Perusahaan</label>
                                    <textarea id="edit_kepada_alamat" class="form-control" rows="2" data-preview="preview_kepada_alamat"><?php echo e($additionalData['kerja_praktek']['alamat_perusahaan'] ?? ''); ?></textarea>
                                </div>

                                <!-- Nota Dinas Fields -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Data Nota Dinas</h6>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi Nota</label>
                                    <input type="text" id="edit_prodi_nota" class="form-control" data-preview="preview_prodi_nota"
                                        value="<?php echo e($pengajuan->prodi->nama_prodi); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Nota</label>
                                    <input type="text" id="edit_nomor_nota" class="form-control" data-preview="preview_nomor_nota"
                                        value="B-53/IF-FSI/VI/2025">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Nota</label>
                                    <input type="text" id="edit_tanggal_nota" class="form-control" data-preview="preview_tanggal_nota"
                                        value="<?php echo e(date('d F Y')); ?>">
                                </div>

                                <!-- Periode KP -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Periode Kerja Praktik</h6>

                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                        <input type="date" id="edit_periode_mulai_date" class="form-control"
                                            value="<?php echo e($additionalData['kerja_praktek']['periode_mulai'] ?? ''); ?>">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                        <input type="date" id="edit_periode_selesai_date" class="form-control"
                                            value="<?php echo e($additionalData['kerja_praktek']['periode_selesai'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- Dynamic Mahasiswa KP -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">
                                    Data Mahasiswa KP
                                    <button type="button" onclick="addMahasiswaRowKP()" class="btn btn-sm btn-primary ml-2">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </h6>

                                <div id="mahasiswa-container-kp">
                                    <?php
                                        $mahasiswaKP = $additionalData['kerja_praktek']['mahasiswa_kp'] ?? [];
                                        if (empty($mahasiswaKP)) {
                                            $mahasiswaKP = [
                                                ['nama' => $pengajuan->nama_mahasiswa, 'nim' => $pengajuan->nim, 'prodi' => $pengajuan->prodi->nama_prodi]
                                            ];
                                        }
                                    ?>
                                    
                                    <?php $__currentLoopData = $mahasiswaKP; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mahasiswa-row-kp border border-gray-200 rounded p-2 mb-2" data-index="<?php echo e($index); ?>">
                                        <div class="flex justify-between items-center mb-1">
                                            <small class="text-gray-600">Mahasiswa <?php echo e($index + 1); ?></small>
                                            <?php if($index > 0): ?>
                                            <button type="button" onclick="removeMahasiswaRowKP(this)" class="text-red-500 text-xs">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <input type="text" id="edit_mhs_<?php echo e($index); ?>_nama" class="form-control mt-1" 
                                            placeholder="Nama Lengkap" value="<?php echo e($mhs['nama'] ?? ''); ?>" 
                                            data-preview="preview_mhs_kp_<?php echo e($index); ?>_nama">
                                        <input type="text" id="edit_mhs_<?php echo e($index); ?>_nim" class="form-control mt-1" 
                                            placeholder="NIM" value="<?php echo e($mhs['nim'] ?? ''); ?>" 
                                            data-preview="preview_mhs_kp_<?php echo e($index); ?>_nim">
                                        <input type="text" id="edit_mhs_<?php echo e($index); ?>_prodi" class="form-control mt-1" 
                                            placeholder="Program Studi" value="<?php echo e($mhs['prodi'] ?? $pengajuan->prodi->nama_prodi); ?>" 
                                            data-preview="preview_mhs_kp_<?php echo e($index); ?>_prodi">
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                

                                <!-- Tembusan -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Tembusan</h6>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi Tembusan</label>
                                    <input type="text" id="edit_prodi_tembusan" class="form-control" data-preview="preview_prodi_tembusan"
                                        value="<?php echo e($pengajuan->prodi->nama_prodi); ?>">
                                </div>
                                <?php endif; ?>
                                <!-- Data TA Section (untuk TA) -->
                                <?php if($pengajuan->jenisSurat->kode_surat === 'TA'): ?>
                                <h5 class="font-bold text-gray-900 mt-4 mb-2">
                                    <i class="fas fa-graduation-cap text-purple-500 mr-2"></i>
                                    Data Tugas Akhir
                                </h5>

                                <!-- Basic TA Fields -->
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sifat Surat</label>
                                    <input type="text" id="edit_sifat_ta" class="form-control" data-preview="preview_sifat_ta"
                                        value="Biasa">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran</label>
                                    <input type="text" id="edit_lampiran_ta" class="form-control" data-preview="preview_lampiran_ta"
                                        value="-">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Penerima</label>
                                    <input type="text" id="edit_kepada_jabatan_ta" class="form-control" data-preview="preview_kepada_jabatan_ta"
                                        value="Bapak/Ibu Pimpinan">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Penelitian</label>
                                    <input type="text" id="edit_lokasi_penelitian" class="form-control" data-preview="preview_lokasi_penelitian"
                                        value="<?php echo e($additionalData['tugas_akhir']['lokasi_penelitian'] ?? 'UNJANI'); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lokasi</label>
                                    <textarea id="edit_alamat_lokasi" class="form-control" rows="2" data-preview="preview_alamat_lokasi">Jl. Terusan Jenderal Sudirman, Cimahi</textarea>
                                </div>

                                <!-- Judul TA -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Judul Tugas Akhir</h6>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul TA</label>
                                    <textarea id="edit_judul_ta" class="form-control" rows="3" data-preview="preview_judul_ta"><?php echo e($additionalData['tugas_akhir']['judul_ta'] ?? ''); ?></textarea>
                                </div>

                                <!-- Pembimbing -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Dosen Pembimbing</h6>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1</label>
                                    <input type="text" id="edit_pembimbing1" class="form-control" data-preview="preview_pembimbing1"
                                        value="<?php echo e($additionalData['tugas_akhir']['dosen_pembimbing1'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2</label>
                                    <input type="text" id="edit_pembimbing2" class="form-control" data-preview="preview_pembimbing2"
                                        value="<?php echo e($additionalData['tugas_akhir']['dosen_pembimbing2'] ?? ''); ?>">
                                </div>

                                <!-- Dynamic Mahasiswa TA -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">
                                    Data Mahasiswa TA
                                    <button type="button" onclick="addMahasiswaRowTA()" class="btn btn-sm btn-primary ml-2">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </h6>

                                <div id="mahasiswa-container-ta">
                                    <?php
                                        $mahasiswaTA = $additionalData['tugas_akhir']['mahasiswa_ta'] ?? [];
                                        if (empty($mahasiswaTA)) {
                                            $mahasiswaTA = [
                                                ['nama' => $pengajuan->nama_mahasiswa, 'nim' => $pengajuan->nim, 'prodi' => $pengajuan->prodi->nama_prodi]
                                            ];
                                        }
                                    ?>
                                    
                                    <?php $__currentLoopData = $mahasiswaTA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mahasiswa-row-ta border border-gray-200 rounded p-2 mb-2" data-index="<?php echo e($index); ?>">
                                        <div class="flex justify-between items-center mb-1">
                                            <small class="text-gray-600">Mahasiswa <?php echo e($index + 1); ?></small>
                                            <?php if($index > 0): ?>
                                            <button type="button" onclick="removeMahasiswaRowTA(this)" class="text-red-500 text-xs">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <input type="text" id="edit_mhs_ta_<?php echo e($index); ?>_nama" class="form-control mt-1" 
                                            placeholder="Nama Lengkap" value="<?php echo e($mhs['nama'] ?? ''); ?>" 
                                            data-preview="preview_mhs_ta_<?php echo e($index); ?>_nama">
                                        <input type="text" id="edit_mhs_ta_<?php echo e($index); ?>_nim" class="form-control mt-1" 
                                            placeholder="NIM" value="<?php echo e($mhs['nim'] ?? ''); ?>" 
                                            data-preview="preview_mhs_ta_<?php echo e($index); ?>_nim">
                                        <input type="text" id="edit_mhs_ta_<?php echo e($index); ?>_prodi" class="form-control mt-1" 
                                            placeholder="Program Studi" value="<?php echo e($mhs['prodi'] ?? $pengajuan->prodi->nama_prodi); ?>" 
                                            data-preview="preview_mhs_ta_<?php echo e($index); ?>_prodi">
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <!-- Nota Dinas & Tembusan -->
                                <h6 class="font-bold text-gray-700 mt-3 mb-2">Data Nota Dinas</h6>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Nota</label>
                                    <input type="text" id="edit_nomor_nota_ta" class="form-control" data-preview="preview_nomor_nota_ta"
                                        value="B-54/<?php echo e(strtoupper($pengajuan->prodi->kode_prodi)); ?>-FSI/IX/2025">
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi Tembusan</label>
                                    <input type="text" id="edit_prodi_tembusan_ta" class="form-control" data-preview="preview_prodi_tembusan_ta"
                                        value="<?php echo e($pengajuan->prodi->nama_prodi); ?>">
                                </div>
                                <?php endif; ?>
                            <!-- Other Additional fields -->
                            <?php if(isset($additionalData['keperluan']) && !empty($additionalData['keperluan'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                                <textarea id="edit_keperluan" class="form-control" rows="2"><?php echo e($additionalData['keperluan']); ?></textarea>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(isset($additionalData['alamat']) && !empty($additionalData['alamat'])): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <textarea id="edit_alamat" class="form-control" rows="2"><?php echo e($additionalData['alamat']); ?></textarea>
                            </div>
                            <?php endif; ?>
                            
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
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="font-bold text-gray-900">
                                <i class="fas fa-cogs text-purple-500 mr-2"></i>
                                Aksi
                            </h4>
                        </div>
                        <div class="card-body space-y-2">
                            <?php if($canPrint && $pengajuan->status != 'sedang_ditandatangani'): ?>
                            <button onclick="printForSignature()" class="btn btn-warning w-full">
                                <i class="fas fa-print mr-2"></i>Cetak untuk TTD Fisik
                            </button>
                            <?php endif; ?>
                            
                            <?php if($pengajuan->status == 'sedang_ditandatangani'): ?>
                            <div class="alert alert-info">
                                <strong>Status:</strong> Sedang proses TTD fisik
                                <?php if($pengajuan->printed_at): ?>
                                    <br><small>Dicetak: <?php echo e($pengajuan->printed_at->format('d/m/Y H:i')); ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <button onclick="printForSignature()" class="btn btn-outline w-full">
                                <i class="fas fa-print mr-2"></i>Cetak Ulang
                            </button>
                            <?php endif; ?>
                            
                            <?php if($canEdit): ?>
                            <button onclick="rejectSurat()" class="btn btn-danger w-full">
                                <i class="fas fa-times mr-2"></i>Tolak Surat
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Upload Signed Document -->
                    <?php if($canUploadSigned): ?>
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
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT PANEL: A4 Preview -->
            <div class="lg:col-span-3">
                <div class="card">
                    <div class="card-header no-print">
                        <h3 class="font-bold text-gray-900">
                            <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                            Preview Surat <?php echo e($pengajuan->jenisSurat->nama_jenis); ?>

                        </h3>
                        <small class="text-gray-500">Klik field kuning untuk edit langsung</small>
                    </div>
                    
                    <div class="p-2 bg-gray-50">
                        <div class="a4-container">
                            <!-- KOP SURAT -->
                            <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px;">
                                <tr>
                                    <td style="width: 15%; text-align: center; vertical-align: middle;">
                                        <img src="<?php echo e(asset('images/logo-ykep.png')); ?>" style="width: 50px; height: 50px;" alt="Logo YKEP" onerror="this.style.display='none'">
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
                                        <img src="<?php echo e(asset('images/logo-unjani.png')); ?>" style="width: 55px; height: 55px;" alt="Logo UNJANI" onerror="this.style.display='none'">
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- DYNAMIC TITLE based on Jenis Surat -->
                            <div style="text-align: center; margin: 20px 0;">
                                <?php switch($pengajuan->jenisSurat->kode_surat):
                                    case ('MA'): ?>
                                        <h3 style="margin: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
                                            SURAT KETERANGAN MAHASISWA AKTIF
                                        </h3>
                                        <div style="margin-top: 8px; font-size: 12pt; font-weight: bold;">
                                    NOMOR: <span id="preview_nomor_surat" class="editable-field" data-input="edit_nomor_surat"><?php echo e($nomorSurat); ?><span class="tooltip-edit">Klik untuk edit</span></span>
                                    </div>
                                        <?php break; ?>
                                    <?php case ('TA'): ?>
                                        <?php break; ?>
                                <?php endswitch; ?>
                                

                            </div>
                            
                            <!-- DYNAMIC CONTENT based on Jenis Surat -->
                            <div id="dynamic-content">
                                <?php switch($pengajuan->jenisSurat->kode_surat):
                                    case ('MA'): ?>
                                        <div style="text-align: justify; font-size: 12pt; line-height: 1.5;">
                                <p style="margin-bottom: 10px;">Yang bertanda tangan di bawah ini :</p>
                                
                                <table style="width: 100%; margin: 10px 0;">
                                    <tr>
                                        <td style="width: 160px; padding: 2px 0;">Nama</td>
                                        <td style="width: 15px; text-align: center;">:</td>
                                        <td>
                                            <span id="preview_ttd_nama" class="editable-field" data-input="edit_ttd_nama">
                                                <?php echo e($penandatangan['nama']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Pangkat/Golongan</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_ttd_pangkat" class="editable-field" data-input="edit_ttd_pangkat">
                                                <?php echo e($penandatangan['pangkat'] ?: 'PENATA MUDA TK.I – III/B'); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Jabatan</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_ttd_jabatan" class="editable-field" data-input="edit_ttd_jabatan">
                                                <?php echo e($penandatangan['jabatan']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> FAKULTAS SAINS DAN INFORMATIKA UNJANI
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="margin: 10px 0;">Dengan ini menyatakan :</p>
                                
                                <table style="width: 100%; margin: 10px 0;">
                                    <tr>
                                        <td style="width: 160px; padding: 2px 0;">Nama</td>
                                        <td style="width: 15px; text-align: center;">:</td>
                                        <td>
                                            <strong style="text-transform: uppercase;">
                                                <span id="preview_nama_mahasiswa" class="editable-field" data-input="edit_nama_mahasiswa">
                                                    <?php echo e(strtoupper($pengajuan->nama_mahasiswa)); ?>

                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                </span>
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">N I M</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_nim" class="editable-field" data-input="edit_nim">
                                                <?php echo e($pengajuan->nim); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Program Studi</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_prodi" class="editable-field" data-input="edit_prodi">
                                                <?php echo e($pengajuan->prodi->nama_prodi); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Program</td>
                                        <td style="text-align: center;">:</td>
                                        <td>S1</td>
                                    </tr>
                                </table>
                                
                                <?php if(isset($additionalData['orang_tua']) && !empty($additionalData['orang_tua'])): ?>
                                <p style="margin: 10px 0;">Nama Orang Tua/Wali dari Mahasiswa tersebut adalah :</p>
                                
                                <table style="width: 100%; margin: 10px 0;">
                                    <?php if(isset($additionalData['orang_tua']['nama']) && !empty($additionalData['orang_tua']['nama'])): ?>
                                    <tr>
                                        <td style="width: 160px; padding: 2px 0;">Nama</td>
                                        <td style="width: 15px; text-align: center;">:</td>
                                        <td style="text-transform: uppercase;">
                                            <span id="preview_nama" class="editable-field" data-input="edit_nama">
                                                <?php echo e(strtoupper($additionalData['orang_tua']['nama'])); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['tempat_lahir']) || isset($additionalData['orang_tua']['tanggal_lahir'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0;">Tempat/Tanggal Lahir</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <?php if(isset($additionalData['orang_tua']['tempat_lahir'])): ?>
                                            <span id="preview_tempat_lahir" class="editable-field" data-input="edit_tempat_lahir">
                                                <?php echo e($additionalData['orang_tua']['tempat_lahir']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                            <?php endif; ?>
                                            <?php if(isset($additionalData['orang_tua']['tanggal_lahir'])): ?>
                                            / <span id="preview_tanggal_lahir" class="editable-field" data-input="edit_tanggal_lahir">
                                                <?php echo e($additionalData['orang_tua']['tanggal_lahir']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['nip']) && !empty($additionalData['orang_tua']['nip'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0;">NIP</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_nip" class="editable-field" data-input="edit_nip">
                                                <?php echo e($additionalData['orang_tua']['nip']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['pangkat_golongan']) && !empty($additionalData['orang_tua']['pangkat_golongan'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0;">Pangkat/Golongan</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_pangkat_golongan" class="editable-field" data-input="edit_pangkat_golongan">
                                                <?php echo e($additionalData['orang_tua']['pangkat_golongan']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['pekerjaan']) && !empty($additionalData['orang_tua']['pekerjaan'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0;">Pekerjaan</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_pekerjaan" class="editable-field" data-input="edit_pekerjaan">
                                                <?php echo e($additionalData['orang_tua']['pekerjaan']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['instansi']) && !empty($additionalData['orang_tua']['instansi'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0;">Instansi</td>
                                        <td style="text-align: center;">:</td>
                                        <td>
                                            <span id="preview_instansi" class="editable-field" data-input="edit_instansi">
                                                <?php echo e($additionalData['orang_tua']['instansi']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['alamat_instansi']) && !empty($additionalData['orang_tua']['alamat_instansi'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0; vertical-align: top;">Alamat Kantor</td>
                                        <td style="text-align: center; vertical-align: top;">:</td>
                                        <td>
                                            <span id="preview_alamat_instansi" class="editable-field" data-input="edit_alamat_instansi">
                                                <?php echo e($additionalData['orang_tua']['alamat_instansi']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($additionalData['orang_tua']['alamat_rumah']) && !empty($additionalData['orang_tua']['alamat_rumah'])): ?>
                                    <tr>
                                        <td style="padding: 2px 0; vertical-align: top;">Alamat Rumah</td>
                                        <td style="text-align: center; vertical-align: top;">:</td>
                                        <td>
                                            <span id="preview_alamat_rumah" class="editable-field" data-input="edit_alamat_rumah">
                                                <?php echo e($additionalData['orang_tua']['alamat_rumah']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                                <?php endif; ?>
                                
                                <p style="margin: 15px 0;">
                                    Merupakan Mahasiswa Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani dan 
                                    <strong>
                                        <span id="preview_status_mahasiswa" class="editable-field" data-input="edit_status_mahasiswa">
                                            <?php echo e($additionalData['status_mahasiswa'] ?? 'Aktif'); ?>

                                            <span class="tooltip-edit">Klik untuk edit</span>
                                        </span>
                                    </strong>
                                    pada Semester 
                                    <span id="preview_semester" class="editable-field" data-input="edit_semester">
                                        <?php echo e($additionalData['semester'] ?? 'Ganjil'); ?>

                                        <span class="tooltip-edit">Klik untuk edit</span>
                                    </span>
                                    Tahun Akademik 
                                    <span id="preview_tahun_akademik" class="editable-field" data-input="edit_tahun_akademik">
                                        <?php echo e($additionalData['tahun_akademik'] ?? '2024/2025'); ?>

                                        <span class="tooltip-edit">Klik untuk edit</span>
                                    </span>.
                                </p>
                                
                                <p style="margin: 15px 0;">Demikian surat pernyataan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
                            </div>

                            <!-- TANDA TANGAN -->
                            <div style="margin-top: 40px;">
                                <div style="text-align: right;">
                                    <div style="display: inline-block; text-align: center; width: 250px;">
                                        <p style="margin: 5px 0;">
                                            Cimahi, <span id="preview_tanggal" class="editable-field" data-input="edit_tanggal_surat">
                                                <?php echo e($tanggalSurat); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </p>
                                        <p style="margin: 5px 0;">An. Dekan</p>
                                        <p style="margin: 5px 0;">
                                            <span id="preview_ttd_jabatan_bottom" class="editable-field" data-input="edit_ttd_jabatan">
                                                <?php echo e($penandatangan['jabatan']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> – FSI
                                        </p>
                                        
                                        <!-- Signature Space -->
                                        <div style="margin: 60px 0; position: relative;">
                                            <?php if(isset($ttd_elektronik) && $ttd_elektronik): ?>
                                                <img src="<?php echo e($ttd_elektronik); ?>" style="height: 60px;" alt="TTD Elektronik">
                                            <?php else: ?>
                                                <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                                                    <span style="font-weight: bold; color: #666;">TTD ELEKTRONIK</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p style="margin: 5px 0; font-weight: bold; text-decoration: underline;">
                                            <span id="preview_ttd_nama_bottom" class="editable-field" data-input="edit_ttd_nama">
                                                <?php echo e($penandatangan['nama']); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </p>
                                        <p style="margin: 5px 0;">
                                            NID. <span id="preview_ttd_nid" class="editable-field" data-input="edit_ttd_nid">
                                                <?php echo e($penandatangan['nid'] ?: '4121 758 78'); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php break; ?>
                            case('KP')
                                <!-- KP Header Section -->
                                <div style="display: flex; margin-bottom: 20px;">
                                    <!-- Left: Letter Details -->
                                    <div style="flex: 1;">
                                        <table style="width: 100%; font-size: 12pt;">
                                            <tr>
                                                <td style="width: 80px; padding: 2px 5px;">Nomor</td>
                                                <td style="width: 20px;">:</td>
                                                <td>
                                                    <span id="preview_nomor_surat_kp" class="editable-field" data-input="edit_nomor_surat">
                                                        <?php echo e($nomorSurat); ?>

                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 5px;">Sifat</td>
                                                <td>:</td>
                                                <td>
                                                    <span id="preview_sifat_kp" class="editable-field" data-input="edit_sifat">
                                                        Biasa
                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 5px;">Lampiran</td>
                                                <td>:</td>
                                                <td>
                                                    <span id="preview_lampiran_kp" class="editable-field" data-input="edit_lampiran">
                                                        -
                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 2px 5px;">Perihal</td>
                                                <td>:</td>
                                                <td>Permohonan Izin Melaksanakan Kerja Praktik</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <!-- Right: Date and Recipient -->
                                    <div style="flex: 1; padding-left: 50px;">
                                        <!-- Date -->
                                        <div style="text-align: right; margin-bottom: 20px;">
                                            <span id="preview_tempat_tanggal_kp" class="editable-field" data-input="edit_tanggal_surat">
                                                Cimahi, <?php echo e($tanggalSurat); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </div>
                                        
                                        <!-- Recipient Box -->
                                        <div style="margin-top: 20px;">
                                            <p style="margin: 0;">Kepada :</p>
                                            <p style="margin: 5px 0;">
                                                <strong>Yth. <span id="preview_kepada_jabatan" class="editable-field" data-input="edit_kepada_jabatan">
                                                    Bapak/Ibu Pimpinan
                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                </span></strong>
                                            </p>
                                            <p style="margin: 5px 0;">
                                                <span id="preview_kepada_nama" class="editable-field" data-input="edit_kepada_nama">
                                                    <?php echo e($additionalData['kerja_praktek']['nama_perusahaan'] ?? 'PT. [Nama Perusahaan]'); ?>

                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                </span>
                                            </p>
                                            <p style="margin: 5px 0;">
                                                <span id="preview_kepada_alamat" class="editable-field" data-input="edit_kepada_alamat">
                                                    <?php echo e($additionalData['kerja_praktek']['alamat_perusahaan'] ?? '[Alamat Perusahaan]'); ?>

                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- KP Letter Body -->
                                <div style="text-align: justify; font-size: 12pt; line-height: 1.5; margin: 20px 0;">
                                    <p style="margin-bottom: 15px;">Dengan hormat,</p>
                                    
                                    <ol style="margin-left: 20px; padding-left: 0;">
                                        <li style="margin-bottom: 15px;">
                                            Dasar: Nota Dinas Ketua Program Studi 
                                            <span id="preview_prodi_nota" class="editable-field" data-input="edit_prodi_nota">
                                                <?php echo e($pengajuan->prodi->nama_prodi); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> 
                                            Nomor: <span id="preview_nomor_nota" class="editable-field" data-input="edit_nomor_nota">
                                                B-53/IF-FSI/VI/2025
                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> 
                                            tanggal <span id="preview_tanggal_nota" class="editable-field" data-input="edit_tanggal_nota">
                                                <?php echo e(date('d F Y')); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> 
                                            perihal Surat Pengantar.
                                        </li>
                                        
                                        <li style="margin-bottom: 15px;">
                                            Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan izin untuk melaksanakan Kerja Praktik pada 
                                            <span id="preview_periode_kp" class="editable-field" data-input="edit_periode_kp">
                                                <?php echo e(isset($additionalData['kerja_praktek']['periode_mulai']) && isset($additionalData['kerja_praktek']['periode_selesai']) 
                                                    ? date('d F Y', strtotime($additionalData['kerja_praktek']['periode_mulai'])) . ' s.d. ' . date('d F Y', strtotime($additionalData['kerja_praktek']['periode_selesai']))
                                                    : '[Tanggal Mulai] s.d. [Tanggal Selesai]'); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span> 
                                            di Instansi/Perusahaan yang Bapak/Ibu Pimpin kepada mahasiswa sebagai berikut :
                                            
                                            <!-- Dynamic Student Table -->
                                            <table id="mahasiswa-table-kp" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                                                <thead>
                                                    <tr>
                                                        <th style="border: 1px solid #000; padding: 8px; width: 50px; text-align: center; background: #f0f0f0;">No</th>
                                                        <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0;">Nama</th>
                                                        <th style="border: 1px solid #000; padding: 8px; width: 120px; text-align: center; background: #f0f0f0;">NIM</th>
                                                        <th style="border: 1px solid #000; padding: 8px; text-align: center; background: #f0f0f0;">Program Studi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="mahasiswa-table-body-kp">
                                                    <?php
                                                        $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'] ?? [];
                                                        if (empty($mahasiswaList)) {
                                                            $mahasiswaList = [
                                                                ['nama' => $pengajuan->nama_mahasiswa, 'nim' => $pengajuan->nim, 'prodi' => $pengajuan->prodi->nama_prodi]
                                                            ];
                                                        }
                                                    ?>
                                                    
                                                    <?php $__currentLoopData = $mahasiswaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td style="border: 1px solid #000; padding: 8px; text-align: center;"><?php echo e($index + 1); ?></td>
                                                        <td style="border: 1px solid #000; padding: 8px;">
                                                            <span id="preview_mhs_kp_<?php echo e($index); ?>_nama" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_nama">
                                                                <?php echo e($mhs['nama'] ?? ''); ?>

                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span>
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                                            <span id="preview_mhs_kp_<?php echo e($index); ?>_nim" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_nim">
                                                                <?php echo e($mhs['nim'] ?? ''); ?>

                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span>
                                                        </td>
                                                        <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                                            <span id="preview_mhs_kp_<?php echo e($index); ?>_prodi" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_prodi">
                                                                <?php echo e($mhs['prodi'] ?? $pengajuan->prodi->nama_prodi); ?>

                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </li>
                                        
                                        <li style="margin-bottom: 15px;">
                                            Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.
                                        </li>
                                    </ol>
                                </div>

                                <!-- Signature and CC Section -->
                                <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                                    <!-- Left: CC/Tembusan -->
                                    <div style="flex: 1;">
                                        <p style="text-decoration: underline; margin-bottom: 5px; font-size: 11pt;">Tembusan Yth :</p>
                                        <ol style="margin: 5px 0; padding-left: 20px; font-size: 11pt;">
                                            <li>Dekan F.SI (sebagai laporan)</li>
                                            <li style="text-decoration: underline;">
                                                Ketua Program Studi 
                                                <span id="preview_prodi_tembusan" class="editable-field" data-input="edit_prodi_tembusan">
                                                    <?php echo e($pengajuan->prodi->nama_prodi); ?>

                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                </span> 
                                                FSI Unjani
                                            </li>
                                        </ol>
                                    </div>
                                    
                                    <!-- Right: Signature -->
                                    <div style="flex: 1; text-align: center;">
                                        <p>a.n. Dekan</p>
                                        <p>Wakil Dekan I</p>
                                        
                                        <?php if(isset($ttd_elektronik) && $ttd_elektronik): ?>
                                        <div style="margin: 20px 0;">
                                            <img src="<?php echo e($ttd_elektronik); ?>" style="height: 60px;" alt="TTD Elektronik">
                                        </div>
                                        <?php else: ?>
                                        <div style="margin: 60px 0;">
                                            <p style="font-weight: bold;">TTD ELEKTRONIK</p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <p style="margin-top: 20px; font-weight: bold; text-decoration: underline;">
                                            <span id="preview_ttd_nama_kp" class="editable-field" data-input="edit_ttd_nama">
                                                <?php echo e($penandatangan['nama'] ?? 'AGUS KOMARUDIN, S.Kom., M.T.'); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </p>
                                        <p style="margin-top: 5px;">
                                            NID. <span id="preview_ttd_nid_kp" class="editable-field" data-input="edit_ttd_nid">
                                                <?php echo e($penandatangan['nid'] ?? '41217587'); ?>

                                                <span class="tooltip-edit">Klik untuk edit</span>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <?php break; ?>
                                <?php case ('TA'): ?>
                                        <!-- TA Header Section - LAYOUT 2 KOLOM -->
                                        <table style="width: 100%; margin-bottom: 20px;">
                                            <tr>
                                                <!-- Kolom Kiri: Detail Surat -->
                                                <td style="width: 50%; vertical-align: top;">
                                                    <table style="width: 100%; font-size: 11pt;">
                                                        <tr>
                                                            <td style="width: 80px; padding: 2px 5px;">Nomor</td>
                                                            <td style="width: 20px;">:</td>
                                                            <td>
                                                                <span id="preview_nomor_surat_ta" class="editable-field" data-input="edit_nomor_surat">
                                                                    <?php echo e($nomorSurat); ?>

                                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 2px 5px;">Sifat</td>
                                                            <td>:</td>
                                                            <td>
                                                                <span id="preview_sifat_ta" class="editable-field" data-input="edit_sifat_ta">
                                                                    Biasa
                                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 2px 5px;">Lampiran</td>
                                                            <td>:</td>
                                                            <td>
                                                                <span id="preview_lampiran_ta" class="editable-field" data-input="edit_lampiran_ta">
                                                                    -
                                                                    <span class="tooltip-edit">Klik untuk edit</span>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 2px 5px;">Perihal</td>
                                                            <td>:</td>
                                                            <td>Permohonan Izin Penelitian Tugas Akhir</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                
                                                <!-- Kolom Kanan: Tanggal dan Penerima -->
                                                <td style="width: 50%; vertical-align: top; padding-left: 50px;">
                                                    <div style="text-align: right; margin-bottom: 20px;">
                                                        <span id="preview_tempat_tanggal_ta" class="editable-field" data-input="edit_tanggal_surat">
                                                            Cimahi, <?php echo e($tanggalSurat); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span>
                                                    </div>
                                                    
                                                    <div style="margin-top: 20px;">
                                                        <p style="margin: 0;">Kepada :</p>
                                                        <p style="margin: 5px 0;">
                                                            <strong>Yth. <span id="preview_kepada_jabatan_ta" class="editable-field" data-input="edit_kepada_jabatan_ta">
                                                                Bapak/Ibu Pimpinan
                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span></strong>
                                                        </p>
                                                        <p style="margin: 5px 0;">
                                                            <span id="preview_lokasi_penelitian" class="editable-field" data-input="edit_lokasi_penelitian">
                                                                <?php echo e($additionalData['tugas_akhir']['lokasi_penelitian'] ?? 'UNJANI'); ?>

                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span>
                                                        </p>
                                                        <p style="margin: 5px 0;">
                                                            <span id="preview_alamat_lokasi" class="editable-field" data-input="edit_alamat_lokasi">
                                                                Jl. Terusan Jenderal Sudirman, Cimahi
                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- ISI SURAT TA -->
                                        <div style="text-align: justify; font-size: 11pt; line-height: 1.3; margin: 20px 0;">
                                            <p style="margin-bottom: 15px;">Dengan hormat,</p>
                                            
                                            <ol style="margin-left: 20px; padding-left: 0;">
                                                <li style="margin-bottom: 15px;">
                                                    Dasar: Nota Dinas Ketua Program Studi <?php echo e($pengajuan->prodi->nama_prodi); ?> 
                                                    Nomor: <span id="preview_nomor_nota_ta" class="editable-field" data-input="edit_nomor_nota_ta">
                                                        B-54/<?php echo e(strtoupper($pengajuan->prodi->kode_prodi)); ?>-FSI/IX/2025
                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                    </span> 
                                                    perihal Surat Pengantar.
                                                </li>
                                                
                                                <li style="margin-bottom: 15px;">
                                                    Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan izin untuk melaksanakan penelitian Tugas Akhir di instansi yang Bapak/Ibu pimpin kepada mahasiswa sebagai berikut :
                                                    
                                                    <!-- Tabel Mahasiswa TA -->
                                                    <table id="mahasiswa-table-ta" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                                                        <thead>
                                                            <tr>
                                                                <th style="border: 1px solid #000; padding: 8px; width: 50px; text-align: center; background: #f0f0f0;">No</th>
                                                                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: left;">Nama</th>
                                                                <th style="border: 1px solid #000; padding: 8px; width: 120px; text-align: center; background: #f0f0f0;">NIM</th>
                                                                <th style="border: 1px solid #000; padding: 8px; text-align: center; background: #f0f0f0;">Program Studi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="mahasiswa-table-body-ta">
                                                            <?php
                                                                $mahasiswaTA = $additionalData['tugas_akhir']['mahasiswa_ta'] ?? [];
                                                                if (empty($mahasiswaTA)) {
                                                                    $mahasiswaTA = [
                                                                        ['nama' => $pengajuan->nama_mahasiswa, 'nim' => $pengajuan->nim, 'prodi' => $pengajuan->prodi->nama_prodi]
                                                                    ];
                                                                }
                                                            ?>
                                                            
                                                            <?php $__currentLoopData = $mahasiswaTA; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td style="border: 1px solid #000; padding: 8px; text-align: center;"><?php echo e($index + 1); ?></td>
                                                                <td style="border: 1px solid #000; padding: 8px;">
                                                                    <span id="preview_mhs_ta_<?php echo e($index); ?>_nama" class="editable-field" data-input="edit_mhs_ta_<?php echo e($index); ?>_nama">
                                                                        <?php echo e($mhs['nama'] ?? ''); ?>

                                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                                    </span>
                                                                </td>
                                                                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                                                    <span id="preview_mhs_ta_<?php echo e($index); ?>_nim" class="editable-field" data-input="edit_mhs_ta_<?php echo e($index); ?>_nim">
                                                                        <?php echo e($mhs['nim'] ?? ''); ?>

                                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                                    </span>
                                                                </td>
                                                                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                                                    <span id="preview_mhs_ta_<?php echo e($index); ?>_prodi" class="editable-field" data-input="edit_mhs_ta_<?php echo e($index); ?>_prodi">
                                                                        <?php echo e($mhs['prodi'] ?? $pengajuan->prodi->nama_prodi); ?>

                                                                        <span class="tooltip-edit">Klik untuk edit</span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <div style="margin-top: 15px;">
                                                        <strong>Judul Tugas Akhir:</strong><br>
                                                        "<span id="preview_judul_ta" class="editable-field" data-input="edit_judul_ta">
                                                            <?php echo e($additionalData['tugas_akhir']['judul_ta'] ?? '[Judul TA]'); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span>"
                                                    </div>
                                                    
                                                    <div style="margin-top: 10px;">
                                                        <strong>Dosen Pembimbing:</strong><br>
                                                        1. <span id="preview_pembimbing1" class="editable-field" data-input="edit_pembimbing1">
                                                            <?php echo e($additionalData['tugas_akhir']['dosen_pembimbing1'] ?? '-'); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span><br>
                                                        2. <span id="preview_pembimbing2" class="editable-field" data-input="edit_pembimbing2">
                                                            <?php echo e($additionalData['tugas_akhir']['dosen_pembimbing2'] ?? '-'); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span>
                                                    </div>
                                                </li>
                                                
                                                <li style="margin-bottom: 15px;">
                                                    Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.
                                                </li>
                                            </ol>
                                        </div>

                                        <!-- TANDA TANGAN DAN TEMBUSAN - LAYOUT 2 KOLOM -->
                                        <table style="width: 100%; margin-top: 40px;">
                                            <tr>
                                                <!-- Kolom Kiri: Tembusan -->
                                                <td style="width: 50%; vertical-align: top;">
                                                    <p style="text-decoration: underline; margin-bottom: 5px; font-size: 11pt;">Tembusan Yth :</p>
                                                    <ol style="margin: 5px 0; padding-left: 20px; font-size: 11pt;">
                                                        <li>Dekan F.SI (sebagai laporan)</li>
                                                        <li style="text-decoration: underline;">
                                                            Ketua Program Studi 
                                                            <span id="preview_prodi_tembusan_ta" class="editable-field" data-input="edit_prodi_tembusan_ta">
                                                                <?php echo e($pengajuan->prodi->nama_prodi); ?>

                                                                <span class="tooltip-edit">Klik untuk edit</span>
                                                            </span> 
                                                            FSI Unjani
                                                        </li>
                                                    </ol>
                                                </td>
                                                
                                                <!-- Kolom Kanan: Tanda Tangan -->
                                                <td style="width: 50%; text-align: center; vertical-align: top;">
                                                    <p style="margin: 5px 0;">a.n. Dekan</p>
                                                    <p style="margin: 5px 0;">Wakil Dekan I</p>
                                                    
                                                    <?php if(isset($ttd_elektronik) && $ttd_elektronik): ?>
                                                    <div style="margin: 20px 0;">
                                                        <img src="<?php echo e($ttd_elektronik); ?>" style="height: 60px;" alt="TTD Elektronik">
                                                    </div>
                                                    <?php else: ?>
                                                    <div style="margin: 60px 0; height: 60px;">
                                                        <!-- Space TTD -->
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <p style="margin: 5px 0; font-weight: bold; text-decoration: underline;">
                                                        <span id="preview_ttd_nama_ta" class="editable-field" data-input="edit_ttd_nama">
                                                            <?php echo e($penandatangan['nama']); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span>
                                                    </p>
                                                    <p style="margin: 5px 0;">
                                                        NID. <span id="preview_ttd_nid_ta" class="editable-field" data-input="edit_ttd_nid">
                                                            <?php echo e($penandatangan['nid']); ?>

                                                            <span class="tooltip-edit">Klik untuk edit</span>
                                                        </span>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        <?php break; ?>

                                        <?php break; ?>
                                    <?php default: ?>
                                        <div style="text-align: center; color: #666; padding: 40px;">
                                            Template untuk <?php echo e($pengajuan->jenisSurat->nama_jenis); ?> belum tersedia
                                        </div>
                                <?php endswitch; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ========== GLOBAL FUNCTIONS (accessible dari onclick) ==========

function printForSignature() {
    if (confirm('Print surat dan ubah status menjadi "Sedang Ditandatangani"?\n\nPastikan Anda sudah menyimpan perubahan terlebih dahulu.')) {
        window.location.href = `<?php echo e(route('fakultas.surat.fsi.print', $pengajuan->id)); ?>`;
    }
}

function updateMahasiswaTableKP() {
    const tableBody = document.getElementById('mahasiswa-table-body-kp');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    const mahasiswaRows = document.querySelectorAll('.mahasiswa-row-kp');
    let rowCount = 0;
    
    mahasiswaRows.forEach((row, index) => {
        const dataIndex = row.getAttribute('data-index');
        const namaInput = document.getElementById(`edit_mhs_${dataIndex}_nama`);
        const nimInput = document.getElementById(`edit_mhs_${dataIndex}_nim`);
        const prodiInput = document.getElementById(`edit_mhs_${dataIndex}_prodi`);
        
        const nama = namaInput ? namaInput.value.trim() : '';
        const nim = nimInput ? nimInput.value.trim() : '';
        const prodi = prodiInput ? prodiInput.value.trim() : '<?php echo e($pengajuan->prodi->nama_prodi); ?>';
        
        if (nama || nim) {
            rowCount++;
            const tableRow = document.createElement('tr');
            tableRow.innerHTML = `
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${rowCount}</td>
                <td style="border: 1px solid #000; padding: 8px;">
                    <span id="preview_mhs_kp_${dataIndex}_nama" class="editable-field" data-input="edit_mhs_${dataIndex}_nama">
                        ${nama || '-'}
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <span id="preview_mhs_kp_${dataIndex}_nim" class="editable-field" data-input="edit_mhs_${dataIndex}_nim">
                        ${nim || '-'}
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <span id="preview_mhs_kp_${dataIndex}_prodi" class="editable-field" data-input="edit_mhs_${dataIndex}_prodi">
                        ${prodi}
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
            `;
            tableBody.appendChild(tableRow);
        }
    });
    
    attachEditableFieldEvents();
}

function addMahasiswaRowKP() {
    const container = document.getElementById('mahasiswa-container-kp');
    const existingRows = container.querySelectorAll('.mahasiswa-row-kp');
    const newIndex = existingRows.length;
    
    const newRow = document.createElement('div');
    newRow.className = 'mahasiswa-row-kp border border-gray-200 rounded p-2 mb-2';
    newRow.setAttribute('data-index', newIndex);
    
    newRow.innerHTML = `
        <div class="flex justify-between items-center mb-1">
            <small class="text-gray-600">Mahasiswa ${newIndex + 1}</small>
            ${newIndex > 0 ? '<button type="button" onclick="removeMahasiswaRowKP(this)" class="text-red-500 text-xs"><i class="fas fa-times"></i> Hapus</button>' : ''}
        </div>
        <input type="text" id="edit_mhs_${newIndex}_nama" class="form-control mt-1" 
               placeholder="Nama Lengkap" data-preview="preview_mhs_kp_${newIndex}_nama">
        <input type="text" id="edit_mhs_${newIndex}_nim" class="form-control mt-1" 
               placeholder="NIM" data-preview="preview_mhs_kp_${newIndex}_nim">
        <input type="text" id="edit_mhs_${newIndex}_prodi" class="form-control mt-1" 
               placeholder="Program Studi" value="<?php echo e($pengajuan->prodi->nama_prodi); ?>" 
               data-preview="preview_mhs_kp_${newIndex}_prodi">
    `;
    
    container.appendChild(newRow);
    
    // Add event listeners
    newRow.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => updateMahasiswaTableKP());
    });
    
    updateMahasiswaTableKP();
}

function removeMahasiswaRowKP(button) {
    const row = button.closest('.mahasiswa-row-kp');
    const allRows = document.querySelectorAll('.mahasiswa-row-kp');
    
    if (allRows.length > 1) {
        row.remove();
        updateMahasiswaTableKP();
    } else {
        alert('Minimal harus ada 1 mahasiswa');
    }
}

function attachEditableFieldEvents() {
    document.querySelectorAll('.editable-field').forEach(field => {
        field.removeEventListener('click', handleEditableClick);
        field.addEventListener('click', handleEditableClick);
    });
}

function handleEditableClick() {
    const inputId = this.getAttribute('data-input');
    if (!inputId) return;
    
    const inputEl = document.getElementById(inputId);
    if (!inputEl) return;
    
    document.querySelectorAll('.editable-field').forEach(f => f.classList.remove('active'));
    document.querySelectorAll('.form-control').forEach(f => f.classList.remove('highlight'));
    
    this.classList.add('active');
    inputEl.classList.add('highlight');
    inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    setTimeout(() => {
        inputEl.focus();
        inputEl.select();
    }, 300);
}

function saveAllChanges() {
    const saveBtn = document.getElementById('saveBtn');
    const btnText = saveBtn.querySelector('.btn-text');
    const loadingText = saveBtn.querySelector('.loading-text');
    
    saveBtn.disabled = true;
    btnText.classList.add('hidden');
    loadingText.classList.remove('hidden');
    
    // Collect data
    const orangTuaData = {};
    ['nama', 'nama_ayah', 'nama_ibu', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'nip', 'pangkat_golongan', 'instansi', 'alamat_instansi', 'alamat_rumah'].forEach(field => {
        const el = document.getElementById(`edit_${field}`);
        if (el && el.value) orangTuaData[field] = el.value;
    });
    
    const kpData = {};
    const rows = document.querySelectorAll('.mahasiswa-row-kp');
    kpData.mahasiswa_kp = [];
    
    rows.forEach((row, i) => {
        const dataIndex = row.getAttribute('data-index');
        const nama = document.getElementById(`edit_mhs_${dataIndex}_nama`)?.value.trim();
        const nim = document.getElementById(`edit_mhs_${dataIndex}_nim`)?.value.trim();
        const prodi = document.getElementById(`edit_mhs_${dataIndex}_prodi`)?.value.trim();
        
        if (nama) {
            kpData.mahasiswa_kp.push({ nama, nim, prodi });
        }
    });
    
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
            status_mahasiswa: document.getElementById('edit_status_mahasiswa')?.value || 'Aktif',
            orang_tua: orangTuaData,
            kerja_praktek: kpData
        }
    };
    
    fetch(`<?php echo e(route('fakultas.surat.fsi.save-edits', $pengajuan->id)); ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        saveBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        
        if (result.success) {
            alert('Perubahan berhasil disimpan!');
        } else {
            alert(result.message || 'Gagal menyimpan');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        btnText.classList.remove('hidden');
        loadingText.classList.add('hidden');
        alert('Error: ' + error.message);
    });
}

function uploadSignedDocument() {
    const url = document.getElementById('signed_url').value.trim();
    if (!url || !url.startsWith('http')) {
        alert('Masukkan URL valid');
        return;
    }
    
    if (confirm('Selesaikan surat dan kirim ke mahasiswa?')) {
        const btn = document.getElementById('uploadBtn');
        btn.disabled = true;
        
        fetch(`<?php echo e(route('fakultas.surat.fsi.upload-signed', $pengajuan->id)); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({
                signed_url: url,
                notes: document.getElementById('signed_notes').value.trim()
            })
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                alert('Surat diselesaikan!');
                window.location.href = '<?php echo e(route("fakultas.surat.index")); ?>';
            } else {
                alert(result.message);
                btn.disabled = false;
            }
        });
    }
}

function rejectSurat() {
    const reason = prompt('Alasan penolakan:');
    if (!reason) return;
    
    if (confirm('Tolak surat ini?')) {
        fetch(`<?php echo e(route('fakultas.surat.fsi.reject', $pengajuan->id)); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
        .then(r => r.json())
        .then(result => {
            alert(result.message);
            if (result.success) window.location.href = '<?php echo e(route("fakultas.surat.index")); ?>';
        });
    }
}

// TA Functions
function addMahasiswaRowTA() {
    const container = document.getElementById('mahasiswa-container-ta');
    const existingRows = container.querySelectorAll('.mahasiswa-row-ta');
    const newIndex = existingRows.length;
    
    const newRow = document.createElement('div');
    newRow.className = 'mahasiswa-row-ta border border-gray-200 rounded p-2 mb-2';
    newRow.setAttribute('data-index', newIndex);
    
    newRow.innerHTML = `
        <div class="flex justify-between items-center mb-1">
            <small class="text-gray-600">Mahasiswa ${newIndex + 1}</small>
            ${newIndex > 0 ? '<button type="button" onclick="removeMahasiswaRowTA(this)" class="text-red-500 text-xs"><i class="fas fa-times"></i> Hapus</button>' : ''}
        </div>
        <input type="text" id="edit_mhs_ta_${newIndex}_nama" class="form-control mt-1" placeholder="Nama Lengkap" data-preview="preview_mhs_ta_${newIndex}_nama">
        <input type="text" id="edit_mhs_ta_${newIndex}_nim" class="form-control mt-1" placeholder="NIM" data-preview="preview_mhs_ta_${newIndex}_nim">
        <input type="text" id="edit_mhs_ta_${newIndex}_prodi" class="form-control mt-1" placeholder="Program Studi" value="<?php echo e($pengajuan->prodi->nama_prodi); ?>" data-preview="preview_mhs_ta_${newIndex}_prodi">
    `;
    
    container.appendChild(newRow);
    newRow.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => updateMahasiswaTableTA());
    });
    updateMahasiswaTableTA();
}

function removeMahasiswaRowTA(button) {
    const row = button.closest('.mahasiswa-row-ta');
    const allRows = document.querySelectorAll('.mahasiswa-row-ta');
    if (allRows.length > 1) {
        row.remove();
        updateMahasiswaTableTA();
    } else {
        alert('Minimal harus ada 1 mahasiswa');
    }
}

function updateMahasiswaTableTA() {
    const tableBody = document.getElementById('mahasiswa-table-body-ta');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    const rows = document.querySelectorAll('.mahasiswa-row-ta');
    
    rows.forEach((row, index) => {
        const dataIndex = row.getAttribute('data-index');
        const nama = document.getElementById(`edit_mhs_ta_${dataIndex}_nama`)?.value.trim();
        const nim = document.getElementById(`edit_mhs_ta_${dataIndex}_nim`)?.value.trim();
        const prodi = document.getElementById(`edit_mhs_ta_${dataIndex}_prodi`)?.value.trim();
        
        if (nama || nim) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">${index + 1}</td>
                <td style="border: 1px solid #000; padding: 8px;">
                    <span id="preview_mhs_ta_${dataIndex}_nama" class="editable-field" data-input="edit_mhs_ta_${dataIndex}_nama">
                        ${nama || '-'} <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <span id="preview_mhs_ta_${dataIndex}_nim" class="editable-field" data-input="edit_mhs_ta_${dataIndex}_nim">
                        ${nim || '-'} <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                    <span id="preview_mhs_ta_${dataIndex}_prodi" class="editable-field" data-input="edit_mhs_ta_${dataIndex}_prodi">
                        ${prodi} <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
            `;
            tableBody.appendChild(tr);
        }
    });
    
    attachEditableFieldEvents();
}

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Real-time preview
    document.querySelectorAll('input[data-preview], textarea[data-preview]').forEach(input => {
        input.addEventListener('input', function() {
            const previewIds = this.getAttribute('data-preview');
            if (!previewIds) return;
            
            previewIds.split(',').forEach(previewId => {
                const previewEl = document.getElementById(previewId.trim());
                if (previewEl && previewEl.childNodes[0]) {
                    previewEl.childNodes[0].textContent = this.value || '-';
                }
            });
        });
    });
    
    attachEditableFieldEvents();
});

</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/surat/fsi/preview-editable.blade.php ENDPATH**/ ?>