<!-- layout.public -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - Sistem Surat FSI</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
   
    
    <!-- Custom Styles -->
    <style>
        /* Smooth transitions */
        * {
            transition: all 0.2s ease-in-out;
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
    
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center group">
                        <div class="bg-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-700 transition-colors">
                            <i class="fas fa-university text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 group-hover:text-blue-600">
                                Sistem Surat FSI
                            </h1>
                            <p class="text-xs text-gray-500 hidden sm:block">
                                Fakultas Sains & Informatika
                            </p>
                        </div>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-1">
                    
                    <a href="{{ route('public.pengajuan.create') }}" 
                       class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('public.pengajuan.*') ? 'text-blue-600 bg-blue-50 shadow-sm' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Ajukan Surat
                    </a>
                    
                    <a href="{{ route('tracking.public') }}" 
                       class="flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('tracking.*') ? 'text-blue-600 bg-blue-50 shadow-sm' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}">
                        <i class="fas fa-search mr-2"></i>
                        Tracking Surat
                    </a>
                    
                    <!-- Staff Login -->
                </nav>
                
                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                            :class="mobileMenuOpen ? 'bg-gray-100 text-gray-900' : ''">
                        <span class="sr-only">Buka menu mobile</span>
                        <svg class="h-6 w-6" :class="mobileMenuOpen ? 'hidden' : 'block'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" :class="mobileMenuOpen ? 'block' : 'hidden'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="lg:hidden" x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="mobileMenuOpen = false">
                <div class="px-2 pt-2 pb-6 space-y-2 border-t border-gray-200 bg-white shadow-lg rounded-b-lg">

                    
                    <a href="{{ route('public.pengajuan.create') }}" 
                       class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all {{ request()->routeIs('public.pengajuan.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}"
                       @click="mobileMenuOpen = false">
                        <i class="fas fa-plus-circle mr-3"></i>
                        Ajukan Surat
                    </a>
                    
                    <a href="{{ route('tracking.public') }}" 
                       class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all {{ request()->routeIs('tracking.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }}"
                       @click="mobileMenuOpen = false">
                        <i class="fas fa-search mr-3"></i>
                        Tracking Surat
                    </a>
                    
                    <div class="border-t border-gray-200 pt-4 mt-4">

                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mt-6">
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-sm" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                                <span class="font-medium">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mt-6">
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-sm" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                                <span class="font-medium">{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mt-6">
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-sm" 
                         x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle mr-3 mt-0.5 text-red-500"></i>
                                <div>
                                    <p class="font-medium mb-2">Terjadi kesalahan:</p>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <div class="py-8">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Logo & Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-600 p-3 rounded-lg mr-4">
                            <i class="fas fa-university text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Sistem Persuratan</h3>
                            <p class="text-sm text-gray-600">Fakultas Sains dan Informatika</p>
                            <p class="text-sm text-gray-600">Universitas Jenderal Achmad Yani</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 max-w-md">
                        Platform digital untuk mempermudah pengajuan dan tracking surat mahasiswa secara online dengan proses yang cepat dan transparan.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('public.pengajuan.create') }}" 
                               class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                Ajukan Surat Baru
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tracking.public') }}" 
                               class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                Tracking Surat
                            </a>
                        </li>
                        <li>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="border-t border-gray-200 pt-6 mt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} Universitas Jenderal Achmad Yani. Hak Cipta Dilindungi.
                    </p>
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        <p class="text-xs text-gray-400">
                            Bantuan Teknis: 
                            <a href="mailto:support@unjani.ac.id" 
                               class="text-blue-600 hover:text-blue-800 transition-colors">
                                support@unjani.ac.id
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" 
            class="fixed bottom-6 right-6 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-200 opacity-0 invisible"
            onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Custom Notification Container -->
    <div id="customNotification" class="hidden fixed top-4 right-4 z-50 min-w-80 max-w-md animate-slideIn">
        <div id="notificationContent" class="rounded-lg shadow-lg p-4 flex items-start gap-3"></div>
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

        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    </style>

    <!-- Global Scripts -->
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

        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
                backToTop.classList.add('opacity-100', 'visible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
                backToTop.classList.remove('opacity-100', 'visible');
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Auto hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('[x-data*="show: true"]');
                alerts.forEach(alert => {
                    if (alert.__x) {
                        alert.__x.$data.show = false;
                    }
                });
            }, 5000);
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });

        // CSRF token for AJAX requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Set default CSRF token for fetch requests
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            if (args[1] && args[1].method && args[1].method.toUpperCase() !== 'GET') {
                args[1].headers = args[1].headers || {};
                if (!args[1].headers['X-CSRF-TOKEN'] && window.csrfToken) {
                    args[1].headers['X-CSRF-TOKEN'] = window.csrfToken;
                }
            }
            return originalFetch.apply(this, args);
        };
    </script>

    
    
    @stack('scripts')
    
</body>
</html>