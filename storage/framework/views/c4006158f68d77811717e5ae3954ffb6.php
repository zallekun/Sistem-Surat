

<?php $__env->startSection('title', 'Daftar Surat Fakultas'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Daftar Surat Fakultas</h2>
                        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(Auth::user()->prodi->fakultas->nama_fakultas ?? 'Fakultas Sains dan Informatika'); ?></p>
                    </div>
                    <span class="text-sm text-gray-500">
                        Total: <?php echo e($paginationInfo->total ?? 0); ?> item
                    </span>
                </div>
            </div>

            <!-- Filter & Search Section -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('fakultas.surat.index')); ?>" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="<?php echo e(request('search')); ?>" 
                                       placeholder="Nomor, perihal, atau nama..."
                                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Program Studi</label>
                            <select name="prodi_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Prodi</option>
                                <?php $__currentLoopData = $prodis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prodi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($prodi->id); ?>" <?php echo e(request('prodi_id') == $prodi->id ? 'selected' : ''); ?>>
                                        <?php echo e($prodi->nama_prodi); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                            <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Tipe</option>
                                <option value="surat" <?php echo e(request('type') == 'surat' ? 'selected' : ''); ?>>Surat</option>
                                <option value="pengajuan" <?php echo e(request('type') == 'pengajuan' ? 'selected' : ''); ?>>Pengajuan</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status->id); ?>" <?php echo e(request('status_id') == $status->id ? 'selected' : ''); ?>>
                                        <?php echo e($status->nama_status); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="<?php echo e(route('fakultas.surat.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                        </div>
                        
                        <!-- Quick Filters -->
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('fakultas.surat.index', ['status' => 'pending'])); ?>" 
                               class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium hover:bg-yellow-200 transition">
                                Perlu Review (<?php echo e($items->where('status_class', 'bg-yellow-100 text-yellow-800')->count()); ?>)
                            </a>
                            <a href="<?php echo e(route('fakultas.surat.index', ['status' => 'completed'])); ?>" 
                               class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-xs font-medium hover:bg-green-200 transition">
                                Selesai (<?php echo e($items->where('status_class', 'bg-green-100 text-green-800')->count()); ?>)
                            </a>
                            <a href="<?php echo e(route('fakultas.surat.index', ['type' => 'pengajuan'])); ?>" 
                               class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium hover:bg-blue-200 transition">
                                Pengajuan (<?php echo e($items->where('type', 'pengajuan')->count()); ?>)
                            </a>
                        </div>
                    </div>

                    <!-- Applied Filters -->
                    <?php if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id'])): ?>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                    
                                    <?php if(request('search')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            "<?php echo e(request('search')); ?>"
                                            <a href="<?php echo e(route('fakultas.surat.index', request()->except('search'))); ?>" class="ml-1.5 hover:text-blue-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('prodi_id')): ?>
                                        <?php $selectedProdi = $prodis->find(request('prodi_id')) ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo e($selectedProdi->nama_prodi ?? 'N/A'); ?>

                                            <a href="<?php echo e(route('fakultas.surat.index', request()->except('prodi_id'))); ?>" class="ml-1.5 hover:text-green-900">×</a>
                                        </span>
                                    <?php endif; ?>

                                    <?php if(request('type')): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?php echo e(ucfirst(request('type'))); ?>

                                            <a href="<?php echo e(route('fakultas.surat.index', request()->except('type'))); ?>" class="ml-1.5 hover:text-purple-900">×</a>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?php echo e(route('fakultas.surat.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Hapus semua
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Section with Fixed Header -->
            <?php if($items->count() > 0): ?>
                <div class="relative">
                    <div class="overflow-hidden">
                        <!-- Fixed Header -->
                        <div class="bg-gray-50 border-b-2 border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-16">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-24">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Nomor/ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Perihal</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Prodi</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Dibuat Oleh</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-28">Tanggal</th>
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
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-blue-50">
                                        <td class="px-4 py-4 w-16 text-center">
                                            <span class="text-sm font-medium text-gray-700"><?php echo e($paginationInfo->from + $index); ?></span>
                                        </td>
                                        <td class="px-4 py-4 w-24">
                                            <?php if($item->type === 'pengajuan'): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-file-import mr-1"></i>
                                                    Pengajuan
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-file-alt mr-1"></i>
                                                    Surat
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 w-32">
                                            <?php if($item->type === 'pengajuan'): ?>
                                                <div class="text-xs font-mono text-blue-600 font-medium"><?php echo e($item->nomor_surat); ?></div>
                                                <div class="text-xs text-gray-500 mt-0.5">NIM: <?php echo e($item->original_pengajuan->nim ?? 'N/A'); ?></div>
                                            <?php else: ?>
                                                <div class="text-xs font-mono text-gray-900 font-medium"><?php echo e($item->nomor_surat); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900 line-clamp-2"><?php echo e($item->perihal); ?></div>
                                            <?php if($item->type === 'pengajuan'): ?>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($item->nama_mahasiswa ?? 'N/A'); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 w-32">
                                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                                <?php echo e($item->prodi->nama_prodi ?? 'N/A'); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-4 w-40">
                                            <div class="text-sm text-gray-900"><?php echo e($item->createdBy->nama ?? 'N/A'); ?></div>
                                            <?php if($item->type === 'surat' && isset($item->original_surat->createdBy->jabatan)): ?>
                                                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($item->original_surat->createdBy->jabatan->nama_jabatan); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 w-28">
                                            <div class="text-sm text-gray-900"><?php echo e($item->created_at->format('d/m/Y')); ?></div>
                                            <div class="text-xs text-gray-500 mt-0.5"><?php echo e($item->created_at->format('H:i')); ?></div>
                                        </td>
                                        <td class="px-4 py-4 w-36">
                                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full <?php echo e($item->status_class); ?>">
                                                <?php echo e($item->status_display); ?>

                                            </span>
                                        </td>
                                        <td class="px-4 py-4 w-32">
                                            <div class="flex items-center justify-center">
                                                <?php if($item->type === 'pengajuan'): ?>
                                                    <?php 
                                                        $pengajuan = $item->original_pengajuan;
                                                        $needsPengantar = method_exists($pengajuan, 'needsSuratPengantar') ? $pengajuan->needsSuratPengantar() : false;
                                                        $hasPengantar = method_exists($pengajuan, 'hasSuratPengantar') ? $pengajuan->hasSuratPengantar() : false;
                                                    ?>
                                                    
                                                    <?php if($pengajuan->status === 'approved_prodi' && $needsPengantar && !$hasPengantar): ?>
                                                        <span class="text-yellow-600 text-xs">
                                                            <i class="fas fa-exclamation-triangle"></i> Menunggu Pengantar
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="<?php echo e(route('fakultas.surat.show', $pengajuan->id)); ?>" 
                                                           class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                            <i class="fas fa-eye mr-1.5"></i>
                                                            Detail
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <a href="<?php echo e(route('fakultas.surat.show', $item->id)); ?>" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                        <i class="fas fa-eye mr-1.5"></i>
                                                        Detail
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
                <?php if(method_exists($paginationInfo, 'links') || ($paginationInfo->has_pages ?? false)): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Menampilkan <span class="font-medium"><?php echo e($paginationInfo->from); ?></span> sampai 
                                <span class="font-medium"><?php echo e($paginationInfo->to); ?></span> dari 
                                <span class="font-medium"><?php echo e($paginationInfo->total); ?></span> item
                            </div>
                            <div class="flex gap-1">
                                <?php if($paginationInfo->current_page > 1): ?>
                                    <a href="?page=<?php echo e($paginationInfo->current_page - 1); ?>&<?php echo e(http_build_query(request()->except('page'))); ?>" 
                                       class="px-3 py-2 text-sm bg-white border rounded-lg hover:bg-gray-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for($i = max(1, $paginationInfo->current_page - 2); $i <= min($paginationInfo->last_page, $paginationInfo->current_page + 2); $i++): ?>
                                    <a href="?page=<?php echo e($i); ?>&<?php echo e(http_build_query(request()->except('page'))); ?>" 
                                       class="px-3 py-2 text-sm border rounded-lg <?php echo e($i == $paginationInfo->current_page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50'); ?>">
                                        <?php echo e($i); ?>

                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($paginationInfo->current_page < $paginationInfo->last_page): ?>
                                    <a href="?page=<?php echo e($paginationInfo->current_page + 1); ?>&<?php echo e(http_build_query(request()->except('page'))); ?>" 
                                       class="px-3 py-2 text-sm bg-white border rounded-lg hover:bg-gray-50">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        <?php if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id'])): ?>
                            Tidak Ada Data yang Cocok
                        <?php else: ?>
                            Tidak Ada Data
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-500 text-sm">
                        <?php if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id'])): ?>
                            Coba ubah filter atau <a href="<?php echo e(route('fakultas.surat.index')); ?>" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        <?php else: ?>
                            Belum ada surat atau pengajuan yang perlu diproses
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/fakultas/surat/index.blade.php ENDPATH**/ ?>