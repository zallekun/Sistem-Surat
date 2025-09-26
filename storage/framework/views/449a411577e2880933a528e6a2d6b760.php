


<?php $__env->startSection('title', 'Status Pengajuan - ' . ($pengajuan->tracking_token ?? 'Unknown')); ?>

<?php $__env->startPush('head'); ?>
<style>
:root {
    --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    --info-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
    --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.gradient-bg {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.tracking-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.tracking-card:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--primary-gradient);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    padding-left: 2.5rem;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -12px;
    top: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    border: 4px solid #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    z-index: 2;
    transition: all 0.3s ease;
}

.timeline-marker.active {
    background: var(--primary-gradient);
    border-color: white;
    animation: pulse 2s infinite;
}

.timeline-marker.completed {
    background: var(--success-gradient);
    border-color: white;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1); }
    100% { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); }
}

.timeline-content {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #3b82f6;
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateX(5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.timeline-content.active {
    border-left-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.timeline-content.current {
    border-left-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.8s;
}

.status-badge:hover::before {
    left: 100%;
}

.status-completed {
    background: var(--success-gradient);
    color: white;
}

.status-signing {
    background: var(--warning-gradient);
    color: white;
}

.status-approved {
    background: var(--info-gradient);
    color: white;
}

.status-pending {
    background: var(--warning-gradient);
    color: white;
}

.status-rejected {
    background: var(--danger-gradient);
    color: white;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.icon-circle:hover {
    transform: scale(1.1);
}

.btn-enhanced {
    border-radius: 9999px;
    font-weight: 600;
    padding: 0.75rem 2rem;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: relative;
    overflow: hidden;
}

.btn-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-enhanced:hover::before {
    left: 100%;
}

.btn-enhanced:hover {
    transform: translateY(-2px);
}

.progress-custom {
    height: 8px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.progress-bar-custom {
    height: 100%;
    border-radius: 4px;
    background: var(--success-gradient);
    transition: width 0.6s ease;
}

.fade-in {
    animation: fadeIn 0.6s ease-in;
}

.slide-in-up {
    animation: slideInUp 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php if(!isset($pengajuan)): ?>
<div class="gradient-bg min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-red-50 border-l-4 border-red-400 p-6 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-800">Data Tidak Ditemukan</h3>
                    <p class="mt-2 text-red-700">Data pengajuan tidak ditemukan. Silakan coba lagi atau hubungi administrator.</p>
                    <div class="mt-4">
                        <a href="<?php echo e(route('tracking.public')); ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Tracking
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="gradient-bg min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4">
        
        <!-- Header Card -->
        <div class="tracking-card fade-in mb-8">
            <!-- Header dengan gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center">
                        <div class="icon-circle bg-white bg-opacity-20 border-3 border-white border-opacity-30 text-white mr-4">
                            <i class="fas fa-file-alt text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Status Pengajuan Surat</h2>
                            <p class="text-white text-opacity-75">
                                <strong>Token:</strong> <?php echo e($pengajuan->tracking_token ?? 'N/A'); ?> | 
                                <strong>NIM:</strong> <?php echo e($pengajuan->nim ?? 'N/A'); ?>

                            </p>
                        </div>
                    </div>
                    <div class="text-right">
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
                        
                        <!-- Progress Indicator -->
                        <?php
                            $progressSteps = ['pending', 'processed', 'approved_prodi', 'approved_fakultas', 'sedang_ditandatangani', 'completed'];
                            $currentStep = array_search($pengajuan->status, $progressSteps);
                            $progress = $currentStep !== false ? (($currentStep + 1) / count($progressSteps)) * 100 : 10;
                        ?>
                        <div class="mt-2">
                            <div class="text-sm text-white text-opacity-75 mb-1">Progress: <?php echo e(number_format($progress, 0)); ?>%</div>
                            <div class="progress-custom w-32">
                                <div class="progress-bar-custom" style="width: <?php echo e($progress); ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Body dengan info detail -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-user text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Nama</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->nama_mahasiswa ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Program Studi</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Email</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->email ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-file-alt text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Jenis Surat</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Tanggal Pengajuan</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->created_at ? $pengajuan->created_at->format('d M Y H:i') : 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-blue-500 w-6 mr-3"></i>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Telepon</div>
                                <div class="text-gray-900"><?php echo e($pengajuan->phone ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if($pengajuan->keperluan): ?>
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Keperluan
                    </h4>
                    <p class="text-gray-700"><?php echo e($pengajuan->keperluan); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Completed Alert -->
<?php if($pengajuan->status == 'completed' && $pengajuan->suratGenerated): ?>
<div class="bg-green-50 border-l-4 border-green-400 p-6 rounded-lg mb-8 slide-in-up">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400 text-3xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-green-800">🎉 Surat Telah Selesai!</h3>
                <p class="text-green-700">Surat Anda telah selesai ditandatangani dan siap untuk didownload.</p>
                <?php if($pengajuan->suratGenerated->signed_at ?? $pengajuan->completed_at): ?>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-clock mr-1"></i>
                    Selesai pada: <?php echo e(($pengajuan->suratGenerated->signed_at ?? $pengajuan->completed_at)->format('d M Y H:i')); ?>

                </p>
                <?php endif; ?>
                
                
                <?php if($pengajuan->suratGenerated->notes): ?>
                <div class="mt-3 p-3 bg-green-100 rounded-lg border border-green-200">
                    <p class="text-sm text-green-800">
                        <i class="fas fa-sticky-note mr-2"></i>
                        <strong>Catatan dari Fakultas:</strong> <?php echo e($pengajuan->suratGenerated->notes); ?>

                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex flex-col space-y-2">
            <?php
                $downloadUrl = null;
                $isExternalLink = false;
                if ($pengajuan->suratGenerated) {
                    if ($pengajuan->suratGenerated->signed_url) {
                        $downloadUrl = $pengajuan->suratGenerated->signed_url;
                        $isExternalLink = true;
                    } elseif ($pengajuan->suratGenerated->file_path) {
                        $downloadUrl = route('tracking.download', $pengajuan->id);
                    }
                }
            ?>
            
            <?php if($downloadUrl): ?>
                <?php if($isExternalLink): ?>
                
                <a href="<?php echo e($downloadUrl); ?>" target="_blank" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 hover:scale-105">
                    <i class="fas fa-external-link-alt mr-2"></i>
                    Buka Surat (Google Drive)
                </a>
                <p class="text-xs text-green-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    Link aman dari fakultas
                </p>
                <?php else: ?>
                
                <a href="<?php echo e($downloadUrl); ?>" target="_blank" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-download mr-2"></i>
                    Download Surat (PDF)
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

        <!-- In Progress Alert -->
        <?php if($pengajuan->status == 'sedang_ditandatangani'): ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg mb-8 slide-in-up">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-signature text-blue-400 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-blue-800">Sedang Proses Tanda Tangan</h3>
                    <p class="text-blue-700">Surat Anda sedang dalam proses tanda tangan fisik oleh pejabat fakultas.</p>
                    <?php if($pengajuan->printed_at): ?>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-print mr-1"></i>
                        Dicetak untuk TTD pada: <?php echo e($pengajuan->printed_at->format('d M Y H:i')); ?>

                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rejected Alert -->
        <?php if(str_contains($pengajuan->status ?? '', 'rejected')): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-6 rounded-lg mb-8 slide-in-up">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-400 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-800">Pengajuan Ditolak</h3>
                    <p class="text-red-700">
                        <strong>Alasan:</strong> 
                        <?php echo e($pengajuan->rejection_reason_fakultas ?? $pengajuan->rejection_reason_prodi ?? $pengajuan->rejection_reason ?? 'Tidak ada alasan yang tercatat'); ?>

                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline Card -->
        <div class="tracking-card slide-in-up mb-8">
            <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-6">
                <div class="flex items-center text-white">
                    <div class="icon-circle bg-white bg-opacity-20 border-3 border-white border-opacity-30 text-white mr-4">
                        <i class="fas fa-history text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Riwayat Pengajuan</h3>
                </div>
            </div>
            <div class="p-6">
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
                        <div class="timeline-marker <?php echo e($loop->first ? 'active' : 'completed'); ?>"></div>
                        <div class="timeline-content <?php echo e($loop->first ? 'current' : 'active'); ?>">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1"><?php echo e($history->description ?? $history->status ?? 'Update Status'); ?></h4>
                                    <?php if($history->notes ?? '' !== ''): ?>
                                    <p class="text-gray-600 text-sm mb-2"><?php echo e($history->notes); ?></p>
                                    <?php endif; ?>
                                    <div class="text-sm text-gray-500">
                                        <?php if(isset($history->createdBy) && $history->createdBy): ?>
                                            <i class="fas fa-user mr-1"></i>
                                            oleh <?php echo e($history->createdBy->nama ?? $history->createdBy->name ?? 'System'); ?>

                                        <?php else: ?>
                                            <i class="fas fa-robot mr-1"></i>
                                            System
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-400 ml-4">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo e($history->created_at ? $history->created_at->format('d M Y H:i') : 'N/A'); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <!-- Default timeline if no tracking history -->
                    <div class="timeline-item">
                        <div class="timeline-marker active"></div>
                        <div class="timeline-content current">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">Pengajuan Diterima</h4>
                                    <p class="text-gray-600 text-sm mb-2">Pengajuan berhasil diterima sistem dan sedang menunggu proses review</p>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-robot mr-1"></i>
                                        System
                                    </div>
                                </div>
                                <div class="text-sm text-gray-400 ml-4">
                                    <i class="fas fa-clock mr-1"></i>
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
<?php if($pengajuan->suratGenerated || $pengajuan->notes): ?>
<div class="tracking-card slide-in-up mb-8">
    <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
        <div class="flex items-center text-white">
            <div class="icon-circle bg-white bg-opacity-20 border-3 border-white border-opacity-30 text-white mr-4">
                <i class="fas fa-info-circle text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold">Informasi Surat</h3>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if($pengajuan->suratGenerated): ?>
            <div class="space-y-4">
                <?php if($pengajuan->suratGenerated->nomor_surat ?? '' !== ''): ?>
                <div class="flex items-center">
                    <i class="fas fa-file-contract text-blue-500 w-6 mr-3"></i>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Nomor Surat</div>
                        <div class="text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo e($pengajuan->suratGenerated->nomor_surat); ?>

                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($pengajuan->suratGenerated->signed_by ?? '' !== ''): ?>
                <div class="flex items-center">
                    <i class="fas fa-user-tie text-blue-500 w-6 mr-3"></i>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Ditandatangani Oleh</div>
                        <div class="text-gray-900"><?php echo e($pengajuan->suratGenerated->signed_by); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                
                <?php if($pengajuan->suratGenerated->signed_url): ?>
                <div class="flex items-start">
                    <i class="fas fa-link text-green-500 w-6 mr-3 mt-1"></i>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-500">Link Surat Final</div>
                        <div class="text-gray-900 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Tersedia
                            </span>
                        </div>
                        <a href="<?php echo e($pengajuan->suratGenerated->signed_url); ?>" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Buka Link
                        </a>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Link aman yang disediakan oleh staff fakultas
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            
            <div class="space-y-4">
                <?php if($pengajuan->suratGenerated && $pengajuan->suratGenerated->signed_at): ?>
                <div class="flex items-center">
                    <i class="fas fa-calendar-check text-green-500 w-6 mr-3"></i>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Tanggal Selesai</div>
                        <div class="text-gray-900"><?php echo e($pengajuan->suratGenerated->signed_at->format('d M Y H:i')); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($pengajuan->suratGenerated && ($pengajuan->suratGenerated->notes || $pengajuan->notes)): ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-900 mb-2">
                        <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                        Catatan dari Fakultas
                    </h4>
                    <p class="text-gray-700 text-sm">
                        <?php echo e($pengajuan->suratGenerated->notes ?? $pengajuan->notes ?? 'Tidak ada catatan tambahan.'); ?>

                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

        <!-- Action Buttons -->
        <div class="text-center slide-in-up">
            <a href="<?php echo e(route('tracking.public')); ?>" 
               class="btn-enhanced inline-flex items-center px-6 py-3 mr-4 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Tracking
            </a>
            
            <?php if($pengajuan->status == 'completed'): ?>
                <?php
                    $hasDownload = false;
                    $downloadUrl = null;
                    
                    if ($pengajuan->suratGenerated) {
                        if ($pengajuan->suratGenerated->signed_url) {
                            $hasDownload = true;
                            $downloadUrl = $pengajuan->suratGenerated->signed_url;
                        } elseif ($pengajuan->suratGenerated->file_path) {
                            $hasDownload = true;
                            $downloadUrl = route('tracking.download', $pengajuan->id);
                        }
                    }
                ?>
                
                <?php if($hasDownload): ?>
                <a href="<?php echo e($downloadUrl); ?>" target="_blank" 
                   class="btn-enhanced inline-flex items-center px-6 py-3 border border-transparent text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-download mr-2"></i>
                    Download Surat
                </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe all cards
    document.querySelectorAll('.tracking-card').forEach(card => {
        observer.observe(card);
    });

    // Timeline items animation
    document.querySelectorAll('.timeline-item').forEach((item, index) => {
        item.style.animationDelay = `${index * 0.2}s`;
        item.classList.add('slide-in-up');
    });

    // Enhanced hover effects
    document.querySelectorAll('.timeline-content').forEach(content => {
        content.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px) scale(1.02)';
        });
        
        content.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0) scale(1)';
        });
    });

    // Progress bar animation
    const progressBar = document.querySelector('.progress-bar-custom');
    if (progressBar) {
        setTimeout(() => {
            const width = progressBar.style.width;
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.width = width;
            }, 100);
        }, 500);
    }

    // Button enhanced effects
    document.querySelectorAll('.btn-enhanced').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Status badge pulse effect for active status
    const activeBadge = document.querySelector('.status-badge.status-pending, .status-badge.status-signing');
    if (activeBadge) {
        setInterval(() => {
            activeBadge.style.transform = 'scale(1.05)';
            setTimeout(() => {
                activeBadge.style.transform = 'scale(1)';
            }, 200);
        }, 3000);
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/public/tracking/show.blade.php ENDPATH**/ ?>