

<?php $__env->startSection('title', 'Daftar Pengajuan Surat'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Daftar Pengajuan Surat</h2>
                    <span class="text-sm text-gray-500">
                        Total: <?php echo e(isset($pengajuans) ? $pengajuans->count() : 0); ?> pengajuan
                    </span>
                </div>
            </div>

            <!-- Filter & Search Section -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('staff.pengajuan.index')); ?>" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="Token, NIM, atau Nama..."
                                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="approved_prodi" <?php echo e(request('status') == 'approved_prodi' ? 'selected' : ''); ?>>Disetujui Prodi</option>
                                <option value="processed" <?php echo e(request('status') == 'processed' ? 'selected' : ''); ?>>Sudah Diproses</option>
                                <option value="rejected_prodi" <?php echo e(request('status') == 'rejected_prodi' ? 'selected' : ''); ?>>Ditolak Prodi</option>
                                <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Selesai</option>
                            </select>
                        </div>

                        <!-- Jenis Surat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Surat</label>
                            <select name="jenis_surat" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Jenis</option>
                                <?php if(isset($jenisSurat)): ?>
                                    <?php $__currentLoopData = $jenisSurat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($jenis->id); ?>" <?php echo e(request('jenis_surat') == $jenis->id ? 'selected' : ''); ?>>
                                            <?php echo e($jenis->nama_jenis); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rentang Tanggal</label>
                            <div class="flex gap-2">
                                <input type="date" 
                                       name="date_from" 
                                       value="<?php echo e(request('date_from')); ?>" 
                                       class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="date" 
                                       name="date_to" 
                                       value="<?php echo e(request('date_to')); ?>" 
                                       class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="<?php echo e(route('staff.pengajuan.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                        </div>
                        
                        <!-- Quick Filters -->
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('staff.pengajuan.index', ['status' => 'pending'])); ?>" 
                               class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium hover:bg-yellow-200 transition">
                                Pending (<?php echo e($pendingCount ?? 0); ?>)
                            </a>
                            <a href="<?php echo e(route('staff.pengajuan.index', ['status' => 'approved_prodi'])); ?>" 
                               class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-xs font-medium hover:bg-green-200 transition">
                                Disetujui (<?php echo e($approvedCount ?? 0); ?>)
                            </a>
                            <a href="<?php echo e(route('staff.pengajuan.index', ['status' => 'completed'])); ?>" 
                               class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium hover:bg-blue-200 transition">
                                Selesai (<?php echo e($completedCount ?? 0); ?>)
                            </a>
                        </div>
                    </div>

                    <!-- Applied Filters -->
                    <?php if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to'])): ?>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                    
                                    <?php if(request('search')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            "<?php echo e(request('search')); ?>"
                                            <a href="<?php echo e(route('staff.pengajuan.index', request()->except('search'))); ?>" class="ml-1.5 hover:text-blue-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('status')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo e(ucfirst(str_replace('_', ' ', request('status')))); ?>

                                            <a href="<?php echo e(route('staff.pengajuan.index', request()->except('status'))); ?>" class="ml-1.5 hover:text-green-900">×</a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?php echo e(route('staff.pengajuan.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Hapus semua
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Section with Fixed Header -->
            <?php if(isset($pengajuans) && $pengajuans->count() > 0): ?>
                <div class="relative">
                    <!-- Table Container - Fixed height with scroll -->
                    <div class="overflow-hidden">
                        <!-- Fixed Header -->
                        <div class="bg-gray-50 border-b-2 border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-16">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Token</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-48">Mahasiswa</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Jenis Surat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-36">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Tanggal</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-52">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        
                        <!-- Scrollable Body -->
                        <div class="overflow-y-auto scroll-smooth" style="max-height: 500px; will-change: scroll-position;">
                            <table class="min-w-full">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <?php $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-blue-50">
                                            <td class="px-4 py-4 w-16 text-center">
                                                <span class="text-sm font-medium text-gray-700"><?php echo e($index + 1); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <span class="text-xs font-mono text-blue-600 font-medium"><?php echo e($pengajuan->tracking_token); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-48">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($pengajuan->nama_mahasiswa); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->nim); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->jenisSurat->kode_surat ?? ''); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-36">
                                                <?php
                                                    $statusConfig = [
                                                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                                                        'processed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Diproses'],
                                                        'approved_prodi' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui'],
                                                        'rejected_prodi' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                                                        'completed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Selesai'],
                                                    ];
                                                    $status = $statusConfig[$pengajuan->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($pengajuan->status)];
                                                ?>
                                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full <?php echo e($status['bg']); ?> <?php echo e($status['text']); ?>">
                                                    <?php echo e($status['label']); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->created_at->format('d/m/Y')); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->created_at->format('H:i')); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-52">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="<?php echo e(route('staff.pengajuan.show', $pengajuan->id)); ?>" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                        <i class="fas fa-eye mr-1.5"></i>
                                                        Detail
                                                    </a>
                                                    
                                                    <?php if($pengajuan->status === 'pending'): ?>
                                                        <button onclick="processAction(<?php echo e($pengajuan->id); ?>, 'approve')" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-xs font-medium">
                                                            <i class="fas fa-check mr-1.5"></i>
                                                            Setuju
                                                        </button>
                                                        <button onclick="rejectAction(<?php echo e($pengajuan->id); ?>)" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-xs font-medium">
                                                            <i class="fas fa-times mr-1.5"></i>
                                                            Tolak
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if(method_exists($pengajuans, 'links')): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($pengajuans->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        <?php if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to'])): ?>
                            Tidak Ada Data yang Cocok
                        <?php else: ?>
                            Tidak Ada Pengajuan
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">
                        <?php if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to'])): ?>
                            Coba ubah filter atau <a href="<?php echo e(route('staff.pengajuan.index')); ?>" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        <?php else: ?>
                            Belum ada pengajuan surat yang masuk
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function processAction(id, action) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
    
    fetch(`/staff/pengajuan/${id}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ action: 'approve' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
    });
}

function rejectAction(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (!reason || reason.trim() === '') {
        alert('Alasan penolakan harus diisi!');
        return;
    }
    
    fetch(`/staff/pengajuan/${id}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action: 'reject',
            rejection_reason: reason.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
    });
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/staff/pengajuan/index.blade.php ENDPATH**/ ?>