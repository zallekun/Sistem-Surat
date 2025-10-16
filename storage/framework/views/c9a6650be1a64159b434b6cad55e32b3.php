<?php $__env->startSection('title', 'Pengaturan Profil'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <a href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600">Profil</a>
        </div>
    </li>
    <li aria-current="page">
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <span class="text-sm font-medium text-gray-500">Pengaturan</span>
        </div>
    </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <?php echo $__env->make('profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/profile/edit.blade.php ENDPATH**/ ?>