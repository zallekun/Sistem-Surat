@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header/Welcome Section -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <h2 class="text-3xl font-extrabold mb-2 text-white">
                    Selamat Datang, {{ Auth::user()->nama }}!
                </h2>
                <p class="text-white opacity-100">
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
                <p class="text-blue-200 text-sm mt-1">
                    {{ now()->format('l, d F Y') }}
                </p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ Auth::user()->hasRole(['staff_prodi', 'staff_fakultas']) ? '5' : '4' }} gap-4 mb-6">
            <!-- Card 1: Total Surat -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Surat
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['total_surat'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Surat Selesai -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Surat Selesai
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['surat_selesai'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Menunggu Proses -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Proses
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['surat_proses'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Draft/Disposisi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    @if(Auth::user()->hasRole('admin') || in_array(Auth::user()->jabatan?->nama_jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                                        Disposisi
                                    @else
                                        Draft
                                    @endif
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['surat_draft'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Card 5: Pengajuan dari Prodi (Only for Staff Fakultas) -->
            @if(Auth::user()->hasRole('staff_fakultas'))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Pengajuan dari Prodi
                                </dt>
                                <dd class="text-3xl font-semibold text-gray-900">
                                    {{ $stats['pengajuan_fakultas_pending'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Grid for Quick Actions and Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Menu Cepat</h3>
                    <div class="grid grid-cols-2 gap-3">
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <svg class="h-6 w-6 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Kelola User</span>
                            </a>
                            <a href="#" class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <svg class="h-6 w-6 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Master Data</span>
                            </a>
                        @endif
                        
                        @if(Auth::user()->hasRole('staff_fakultas'))
                            <a href="{{ route('fakultas.surat.index') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <svg class="h-6 w-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Surat Fakultas</span>
                            </a>
                            
                            
                        @endif
                        
                        @if(Auth::user()->hasRole('staff_prodi') || in_array(Auth::user()->jabatan?->nama_jabatan, ['Staff Program Studi', 'Staff Fakultas']))
                            <a href="{{ route('staff.surat.create') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <svg class="h-6 w-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Buat Surat</span>
                            </a>
                        @endif
    
                        @if(Auth::user()->hasRole('staff_prodi'))
                            <a href="{{ route('staff.pengajuan.index') }}" class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                                <svg class="h-6 w-6 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Pengajuan Mahasiswa</span>
                                @if(isset($stats['pengajuan_pending']) && $stats['pengajuan_pending'] > 0)
                                    <span class="mt-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                        {{ $stats['pengajuan_pending'] }} baru
                                    </span>
                                @endif
                            </a>
                        @endif
                        
                        @if(Auth::user()->hasRole('kaprodi') || Auth::user()->jabatan?->nama_jabatan == 'Kepala Program Studi')
                            <a href="{{ route('kaprodi.surat.approval') }}" class="flex flex-col items-center justify-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                                <svg class="h-6 w-6 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Approval</span>
                            </a>
                            <a href="{{ route('surat.index') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <svg class="h-6 w-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Daftar Surat</span>
                            </a>
                        @endif
                        
                        @if(in_array(Auth::user()->jabatan?->nama_jabatan, ['dekan', 'wd1', 'wd2', 'wd3']))
                            <a href="{{ route('pimpinan.surat.disposisi') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                <svg class="h-6 w-6 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Disposisi</span>
                            </a>
                            <a href="{{ route('pimpinan.surat.ttd') }}" class="flex flex-col items-center justify-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                <svg class="h-6 w-6 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Tanda Tangan</span>
                            </a>
                        @endif
                        
                        <!-- Common menus -->
                        <a href="#" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <svg class="h-6 w-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="text-sm text-blue-700">Tracking</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <svg class="h-6 w-6 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm text-blue-700">Profil</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activities / Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Jam Operasional</p>
                                <p class="text-sm text-gray-500">Senin - Jumat: 08:00 - 16:00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Status Sistem</p>
                                <p class="text-sm text-gray-500">Online - Berjalan Normal</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-yellow-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Pengumuman</p>
                                <p class="text-sm text-gray-500">Sistem akan maintenance pada Sabtu, 20:00 - 22:00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-purple-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Bantuan</p>
                                <p class="text-sm text-gray-500">Hubungi IT Support: ext. 1234</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Pengajuan Section (Outside Grid) -->
        @if(Auth::user()->hasRole('staff_prodi') && isset($recent_pengajuan) && $recent_pengajuan && $recent_pengajuan->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pengajuan Surat Terbaru</h3>
                    <a href="{{ route('staff.pengajuan.index') }}" class="text-sm text-blue-600 hover:text-blue-900">
                        Lihat semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($recent_pengajuan as $pengajuan)
                    <div class="flex items-center justify-between py-2 border-b">
                        <div>
                            <p class="text-sm font-medium">{{ $pengajuan->nama_mahasiswa }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $pengajuan->nim }} • {{ $pengajuan->jenis_surat ?? 'Pengajuan Surat' }} • {{ $pengajuan->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form action="{{ route('staff.pengajuan.process', $pengajuan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
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