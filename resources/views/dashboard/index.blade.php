@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header/Welcome Section -->
        <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <h2 class="text-2xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->nama }}!
                </h2>
                <p class="text-white/90 text-sm">
                    @php
                        $user = Auth::user();
                        $roleName = '';
                        
                        if ($user->hasRole('staff_fakultas')) {
                            $roleName = 'Staff Fakultas';
                        } elseif ($user->hasRole('staff_prodi')) {
                            $roleName = 'Staff Prodi';
                        } elseif ($user->hasRole('kaprodi')) {
                            $roleName = 'Kepala Program Studi';
                        } elseif ($user->hasRole('admin')) {
                            $roleName = 'Administrator';
                        } elseif ($user->hasRole('super_admin')) {
                            $roleName = 'Super Administrator';
                        } elseif (method_exists($user, 'getRoleNames') && $user->getRoleNames()->isNotEmpty()) {
                            $roleName = ucfirst(str_replace('_', ' ', $user->getRoleNames()->first()));
                        }
                    @endphp
                    <span class="font-medium">{{ $roleName }}</span>
                    @if(Auth::user()->jabatan)
                        • {{ Auth::user()->jabatan->deskripsi ?? ucfirst(str_replace('_', ' ', Auth::user()->jabatan->nama_jabatan)) }}
                    @endif
                    @if(Auth::user()->prodi)
                        • {{ Auth::user()->prodi->nama_prodi }}
                    @endif
                </p>
                <p class="text-white/80 text-xs mt-1">
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Total Surat -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dt class="text-sm font-medium text-gray-600 mb-1">
                                Total Surat
                            </dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $stats['total_surat'] ?? 0 }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Surat Selesai -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dt class="text-sm font-medium text-gray-600 mb-1">
                                Surat Selesai
                            </dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $stats['surat_selesai'] ?? 0 }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Menunggu Proses -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dt class="text-sm font-medium text-gray-600 mb-1">
                                Proses
                            </dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $stats['surat_proses'] ?? 0 }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Draft/Disposisi -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <dt class="text-sm font-medium text-gray-600 mb-1">
                                @if(Auth::user()->hasRole('admin') || in_array(Auth::user()->jabatan?->nama_jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                                    Disposisi
                                @else
                                    Draft
                                @endif
                            </dt>
                            <dd class="text-3xl font-bold text-gray-900">
                                {{ $stats['surat_draft'] ?? 0 }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid for Quick Actions and Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 border-2 border-transparent transition">
                                <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Kelola User</span>
                            </a>
                        @endif
                        
                        @if(Auth::user()->hasRole('staff_fakultas'))
                            <a href="{{ route('fakultas.surat.index') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 border-2 border-blue-200 transition">
                                <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-blue-700">Surat Fakultas</span>
                            </a>
                        @endif
                        
                        @if(Auth::user()->hasRole('staff_prodi') || in_array(Auth::user()->jabatan?->nama_jabatan, ['Staff Program Studi']))
                            <div class="relative group">
                                <div class="flex flex-col items-center justify-center p-4 bg-gray-100 rounded-lg border-2 border-gray-300 cursor-not-allowed opacity-60">
                                    <svg class="h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-500">Buat Surat</span>
                                    <span class="text-xs text-gray-400 mt-1">Segera Hadir</span>
                                </div>
                                
                                <!-- Tooltip -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                                    <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap shadow-lg">
                                        <p class="font-medium mb-1">Fitur Dalam Pengembangan</p>
                                        <p class="text-gray-300">Menu ini akan segera tersedia</p>
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                            <div class="border-4 border-transparent border-t-gray-900"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('staff.pengajuan.index') }}" class="relative flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 border-2 border-orange-200 transition">
                                <svg class="h-8 w-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm font-medium text-orange-700">Pengajuan</span>
                                @if(isset($stats['pengajuan_pending']) && $stats['pengajuan_pending'] > 0)
                                    <span class="absolute top-2 right-2 px-2 py-0.5 text-xs rounded-full bg-red-500 text-white font-semibold">
                                        {{ $stats['pengajuan_pending'] }}
                                    </span>
                                @endif
                            </a>
                        @endif
                        
                        <a href="#" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 border-2 border-transparent transition">
                            <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Tracking</span>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 border-2 border-transparent transition">
                            <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Profil</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Jam Operasional</p>
                                <p class="text-sm text-gray-600 mt-0.5">Senin - Jumat: 08:00 - 16:00 WIB</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Status Sistem</p>
                                <p class="text-sm text-gray-600 mt-0.5">Online - Berjalan Normal</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Pengumuman</p>
                                <p class="text-sm text-gray-600 mt-0.5">Sistem berjalan normal</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-purple-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Bantuan</p>
                                <p class="text-sm text-gray-600 mt-0.5">Hubungi IT Support: ext. 1234</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Pengajuan (if exists) -->
        @if(Auth::user()->hasRole('staff_prodi') && isset($recent_pengajuan) && $recent_pengajuan && $recent_pengajuan->count() > 0)
        <div class="bg-white/95 backdrop-blur-sm overflow-hidden shadow-sm rounded-xl mt-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pengajuan Surat Terbaru</h3>
                    <a href="{{ route('staff.pengajuan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Lihat semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($recent_pengajuan as $pengajuan)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $pengajuan->nama_mahasiswa }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $pengajuan->nim }} • {{ $pengajuan->jenis_surat ?? 'Pengajuan Surat' }} • {{ $pengajuan->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form action="{{ route('staff.pengajuan.process', $pengajuan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                                Proses
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
    </div>
</div>
@endsection