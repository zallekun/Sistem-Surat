<!-- layouts/app.blade.php with Sidebar -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistem Persuratan') }} - @yield('title', 'Dashboard')</title>
    
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
            z-index: 50;
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            position: relative;
            background: transparent;
        }
        
        .bg-image-wrapper {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            bottom: 0;
            z-index: -1;
        }
        
        .bg-image-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset('images/background.webp') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.20;
            pointer-events: none;
        }
        
        .content-overlay {
            position: relative;
            z-index: 2;
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
                transform: translateX(0) !important;
            }
            
            .main-content {
                margin-left: 0;
                transition: margin-left 0.3s;
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
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @livewireStyles
</head>
<body class="antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    @if (session('error'))
        <div style="background-color: red; color: white; padding: 10px;">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Sidebar -->
    <div class="sidebar" :class="{ 'open': sidebarOpen }">
        <!-- Logo -->
        <div class="p-4 border-b border-gray-200">
            <a href="{{ route('dashboard') }}" class="flex items-center">
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
                    <div class="text-sm font-medium text-gray-800">{{ Auth::user()->nama ?? 'User' }}</div>
                    <div class="text-xs text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', Auth::user()->role->nama_role ?? '')) }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="py-4">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            @if(Auth::check())
                @php
                    $jabatan = Auth::user()->jabatan->nama_jabatan ?? '';
                @endphp
                
                {{-- ADMIN MENU --}}
                @if(Auth::user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Admin Dashboard</span>
                    </a>
                    
                    <a href="{{ route('admin.pengajuan.index') }}" class="nav-item {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}">
                        <i class="fas fa-folder-open"></i>
                        <span>Kelola Pengajuan</span>
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog"></i>
                        <span>Kelola User</span>
                    </a>
                    
                    {{-- NEW: Master Data Menu --}}
                    <div class="px-4 py-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Master Data</p>
                    </div>
                    
                    <a href="{{ route('admin.prodi.index') }}" class="nav-item {{ request()->routeIs('admin.prodi.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>Prodi</span>
                    </a>
                    
                    
                    
                    <a href="{{ route('admin.fakultas.index') }}" class="nav-item {{ request()->routeIs('admin.fakultas.*') ? 'active' : '' }}">
                        <i class="fas fa-university"></i>
                        <span>Fakultas</span>
                    </a>
                    
                    <a href="{{ route('admin.dosen-wali.index') }}" class="nav-item {{ request()->routeIs('admin.dosen-wali.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Dosen Wali</span>
                    </a>
                        <div class="px-4 py-2 mt-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Logs & Reports</p>
                    </div>
                    
                    <a href="{{ route('admin.audit-trail.index') }}" class="nav-item {{ request()->routeIs('admin.audit-trail.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Audit Trail</span>
                    </a>

                    <div class="px-4 py-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase">System</p>
                    </div>

                    <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                @endif
                
                {{-- STAFF PRODI --}}
                @if(Auth::user()->hasRole('staff_prodi') || $jabatan === 'Staff Program Studi')
                    <a href="{{ route('staff.pengajuan.index') }}" class="nav-item {{ request()->routeIs('staff.pengajuan.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox"></i>
                        <span>Pengajuan</span>
                    </a>
                    
                    <a href="{{ route('staff.surat.create') }}" class="nav-item {{ request()->routeIs('staff.surat.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Surat</span>
                    </a>
                    
                    <a href="{{ route('staff.arsip.index') }}" class="nav-item {{ request()->routeIs('staff.arsip.*') ? 'active' : '' }}">
                        <i class="fas fa-archive"></i>
                        <span>Arsip Surat</span>
                    </a>
                @endif
                
                {{-- STAFF FAKULTAS --}}
                @if(Auth::user()->hasRole('staff_fakultas'))
                    <a href="{{ route('fakultas.surat.index') }}" class="nav-item {{ request()->routeIs('fakultas.surat.*') && !request()->routeIs('fakultas.arsip.*') ? 'active' : '' }}">
                        <i class="fas fa-inbox"></i>
                        <span>Surat Fakultas</span>
                    </a>
                    
                    <a href="{{ route('fakultas.arsip.index') }}" class="nav-item {{ request()->routeIs('fakultas.arsip.*') ? 'active' : '' }}">
                        <i class="fas fa-archive"></i>
                        <span>Arsip Surat</span>
                    </a>
                @endif
                
                @if(Auth::user()->hasRole('kaprodi') || $jabatan == 'kaprodi')
                    <a href="{{ route('kaprodi.surat.approval') }}" class="nav-item {{ request()->routeIs('kaprodi.surat.approval') ? 'active' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Approval</span>
                    </a>
                @endif
                
                @if(in_array($jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                    <a href="{{ route('pimpinan.surat.disposisi') }}" class="nav-item {{ request()->routeIs('pimpinan.surat.disposisi') ? 'active' : '' }}">
                        <i class="fas fa-file-signature"></i>
                        <span>Disposisi</span>
                    </a>
                    
                    <a href="{{ route('pimpinan.surat.ttd') }}" class="nav-item {{ request()->routeIs('pimpinan.surat.ttd') ? 'active' : '' }}">
                        <i class="fas fa-pen-fancy"></i>
                        <span>Tanda Tangan</span>
                    </a>
                @endif
                
                <a href="{{ route('tracking.public') }}" class="nav-item">
                    <i class="fas fa-search"></i>
                    <span>Tracking Surat</span>
                </a>
            @endif
            
            <div class="border-t border-gray-200 my-2"></div>
            
            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                <span>Pengaturan Profil</span>
            </a>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="nav-item text-red-600 hover:text-red-700 hover:bg-red-50" 
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
            @if (isset($header))
                <header class="bg-white/90 backdrop-blur-sm shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="relative z-10 min-h-screen">
                <!-- Breadcrumb -->
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                    <i class="fas fa-home mr-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                </div>

                <!-- Content Wrapper -->
                <div class="px-4 sm:px-6 lg:px-8 pb-8">
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-6">
                        @yield('content')
                    </div>
                </div>
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
            @if (session('success'))
                show = true;
                message = '{{ session('success') }}';
                type = 'success';
                setTimeout(() => show = false, 3000);
            @elseif (session('error'))
                show = true;
                message = '{{ session('error') }}';
                type = 'error';
                setTimeout(() => show = false, 3000);
            @endif
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
    @livewireScripts
    @stack('scripts')

    <!-- Custom Notification System -->
    <div id="customNotification" class="hidden fixed top-4 right-4 z-50 min-w-80 max-w-md animate-slideIn">
        <div id="notificationContent" class="rounded-lg shadow-lg p-4 flex items-start gap-3"></div>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="customConfirm" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 animate-scaleIn">
            <div id="confirmContent" class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-question-circle text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">Konfirmasi</h3>
                        <p id="confirmMessage" class="text-sm text-gray-600 mt-1"></p>
                    </div>
                </div>
                <div class="flex gap-3 justify-end">
                    <button onclick="resolveConfirm(false)" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        Batal
                    </button>
                    <button onclick="resolveConfirm(true)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }

        .animate-scaleIn {
            animation: scaleIn 0.2s ease-out;
        }
    </style>

    <script>
        // Custom Alert Function
        function alert(message, type = 'info') {
            const notification = document.getElementById('customNotification');
            const content = document.getElementById('notificationContent');

            const icons = {
                success: { icon: 'fa-check-circle', bg: 'bg-green-50', iconColor: 'text-green-600', borderColor: 'border-green-200' },
                error: { icon: 'fa-exclamation-circle', bg: 'bg-red-50', iconColor: 'text-red-600', borderColor: 'border-red-200' },
                warning: { icon: 'fa-exclamation-triangle', bg: 'bg-yellow-50', iconColor: 'text-yellow-600', borderColor: 'border-yellow-200' },
                info: { icon: 'fa-info-circle', bg: 'bg-blue-50', iconColor: 'text-blue-600', borderColor: 'border-blue-200' }
            };

            const config = icons[type] || icons.info;

            content.className = `rounded-lg shadow-lg p-4 flex items-start gap-3 border-2 ${config.bg} ${config.borderColor}`;
            content.innerHTML = `
                <div class="flex-shrink-0">
                    <i class="fas ${config.icon} ${config.iconColor} text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-800 font-medium">${message}</p>
                </div>
                <button onclick="closeNotification()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            `;

            notification.classList.remove('hidden');

            setTimeout(() => {
                closeNotification();
            }, 5000);
        }

        function closeNotification() {
            const notification = document.getElementById('customNotification');
            notification.classList.add('hidden');
        }

        // Custom Confirm Function
        let confirmResolver = null;

        function confirm(message) {
            return new Promise((resolve) => {
                confirmResolver = resolve;
                const confirmDialog = document.getElementById('customConfirm');
                const messageEl = document.getElementById('confirmMessage');

                messageEl.textContent = message;
                confirmDialog.classList.remove('hidden');
            });
        }

        function resolveConfirm(result) {
            const confirmDialog = document.getElementById('customConfirm');
            confirmDialog.classList.add('hidden');

            if (confirmResolver) {
                confirmResolver(result);
                confirmResolver = null;
            }
        }

        // Close confirm dialog when clicking outside
        document.getElementById('customConfirm')?.addEventListener('click', function(e) {
            if (e.target === this) {
                resolveConfirm(false);
            }
        });

        // Success notification helper
        window.showSuccess = function(message) {
            alert(message, 'success');
        };

        // Error notification helper
        window.showError = function(message) {
            alert(message, 'error');
        };

        // Warning notification helper
        window.showWarning = function(message) {
            alert(message, 'warning');
        };

        // Info notification helper
        window.showInfo = function(message) {
            alert(message, 'info');
        };

        // Handle delete with async confirm
        window.handleDelete = function(event, message = 'Apakah Anda yakin ingin menghapus data ini?') {
            event.preventDefault();

            confirm(message).then(result => {
                if (result) {
                    event.target.submit();
                }
            });

            return false;
        };
    </script>
</body>
</html>