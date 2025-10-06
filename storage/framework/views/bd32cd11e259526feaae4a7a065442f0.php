


<?php $__env->startSection('title', 'Audit Trail'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Audit Trail</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Log aktivitas admin intervention</p>
                    </div>
                    <a href="<?php echo e(route('admin.audit-trail.export', request()->query())); ?>" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                        <i class="fas fa-download mr-2"></i>Export Excel
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Search</label>
                            <input type="text" 
                                   name="search" 
                                   value="<?php echo e(request('search')); ?>" 
                                   placeholder="Cari di reason atau model ID..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Action</label>
                            <select name="action" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Action</option>
                                <option value="force_complete" <?php echo e(request('action') == 'force_complete' ? 'selected' : ''); ?>>Force Complete</option>
                                <option value="reopen" <?php echo e(request('action') == 'reopen' ? 'selected' : ''); ?>>Reopen</option>
                                <option value="change_status" <?php echo e(request('action') == 'change_status' ? 'selected' : ''); ?>>Change Status</option>
                                <option value="delete" <?php echo e(request('action') == 'delete' ? 'selected' : ''); ?>>Delete</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">User</label>
                            <select name="user_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua User</option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($u->id); ?>" <?php echo e(request('user_id') == $u->id ? 'selected' : ''); ?>>
                                        <?php echo e($u->nama); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal</label>
                            <input type="date" 
                                   name="date" 
                                   value="<?php echo e(request('date')); ?>" 
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        <a href="<?php echo e(route('admin.audit-trail.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                            <i class="fas fa-redo mr-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <?php if($logs->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Model</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">IP Address</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        <?php echo e($log->created_at->format('d M Y H:i')); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-blue-600 text-xs font-semibold"><?php echo e(substr($log->user->nama ?? 'U', 0, 2)); ?></span>
                                            </div>
                                            <span class="text-gray-900"><?php echo e($log->user->nama ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php
                                            $actionBadge = match($log->action) {
                                                'force_complete' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Force Complete'],
                                                'reopen' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Reopen'],
                                                'change_status' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Change Status'],
                                                'delete' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Delete'],
                                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($log->action)]
                                            };
                                        ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($actionBadge['bg']); ?> <?php echo e($actionBadge['text']); ?>">
                                            <?php echo e($actionBadge['label']); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?php echo e(class_basename($log->model_type)); ?> #<?php echo e($log->model_id); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-md truncate" title="<?php echo e($log->reason); ?>">
                                        <?php echo e(Str::limit($log->reason, 50)); ?>

                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                        <?php echo e($log->ip_address); ?>

                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button onclick="showDetail(<?php echo e($log->id); ?>)" 
                                                class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs font-medium">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    <?php echo e($logs->appends(request()->query())->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tidak ada audit log</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Audit Detail</h3>
                <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="detailContent" class="space-y-4">
                <!-- Will be filled by JavaScript -->
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function showDetail(id) {
    fetch(`/admin/audit-trail/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600 mb-2"><strong>Action:</strong> ${data.action}</p>
                    <p class="text-sm text-gray-600 mb-2"><strong>User:</strong> ${data.user_name}</p>
                    <p class="text-sm text-gray-600 mb-2"><strong>Waktu:</strong> ${data.created_at}</p>
                    <p class="text-sm text-gray-600 mb-2"><strong>IP Address:</strong> ${data.ip_address}</p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Reason:</h4>
                    <p class="text-sm text-gray-700 bg-yellow-50 p-3 rounded border-l-4 border-yellow-500">${data.reason}</p>
                </div>
                
                ${data.old_data ? `
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Old Data:</h4>
                        <pre class="text-xs bg-gray-100 p-3 rounded overflow-x-auto">${JSON.stringify(data.old_data, null, 2)}</pre>
                    </div>
                ` : ''}
                
                ${data.new_data ? `
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">New Data:</h4>
                        <pre class="text-xs bg-gray-100 p-3 rounded overflow-x-auto">${JSON.stringify(data.new_data, null, 2)}</pre>
                    </div>
                ` : ''}
            `;
            
            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail');
        });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/admin/audit-trail/index.blade.php ENDPATH**/ ?>