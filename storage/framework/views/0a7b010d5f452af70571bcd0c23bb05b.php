


<?php $__env->startSection('title', 'Tracking Surat'); ?>

<?php $__env->startPush('head'); ?>
<style>
:root {
    --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    --info-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
    --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

.gradient-bg {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.tracking-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.tracking-card:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

.card-header-gradient {
    background: var(--primary-gradient);
    padding: 2rem;
    text-align: center;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.icon-circle:hover {
    transform: scale(1.1);
}

.stats-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    height: 100%;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.btn-gradient {
    background: var(--primary-gradient);
    border: none;
    border-radius: 25px;
    font-weight: 600;
    padding: 12px 30px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-gradient::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-gradient:hover::before {
    left: 100%;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4);
}

.enhanced-input {
    border-radius: 15px;
    border: 2px solid #e5e7eb;
    padding: 12px 20px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
}

.enhanced-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    transform: scale(1.02);
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="gradient-bg min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Main Tracking Card -->
        <div class="tracking-card fade-in">
            <!-- Header -->
            <div class="card-header-gradient">
                <div class="icon-circle bg-white bg-opacity-20 border-3 border-white border-opacity-30 text-white">
                    <i class="fas fa-search text-3xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Tracking Pengajuan Surat</h2>
                <p class="text-white text-opacity-75">Lacak status pengajuan surat Anda dengan mudah dan cepat</p>
            </div>

            <!-- Body -->
            <div class="p-8">
                <!-- Error/Success Messages -->
                <?php if($errors->any()): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terjadi Kesalahan</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(session('success')): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Berhasil</h3>
                                <p class="text-sm text-green-700"><?php echo e(session('success')); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tracking Form -->
                <form action="<?php echo e(route('tracking.search')); ?>" method="POST" class="mb-8" id="trackingForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="mb-6">
                        <label for="token" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-key text-blue-600 mr-2"></i>
                            Token Tracking
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-barcode text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   class="enhanced-input block w-full pl-10 pr-4 <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="token" 
                                   name="token" 
                                   placeholder="Contoh: TRK-TEST001"
                                   value="<?php echo e(old('token')); ?>"
                                   required
                                   autocomplete="off">
                        </div>
                        <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <p class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                            Token diberikan setelah pengajuan surat berhasil disubmit
                        </p>
                    </div>

                    <button type="submit" class="btn-gradient w-full text-white font-bold py-3 px-6" id="trackingBtn">
                        <span class="btn-text flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Lacak Status Pengajuan
                        </span>
                        <span class="loading-text hidden flex items-center justify-center">
                            <div class="loading-spinner mr-2"></div>
                            Mencari...
                        </span>
                    </button>
                </form>

                <!-- Testing Info -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 mb-2">Token untuk Testing</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <code class="bg-white px-2 py-1 rounded cursor-pointer hover:bg-gray-100 transition-colors" 
                                          onclick="fillToken('TRK-TEST001')">TRK-TEST001</code>
                                    <div class="text-xs text-blue-600 mt-1">Syahrul Ramadhan (Pending)</div>
                                </div>
                                <div>
                                    <code class="bg-white px-2 py-1 rounded cursor-pointer hover:bg-gray-100 transition-colors" 
                                          onclick="fillToken('TRK-A6C2BCDD')">TRK-A6C2BCDD</code>
                                    <div class="text-xs text-blue-600 mt-1">User zzzzz (Processed)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-to-r from-blue-500 to-purple-600">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Total</h4>
                        <div class="text-2xl font-bold text-blue-600"><?php echo e(\App\Models\PengajuanSurat::count()); ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-to-r from-green-500 to-green-600">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Selesai</h4>
                        <div class="text-2xl font-bold text-green-600"><?php echo e(\App\Models\PengajuanSurat::where('status', 'completed')->count()); ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-icon bg-gradient-to-r from-yellow-500 to-orange-500">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4 class="text-sm font-medium text-gray-600 mb-1">Proses</h4>
                        <div class="text-2xl font-bold text-yellow-600"><?php echo e(\App\Models\PengajuanSurat::whereIn('status', ['pending', 'processed', 'approved_prodi'])->count()); ?></div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 my-8"></div>

                <!-- New Application Section -->
                <div class="text-center">
                    <div class="icon-circle bg-green-50 border-3 border-green-200 text-green-600 mx-auto mb-4">
                        <i class="fas fa-plus text-2xl"></i>
                    </div>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-question-circle text-blue-500 mr-1"></i>
                        Belum punya token tracking?
                    </p>
                    <a href="<?php echo e(route('public.pengajuan.create')); ?>" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transform transition hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        Ajukan Surat Baru
                    </a>
                </div>

                <!-- Help Section -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                        Bantuan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Token tracking dikirim via email setelah pengajuan</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Format token: TRK-XXXXXXXX</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Token bersifat case-insensitive</span>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-arrow-right text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                            <span>Hubungi admin jika token hilang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tokenInput = document.getElementById('token');
    const trackingBtn = document.getElementById('trackingBtn');
    const trackingForm = document.getElementById('trackingForm');
    const btnText = trackingBtn.querySelector('.btn-text');
    const loadingText = trackingBtn.querySelector('.loading-text');

    // Auto uppercase token input
    tokenInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
        
        // Visual feedback
        if (e.target.value.length > 0) {
            e.target.style.transform = 'scale(1.02)';
        } else {
            e.target.style.transform = 'scale(1)';
        }
    });

    // Form submission with loading state
    trackingForm.addEventListener('submit', function(e) {
        if (tokenInput.value.trim() === '') {
            e.preventDefault();
            tokenInput.focus();
            tokenInput.classList.add('border-red-300');
            return false;
        }

        // Show loading state
        trackingBtn.disabled = true;
        btnText.classList.add('hidden');
        loadingText.classList.remove('hidden');
    });

    // Enhanced hover effects
    document.querySelectorAll('.stats-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Input validation
    tokenInput.addEventListener('input', function() {
        const value = this.value.trim();
        const isValid = value.length >= 8 && value.includes('-');
        
        this.classList.remove('border-red-300', 'border-green-300');
        
        if (value.length > 0) {
            if (isValid) {
                this.classList.add('border-green-300');
            } else if (value.length > 3) {
                this.classList.add('border-yellow-300');
            }
        }
    });
});

// Fill token from examples
function fillToken(token) {
    const tokenInput = document.getElementById('token');
    tokenInput.value = token;
    tokenInput.dispatchEvent(new Event('input'));
    tokenInput.focus();
    
    // Visual feedback
    event.target.style.background = '#10b981';
    event.target.style.color = 'white';
    setTimeout(() => {
        event.target.style.background = '';
        event.target.style.color = '';
    }, 1000);
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/public/tracking/index.blade.php ENDPATH**/ ?>