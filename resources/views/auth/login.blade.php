{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{-- Logo & Title --}}
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Sistem Persuratan</h1>
                <p class="text-gray-600 mt-1">Fakultas Teknik</p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email Address --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        autocomplete="username"
                        placeholder="contoh@universitas.ac.id" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember Me --}}
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                            name="remember">
                        <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-between mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                            href="{{ route('password.request') }}">
                            {{ __('Lupa password?') }}
                        </a>
                    @endif

                    <x-primary-button class="ml-3">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

            {{-- Demo Accounts Info (Remove in production) --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center mb-3">Demo Accounts:</p>
                <div class="text-xs text-gray-600 space-y-1">
                    <div class="flex justify-between">
                        <span>Admin:</span>
                        <span class="text-gray-500">admin@surat.com</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Dekan:</span>
                        <span class="text-gray-500">dekan@ft.univ.ac.id</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kaprodi:</span>
                        <span class="text-gray-500">kaprodi.ti@ft.univ.ac.id</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Staff:</span>
                        <span class="text-gray-500">staff.ti@ft.univ.ac.id</span>
                    </div>
                    <div class="text-center mt-2">
                        <span class="text-gray-500">Password: password</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>