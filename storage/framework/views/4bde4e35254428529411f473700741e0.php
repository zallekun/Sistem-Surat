

<?php $__env->startSection('title', 'Detail Pengajuan'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="text-sm font-medium text-gray-700 hover:text-blue-600">Kelola Pengajuan</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Detail</span>
    </div>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto -mt-6">
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
        <div class="bg-white shadow-sm rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Detail Pengajuan Surat</h2>
                        <p class="text-sm text-white/90 mt-1"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'Surat'); ?></p>
                    </div>
                    <div class="text-right">
                        <?php
                            $statusConfig = [
                                'pending' => ['bg' => 'bg-yellow-500', 'icon' => 'fa-clock', 'text' => 'Pending'],
                                'approved_prodi' => ['bg' => 'bg-blue-500', 'icon' => 'fa-check', 'text' => 'Approved Prodi'],
                                'approved_fakultas' => ['bg' => 'bg-indigo-500', 'icon' => 'fa-check-double', 'text' => 'Approved Fakultas'],
                                'completed' => ['bg' => 'bg-green-500', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                                'rejected_prodi' => ['bg' => 'bg-red-500', 'icon' => 'fa-times-circle', 'text' => 'Ditolak Prodi'],
                                'rejected_fakultas' => ['bg' => 'bg-red-500', 'icon' => 'fa-times-circle', 'text' => 'Ditolak Fakultas'],
                            ];
                            $status = $statusConfig[$pengajuan->status] ?? ['bg' => 'bg-gray-500', 'icon' => 'fa-circle', 'text' => ucfirst($pengajuan->status)];
                        ?>
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            <i class="fas <?php echo e($status['icon']); ?> mr-1.5"></i>
                            <?php echo e($status['text']); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pengajuan -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Pengajuan</h3>
                    </div>
                    <div class="p-6">
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
                                <label class="text-xs font-medium text-gray-500 uppercase">Status</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($status['text']); ?></p>
                            </div>
                            <?php if($pengajuan->completed_at): ?>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Selesai</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->completed_at->format('d F Y, H:i')); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Durasi Proses</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    <?php echo e($pengajuan->created_at->diffForHumans($pengajuan->completed_at, true)); ?>

                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Data Mahasiswa</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">NIM</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->nim ?? $pengajuan->nim ?? '-'); ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nama Lengkap</label>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa ?? '-'); ?></p>
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
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Keperluan</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase">Jenis Surat</label>
                            <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? '-'); ?></p>
                        </div>
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase">Keperluan</label>
                            <p class="text-sm text-gray-900 mt-1"><?php echo e($pengajuan->keperluan ?? '-'); ?></p>
                        </div>

                        <?php if($additionalData && !empty($additionalData)): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-medium text-gray-500 uppercase mb-3 block">Data Tambahan</label>

                                
                                <?php $__currentLoopData = $additionalData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa']) && is_array($value)): ?>
                                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                            <h4 class="text-xs font-semibold text-gray-700 uppercase mb-3"><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?></h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subKey => $subValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div>
                                                        <label class="text-xs font-medium text-gray-500"><?php echo e(ucfirst(str_replace('_', ' ', $subKey))); ?></label>
                                                        <?php if(is_array($subValue)): ?>
                                                            <div class="mt-1">
                                                                <?php $__currentLoopData = $subValue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <?php if(is_array($item) || is_object($item)): ?>
                                                            <div class="mb-2 p-2 bg-white border rounded">
                                                                <?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <div class="grid grid-cols-2 gap-3">
                                                                        <div>
                                                                            <label class="text-xs font-medium text-gray-500"><?php echo e(ucfirst(str_replace('_', ' ', $k))); ?></label>
                                                                        </div>
                                                                        <div>
                                                                            <p class="text-sm text-gray-900 mt-1"><?php echo e(is_array($v) ? implode(', ', $v) : $v); ?></p>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                                    <?php else: ?>
                                                                        <p class="text-sm text-gray-900"><?php echo e($item); ?></p>
                                                                    <?php endif; ?>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-sm text-gray-900 mt-1"><?php echo e($subValue ?? '-'); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                
                                <?php
                                    $simpleData = array_filter($additionalData, function($value, $key) {
                                        return !in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa']) && !is_array($value);
                                    }, ARRAY_FILTER_USE_BOTH);
                                ?>

                                <?php if(!empty($simpleData)): ?>
                                    <div class="grid grid-cols-2 gap-4">
                                        <?php $__currentLoopData = $simpleData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <label class="text-xs font-medium text-gray-500 uppercase"><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?></label>
                                                <p class="text-sm text-gray-900 mt-1"><?php echo e($value ?? '-'); ?></p>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline Approval -->
                <?php if($pengajuan->approvalHistories && $pengajuan->approvalHistories->count() > 0): ?>
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
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
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                    <?php echo e($history->action == 'approved' ? 'bg-green-500' : ($history->action == 'rejected' ? 'bg-red-500' : 'bg-blue-500')); ?>">
                                                    <i class="fas <?php echo e($history->action == 'approved' ? 'fa-check' : ($history->action == 'rejected' ? 'fa-times' : 'fa-clock')); ?> text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php echo e(ucfirst($history->action)); ?> oleh <?php echo e($history->performedBy->name ?? 'System'); ?>

                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        <?php echo e($history->created_at->format('d F Y, H:i')); ?>

                                                    </p>
                                                </div>
                                                <?php if($history->notes): ?>
                                                    <div class="mt-2 text-sm text-gray-700 bg-gray-50 p-2 rounded">
                                                        <?php echo e($history->notes); ?>

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

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Aksi Cepat</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <?php if(!$pengajuan->trashed()): ?>
                            <button onclick="showChangeStatusModal()"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Ubah Status
                            </button>
                        <?php endif; ?>

                        <?php if($pengajuan->surat_pengantar_url): ?>
                            <a href="<?php echo e($pengajuan->surat_pengantar_url); ?>"
                               target="_blank"
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                <i class="fas fa-download mr-2"></i>
                                Download Surat
                            </a>
                        <?php endif; ?>

                        <?php if(!$pengajuan->trashed()): ?>
                            <button onclick="deletePengajuan(<?php echo e($pengajuan->id); ?>)"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus Pengajuan
                            </button>
                        <?php endif; ?>

                        <a href="<?php echo e(route('admin.pengajuan.index')); ?>"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>

                <!-- Info Status -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Status Info</h3>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Saat Ini:</span>
                            <span class="font-medium text-gray-900"><?php echo e($status['text']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibuat:</span>
                            <span class="font-medium text-gray-900"><?php echo e($pengajuan->created_at->diffForHumans()); ?></span>
                        </div>
                        <?php if($pengajuan->updated_at != $pengajuan->created_at): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Update Terakhir:</span>
                            <span class="font-medium text-gray-900"><?php echo e($pengajuan->updated_at->diffForHumans()); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Change Status -->
<div id="changeStatusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Ubah Status Pengajuan</h3>
            <p class="text-sm text-gray-500 mt-1">Status saat ini: <span class="font-medium"><?php echo e($status['text']); ?></span></p>
        </div>

        <form id="changeStatusForm" onsubmit="submitChangeStatus(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                <select name="new_status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Status --</option>
                    <option value="pending">Pending</option>
                    <option value="approved_prodi">Approved Prodi</option>
                    <option value="approved_fakultas">Approved Fakultas</option>
                    <option value="completed">Completed</option>
                    <option value="rejected_prodi">Rejected Prodi</option>
                    <option value="rejected_fakultas">Rejected Fakultas</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Perubahan</label>
                <textarea name="reason" required rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Jelaskan alasan perubahan status..."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeChangeStatusModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showChangeStatusModal() {
    document.getElementById('changeStatusModal').classList.remove('hidden');
}

function closeChangeStatusModal() {
    document.getElementById('changeStatusModal').classList.add('hidden');
    document.getElementById('changeStatusForm').reset();
}

function submitChangeStatus(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    fetch('/admin/pengajuan/<?php echo e($pengajuan->id); ?>/change-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showError(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan jaringan');
    });
}

async function deletePengajuan(id) {
    const confirmed = await confirm('Apakah Anda yakin ingin menghapus pengajuan ini?');
    if (!confirmed) return;

    fetch(`/admin/pengajuan/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                window.location.href = '<?php echo e(route("admin.pengajuan.index")); ?>';
            }, 1500);
        } else {
            showError(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan jaringan');
    });
}

async function restorePengajuan(id) {
    const confirmed = await confirm('Apakah Anda yakin ingin memulihkan pengajuan ini?');
    if (!confirmed) return;

    fetch(`/admin/pengajuan/${id}/restore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showError(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan jaringan');
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/admin/pengajuan/show.blade.php ENDPATH**/ ?>