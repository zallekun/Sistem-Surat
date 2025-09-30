

<?php $__env->startSection('title', 'Detail Pengajuan'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Main Card -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-file-alt mr-2"></i>
                        Detail Pengajuan Surat
                    </h2>
                    <?php
                        $statusConfig = [
                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                            'processed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Diproses'],
                            'approved_prodi' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui Prodi'],
                            'rejected_prodi' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                            'completed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Selesai'],
                            'pengantar_generated' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'label' => 'Surat Pengantar Dibuat'],
                        ];
                        $status = $statusConfig[$pengajuan->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($pengajuan->status)];
                    ?>
                    <span class="px-3 py-1.5 rounded-full text-sm font-semibold <?php echo e($status['bg']); ?> <?php echo e($status['text']); ?>">
                        <?php echo e($status['label']); ?>

                    </span>
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-6 space-y-5">
                <!-- Info Cards Grid -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Info Pengajuan -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="font-semibold text-blue-800 mb-3 text-sm flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            INFORMASI PENGAJUAN
                        </h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Token:</span>
                                <span class="font-mono bg-white px-2.5 py-1 rounded text-xs font-semibold text-blue-600">
                                    <?php echo e($pengajuan->tracking_token); ?>

                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Tanggal:</span>
                                <span class="font-medium text-gray-900"><?php echo e($pengajuan->created_at->format('d/m/Y H:i')); ?></span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-gray-700">Jenis Surat:</span>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-mono">
                                        <?php echo e($pengajuan->jenisSurat->kode_surat ?? ''); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Mahasiswa -->
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <h3 class="font-semibold text-green-800 mb-3 text-sm flex items-center">
                            <i class="fas fa-user-graduate mr-2"></i>
                            DATA MAHASISWA
                        </h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">NIM:</span>
                                <span class="font-mono font-semibold text-gray-900"><?php echo e($pengajuan->nim); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Nama:</span>
                                <span class="font-medium text-gray-900"><?php echo e($pengajuan->nama_mahasiswa); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Prodi:</span>
                                <span class="text-gray-900"><?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?></span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-gray-700">Email:</span>
                                <span class="text-xs text-gray-900 break-all text-right max-w-[200px]"><?php echo e($pengajuan->email); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Keperluan -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-800 mb-2 text-sm flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        KEPERLUAN SURAT
                    </h3>
                    <p class="text-gray-700 text-sm leading-relaxed"><?php echo e($pengajuan->keperluan); ?></p>
                </div>
                
                <!-- Additional Data -->
                <?php if($pengajuan->additional_data): ?>
                    <?php
                        $additionalData = $pengajuan->additional_data;
                        $jenisSurat = $pengajuan->jenisSurat;
                    ?>
                    
                    <div class="border-t pt-5">
                        <h3 class="font-semibold text-gray-800 mb-4 text-sm flex items-center">
                            <i class="fas fa-list-alt mr-2"></i>
                            DATA TAMBAHAN
                        </h3>

                        
                        <?php if(($jenisSurat->kode_surat ?? '') === 'KP' && isset($additionalData['kerja_praktek'])): ?>
                            <!-- Info Perusahaan -->
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-4">
                                <h4 class="font-medium text-blue-800 mb-3 text-sm flex items-center">
                                    <i class="fas fa-building mr-2"></i>
                                    Informasi Perusahaan
                                </h4>
                                <div class="grid md:grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-700">Nama Perusahaan:</span>
                                        <div class="font-medium text-gray-900"><?php echo e($additionalData['kerja_praktek']['nama_perusahaan'] ?? '-'); ?></div>
                                    </div>
                                    <div>
                                        <span class="text-gray-700">Bidang Kerja:</span>
                                        <div class="text-gray-900"><?php echo e($additionalData['kerja_praktek']['bidang_kerja'] ?? '-'); ?></div>
                                    </div>
                                    <div>
                                        <span class="text-gray-700">Periode:</span>
                                        <div class="text-gray-900">
                                            <?php echo e($additionalData['kerja_praktek']['periode_mulai'] ?? '-'); ?> s/d 
                                            <?php echo e($additionalData['kerja_praktek']['periode_selesai'] ?? '-'); ?>

                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-700">Jumlah Mahasiswa:</span>
                                        <span class="inline-block bg-blue-100 px-2.5 py-1 rounded text-blue-800 font-semibold text-xs">
                                            <?php echo e($additionalData['kerja_praktek']['jumlah_mahasiswa'] ?? count($additionalData['kerja_praktek']['mahasiswa_kp'] ?? [])); ?> orang
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if($additionalData['kerja_praktek']['alamat_perusahaan'] ?? false): ?>
                                    <div class="mt-3 pt-3 border-t border-blue-200">
                                        <span class="text-gray-700 text-sm font-medium">Alamat Perusahaan:</span>
                                        <p class="mt-1 text-sm text-gray-900"><?php echo e($additionalData['kerja_praktek']['alamat_perusahaan']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Daftar Mahasiswa KP -->
                            <?php if(isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp'])): ?>
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h4 class="font-medium text-gray-800 text-sm flex items-center">
                                            <i class="fas fa-users mr-2"></i>
                                            Daftar Mahasiswa Kerja Praktek
                                        </h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NIM</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prodi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-100">
                                                <?php $__currentLoopData = $additionalData['kerja_praktek']['mahasiswa_kp']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mahasiswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($index + 1); ?></td>
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($mahasiswa['nama'] ?? '-'); ?></td>
                                                        <td class="px-4 py-3 text-sm font-mono text-gray-900"><?php echo e($mahasiswa['nim'] ?? '-'); ?></td>
                                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($mahasiswa['prodi'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        
                        <?php if(($jenisSurat->kode_surat ?? '') === 'TA' && isset($additionalData['tugas_akhir'])): ?>
                            <!-- Info Tugas Akhir -->
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200 mb-4">
                                <h4 class="font-medium text-indigo-800 mb-3 text-sm flex items-center">
                                    <i class="fas fa-book mr-2"></i>
                                    Informasi Tugas Akhir
                                </h4>
                                
                                <?php if($additionalData['tugas_akhir']['judul_ta'] ?? false): ?>
                                    <div class="mb-3">
                                        <span class="text-gray-700 font-medium text-sm">Judul:</span>
                                        <p class="mt-1 text-sm text-gray-900 leading-relaxed"><?php echo e($additionalData['tugas_akhir']['judul_ta']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="grid md:grid-cols-2 gap-3 text-sm">
                                    <?php if($additionalData['tugas_akhir']['dosen_pembimbing1'] ?? false): ?>
                                        <div>
                                            <span class="text-gray-700">Pembimbing 1:</span>
                                            <div class="font-medium text-gray-900"><?php echo e($additionalData['tugas_akhir']['dosen_pembimbing1']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($additionalData['tugas_akhir']['dosen_pembimbing2'] ?? false): ?>
                                        <div>
                                            <span class="text-gray-700">Pembimbing 2:</span>
                                            <div class="font-medium text-gray-900"><?php echo e($additionalData['tugas_akhir']['dosen_pembimbing2']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if($additionalData['tugas_akhir']['lokasi_penelitian'] ?? false): ?>
                                        <div class="md:col-span-2">
                                            <span class="text-gray-700">Lokasi Penelitian:</span>
                                            <div class="text-gray-900"><?php echo e($additionalData['tugas_akhir']['lokasi_penelitian']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Daftar Mahasiswa TA -->
                            <?php if(isset($additionalData['tugas_akhir']['mahasiswa_ta']) && is_array($additionalData['tugas_akhir']['mahasiswa_ta'])): ?>
                                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <h4 class="font-medium text-gray-800 text-sm flex items-center">
                                            <i class="fas fa-users mr-2"></i>
                                            Daftar Mahasiswa Tugas Akhir
                                            <span class="ml-auto bg-purple-100 px-2.5 py-1 rounded text-purple-800 font-semibold text-xs">
                                                <?php echo e(count($additionalData['tugas_akhir']['mahasiswa_ta'])); ?> orang
                                            </span>
                                        </h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NIM</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prodi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-100">
                                                <?php $__currentLoopData = $additionalData['tugas_akhir']['mahasiswa_ta']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mahasiswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($index + 1); ?></td>
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($mahasiswa['nama'] ?? '-'); ?></td>
                                                        <td class="px-4 py-3 text-sm font-mono text-gray-900"><?php echo e($mahasiswa['nim'] ?? '-'); ?></td>
                                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($mahasiswa['prodi'] ?? '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Surat Pengantar Info -->
                <?php if($pengajuan->hasSuratPengantar()): ?>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-semibold text-green-800 mb-3 flex items-center text-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            SURAT PENGANTAR SUDAH DIBUAT
                        </h4>
                        <div class="text-sm text-gray-700 space-y-2">
                            <div class="grid md:grid-cols-2 gap-2">
                                <div><span class="font-medium">Nomor:</span> <?php echo e($pengajuan->surat_pengantar_nomor); ?></div>
                                <div><span class="font-medium">Dibuat oleh:</span> <?php echo e($pengajuan->suratPengantarGeneratedBy->nama ?? 'N/A'); ?></div>
                                <div><span class="font-medium">Tanggal:</span> <?php echo e($pengajuan->surat_pengantar_generated_at?->format('d/m/Y H:i')); ?></div>
                                <?php if($pengajuan->nota_dinas_number): ?>
                                    <div><span class="font-medium">No. Nota Dinas:</span> <?php echo e($pengajuan->nota_dinas_number); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="<?php echo e($pengajuan->surat_pengantar_url); ?>" target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium mt-3 transition">
                            <i class="fas fa-download mr-2"></i>
                            Download Surat Pengantar
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Approval Timeline Section -->
            <?php if($pengajuan->approvalHistories->count() > 0): ?>
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-history mr-2"></i>Riwayat Persetujuan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php $__currentLoopData = $pengajuan->approvalHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-4">
                            <!-- Timeline Dot -->
                            <div class="flex-shrink-0 relative">
                                <?php if(!$loop->last): ?>
                                <div class="absolute top-10 left-1/2 w-0.5 h-full bg-gray-200 -translate-x-1/2"></div>
                                <?php endif; ?>
                                
                                <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10
                                    <?php if($approval->isApproval()): ?>
                                        bg-green-100
                                    <?php elseif($approval->isRejection()): ?>
                                        bg-red-100
                                    <?php else: ?>
                                        bg-blue-100
                                    <?php endif; ?>">
                                    <i class="fas 
                                        <?php if($approval->isApproval()): ?>
                                            fa-check text-green-600
                                        <?php elseif($approval->isRejection()): ?>
                                            fa-times text-red-600
                                        <?php else: ?>
                                            fa-clock text-blue-600
                                        <?php endif; ?>"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="font-medium text-gray-900"><?php echo e($approval->action_label); ?></span>
                                        <?php if($approval->performedBy): ?>
                                            <span class="text-sm text-gray-600">
                                                oleh <span class="font-medium"><?php echo e($approval->performedBy->nama); ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            <?php echo e($approval->created_at->format('d/m/Y H:i')); ?>

                                        </div>
                                        <div class="text-xs text-gray-400">
                                            <?php echo e($approval->created_at->diffForHumans()); ?>

                                        </div>
                                    </div>
                                </div>
                                
                                <?php if($approval->notes): ?>
                                    <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <p class="text-sm text-gray-700">
                                            <i class="fas fa-comment-alt text-gray-400 mr-1"></i>
                                            <?php echo e($approval->notes); ?>

                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($approval->metadata): ?>
                                    <div class="mt-2">
                                        <details class="text-xs text-gray-500">
                                            <summary class="cursor-pointer hover:text-gray-700">Detail metadata</summary>
                                            <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto"><?php echo e(json_encode($approval->metadata, JSON_PRETTY_PRINT)); ?></pre>
                                        </details>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <a href="<?php echo e(route('staff.pengajuan.index')); ?>" 
                       class="inline-flex items-center px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition w-full sm:w-auto justify-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    
                    <div class="flex gap-2 w-full sm:w-auto">
                        <?php if(($pengajuan->status === 'pending' || $pengajuan->status === 'processed') && auth()->user()->hasRole(['staff_prodi', 'kaprodi'])): ?>
                            <button onclick="showApproveConfirm()" 
                                    class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-check mr-2"></i>
                                Setujui
                            </button>
                            
                            <button onclick="showRejectModal()" 
                                    class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-times mr-2"></i>
                                Tolak
                            </button>
                        <?php elseif($pengajuan->status === 'approved_prodi' && in_array($pengajuan->jenisSurat->kode_surat, ['KP', 'TA']) && !$pengajuan->hasSuratPengantar()): ?>
                            <a href="<?php echo e(route('staff.pengajuan.pengantar.preview', $pengajuan->id)); ?>" 
                               class="flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-file-alt mr-2"></i>
                                Buat Surat Pengantar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-semibold text-gray-900">Konfirmasi Persetujuan</h3>
                    <p class="text-sm text-gray-500 mt-1">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-full transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <p class="text-gray-700 mb-4 leading-relaxed">
                Apakah Anda yakin ingin menyetujui pengajuan surat ini?
            </p>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Detail Pengajuan:</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium">Mahasiswa:</span> <?php echo e($pengajuan->nama_mahasiswa); ?> (<?php echo e($pengajuan->nim); ?>)</p>
                    <p><span class="font-medium">Jenis Surat:</span> <?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 bg-gray-50 border-t border-gray-200 rounded-b-xl">
            <button onclick="closeApproveModal()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button onclick="processPengajuan('approve')" 
                    class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-semibold text-gray-900">Tolak Pengajuan</h3>
                    <p class="text-sm text-gray-500 mt-1">Berikan alasan penolakan yang jelas</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-full transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Detail Pengajuan:</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium">Mahasiswa:</span> <?php echo e($pengajuan->nama_mahasiswa); ?> (<?php echo e($pengajuan->nim); ?>)</p>
                    <p><span class="font-medium">Jenis Surat:</span> <?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></p>
                </div>
            </div>
            
            <div>
                <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea id="rejectionReason" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                          rows="4"
                          placeholder="Tuliskan alasan penolakan dengan jelas..."
                          required></textarea>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 bg-gray-50 border-t border-gray-200 rounded-b-xl">
            <button onclick="closeRejectModal()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button onclick="processPengajuan('reject')" 
                    class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                Tolak Pengajuan
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.showApproveConfirm = function() {
    const modal = document.getElementById('approveModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

window.closeApproveModal = function() {
    const modal = document.getElementById('approveModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

window.showRejectModal = function() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

window.closeRejectModal = function() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('rejectionReason').value = '';
        document.body.style.overflow = '';
    }
}

window.processPengajuan = function(action) {
    let data = { action: action };
    
    if (action === 'reject') {
        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Alasan penolakan harus diisi!');
            return;
        }
        data.rejection_reason = reason;
    }
    
    fetch('/staff/pengajuan/<?php echo e($pengajuan->id); ?>/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            window.location.href = '<?php echo e(route("staff.pengajuan.index")); ?>';
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    });
    
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}

// Close on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');
    
    if (approveModal) {
        approveModal.addEventListener('click', function(e) {
            if (e.target === this) closeApproveModal();
        });
    }
    
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeApproveModal();
        closeRejectModal();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/staff/pengajuan/show.blade.php ENDPATH**/ ?>