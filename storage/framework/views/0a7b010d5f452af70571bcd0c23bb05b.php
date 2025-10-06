


<?php $__env->startSection('title', 'Tracking Surat'); ?>

<?php $__env->startPush('head'); ?>
<style>
.main-container {
    background: #f8fafc;
    min-height: 100vh;
    padding: 2rem 1rem;
}

.tracking-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 800px;
    margin: 0 auto;
}

.card-header {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    padding: 2rem;
    text-align: center;
    color: white;
}

.card-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.card-header p {
    opacity: 0.9;
}

.card-body {
    padding: 2rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input.error {
    border-color: #ef4444;
}

.btn-primary {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.alert-success {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    color: #065f46;
}

.alert-info {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    color: #1e40af;
}

.info-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.token-example {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-family: monospace;
    cursor: pointer;
    transition: all 0.2s;
}

.token-example:hover {
    background: #f3f4f6;
    border-color: #3b82f6;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 2rem 0;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.stat-value.blue { color: #3b82f6; }
.stat-value.green { color: #10b981; }
.stat-value.yellow { color: #f59e0b; }

.divider {
    border-top: 1px solid #e5e7eb;
    margin: 2rem 0;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.help-item {
    display: flex;
    align-items: start;
    gap: 0.75rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.icon {
    flex-shrink: 0;
    color: #3b82f6;
}

.text-center {
    text-align: center;
}

.hidden {
    display: none;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .help-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-container">
    <div class="tracking-card">
        <!-- Header -->
        <div class="card-header">
            <h2>Tracking Pengajuan Surat</h2>
            <p>Lacak status pengajuan surat Anda</p>
        </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Error Messages -->
            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <strong>Terjadi Kesalahan:</strong>
                    <ul style="margin: 0.5rem 0 0 1.25rem;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <strong>Berhasil!</strong> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <!-- Tracking Form -->
            <form action="<?php echo e(route('tracking.search')); ?>" method="POST" id="trackingForm">
                <?php echo csrf_field(); ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="token" class="form-label">
                        Token Tracking
                    </label>
                    <input 
                        type="text" 
                        class="form-input <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                        id="token" 
                        name="token" 
                        placeholder="Contoh: TRK-TEST001"
                        value="<?php echo e(old('token')); ?>"
                        required>
                    <?php $__errorArgs = ['token'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #ef4444;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    
                    <div class="info-box">
                        <small style="color: #6b7280;">
                            Token diberikan setelah pengajuan surat berhasil disubmit
                        </small>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="trackingBtn">
                    <span class="btn-text">Lacak Status Pengajuan</span>
                    <span class="loading-text hidden">Mencari...</span>
                </button>
            </form>

            <!-- Testing Tokens -->
            <div class="alert alert-info" style="margin-top: 1.5rem;">
                <strong>Token untuk Testing:</strong>
                <div style="margin-top: 0.75rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <span class="token-example" onclick="fillToken('TRK-TEST001')">TRK-TEST001</span>
                        <div style="font-size: 0.75rem; margin-top: 0.25rem;">Pending</div>
                    </div>
                    <div>
                        <span class="token-example" onclick="fillToken('TRK-A6C2BCDD')">TRK-A6C2BCDD</span>
                        <div style="font-size: 0.75rem; margin-top: 0.25rem;">Processed</div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value blue"><?php echo e(\App\Models\PengajuanSurat::count()); ?></div>
                    <div class="stat-label">Total Pengajuan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value green"><?php echo e(\App\Models\PengajuanSurat::where('status', 'completed')->count()); ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value yellow"><?php echo e(\App\Models\PengajuanSurat::whereIn('status', ['pending', 'processed', 'approved_prodi'])->count()); ?></div>
                    <div class="stat-label">Dalam Proses</div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- New Application -->
            <div class="text-center">
                <p style="color: #6b7280; margin-bottom: 1rem;">
                    Belum punya token tracking?
                </p>
                <a href="<?php echo e(route('public.pengajuan.create')); ?>" class="btn-secondary">
                    <i class="fas fa-plus"></i>
                    Ajukan Surat Baru
                </a>
            </div>

            <!-- Help Section -->
            <div style="margin-top: 2rem; background: #f9fafb; border-radius: 8px; padding: 1.5rem;">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Bantuan</h3>
                <div class="help-grid">
                    <div class="help-item">
                        <span class="icon">→</span>
                        <span>Token tracking dikirim via email setelah pengajuan</span>
                    </div>
                    <div class="help-item">
                        <span class="icon">→</span>
                        <span>Format token: TRK-XXXXXXXX</span>
                    </div>
                    <div class="help-item">
                        <span class="icon">→</span>
                        <span>Token bersifat case-insensitive</span>
                    </div>
                    <div class="help-item">
                        <span class="icon">→</span>
                        <span>Hubungi admin jika token hilang</span>
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

    // Auto uppercase
    tokenInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Form submission
    trackingForm.addEventListener('submit', function() {
        trackingBtn.disabled = true;
        btnText.classList.add('hidden');
        loadingText.classList.remove('hidden');
    });
});

function fillToken(token) {
    const tokenInput = document.getElementById('token');
    tokenInput.value = token;
    tokenInput.focus();
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/public/tracking/index.blade.php ENDPATH**/ ?>