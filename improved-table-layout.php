<?php
// improved-table-layout.php
// Jalankan: php improved-table-layout.php

echo "=== CREATING IMPROVED TABLE LAYOUT ===\n\n";

$staffIndexViewFile = 'resources/views/staff/surat/index.blade.php';

// Backup current view
$backup = $staffIndexViewFile . '.cardbackup.' . date('YmdHis');
copy($staffIndexViewFile, $backup);
echo "Backup created: $backup\n";

// Create improved table layout that looks more professional
$improvedTableLayout = <<<'IMPROVED'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Daftar Surat Saya</h1>
            <a href="{{ route('staff.surat.create') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>Buat Surat Baru
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Professional Table Layout -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                Nomor & Perihal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                Jenis Surat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($surats as $surat)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $surat->nomor_surat ?? 'Belum ada nomor' }}
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ Str::limit($surat->perihal, 60) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $surat->jenisSurat->nama_jenis ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusCode = $surat->currentStatus->kode_status ?? '';
                                    $badgeClass = 'bg-gray-100 text-gray-800';
                                    $statusIcon = 'fas fa-circle';
                                    
                                    if ($statusCode === 'draft') {
                                        $badgeClass = 'bg-blue-100 text-blue-800';
                                        $statusIcon = 'fas fa-edit';
                                    } elseif ($statusCode === 'review_kaprodi') {
                                        $badgeClass = 'bg-yellow-100 text-yellow-800';
                                        $statusIcon = 'fas fa-clock';
                                    } elseif ($statusCode === 'disetujui_kaprodi') {
                                        $badgeClass = 'bg-green-100 text-green-800';
                                        $statusIcon = 'fas fa-check-circle';
                                    } elseif ($statusCode === 'ditolak_kaprodi') {
                                        $badgeClass = 'bg-red-100 text-red-800';
                                        $statusIcon = 'fas fa-times-circle';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    <i class="{{ $statusIcon }} mr-1"></i>
                                    {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $surat->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <!-- View Button -->
                                    <a href="{{ route('surat.show', $surat->id) }}" 
                                       class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-sm font-medium hover:bg-blue-200 transition-colors duration-200 inline-flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        Lihat
                                    </a>
                                    
                                    <!-- Edit Button (if applicable) -->
                                    @if(in_array($statusCode, ['draft', 'ditolak_kaprodi']))
                                        <a href="{{ route('surat.edit', $surat->id) }}" 
                                           class="bg-yellow-100 text-yellow-600 px-3 py-1 rounded text-sm font-medium hover:bg-yellow-200 transition-colors duration-200 inline-flex items-center">
                                            <i class="fas fa-edit mr-1"></i>
                                            Edit
                                        </a>
                                    @endif
                                    
                                    <!-- Submit Button (if draft) -->
                                    @if($statusCode === 'draft')
                                        <form action="{{ route('staff.surat.submit', $surat->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="bg-green-100 text-green-600 px-3 py-1 rounded text-sm font-medium hover:bg-green-200 transition-colors duration-200 inline-flex items-center"
                                                    onclick="return confirm('Submit surat untuk review?')">
                                                <i class="fas fa-paper-plane mr-1"></i>
                                                Submit
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Tracking Button -->
                                    <a href="{{ route('staff.surat.tracking', $surat->id) }}" 
                                       class="bg-purple-100 text-purple-600 px-3 py-1 rounded text-sm font-medium hover:bg-purple-200 transition-colors duration-200 inline-flex items-center">
                                        <i class="fas fa-route mr-1"></i>
                                        Track
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox fa-4x text-gray-400 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada surat</h3>
                                    <p class="text-gray-500 mb-4">Mulai dengan membuat surat baru</p>
                                    <a href="{{ route('staff.surat.create') }}" 
                                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <i class="fas fa-plus mr-2"></i>
                                        Buat Surat Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($surats->hasPages())
            <div class="bg-white px-6 py-3 border-t border-gray-200 flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $surats->firstItem() ?? 0 }} - {{ $surats->lastItem() ?? 0 }} 
                    dari {{ $surats->total() ?? 0 }} surat
                </div>
                <div>
                    {{ $surats->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .py-4 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    .space-x-2 > * + * {
        margin-left: 0.25rem;
    }
    
    .px-3 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .py-1 {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }
}
</style>
@endsection
IMPROVED;

file_put_contents($staffIndexViewFile, $improvedTableLayout);
echo "✓ Created improved table layout\n";

echo "\n2. Creating Alternative Compact Layout\n";
echo str_repeat("-", 50) . "\n";

// Alternative ultra-compact layout
$ultraCompactLayout = <<<'ULTRACOMPACT'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Daftar Surat Saya</h1>
            <a href="{{ route('staff.surat.create') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Buat Surat Baru
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Ultra Compact Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Surat</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($surats as $surat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $surat->nomor_surat ?? 'Belum ada nomor' }}</div>
                                <div class="text-gray-600 text-sm">{{ Str::limit($surat->perihal, 80) }}</div>
                                <div class="text-gray-500 text-xs mt-1">
                                    {{ $surat->jenisSurat->nama_jenis ?? '-' }} • {{ $surat->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $statusCode = $surat->currentStatus->kode_status ?? '';
                                $badgeClass = 'bg-gray-100 text-gray-800';
                                
                                if ($statusCode === 'draft') {
                                    $badgeClass = 'bg-blue-100 text-blue-800';
                                } elseif ($statusCode === 'review_kaprodi') {
                                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                                } elseif ($statusCode === 'disetujui_kaprodi') {
                                    $badgeClass = 'bg-green-100 text-green-800';
                                } elseif ($statusCode === 'ditolak_kaprodi') {
                                    $badgeClass = 'bg-red-100 text-red-800';
                                }
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded {{ $badgeClass }}">
                                {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex justify-center space-x-1">
                                <a href="{{ route('surat.show', $surat->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 px-2 py-1 text-sm">Lihat</a>
                                
                                @if(in_array($statusCode, ['draft', 'ditolak_kaprodi']))
                                    <a href="{{ route('surat.edit', $surat->id) }}" 
                                       class="text-yellow-600 hover:text-yellow-800 px-2 py-1 text-sm">Edit</a>
                                @endif
                                
                                @if($statusCode === 'draft')
                                    <form action="{{ route('staff.surat.submit', $surat->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-800 px-2 py-1 text-sm"
                                                onclick="return confirm('Submit surat?')">Submit</button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('staff.surat.tracking', $surat->id) }}" 
                                   class="text-purple-600 hover:text-purple-800 px-2 py-1 text-sm">Track</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox fa-3x mb-4"></i>
                                <div class="font-medium mb-2">Belum ada surat</div>
                                <a href="{{ route('staff.surat.create') }}" 
                                   class="text-blue-600 hover:text-blue-800">Buat surat pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if($surats->hasPages())
            <div class="px-4 py-3 border-t">
                {{ $surats->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
ULTRACOMPACT;

file_put_contents('resources/views/staff/surat/index-ultracompact.blade.php', $ultraCompactLayout);
echo "✓ Created ultra-compact alternative\n";

echo "\n=== LAYOUT OPTIONS ===\n";
echo "1. Current: Professional table with proper spacing and colored action buttons\n";
echo "2. Alternative: Ultra-compact 3-column layout (index-ultracompact.blade.php)\n";

echo "\n=== FEATURES ===\n";
echo "✓ Proper table layout with adequate spacing\n";
echo "✓ Combined nomor + perihal in first column\n";
echo "✓ Clear action buttons with icons\n";
echo "✓ Status badges with icons\n";
echo "✓ Responsive design\n";
echo "✓ No horizontal scroll needed\n";

echo "\n=== TO SWITCH ===\n";
echo "For ultra-compact: cp index-ultracompact.blade.php index.blade.php\n";

echo "\nTable layout improved! Should look much better now.\n";