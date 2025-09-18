
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-semibold text-gray-800">
                                Sistem Persuratan
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            
                            @if(Auth::check() && Auth::user()->role && auth()->user()->role->nama_role === 'admin')
                                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                    {{ __('Users') }}
                                </x-nav-link>
                            @endif
                            
                            @if(Auth::check() && Auth::user()->jabatan && in_array(Auth::user()->jabatan->nama_jabatan, ['Staff Program Studi', 'Staff Fakultas']))
                                <x-nav-link :href="route('staff.surat.create')" :active="request()->routeIs('staff.surat.create')">
                                    {{ __('Buat Surat') }}
                                </x-nav-link>
                            @endif
                            
                            @if(Auth::check() && Auth::user()->jabatan && Auth::user()->jabatan->nama_jabatan == 'kaprodi')
                                <x-nav-link :href="route('kaprodi.surat.approval')" :active="request()->routeIs('kaprodi.surat.approval')">
                                    {{ __('Approval') }}
                                </x-nav-link>
                            @endif
                            
                            @if(Auth::check() && Auth::user()->jabatan && in_array(Auth::user()->jabatan->nama_jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                                <x-nav-link :href="route('pimpinan.surat.disposisi')" :active="request()->routeIs('pimpinan.surat.disposisi')">
                                    {{ __('Disposisi') }}
                                </x-nav-link>
                            @endif
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->nama }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400 border-b">
                                    @if(Auth::check() && Auth::user()->role)
                                    {{ ucfirst(str_replace('_', ' ', Auth::user()->role->nama_role)) }}
                                    @endif
                                    @if(Auth::check() && Auth::user()->jabatan)
                                        - {{ Auth::user()->jabatan->nama_jabatan }}
                                    @endif
                                </div>
                                
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger -->
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
        </nav>

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
            window.addEventListener('livewire:load', () => {
                Livewire.on('flash', (data) => {
                    show = true;
                    message = data.message;
                    type = data.type || 'success';
                    setTimeout(() => show = false, 3000);
                });
            });
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="fixed bottom-4 right-4 z-50 p-4 rounded-md shadow-lg text-white"
        :class="{ 'bg-green-500': type === 'success', 'bg-red-500': type === 'error' }"
        style="display: none;">
        <span x-text="message"></span>
        <button @click="show = false" class="ml-4 text-white font-bold">&times;</button>
    </div>

    @stack('scripts')
</body>
</html>