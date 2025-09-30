

<?php $__env->startSection('title', 'Arsip Surat'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Arsip Surat</h2>
                        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(Auth::user()->prodi->nama_prodi ?? 'Program Studi'); ?></p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-sm text-gray-500">Total: <?php echo e($totalCount); ?> surat</span>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-400">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-blue-800">Total Arsip</p>
                                <p class="text-xl font-bold text-blue-900 mt-1"><?php echo e($totalCount); ?></p>
                            </div>
                            <i class="fas fa-archive text-blue-300 text-xl"></i>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg shadow-sm p-4 border-l-4 border-green-400">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-green-800">Bulan Ini</p>
                                <p class="text-xl font-bold text-green-900 mt-1"><?php echo e($thisMonthCount); ?></p>
                            </div>
                            <i class="fas fa-calendar-check text-green-300 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Section -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('staff.arsip.index')); ?>" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="NIM, Nama, atau Token..."
                                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
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

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Dari Tanggal</label>
                            <input type="date" 
                                   name="date_from" 
                                   value="<?php echo e(request('date_from')); ?>" 
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                            <input type="date" 
                                   name="date_to" 
                                   value="<?php echo e(request('date_to')); ?>" 
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="<?php echo e(route('staff.arsip.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                        </div>
                        
                        <!-- Export Button -->
                        <a href="<?php echo e(route('staff.arsip.export', request()->all())); ?>" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                        </a>
                    </div>

                    <!-- Applied Filters -->
                    <?php if(request()->hasAny(['search', 'jenis_surat_id', 'date_from', 'date_to'])): ?>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                    
                                    <?php if(request('search')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            "<?php echo e(request('search')); ?>"
                                            <a href="<?php echo e(route('staff.arsip.index', request()->except('search'))); ?>" class="ml-1.5 hover:text-blue-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('jenis_surat_id')): ?>
                                        <?php $selectedJenis = $jenisSurat->find(request('jenis_surat_id')) ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo e($selectedJenis->nama_jenis ?? 'N/A'); ?>

                                            <a href="<?php echo e(route('staff.arsip.index', request()->except('jenis_surat_id'))); ?>" class="ml-1.5 hover:text-green-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('date_from') || request('date_to')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?php echo e(request('date_from') ?? '...'); ?> s/d <?php echo e(request('date_to') ?? '...'); ?>

                                            <a href="<?php echo e(route('staff.arsip.index', request()->except(['date_from', 'date_to']))); ?>" class="ml-1.5 hover:text-purple-900">×</a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?php echo e(route('staff.arsip.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Hapus semua
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Section -->
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
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Jenis Surat</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Tgl Pengajuan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Tgl Selesai</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-40">Aksi</th>
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
                                                <span class="text-sm font-medium text-gray-700"><?php echo e($pengajuans->firstItem() + $index); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <span class="text-xs font-mono text-blue-600 font-medium"><?php echo e($pengajuan->tracking_token); ?></span>
                                            </td>
                                            <td class="px-4 py-4 w-48">
                                                <div class="text-sm font-medium text-gray-900"><?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->mahasiswa->nim ?? $pengajuan->nim); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->jenisSurat->kode_surat ?? ''); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->created_at->format('d/m/Y')); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->created_at->format('H:i')); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                <div class="text-sm text-gray-900"><?php echo e($pengajuan->completed_at?->format('d/m/Y') ?? '-'); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($pengajuan->completed_at?->format('H:i') ?? ''); ?></div>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="<?php echo e(route('staff.arsip.show', $pengajuan->id)); ?>" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                        <i class="fas fa-eye mr-1.5"></i>
                                                        Detail
                                                    </a>
                                                    
                                                    <?php if($pengajuan->surat_generated_id || $pengajuan->surat_pengantar_url): ?>
                                                        <a href="<?php echo e($pengajuan->surat_pengantar_url ?? '#'); ?>" 
                                                           target="_blank"
                                                           class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-xs font-medium">
                                                            <i class="fas fa-download mr-1.5"></i>
                                                            PDF
                                                        </a>
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
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($pengajuans->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-archive fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        <?php if(request()->hasAny(['search', 'jenis_surat_id', 'date_from', 'date_to'])): ?>
                            Tidak Ada Data yang Cocok
                        <?php else: ?>
                            Arsip Masih Kosong
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">
                        <?php if(request()->hasAny(['search', 'jenis_surat_id', 'date_from', 'date_to'])): ?>
                            Coba ubah filter atau <a href="<?php echo e(route('staff.arsip.index')); ?>" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        <?php else: ?>
                            Belum ada surat yang selesai diproses
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/staff/arsip/index.blade.php ENDPATH**/ ?>