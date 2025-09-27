<!-- staff.pengajuan.show -->




<?php $__env->startSection('title', 'Detail Pengajuan'); ?>

<?php $__env->startSection('content'); ?>
<!-- Container dengan padding yang tepat -->
<div class="min-h-screen bg-gray-50">
    <!-- Content container dengan max height -->
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header dengan gradient -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                            Detail Pengajuan Surat
                        </h2>
                        <?php
                            use App\Helpers\StatusHelper;
                            $statusColor = StatusHelper::getPengajuanStatusColor($pengajuan->status);
                            $statusLabel = StatusHelper::getPengajuanStatusLabel($pengajuan->status);
                        ?>
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo e($statusColor); ?>">
                            <?php echo e($statusLabel); ?>

                        </span>
                    </div>
                </div>

                <!-- Content Body -->
                <div class="p-6 space-y-6">
                    <!-- Basic Info Grid -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Informasi Pengajuan -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <h3 class="font-semibold text-blue-800 mb-3 text-sm uppercase tracking-wide">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Pengajuan
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Token:</span>
                                    <span class="font-mono bg-white px-2 py-0.5 rounded text-xs"><?php echo e($pengajuan->tracking_token); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tanggal:</span>
                                    <span class="text-gray-800"><?php echo e($pengajuan->created_at->format('d/m/Y H:i')); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jenis:</span>
                                    <span class="font-medium">
                                        <?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?>

                                        <span class="text-xs bg-gray-200 px-1.5 py-0.5 rounded ml-1"><?php echo e($pengajuan->jenisSurat->kode_surat ?? ''); ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Data Mahasiswa -->
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <h3 class="font-semibold text-green-800 mb-3 text-sm uppercase tracking-wide">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Data Mahasiswa
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">NIM:</span>
                                    <span class="font-medium"><?php echo e($pengajuan->nim); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama:</span>
                                    <span class="font-medium"><?php echo e($pengajuan->nama_mahasiswa); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Prodi:</span>
                                    <span><?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="text-xs"><?php echo e($pengajuan->email); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keperluan -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-2 text-sm uppercase tracking-wide">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Keperluan Surat
                        </h3>
                        <p class="text-gray-700 text-sm leading-relaxed"><?php echo e($pengajuan->keperluan); ?></p>
                    </div>
                    
                    <!-- Additional Data Section -->
                    <?php if($pengajuan->additional_data): ?>
                        <?php
                            $additionalData = $pengajuan->additional_data;
                            $jenisSurat = $pengajuan->jenisSurat;
                        ?>
                        
                        <div class="border-t pt-6">
                            <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">
                                <i class="fas fa-list-alt mr-2"></i>
                                Data Tambahan
                            </h3>

                            
                            <?php if(($jenisSurat->kode_surat ?? '') === 'KP'): ?>
                                <?php if(isset($additionalData['kerja_praktek'])): ?>
                                <!-- Daftar Mahasiswa KP -->
                                    <?php if(isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp'])): ?>
                                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                            <h4 class="font-medium text-green-800 mb-3 text-sm">
                                                <i class="fas fa-users mr-2"></i>
                                                Daftar Mahasiswa Kerja Praktek
                                            </h4>
                                            
                                            <div class="bg-white rounded-lg overflow-hidden">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIM</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prodi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <?php $__currentLoopData = $additionalData['kerja_praktek']['mahasiswa_kp']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mahasiswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-3 py-2 text-sm"><?php echo e($index + 1); ?></td>
                                                                <td class="px-3 py-2 text-sm font-medium"><?php echo e($mahasiswa['nama'] ?? '-'); ?></td>
                                                                <td class="px-3 py-2 text-sm font-mono"><?php echo e($mahasiswa['nim'] ?? '-'); ?></td>
                                                                <td class="px-3 py-2 text-sm"><?php echo e($mahasiswa['prodi'] ?? '-'); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    </div>
                                    <!-- Data Perusahaan -->
                                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4">
                                        <h4 class="font-medium text-blue-800 mb-3 text-sm">
                                            <i class="fas fa-building mr-2"></i>
                                            Informasi Perusahaan
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-600">Nama:</span>
                                                <span class="font-medium ml-1"><?php echo e($additionalData['kerja_praktek']['nama_perusahaan'] ?? '-'); ?></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Bidang:</span>
                                                <span class="ml-1"><?php echo e($additionalData['kerja_praktek']['bidang_kerja'] ?? '-'); ?></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Periode:</span>
                                                <span class="ml-1"><?php echo e($additionalData['kerja_praktek']['periode_mulai'] ?? '-'); ?> s.d <?php echo e($additionalData['kerja_praktek']['periode_selesai'] ?? '-'); ?></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Jumlah:</span>
                                                <span class="bg-blue-100 px-2 py-0.5 rounded text-blue-800 font-medium ml-1">
                                                    <?php echo e($additionalData['kerja_praktek']['jumlah_mahasiswa'] ?? count($additionalData['kerja_praktek']['mahasiswa_kp'] ?? [])); ?> orang
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if($additionalData['kerja_praktek']['alamat_perusahaan'] ?? false): ?>
                                            <div class="mt-3 pt-3 border-t border-blue-200">
                                                <span class="text-gray-600 text-sm">Alamat:</span>
                                                <p class="mt-1 text-sm"><?php echo e($additionalData['kerja_praktek']['alamat_perusahaan']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            
                            <?php if(($jenisSurat->kode_surat ?? '') === 'MA'): ?>
                                <!-- Keep existing MA code but with similar compact styling -->
                                <?php if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik'])): ?>
                                    <div class="bg-green-50 p-4 rounded-lg border border-green-100 mb-4">
                                        <h4 class="font-medium text-green-800 mb-3 text-sm">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Data Akademik
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <?php if($additionalData['semester'] ?? false): ?>
                                                <div><strong>Semester:</strong> <?php echo e($additionalData['semester']); ?></div>
                                            <?php endif; ?>
                                            <?php if($additionalData['tahun_akademik'] ?? false): ?>
                                                <div><strong>Tahun Akademik:</strong> <?php echo e($additionalData['tahun_akademik']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if(isset($additionalData['orang_tua'])): ?>
                                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                                        <h4 class="font-medium text-yellow-800 mb-3 text-sm">
                                            <i class="fas fa-users mr-2"></i>
                                            Biodata Orang Tua
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-3 text-sm">
                                            <div><strong>Nama:</strong> <?php echo e($additionalData['orang_tua']['nama'] ?? '-'); ?></div>
                                            <div><strong>Tempat Lahir:</strong> <?php echo e($additionalData['orang_tua']['tempat_lahir'] ?? '-'); ?></div>
                                            <div><strong>Tanggal Lahir:</strong> <?php echo e($additionalData['orang_tua']['tanggal_lahir'] ?? '-'); ?></div>
                                            <div><strong>Pekerjaan:</strong> <?php echo e($additionalData['orang_tua']['pekerjaan'] ?? '-'); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Actions Footer -->
<div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
    <div class="flex justify-between items-center">
        <a href="<?php echo e(route('staff.pengajuan.index')); ?>" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
        
        <div class="flex space-x-2">
            
            <?php if($pengajuan->status === 'pending' && auth()->user()->hasRole(['staff_prodi', 'kaprodi'])): ?>
                <button onclick="showApproveConfirm()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Setujui
                </button>
                
                <button onclick="showRejectModal()" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Tolak
                </button>
            
            
            <?php elseif($pengajuan->status === 'approved_prodi'): ?>
                <?php if($pengajuan->needsSuratPengantar() && !$pengajuan->hasSuratPengantar()): ?>
                    <a href="<?php echo e(route('staff.pengajuan.pengantar.preview', $pengajuan->id)); ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-file-alt mr-2"></i>Generate Surat Pengantar
                    </a>
                <?php else: ?>
                    <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 text-sm font-medium rounded-md">
                        <i class="fas fa-check-circle mr-2"></i>
                        Sudah Disetujui - Menunggu Proses Pengantar
                    </span>
                <?php endif; ?>
            
            
            <?php elseif($pengajuan->status === 'pengantar_generated'): ?>
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-md">
                    <i class="fas fa-check-double mr-2"></i>
                    Surat Pengantar Sudah Dibuat
                </span>
            
            <?php else: ?>
                <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 text-sm font-medium rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>
                    Status: <?php echo e(ucwords(str_replace('_', ' ', $pengajuan->status))); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if($pengajuan->hasSuratPengantar()): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
        <h4 class="font-semibold text-green-800 mb-2">
            <i class="fas fa-check-circle mr-2"></i>Surat Pengantar Sudah Dibuat
        </h4>
        <div class="text-sm text-green-700 space-y-1">
            <p><strong>Nomor:</strong> <?php echo e($pengajuan->surat_pengantar_nomor); ?></p>
            <p><strong>Dibuat oleh:</strong> <?php echo e($pengajuan->suratPengantarGeneratedBy->nama ?? 'N/A'); ?></p>
            <p><strong>Tanggal:</strong> <?php echo e($pengajuan->surat_pengantar_generated_at?->format('d/m/Y H:i')); ?></p>
            <?php if($pengajuan->nota_dinas_number): ?>
                <p><strong>No. Nota Dinas:</strong> <?php echo e($pengajuan->nota_dinas_number); ?></p>
            <?php endif; ?>
        </div>
        <a href="<?php echo e($pengajuan->surat_pengantar_url); ?>" target="_blank"
           class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded text-sm mt-2 hover:bg-green-700">
            <i class="fas fa-download mr-2"></i>Download Surat Pengantar
        </a>
    </div>
<?php endif; ?>

<!-- Keep existing modals unchanged -->
<!-- Modal Konfirmasi Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[2000]">
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
            <p class="text-gray-700 mb-4">
                Apakah Anda yakin ingin menyetujui pengajuan surat ini? Sebelum pengajuan diteruskan ke fakultas akan ada proses membuat surat pengantar.
            </p>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Detail Pengajuan:</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium">Mahasiswa:</span> <?php echo e($pengajuan->nama_mahasiswa); ?> (<?php echo e($pengajuan->nim); ?>)</p>
                    <p><span class="font-medium">Jenis Surat:</span> <?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 rounded-b-xl flex justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeApproveModal()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Batal
            </button>
            <button onclick="processPengajuan('approve')" 
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[2000]">
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
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                          rows="4"
                          placeholder="Tuliskan alasan penolakan..."
                          required></textarea>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 rounded-b-xl flex justify-end gap-3 p-6 border-t border-gray-200">
            <button onclick="closeRejectModal()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Batal
            </button>
            <button onclick="processPengajuan('reject')" 
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                Tolak Pengajuan
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>

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

// Modal functions - MAKE SURE THESE ARE GLOBAL
window.showApproveConfirm = function() {
    console.log('Show approve modal called');
    disableBodyScroll();
    const modal = document.getElementById('approveModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Approve modal shown');
    } else {
        console.error('Approve modal element not found!');
    }
}

window.closeApproveModal = function() {
    console.log('Close approve modal called');
    enableBodyScroll();
    const modal = document.getElementById('approveModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

window.showRejectModal = function() {
    console.log('Show reject modal called');
    disableBodyScroll();
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        console.log('Reject modal shown');
    } else {
        console.error('Reject modal element not found!');
    }
}

window.closeRejectModal = function() {
    console.log('Close reject modal called');
    enableBodyScroll();
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('rejectionReason').value = '';
    }
}

// Process function
window.processPengajuan = function(action) {
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
    
    // Show loading state
    const button = action === 'approve' 
        ? document.querySelector('button[onclick*="processPengajuan(\'approve\')"]')
        : document.querySelector('button[onclick*="processPengajuan(\'reject\')"]');
    
    if (button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        fetch('/staff/pengajuan/<?php echo e($pengajuan->id); ?>/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
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
                window.location.href = '<?php echo e(route("staff.pengajuan.index")); ?>';
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
    
    // Close modals
    if (action === 'approve') closeApproveModal();
    if (action === 'reject') closeRejectModal();
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up modal listeners');
    
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');
    
    if (approveModal) {
        approveModal.addEventListener('click', function(e) {
            if (e.target === this) closeApproveModal();
        });
        console.log('Approve modal listener added');
    } else {
        console.error('Approve modal not found in DOM');
    }
    
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
        console.log('Reject modal listener added');
    } else {
        console.error('Reject modal not found in DOM');
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const approveModal = document.getElementById('approveModal');
        const rejectModal = document.getElementById('rejectModal');
        
        if (approveModal && !approveModal.classList.contains('hidden')) {
            closeApproveModal();
        }
        if (rejectModal && !rejectModal.classList.contains('hidden')) {
            closeRejectModal();
        }
    }
});

// Test functions are accessible
console.log('Functions defined:', {
    showApproveConfirm: typeof window.showApproveConfirm,
    showRejectModal: typeof window.showRejectModal,
    processPengajuan: typeof window.processPengajuan
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/staff/pengajuan/show.blade.php ENDPATH**/ ?>