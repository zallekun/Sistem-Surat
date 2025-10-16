


<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="text-sm text-gray-600 mt-1">Overview sistem pengajuan surat</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <!-- Total Pengajuan -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Total Pengajuan</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['total_pengajuan'])); ?></p>
                    </div>
                    <i class="fas fa-file-alt text-blue-500 text-2xl"></i>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Pending</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['pengajuan_pending'])); ?></p>
                    </div>
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
            </div>

            <!-- Completed (This Month) -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Completed</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['pengajuan_completed'])); ?></p>
                        <p class="text-xs text-gray-500 mt-0.5">Bulan ini</p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
            </div>

            <!-- Rejected (This Month) -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Rejected</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['pengajuan_rejected'])); ?></p>
                        <p class="text-xs text-gray-500 mt-0.5">Bulan ini</p>
                    </div>
                    <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                </div>
            </div>

            <!-- Stuck -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Stuck > 3 Hari</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['pengajuan_stuck'])); ?></p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-orange-500 text-2xl"></i>
                </div>
            </div>

            <!-- Total Users -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600 uppercase">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['total_users'])); ?></p>
                    </div>
                    <i class="fas fa-users text-purple-500 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Avg Processing Time -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Average Processing Time</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1"><?php echo e($stats['avg_processing_hours'] ?? 0); ?> jam</p>
                </div>
                <i class="fas fa-hourglass-half text-blue-600 text-3xl"></i>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Trend Chart -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Trend Pengajuan (30 Hari Terakhir)</h3>
                <div style="height: 250px;"> 
                    <canvas id="trendChart"></canvas>
        </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Distribution</h3>
                <div style="height: 250px;"> 
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Per Prodi Chart -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengajuan Per Prodi</h3>
            <div style="height: 300px;"> 
                <canvas id="prodiChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity & Stuck Pengajuan -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Pengajuan -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Pengajuan Terbaru</h3>
                        <a href="<?php echo e(route('admin.pengajuan.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800">
                            Lihat Semua →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <?php if($recentPengajuan->count() > 0): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $recentPengajuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa); ?>

                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php echo e($pengajuan->jenisSurat->nama_jenis ?? 'N/A'); ?> • 
                                            <?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?> •
                                            <?php echo e($pengajuan->created_at->diffForHumans()); ?>

                                        </p>
                                    </div>
                                    <a href="<?php echo e(route('admin.pengajuan.show', $pengajuan->id)); ?>" 
                                       class="ml-4 text-xs text-blue-600 hover:text-blue-800">
                                        Detail
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 text-center py-4">Belum ada pengajuan</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stuck Pengajuan Alert -->
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-orange-900">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Pengajuan Stuck
                        </h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-500 text-white">
                            <?php echo e($stuckPengajuan->count()); ?>

                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <?php if($stuckPengajuan->count() > 0): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $stuckPengajuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa); ?>

                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?php echo e($pengajuan->prodi->nama_prodi ?? 'N/A'); ?> •
                                            Stuck <?php echo e($pengajuan->updated_at->diffInDays(now())); ?> hari
                                        </p>
                                    </div>
                                    <a href="<?php echo e(route('admin.pengajuan.show', $pengajuan->id)); ?>" 
                                       class="ml-4 text-xs bg-orange-600 text-white px-3 py-1 rounded hover:bg-orange-700">
                                        Review
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-green-600 text-center py-4">
                            <i class="fas fa-check-circle mr-1"></i>
                            Tidak ada pengajuan stuck
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Trend Chart (Line)
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartTrend['labels'], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: <?php echo json_encode($chartTrend['data'], 15, 512) ?>,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Status Chart (Pie)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($chartStatus['labels'], 15, 512) ?>,
            datasets: [{
                data: <?php echo json_encode($chartStatus['data'], 15, 512) ?>,
                backgroundColor: [
                    'rgb(234, 179, 8)',  // pending - yellow
                    'rgb(34, 197, 94)',  // approved_prodi - green
                    'rgb(59, 130, 246)', // approved_fakultas - blue
                    'rgb(16, 185, 129)', // completed - teal
                    'rgb(239, 68, 68)',  // rejected_prodi - red
                    'rgb(220, 38, 38)',  // rejected_fakultas - dark red
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Prodi Chart (Bar)
    const prodiCtx = document.getElementById('prodiChart').getContext('2d');
    new Chart(prodiCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartProdi['labels'], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: <?php echo json_encode($chartProdi['data'], 15, 512) ?>,
                backgroundColor: 'rgb(59, 130, 246)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rezal-suryadi-putra/Documents/KULIAH/SEMESTER 7/KP/sistem-surat/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>