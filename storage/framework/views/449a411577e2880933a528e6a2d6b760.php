


<?php $__env->startSection('title', 'Status Pengajuan - ' . ($pengajuan->tracking_token ?? 'Unknown')); ?>

<?php $__env->startPush('head'); ?>
<style>
.main-container {
    background: #f8fafc;
    min-height: 100vh;
    padding: 2rem 1rem;
}

.content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    padding: 2rem;
    color: white;
}

.card-body {
    padding: 2rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-completed { background: #10b981; color: white; }
.status-signing { background: #f59e0b; color: white; }
.status-approved { background: #3b82f6; color: white; }
.status-pending { background: #f59e0b; color: white; }
.status-rejected { background: #ef4444; color: white; }

.progress-bar {
    height: 6px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: white;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.info-item {
    display: flex;
    align-items: start;
    gap: 0.75rem;
}

.info-icon {
    color: #3b82f6;
    width: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.info-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    color: #111827;
    margin-top: 0.25rem;
}

.alert {
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #f0fdf4;
    border-color: #10b981;
}

.alert-info {
    background: #eff6ff;
    border-color: #3b82f6;
}

.alert-error {
    background: #fef2f2;
    border-color: #ef4444;
}

.alert-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.alert-success .alert-title { color: #065f46; }
.alert-info .alert-title { color: #1e40af; }
.alert-error .alert-title { color: #991b1b; }

.alert-success p { color: #047857; }
.alert-info p { color: #1e40af; }
.alert-error p { color: #991b1b; }

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -0.5rem;
    top: 0.25rem;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: white;
    border: 3px solid #3b82f6;
    z-index: 2;
}

.timeline-marker.completed {
    background: #10b981;
    border-color: #10b981;
}

.timeline-content {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
}

.timeline-content.completed {
    border-left-color: #10b981;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
}

.timeline-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.timeline-meta {
    font-size: 0.875rem;
    color: #6b7280;
}

.timeline-time {
    font-size: 0.875rem;
    color: #9ca3af;
    white-space: nowrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #f9fafb;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.text-center {
    text-align: center;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-blue {
    background: #dbeafe;
    color: #1e40af;
}

.badge-green {
    background: #d1fae5;
    color: #065f46;
}

.note-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.note-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.note-content {
    color: #4b5563;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .card-header {
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php if(!isset($pengajuan)): ?>
<div class="main-container">
    <div class="content-wrapper">
        <div class="alert alert-error">
            <h3 class="alert-title">Data Tidak Ditemukan</h3>
            <p>Data pengajuan tidak ditemukan. Silakan coba lagi atau hubungi administrator.</p>
            <div style="margin-top: 1rem;">
                <a href="<?php echo e(route('tracking.public')); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Tracking
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="main-container">
    <div class="content-wrapper">
        
        <!-- Header Card -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                            Status Pengajuan Surat
                        </h2>
                        <p style="opacity: 0.9; font-size: 0.875rem;">
                            <strong>Token:</strong> <?php echo e($pengajuan->tracking_token ?? 'N/A'); ?> | 
                            <strong>NIM:</strong> <?php echo e($pengajuan->nim ?? 'N/A'); ?>

                        </p>
                    </div>
                    <div>
                        <span class="status-badge status-<?php echo e($pengajuan->status == 'completed' ? 'completed' : 
                            ($pengajuan->status == 'sedang_ditandatangani' ? 'signing' : 
                            (str_contains($pengajuan->status ?? '', 'approved') ? 'approved' : 
                            (str_contains($pengajuan->status ?? '', 'rejected') ? 'rejected' : 'pending')))); ?>">
                            <i class="fas fa-<?php echo e($pengajuan->status == 'completed' ? 'check-circle' : 
                                ($pengajuan->status == 'sedang_ditandatangani' ? 'signature' : 
                                (str_contains($pengajuan->status ?? '', 'approved') ? 'thumbs-up' : 
                                (str_contains($pengajuan->status ?? '', 'rejected') ? 'times-circle' : 'clock')))); ?>"></i>
                            <?php echo e($pengajuan->status_label ?? ucfirst($pengajuan->status ?? 'Unknown')); ?>

                        </span>
                        
                        <?php
                            $progressSteps = ['pending', 'processed', 'approved_prodi', 'approved_fakultas', 'sedang_ditandatangani', 'completed'];
                            $currentStep = array_search($pengajuan->status, $progressSteps);
                            $progress = $currentStep !== false ? (($currentStep + 1) / count($progressSteps)) * 100 : 10;
                        ?>
                        <div style="margin-top: 0.75rem; min-width: 200px;">
                            <div style="font-size: 0.75rem; opacity: 0.9; margin-bottom: 0.25rem;">
                                Progress: <?php echo e(number_format($progress, 0)); ?>%
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo e($progress); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-user info-icon"></i>
                        <div>
                            <div class="info-label">Nama</div>
                            <div class="info-value"><?php echo e($pengajuan->nama_mahasiswa ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-file-alt info-icon"></i>
                        <div>
                            <div class="info-label">Jenis Surat</div>
                            <div class="info-value"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-graduation-cap info-icon"></i>
                        <div>
                            <div class="info-label">Program Studi</div>
                            <div class="info-value"><?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar info-icon"></i>
                        <div>
                            <div class="info-label">Tanggal Pengajuan</div>
                            <div class="info-value"><?php echo e($pengajuan->created_at ? $pengajuan->created_at->format('d M Y H:i') : 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope info-icon"></i>
                        <div>
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo e($pengajuan->email ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone info-icon"></i>
                        <div>
                            <div class="info-label">Telepon</div>
                            <div class="info-value"><?php echo e($pengajuan->phone ?? 'N/A'); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if($pengajuan->keperluan): ?>
                <div class="note-box">
                    <div class="note-title">
                        <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                        Keperluan
                    </div>
                    <div class="note-content"><?php echo e($pengajuan->keperluan); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Completed Alert -->
        <?php if($pengajuan->status == 'completed' && $pengajuan->suratGenerated): ?>
        <div class="alert alert-success">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1;">
                    <h3 class="alert-title">Surat Telah Selesai!</h3>
                    <p>Surat Anda telah selesai ditandatangani dan siap untuk didownload.</p>
                    <?php if($pengajuan->suratGenerated->signed_at ?? $pengajuan->completed_at): ?>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                        <i class="fas fa-clock"></i>
                        Selesai pada: <?php echo e(($pengajuan->suratGenerated->signed_at ?? $pengajuan->completed_at)->format('d M Y H:i')); ?>

                    </p>
                    <?php endif; ?>
                    
                    <?php if($pengajuan->suratGenerated->notes): ?>
                    <div style="margin-top: 0.75rem; padding: 0.75rem; background: #d1fae5; border-radius: 6px;">
                        <strong>Catatan:</strong> <?php echo e($pengajuan->suratGenerated->notes); ?>

                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <?php
                        $downloadUrl = null;
                        if ($pengajuan->suratGenerated) {
                            if ($pengajuan->suratGenerated->signed_url) {
                                $downloadUrl = $pengajuan->suratGenerated->signed_url;
                            } elseif ($pengajuan->suratGenerated->file_path) {
                                $downloadUrl = route('tracking.download', $pengajuan->id);
                            }
                        }
                    ?>
                    
                    <?php if($downloadUrl): ?>
                    <a href="<?php echo e($downloadUrl); ?>" target="_blank" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        Download Surat
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- In Progress Alert -->
        <?php if($pengajuan->status == 'sedang_ditandatangani'): ?>
        <div class="alert alert-info">
            <h3 class="alert-title">Sedang Proses Tanda Tangan</h3>
            <p>Surat Anda sedang dalam proses tanda tangan fisik oleh pejabat fakultas.</p>
            <?php if($pengajuan->printed_at): ?>
            <p style="font-size: 0.875rem; margin-top: 0.5rem;">
                <i class="fas fa-print"></i>
                Dicetak untuk TTD pada: <?php echo e($pengajuan->printed_at->format('d M Y H:i')); ?>

            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Rejected Alert -->
        <?php if(str_contains($pengajuan->status ?? '', 'rejected')): ?>
        <div class="alert alert-error">
            <h3 class="alert-title">Pengajuan Ditolak</h3>
            <p>
                <strong>Alasan:</strong> 
                <?php echo e($pengajuan->rejection_reason_fakultas ?? $pengajuan->rejection_reason_prodi ?? $pengajuan->rejection_reason ?? 'Tidak ada alasan yang tercatat'); ?>

            </p>
        </div>
        <?php endif; ?>

        <!-- Timeline Card -->
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-history"></i>
                    Riwayat Pengajuan
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php
                        $histories = collect();
                        if (isset($pengajuan->trackingHistory)) {
                            $histories = $pengajuan->trackingHistory;
                        } elseif (isset($pengajuan) && method_exists($pengajuan, 'trackingHistory')) {
                            $histories = $pengajuan->trackingHistory ?? collect();
                        }
                    ?>
                    
                    <?php $__empty_1 = true; $__currentLoopData = $histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo e($loop->first ? '' : 'completed'); ?>"></div>
                        <div class="timeline-content <?php echo e($loop->first ? '' : 'completed'); ?>">
                            <div class="timeline-header">
                                <div style="flex: 1;">
                                    <div class="timeline-title"><?php echo e($history->description ?? $history->status ?? 'Update Status'); ?></div>
                                    <?php if($history->notes ?? '' !== ''): ?>
                                    <p class="timeline-meta" style="margin-top: 0.25rem;"><?php echo e($history->notes); ?></p>
                                    <?php endif; ?>
                                    <div class="timeline-meta" style="margin-top: 0.5rem;">
                                        <?php if(isset($history->createdBy) && $history->createdBy): ?>
                                            <i class="fas fa-user"></i>
                                            <?php echo e($history->createdBy->nama ?? $history->createdBy->name ?? 'System'); ?>

                                        <?php else: ?>
                                            <i class="fas fa-robot"></i>
                                            System
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo e($history->created_at ? $history->created_at->format('d M Y H:i') : 'N/A'); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <div style="flex: 1;">
                                    <div class="timeline-title">Pengajuan Diterima</div>
                                    <p class="timeline-meta" style="margin-top: 0.25rem;">Pengajuan berhasil diterima sistem dan sedang menunggu proses review</p>
                                    <div class="timeline-meta" style="margin-top: 0.5rem;">
                                        <i class="fas fa-robot"></i>
                                        System
                                    </div>
                                </div>
                                <div class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo e($pengajuan->created_at ? $pengajuan->created_at->format('d M Y H:i') : 'N/A'); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Additional Info Card -->
        <?php if($pengajuan->suratGenerated): ?>
        <div class="card">
            <div class="card-header">
                <h3 style="font-size: 1.25rem; font-weight: 700;">
                    <i class="fas fa-info-circle"></i>
                    Informasi Surat
                </h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <?php if($pengajuan->suratGenerated->nomor_surat ?? '' !== ''): ?>
                    <div class="info-item">
                        <i class="fas fa-file-contract info-icon"></i>
                        <div>
                            <div class="info-label">Nomor Surat</div>
                            <div class="info-value">
                                <span class="badge badge-blue"><?php echo e($pengajuan->suratGenerated->nomor_surat); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($pengajuan->suratGenerated->signed_by ?? '' !== ''): ?>
                    <div class="info-item">
                        <i class="fas fa-user-tie info-icon"></i>
                        <div>
                            <div class="info-label">Ditandatangani Oleh</div>
                            <div class="info-value"><?php echo e($pengajuan->suratGenerated->signed_by); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($pengajuan->suratGenerated->signed_at): ?>
                    <div class="info-item">
                        <i class="fas fa-calendar-check info-icon"></i>
                        <div>
                            <div class="info-label">Tanggal Selesai</div>
                            <div class="info-value"><?php echo e($pengajuan->suratGenerated->signed_at->format('d M Y H:i')); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($pengajuan->suratGenerated->signed_url): ?>
                    <div class="info-item">
                        <i class="fas fa-link info-icon"></i>
                        <div>
                            <div class="info-label">Link Surat Final</div>
                            <div class="info-value">
                                <span class="badge badge-green">Tersedia</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if($pengajuan->suratGenerated->notes): ?>
                <div class="note-box">
                    <div class="note-title">
                        <i class="fas fa-sticky-note" style="color: #f59e0b;"></i>
                        Catatan dari Fakultas
                    </div>
                    <div class="note-content"><?php echo e($pengajuan->suratGenerated->notes); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="text-center">
            <a href="<?php echo e(route('tracking.public')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Tracking
            </a>
            
            <?php if($pengajuan->status == 'completed' && $pengajuan->suratGenerated): ?>
                <?php
                    $downloadUrl = null;
                    if ($pengajuan->suratGenerated->signed_url) {
                        $downloadUrl = $pengajuan->suratGenerated->signed_url;
                    } elseif ($pengajuan->suratGenerated->file_path) {
                        $downloadUrl = route('tracking.download', $pengajuan->id);
                    }
                ?>
                
                <?php if($downloadUrl): ?>
                <a href="<?php echo e($downloadUrl); ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-download"></i>
                    Download Surat
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/public/tracking/show.blade.php ENDPATH**/ ?>