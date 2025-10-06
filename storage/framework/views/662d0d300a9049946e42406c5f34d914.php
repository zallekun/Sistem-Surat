<?php $__env->startSection('title', 'Master Data Dosen Wali'); ?>

<?php $__env->startSection('breadcrumb'); ?>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <a href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600">Master Data</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Dosen Wali</span>
    </div>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Master Data Dosen Wali</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola data dosen wali</p>
                    </div>
                    <a href="<?php echo e(route('admin.dosen-wali.create')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Dosen Wali
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?php echo e(request('search')); ?>" 
                               placeholder="Cari nama atau NID dosen wali..."
                               class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-64">
                        <select name="prodi_id" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Prodi</option>
                            <?php $__currentLoopData = $prodis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e(request('prodi_id') == $p->id ? 'selected' : ''); ?>>
                                    <?php echo e($p->nama_prodi); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a href="<?php echo e(route('admin.dosen-wali.index')); ?>" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </form>
            </div>

            <!-- Table -->
            <?php if($dosenWalis->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Dosen</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prodi</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $dosenWalis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dosenWali): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo e($dosenWalis->firstItem() + $index); ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo e($dosenWali->nama); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono text-xs"><?php echo e($dosenWali->nid); ?></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($dosenWali->prodi->nama_prodi ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <?php if($dosenWali->is_active): ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?php echo e(route('admin.dosen-wali.edit', $dosenWali->id)); ?>" 
                                               class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs font-medium">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.dosen-wali.destroy', $dosenWali->id)); ?>"
                                                  method="POST"
                                                  onsubmit="return confirm('Anda yakin ingin menghapus Dosen Wali ini?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs font-medium">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    <?php echo e($dosenWalis->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-user-tie fa-3x text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tidak ada data Dosen Wali</p>
                </div>
            <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/admin/master/dosen-wali/index.blade.php ENDPATH**/ ?>