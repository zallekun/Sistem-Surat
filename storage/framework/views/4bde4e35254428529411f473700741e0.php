


<?php $__env->startSection('title', 'Detail Pengajuan'); ?>
<?php $__env->startSection('content'); ?>

<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar Pengajuan
            </a>
        </div>

        <!-- Alert if Stuck -->
        <?php if($isStuck): ?>
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-orange-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">Pengajuan Stuck!</h3>
                        <p class="text-sm text-orange-700 mt-1">
                            Pengajuan ini sudah <?php echo e($stuckDays); ?> hari tanpa progress di status "<?php echo e($pengajuan->status); ?>".
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alert if Deleted -->
        <?php if($pengajuan->trashed()): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-trash text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Pengajuan Telah Dihapus</h3>
                            <p class="text-sm text-red-700 mt-1">
                                Dihapus pada <?php echo e($pengajuan->deleted_at->format('d F Y, H:i')); ?>

                            </p>
                        </div>
                    </div>
                    <button onclick="restorePengajuan(<?php echo e($pengajuan->id); ?>)"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                        <i class="fas fa-undo mr-1"></i>Pulihkan
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header Card -->
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Detail Pengajuan</h2>
                        <p class="text-sm text-white/90 mt-1"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'Surat'); ?></p>
                    </div>
                    <div class="text-right">
                        <?php
                            $statusBadge = match($pengajuan->status) {
                                'pending' => ['bg' => 'bg-yellow-500', 'icon' => 'fa-clock'],
                                'approved_prodi' => ['bg' => 'bg-blue-500', 'icon' => 'fa-check'],
                                'approved_fakultas' => ['bg' => 'bg-indigo-500', 'icon' => 'fa-check-double'],
                                'completed' => ['bg' => 'bg-green-500', 'icon' => 'fa-check-circle'],
                                'rejected_prodi', 'rejected_fakultas' => ['bg' => 'bg-red-500', 'icon' => 'fa-times-circle'],
                                default => ['bg' => 'bg-gray-500', 'icon' => 'fa-circle']
                            };
                        ?>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            <i class="fas <?php echo e($statusBadge['icon']); ?> mr-1.5"></i>
                            <?php echo e(ucfirst(str_replace('_', ' ', $pengajuan->status))); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pengajuan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Pengajuan</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tracking Token</label>
                                <p class="text-sm font-mono text-blue-600 font-medium mt-1"><?php echo e($pengajuan->tracking_token); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Program Studi</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->prodi->nama_prodi ?? '-'); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Pengajuan</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->created_at->format('d F Y, H:i')); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Last Update</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->updated_at->format('d F Y, H:i')); ?></p>
                                <p class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->updated_at->diffForHumans()); ?></p>
                            </div>
                            <?php if($pengajuan->completed_at): ?>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Selesai</label>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->completed_at->format('d F Y, H:i')); ?></p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processing Time</label>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->created_at->diffForHumans($pengajuan->completed_at, true)); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Data Mahasiswa</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">NIM</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->nim ?? $pengajuan->nim); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nama Lengkap</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Email</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->email ?? $pengajuan->email ?? '-'); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">No. Telepon</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->phone ?? $pengajuan->phone ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Keperluan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Keperluan</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase">Jenis Surat</label>
                            <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Keperluan</label>
                            <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->keperluan ?? '-'); ?></p>
                        </div>
                        
                        <?php if($additionalData && !empty($additionalData)): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-medium text-gray-500 uppercase mb-2 block">Data Tambahan</label>
                                <div class="space-y-2">
                                    <?php $__currentLoopData = $additionalData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(!in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa'])): ?>
                                            <div class="flex justify-between py-2 border-b border-gray-100">
                                                <span class="text-xs text-gray-600"><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?></span>
                                                <span class="text-sm text-gray-900 font-medium"><?php echo e(is_array($value) ? json_encode($value) : $value); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline Approval -->
                <?php if($pengajuan->approvalHistories && $pengajuan->approvalHistories->count() > 0): ?>
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Timeline Approval</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <?php $__currentLoopData = $pengajuan->approvalHistories->sortBy('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <div class="relative pb-8">
                                        <?php if(!$loop->last): ?>
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                        <?php endif; ?>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <?php
                                                    $iconColor = in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'bg-green-500' : 
                                                                (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'bg-red-500' : 'bg-blue-500');
                                                    $icon = in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'fa-check' : 
                                                           (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'fa-times' : 'fa-circle');
                                                ?>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white <?php echo e($iconColor); ?>">
                                                    <i class="fas <?php echo e($icon); ?> text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900"><?php echo e($history->action_label); ?></span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-gray-500">
                                                        <?php echo e($history->created_at->format('d F Y, H:i')); ?>

                                                        <?php if($history->performedBy): ?>
                                                            • oleh <?php echo e($history->performedBy->nama); ?>

                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <?php if($history->notes): ?>
                                                    <div class="mt-2 text-sm text-gray-700">
                                                        <p class="italic"><?php echo e($history->notes); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <!-- Admin Actions -->
                <?php if(!$pengajuan->trashed()): ?>
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Admin Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        
                        <?php if($pengajuan->surat_pengantar_url): ?>
                            <a href="<?php echo e($pengajuan->surat_pengantar_url); ?>" 
                            target="_blank"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                                <i class="fas fa-download mr-2"></i>Download Surat
                            </a>
                        <?php endif; ?>
                        
                        
                        <?php if($isStuck || in_array($pengajuan->status, ['approved_prodi', 'approved_fakultas'])): ?>
                            <button onclick="showForceCompleteModal()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium transition">
                                <i class="fas fa-bolt mr-2"></i>Force Complete
                            </button>
                        <?php endif; ?>
                        
                        
                        <?php if(in_array($pengajuan->status, ['rejected_prodi', 'rejected_fakultas'])): ?>
                            <button onclick="showReopenModal()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-undo mr-2"></i>Reopen Pengajuan
                            </button>
                        <?php endif; ?>
                        
                        
                        <button onclick="showChangeStatusModal()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium transition">
                            <i class="fas fa-sync mr-2"></i>Change Status
                        </button>
                        
                        
                        <button onclick="deletePengajuan(<?php echo e($pengajuan->id); ?>)" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                            <i class="fas fa-trash mr-2"></i>Hapus Pengajuan
                        </button>
                        
                        <a href="<?php echo e(route('admin.pengajuan.index')); ?>" 
                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
                <?php endif; ?>


                <!-- Status Info -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Status Info</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-600">Current Status</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $pengajuan->status))); ?></span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Prodi</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e($pengajuan->prodi->nama_prodi ?? '-'); ?></span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Fakultas</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo e($pengajuan->prodi->fakultas->nama_fakultas ?? '-'); ?></span>
                        </div>
                        <?php if($isStuck): ?>
                            <div class="flex items-center justify-between py-2 border-t border-gray-100">
                                <span class="text-sm text-orange-600 font-medium">Stuck Duration</span>
                                <span class="text-sm font-bold text-orange-600"><?php echo e($stuckDays); ?> hari</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="forceCompleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Force Complete Pengajuan</h3>
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-4">
                <p class="text-sm text-orange-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tindakan ini akan memaksa pengajuan menjadi status <strong>completed</strong> dan menandai sebagai selesai.
                </p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Force Complete (wajib)</label>
                <textarea id="forceCompleteReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Jelaskan alasan admin intervention..."></textarea>
            </div>
            <div class="flex gap-3">
                <button onclick="closeForceCompleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmForceComplete()" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                    Force Complete
                </button>
            </div>
        </div>
    </div>
</div>


<div id="reopenModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reopen Pengajuan</h3>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Pengajuan akan dibuka kembali dan bisa diproses ulang.
                </p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reset ke Status</label>
                <select id="reopenStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="pending">Pending (Staff Prodi review ulang)</option>
                    <option value="approved_prodi">Approved Prodi (Langsung ke Fakultas)</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Reopen (wajib)</label>
                <textarea id="reopenReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan alasan reopen..."></textarea>
            </div>
            <div class="flex gap-3">
                <button onclick="closeReopenModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmReopen()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Reopen
                </button>
            </div>
        </div>
    </div>
</div>


<div id="changeStatusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Change Status Manual</h3>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-4">
                <p class="text-sm text-purple-800">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Gunakan fitur ini dengan hati-hati. Status akan berubah tanpa validasi workflow.
                </p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                <input type="text" value="<?php echo e(ucfirst(str_replace('_', ' ', $pengajuan->status))); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                <select id="newStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="pending">Pending</option>
                    <option value="approved_prodi">Approved Prodi</option>
                    <option value="approved_fakultas">Approved Fakultas</option>
                    <option value="completed">Completed</option>
                    <option value="rejected_prodi">Rejected Prodi</option>
                    <option value="rejected_fakultas">Rejected Fakultas</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Perubahan (wajib)</label>
                <textarea id="changeStatusReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Jelaskan alasan perubahan status..."></textarea>
            </div>
            <div class="flex gap-3">
                <button onclick="closeChangeStatusModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmChangeStatus()" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Change Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Hapus Pengajuan</h3>
            <p class="text-sm text-gray-600 mb-4">
                Anda yakin ingin menghapus pengajuan ini? Data masih bisa dipulihkan nanti.
            </p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penghapusan (wajib)</label>
                <textarea id="deleteReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Masukkan alasan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let deleteId = null;
const pengajuanId = <?php echo e($pengajuan->id); ?>;
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Force Complete
function showForceCompleteModal() {
    document.getElementById('forceCompleteModal').classList.remove('hidden');
}

function closeForceCompleteModal() {
    document.getElementById('forceCompleteModal').classList.add('hidden');
    document.getElementById('forceCompleteReason').value = '';
}

function confirmForceComplete() {
    const reason = document.getElementById('forceCompleteReason').value.trim();
    
    if (!reason) {
        alert('Alasan wajib diisi');
        return;
    }
    
    fetch(`/admin/pengajuan/${pengajuanId}/force-complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Gagal force complete');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Reopen
function showReopenModal() {
    document.getElementById('reopenModal').classList.remove('hidden');
}

function closeReopenModal() {
    document.getElementById('reopenModal').classList.add('hidden');
    document.getElementById('reopenReason').value = '';
}

function confirmReopen() {
    const reason = document.getElementById('reopenReason').value.trim();
    const resetTo = document.getElementById('reopenStatus').value;
    
    if (!reason) {
        alert('Alasan wajib diisi');
        return;
    }
    
    fetch(`/admin/pengajuan/${pengajuanId}/reopen`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason, reset_to: resetTo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Gagal reopen');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Change Status
function showChangeStatusModal() {
    document.getElementById('changeStatusModal').classList.remove('hidden');
}

function closeChangeStatusModal() {
    document.getElementById('changeStatusModal').classList.add('hidden');
    document.getElementById('changeStatusReason').value = '';
}

function confirmChangeStatus() {
    const reason = document.getElementById('changeStatusReason').value.trim();
    const newStatus = document.getElementById('newStatus').value;
    
    if (!reason) {
        alert('Alasan wajib diisi');
        return;
    }
    
    fetch(`/admin/pengajuan/${pengajuanId}/change-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason, new_status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Gagal change status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Delete
function deletePengajuan(id) {
    deleteId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteReason').value = '';
}

function confirmDelete() {
    const reason = document.getElementById('deleteReason').value.trim();
    
    if (!reason) {
        alert('Alasan penghapusan wajib diisi');
        return;
    }
    
    fetch(`/admin/pengajuan/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo e(route("admin.pengajuan.index")); ?>';
        } else {
            alert(data.message || 'Gagal menghapus pengajuan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

// Restore
function restorePengajuan(id) {
    if (!confirm('Yakin ingin memulihkan pengajuan ini?')) return;
    
    fetch(`/admin/pengajuan/${id}/restore`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal memulihkan pengajuan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/admin/pengajuan/show.blade.php ENDPATH**/ ?>