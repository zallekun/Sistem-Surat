<?php
// restore-original-layout.php
// Jalankan: php restore-original-layout.php

echo "=== RESTORING ORIGINAL LAYOUT ===\n\n";

$staffIndexViewFile = 'resources/views/staff/surat/index.blade.php';

echo "1. Looking for backup files\n";
echo str_repeat("-", 50) . "\n";

// Find the most recent backup before responsive changes
$backupPattern = $staffIndexViewFile . '.*.2025*';
$backups = glob($backupPattern);

if (!empty($backups)) {
    // Sort by modification time, newest first
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    echo "Found backup files:\n";
    foreach ($backups as $backup) {
        $time = date('Y-m-d H:i:s', filemtime($backup));
        echo "  " . basename($backup) . " ($time)\n";
    }
    
    // Use the first backup (most recent before changes)
    $originalBackup = $backups[0];
    echo "\nUsing backup: " . basename($originalBackup) . "\n";
    
} else {
    echo "No backup files found. Creating original simple layout...\n";
    $originalBackup = null;
}

echo "\n2. Restoring Original Layout\n";
echo str_repeat("-", 50) . "\n";

if ($originalBackup && file_exists($originalBackup)) {
    // Restore from backup
    copy($originalBackup, $staffIndexViewFile);
    echo "✓ Restored from backup file\n";
} else {
    // Create original simple layout
    $originalLayout = <<<'ORIGINAL'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Daftar Surat Saya</h1>
            <a href="{{ route('staff.surat.create') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
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

        <!-- Surat Table -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nomor Surat
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Perihal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jenis
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($surats as $surat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $surat->nomor_surat ?? 'Belum ada nomor' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ Str::limit($surat->perihal, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $surat->jenisSurat->nama_jenis ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $surat->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- View -->
                                    <a href="{{ route('surat.show', $surat->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Edit (if draft or rejected) -->
                                    @if(in_array($statusCode, ['draft', 'ditolak_kaprodi']))
                                        <a href="{{ route('surat.edit', $surat->id) }}" 
                                           class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    <!-- Submit (if draft) -->
                                    @if($statusCode === 'draft')
                                        <form action="{{ route('staff.surat.submit', $surat->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900"
                                                    onclick="return confirm('Submit surat untuk review?')">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Tracking -->
                                    <a href="{{ route('staff.surat.tracking', $surat->id) }}" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-route"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox fa-3x mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada surat</h3>
                                    <p class="text-gray-500 mb-4">Mulai dengan membuat surat baru</p>
                                    <a href="{{ route('staff.surat.create') }}" 
                                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
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
            <div class="bg-white px-6 py-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $surats->firstItem() ?? 0 }} - {{ $surats->lastItem() ?? 0 }} 
                        dari {{ $surats->total() ?? 0 }} surat
                    </div>
                    <div>
                        {{ $surats->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
ORIGINAL;

    file_put_contents($staffIndexViewFile, $originalLayout);
    echo "✓ Created original simple layout\n";
}

echo "\n3. Cleaning Up Alternative Files\n";
echo str_repeat("-", 50) . "\n";

// Remove alternative files that were created
$filesToRemove = [
    'resources/views/staff/surat/index-compact.blade.php',
    'resources/views/staff/surat/index-ultracompact.blade.php'
];

foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✓ Removed " . basename($file) . "\n";
    }
}

echo "\n=== RESTORE COMPLETE ===\n";
echo "✓ Layout restored to original simple table\n";
echo "✓ 6 columns: Nomor, Perihal, Jenis, Status, Tanggal, Aksi\n";
echo "✓ Icon-only actions in the last column\n";
echo "✓ Horizontal scroll available if needed\n";
echo "✓ Clean and simple design\n";

echo "\nRefresh your browser to see the original layout!\n";