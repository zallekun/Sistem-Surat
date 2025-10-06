


<?php $__env->startSection('title', 'Kelola Pengajuan'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Kelola Pengajuan</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Manajemen semua pengajuan surat</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="<?php echo e(route('admin.pengajuan.export', request()->query())); ?>" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                            <i class="fas fa-download mr-2"></i>Export Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('admin.pengajuan.index')); ?>" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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

                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Prodi</label>
                            <select name="prodi_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Prodi</option>
                                <?php $__currentLoopData = $prodis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prodi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($prodi->id); ?>" <?php echo e(request('prodi_id') == $prodi->id ? 'selected' : ''); ?>>
                                        <?php echo e($prodi->nama_prodi); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(request('status') == $key ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Jenis Surat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Surat</label>
                            <select name="jenis_surat_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Jenis</option>
                                <?php $__currentLoopData = $jenisSurat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($jenis->id); ?>" <?php echo e(request('jenis_surat_id') == $jenis->id ? 'selected' : ''); ?>>
                                        <?php echo e($jenis->nama_jenis); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="<?php echo e(request('date_from')); ?>" 
                                   placeholder="Dari"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Action Buttons & Checkboxes -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-4 items-center">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                            
                            <div class="flex gap-3 ml-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="show_stuck" value="1" <?php echo e(request('show_stuck') ? 'checked' : ''); ?> class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Stuck > 3 hari</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="show_deleted" value="1" <?php echo e(request('show_deleted') ? 'checked' : ''); ?> class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Tampilkan yang dihapus</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Applied Filters -->
                    <?php if(request()->hasAny(['search', 'prodi_id', 'status', 'jenis_surat_id', 'date_from'])): ?>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                    
                                    <?php if(request('search')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            "<?php echo e(request('search')); ?>"
                                            <a href="<?php echo e(route('admin.pengajuan.index', request()->except('search'))); ?>" class="ml-1.5 hover:text-blue-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('prodi_id')): ?>
                                        <?php $selectedProdi = $prodis->find(request('prodi_id')) ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo e($selectedProdi->nama_prodi ?? 'N/A'); ?>

                                            <a href="<?php echo e(route('admin.pengajuan.index', request()->except('prodi_id'))); ?>" class="ml-1.5 hover:text-green-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('status')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?php echo e($statuses[request('status')] ?? request('status')); ?>

                                            <a href="<?php echo e(route('admin.pengajuan.index', request()->except('status'))); ?>" class="ml-1.5 hover:text-purple-900">×</a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Hapus semua
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table -->
            <?php if($pengajuans->count() > 0): ?>
                <div class="relative">
                    <div class="overflow-hidden">
                        <!-- Fixed Header -->
                        <div class="bg-gray-50 border-b-2 border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-16">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Token</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-48">Mahasiswa</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Prodi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Jenis Surat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-36">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        
                        <!-- Scrollable Body -->
                        <div class="overflow-y-auto scroll-smooth" style="max-height: 500px; will-change: scroll-position;">
                            <table class="min-w-full">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <?php $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-blue-50 <?php echo e($pengajuan->trashed() ? 'bg-red-50' : ''); ?>">
                                            <td class="px-4 py-4 w-16 text-center">
                                                <span class="text-sm font-medium text-gray-700"><?php echo e($pengajuans->firstItem() + $index); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <span class="text-xs font-mono text-blue-600 font-medium"><?php echo e($pengajuan->tracking_token); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-48">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->mahasiswa->nim ?? $pengajuan->nim); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                                    <?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->created_at->format('d/m/Y')); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->created_at->format('H:i')); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-36">
                                                <?php if($pengajuan->trashed()): ?>
                                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-trash mr-1"></i>Dihapus
                                                    </span>
                                                <?php else: ?>
                                                    <?php
                                                        $statusClass = match($pengajuan->status) {
                                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                                            'approved_prodi' => 'bg-blue-100 text-blue-800',
                                                            'approved_fakultas' => 'bg-indigo-100 text-indigo-800',
                                                            'completed' => 'bg-green-100 text-green-800',
                                                            'rejected_prodi', 'rejected_fakultas' => 'bg-red-100 text-red-800',
                                                            default => 'bg-gray-100 text-gray-800'
                                                        };
                                                    ?>
                                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full <?php echo e($statusClass); ?>">
                                                        <?php echo e($statuses[$pengajuan->status] ?? ucfirst($pengajuan->status)); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="<?php echo e(route('admin.pengajuan.show', $pengajuan->id)); ?>" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                        <i class="fas fa-eye mr-1.5"></i>Detail
                                                    </a>
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
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($pengajuans->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data</h3>
                    <p class="text-gray-500 text-sm">
                        <?php if(request()->hasAny(['search', 'prodi_id', 'status', 'jenis_surat_id'])): ?>
                            Coba ubah filter atau <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        <?php else: ?>
                            Belum ada pengajuan surat
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/admin/pengajuan/index.blade.php ENDPATH**/ ?>