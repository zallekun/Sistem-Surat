<!-- layouts/app.blade.php with Sidebar -->
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'Sistem Persuratan')); ?> - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            position: relative;
        }
        
        .bg-image-wrapper {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            bottom: 0;
            z-index: 0;
        }
        
        .bg-image-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo e(asset('images/background.webp')); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.40;
        }
        
        .content-overlay {
            position: relative;
            z-index: 1;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background-color: #f3f4f6;
            color: #4f46e5;
            border-left-color: #4f46e5;
        }
        
        .nav-item.active {
            background-color: #eef2ff;
            color: #4f46e5;
            border-left-color: #4f46e5;
            font-weight: 500;
        }
        
        .nav-item i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .bg-image-wrapper {
                left: 0;
            }
        }
        
        .modal {
            z-index: 2000 !important;
        }
        
        [x-cloak] {
            display: none !important;
        }
    </style>
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar -->
    <div class="sidebar" :class="{ 'open': sidebarOpen }">
        <!-- Logo -->
        <div class="p-4 border-b border-gray-200">
            <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope-open-text text-white"></i>
                </div>
                <div class="ml-3">
                    <div class="font-semibold text-gray-800">Sistem Persuratan</div>
                    <div class="text-xs text-gray-500">FSI</div>
                </div>
            </a>
        </div>
        
        <!-- User Info -->
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-indigo-600"></i>
                </div>
                <div class="ml-3 flex-1">
                    <div class="text-sm font-medium text-gray-800"><?php echo e(Auth::user()->nama ?? 'User'); ?></div>
                    <div class="text-xs text-gray-500">
                        <?php echo e(ucfirst(str_replace('_', ' ', Auth::user()->role->nama_role ?? ''))); ?>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="py-4">
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <?php if(Auth::check()): ?>
                <?php
                    $user = Auth::user();
                    $role = $user->role->nama_role ?? '';
                    $jabatan = $user->jabatan->nama_jabatan ?? '';
                ?>
                
                <?php if($role === 'admin'): ?>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                        <i class="fas fa-users"></i>
                        <span>Kelola Users</span>
                    </a>
                <?php endif; ?>
                
                <?php if(in_array($jabatan, ['Staff Program Studi']) || in_array($role, ['staff_prodi', 'staff_fakultas'])): ?>
                    <a href="<?php echo e(route('staff.pengajuan.index')); ?>" class="nav-item <?php echo e(request()->routeIs('staff.pengajuan.*') ? 'active' : ''); ?>">
                        <i class="fas fa-inbox"></i>
                        <span>Pengajuan</span>
                    </a>
                    
                    <a href="<?php echo e(route('staff.surat.create')); ?>" class="nav-item <?php echo e(request()->routeIs('staff.surat.create') ? 'active' : ''); ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Surat</span>
                    </a>
                <?php endif; ?>
                
                <?php if($jabatan == 'kaprodi' || $role == 'kaprodi'): ?>
                    <a href="<?php echo e(route('kaprodi.surat.approval')); ?>" class="nav-item <?php echo e(request()->routeIs('kaprodi.surat.approval') ? 'active' : ''); ?>">
                        <i class="fas fa-check-circle"></i>
                        <span>Approval</span>
                    </a>
                <?php endif; ?>
                
                <?php if(in_array($jabatan, ['dekan', 'wd1', 'wd2', 'wd3'])): ?>
                    <a href="<?php echo e(route('pimpinan.surat.disposisi')); ?>" class="nav-item <?php echo e(request()->routeIs('pimpinan.surat.disposisi') ? 'active' : ''); ?>">
                        <i class="fas fa-file-signature"></i>
                        <span>Disposisi</span>
                    </a>
                    
                    <a href="<?php echo e(route('pimpinan.surat.ttd')); ?>" class="nav-item <?php echo e(request()->routeIs('pimpinan.surat.ttd') ? 'active' : ''); ?>">
                        <i class="fas fa-pen-fancy"></i>
                        <span>Tanda Tangan</span>
                    </a>
                <?php endif; ?>
                
                <a href="#" class="nav-item">
                    <i class="fas fa-search"></i>
                    <span>Tracking Surat</span>
                </a>
            <?php endif; ?>
            
            <div class="border-t border-gray-200 my-2"></div>
            
            <a href="<?php echo e(route('profile.edit')); ?>" class="nav-item <?php echo e(request()->routeIs('profile.edit') ? 'active' : ''); ?>">
                <i class="fas fa-user-cog"></i>
                <span>Pengaturan Profil</span>
            </a>
            
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <a href="<?php echo e(route('logout')); ?>" class="nav-item text-red-600 hover:text-red-700 hover:bg-red-50" 
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </form>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Background Image -->
        <div class="bg-image-wrapper"></div>
        
        <!-- Top Bar (Mobile) -->
        <div class="md:hidden bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="font-semibold text-gray-800">Sistem Persuratan</div>
                <div class="w-6"></div>
            </div>
        </div>
        
        <!-- Content Overlay -->
        <div class="content-overlay">
            <?php if(isset($header)): ?>
                <header class="bg-white/90 backdrop-blur-sm shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?>
            
            <main>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
         style="display: none;">
    </div>
    
    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-init="
            <?php if(session('success')): ?>
                show = true;
                message = '<?php echo e(session('success')); ?>';
                type = 'success';
                setTimeout(() => show = false, 3000);
            <?php elseif(session('error')): ?>
                show = true;
                message = '<?php echo e(session('error')); ?>';
                type = 'error';
                setTimeout(() => show = false, 3000);
            <?php endif; ?>
        "
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white z-50"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }"
        style="display: none;">
        <div class="flex items-center">
            <span x-text="message"></span>
            <button @click="show = false" class="ml-4 font-bold">&times;</button>
        </div>
    </div>
    
    <script src="//unpkg.com/alpinejs" defer></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\sistem-surat\resources\views/layouts/app.blade.php ENDPATH**/ ?>