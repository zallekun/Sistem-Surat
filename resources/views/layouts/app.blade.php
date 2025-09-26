<!-- layouts.app -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistem Persuratan') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- Tailwind CSS CDN sebagai primary karena Vite belum berjalan baik -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Critical CSS untuk fix layout issues -->
    <style>
        /* Reset dan base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Fix navbar positioning - PENTING! */
        nav.main-navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            background-color: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            height: 64px;
        }
        
        /* Content wrapper dengan padding untuk kompensasi navbar */
        .content-wrapper {
            padding-top: 64px !important;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        
        /* Modal z-index yang benar */
        #approveModal, 
        #rejectModal,
        .modal {
            z-index: 2000 !important;
        }
        
        #approveModal > div,
        #rejectModal > div,
        .modal-content {
            z-index: 2001 !important;
        }
        
        /* Toast notifications */
        .toast-notification {
            z-index: 3000 !important;
        }
        
        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }
        
        /* Dropdown z-index */
        .dropdown-menu {
            z-index: 1100 !important;
        }
        
        /* Prevent body scroll saat modal */
        body.modal-open {
            overflow: hidden !important;
        }
    </style>
    
    <!-- Vite (tetap ada untuk future use) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Stack untuk additional styles dari views -->
    @stack('styles')
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
    
    <!-- Fixed Navbar -->
    <nav class="main-navbar bg-white border-b border-gray-200" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-envelope-open-text mr-2"></i>
                            Sistem Persuratan
                        </a>
                    </div>

                    <!-- Navigation Links (Desktop) -->
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="fas fa-tachometer-alt mr-1"></i>
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        
                        @if(Auth::check())
                            @php
                                $user = Auth::user();
                                $role = $user->role->nama_role ?? '';
                                $jabatan = $user->jabatan->nama_jabatan ?? '';
                            @endphp
                            
                            @if($role === 'admin')
                                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                    <i class="fas fa-users mr-1"></i>
                                    {{ __('Users') }}
                                </x-nav-link>
                            @endif
                            
                            @if(in_array($jabatan, ['Staff Program Studi', 'Staff Fakultas']) || in_array($role, ['staff_prodi', 'staff_fakultas']))
                                <x-nav-link :href="route('staff.pengajuan.index')" :active="request()->routeIs('staff.pengajuan.*')">
                                    <i class="fas fa-inbox mr-1"></i>
                                    {{ __('Pengajuan') }}
                                </x-nav-link>
                                
                                <x-nav-link :href="route('staff.surat.create')" :active="request()->routeIs('staff.surat.create')">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    {{ __('Buat Surat') }}
                                </x-nav-link>
                            @endif
                            
                            @if($jabatan == 'kaprodi' || $role == 'kaprodi')
                                <x-nav-link :href="route('kaprodi.surat.approval')" :active="request()->routeIs('kaprodi.surat.approval')">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ __('Approval') }}
                                </x-nav-link>
                            @endif
                            
                            @if(in_array($jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                                <x-nav-link :href="route('pimpinan.surat.disposisi')" :active="request()->routeIs('pimpinan.surat.disposisi')">
                                    <i class="fas fa-file-signature mr-1"></i>
                                    {{ __('Disposisi') }}
                                </x-nav-link>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Settings Dropdown (Desktop) -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    <span>{{ Auth::user()->nama ?? 'User' }}</span>
                                </div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 text-xs text-gray-400 border-b">
                                @if(Auth::check())
                                    <div class="font-semibold">{{ Auth::user()->nama }}</div>
                                    <div>{{ ucfirst(str_replace('_', ' ', Auth::user()->role->nama_role ?? 'User')) }}</div>
                                    @if(Auth::user()->jabatan)
                                        <div>{{ Auth::user()->jabatan->nama_jabatan }}</div>
                                    @endif
                                @endif
                            </div>
                            
                            <x-dropdown-link :href="route('profile.edit')">
                                <i class="fas fa-user-edit mr-2"></i>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger (Mobile) -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <!-- Mobile navigation links here -->
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
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
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="toast-notification fixed bottom-4 right-4 p-4 rounded-md shadow-lg text-white"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }"
        style="display: none;">
        <div class="flex items-center">
            <span x-text="message"></span>
            <button @click="show = false" class="ml-4 text-white font-bold text-xl">&times;</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>