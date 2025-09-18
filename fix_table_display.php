<?php
// fix_table_display.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING TABLE DISPLAY IMPROVEMENTS ===\n\n";

// Backup existing files
$backupDir = storage_path('backup_table_fix_' . date('Y-m-d_H-i-s'));
mkdir($backupDir, 0777, true);

$filesToUpdate = [
    'resources/views/staff/surat/index.blade.php',
    'resources/views/components/table/td.blade.php',
    'resources/views/components/table/status.blade.php',
    'resources/views/components/table/action-button.blade.php'
];

// Backup files
foreach ($filesToUpdate as $file) {
    if (file_exists($file)) {
        $backupPath = $backupDir . '/' . basename($file);
        copy($file, $backupPath);
        echo "Backed up: " . basename($file) . "\n";
    }
}

// 1. Update td.blade.php for better alignment
$tdContent = '@props([\'align\' => \'left\', \'wrap\' => false])
@php
    $alignClass = match($align) {
        \'center\' => \'text-center\',
        \'right\' => \'text-right\',
        default => \'text-left\'
    };
    $wrapClass = $wrap ? \'text-wrap break-words\' : \'whitespace-nowrap\';
@endphp
<td {{ $attributes->merge([\'class\' => "px-6 py-4 $wrapClass $alignClass text-sm text-gray-900"]) }}>
    {{ $slot }}
</td>';

file_put_contents('resources/views/components/table/td.blade.php', $tdContent);
echo "✓ Updated td.blade.php with better text handling\n";

// 2. Update status.blade.php with better styling
$statusContent = '@props([\'status\'])
@php
    $statusConfig = [
        \'draft\' => [\'bg-gray-100 text-gray-800\', \'Draft\'],
        \'review_kaprodi\' => [\'bg-yellow-100 text-yellow-800\', \'Review\'],
        \'disetujui_kaprodi\' => [\'bg-blue-100 text-blue-800\', \'Disetujui Kaprodi\'],
        \'ditolak_kaprodi\' => [\'bg-red-100 text-red-800\', \'Ditolak\'],
        \'diproses_fakultas\' => [\'bg-indigo-100 text-indigo-800\', \'Proses Fakultas\'],
        \'disetujui_fakultas\' => [\'bg-green-100 text-green-800\', \'Selesai\'],
        \'ditolak_fakultas\' => [\'bg-red-100 text-red-800\', \'Ditolak Fakultas\'],
        \'ditolak_umum\' => [\'bg-red-100 text-red-800\', \'Ditolak\'],
        \'diajukan\' => [\'bg-yellow-100 text-yellow-800\', \'Diajukan\'],
    ];
    $statusCode = $status->kode_status ?? \'draft\';
    $config = $statusConfig[$statusCode] ?? [\'bg-gray-100 text-gray-800\', \'Unknown\'];
    $statusClass = $config[0];
    $statusLabel = $config[1];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
    {{ $statusLabel }}
</span>';

file_put_contents('resources/views/components/table/status.blade.php', $statusContent);
echo "✓ Updated status.blade.php with better badge styling\n";

// 3. Update action-button.blade.php with tooltips
$actionButtonContent = '@props([\'type\' => \'view\', \'href\' => \'#\', \'onclick\' => null, \'title\' => null])
@php
    $configs = [
        \'view\' => [
            \'color\' => \'text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50\',
            \'icon\' => \'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\',
            \'defaultTitle\' => \'Lihat Detail\'
        ],
        \'edit\' => [
            \'color\' => \'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50\',
            \'icon\' => \'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\',
            \'defaultTitle\' => \'Edit\'
        ],
        \'approve\' => [
            \'color\' => \'text-green-600 hover:text-green-900 hover:bg-green-50\',
            \'icon\' => \'M5 13l4 4L19 7\',
            \'defaultTitle\' => \'Setujui\'
        ],
        \'reject\' => [
            \'color\' => \'text-red-600 hover:text-red-900 hover:bg-red-50\',
            \'icon\' => \'M6 18L18 6M6 6l12 12\',
            \'defaultTitle\' => \'Tolak\'
        ],
    ];
    $config = $configs[$type] ?? $configs[\'view\'];
    $buttonTitle = $title ?? $config[\'defaultTitle\'];
@endphp

@if($onclick)
    <button type="button" 
            onclick="{{ $onclick }}" 
            class="{{ $config[\'color\'] }} p-1.5 rounded-lg transition-all duration-200 relative group"
            title="{{ $buttonTitle }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $config[\'icon\'] }}"/>
        </svg>
        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
            {{ $buttonTitle }}
        </span>
    </button>
@else
    <a href="{{ $href }}" 
       class="{{ $config[\'color\'] }} p-1.5 rounded-lg transition-all duration-200 relative group inline-block"
       title="{{ $buttonTitle }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $config[\'icon\'] }}"/>
        </svg>
        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-10">
            {{ $buttonTitle }}
        </span>
    </a>
@endif';

file_put_contents('resources/views/components/table/action-button.blade.php', $actionButtonContent);
echo "✓ Updated action-button.blade.php with tooltips and hover effects\n";

// 4. Create improved staff/surat/index.blade.php
$indexContent = '@extends(\'layouts.app\')

@section(\'title\', \'Daftar Surat\')

@section(\'content\')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Daftar Surat</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ Auth::user()->role->name === \'kaprodi\' ? \'Kelola surat program studi\' : \'Daftar surat yang Anda buat\' }}
                </p>
            </div>
            @if(Auth::user()->hasRole(\'staff_prodi\'))
            <a href="{{ route(\'staff.surat.create\') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Surat Baru
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards with improved styling --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-gray-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Surat</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $surats->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Menunggu</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $surats->where(\'currentStatus.kode_status\', \'review_kaprodi\')->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Disetujui</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $surats->whereIn(\'currentStatus.kode_status\', [\'disetujui_kaprodi\', \'disetujui_fakultas\'])->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Ditolak</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $surats->whereIn(\'currentStatus.kode_status\', [\'ditolak_kaprodi\', \'ditolak_fakultas\'])->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table with improved styling --}}
    <x-table.wrapper>
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th width="5%">#</x-table.th>
                    <x-table.th width="15%">Nomor Surat</x-table.th>
                    <x-table.th width="25%">Perihal</x-table.th>
                    <x-table.th width="10%">Jenis</x-table.th>
                    <x-table.th width="10%">Prodi</x-table.th>
                    <x-table.th width="15%">Dibuat Oleh</x-table.th>
                    <x-table.th width="10%">Tanggal</x-table.th>
                    <x-table.th width="10%" align="center">Status</x-table.th>
                    <x-table.th width="10%" align="center">Aksi</x-table.th>
                </tr>
            </x-table.thead>
            <x-table.tbody>
                @forelse($surats as $index => $surat)
                <x-table.tr>
                    <x-table.td>{{ $surats->firstItem() + $index }}</x-table.td>
                    <x-table.td>
                        <div class="font-medium text-gray-900">{{ $surat->nomor_surat }}</div>
                    </x-table.td>
                    <x-table.td>
                        <div class="max-w-xs">
                            <p class="text-sm text-gray-900 truncate" title="{{ $surat->perihal }}">
                                {{ Str::limit($surat->perihal, 50) }}
                            </p>
                        </div>
                    </x-table.td>
                    <x-table.td>
                        <span class="text-sm text-gray-900">{{ $surat->jenisSurat->nama_jenis ?? \'Surat Biasa\' }}</span>
                    </x-table.td>
                    <x-table.td>
                        <span class="text-sm text-gray-900">{{ $surat->prodi->nama_prodi ?? \'Informatika\' }}</span>
                    </x-table.td>
                    <x-table.td>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $surat->createdBy->nama ?? $surat->createdBy->name ?? \'Staff Prodi Informatika\' }}
                            </span>
                            @if($surat->createdBy && $surat->createdBy->jabatan)
                            <span class="text-xs text-gray-500 mt-0.5">
                                {{ $surat->createdBy->jabatan->nama_jabatan ?? \'Staff Program Studi\' }}
                            </span>
                            @else
                            <span class="text-xs text-gray-500 mt-0.5">Staff Program Studi</span>
                            @endif
                        </div>
                    </x-table.td>
                    <x-table.td>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-900">{{ $surat->created_at->format(\'d/m/Y\') }}</span>
                            <span class="text-xs text-gray-500 mt-0.5">{{ $surat->created_at->format(\'H:i\') }}</span>
                        </div>
                    </x-table.td>
                    <x-table.td align="center">
                        <x-table.status :status="$surat->currentStatus" />
                    </x-table.td>
                    <x-table.td align="center">
                        <div class="flex items-center justify-center gap-1">
                            <x-table.action-button 
                                type="view" 
                                :href="route(\'surat.show\', $surat->id)" />
                            
                            @if(Auth::user()->hasRole(\'kaprodi\') && in_array($surat->currentStatus->kode_status ?? \'\', [\'review_kaprodi\', \'diajukan\']))
                                <x-table.action-button 
                                    type="approve" 
                                    onclick="confirmApprove({{ $surat->id }})" />
                                <x-table.action-button 
                                    type="reject" 
                                    onclick="confirmReject({{ $surat->id }})" />
                            @endif
                            
                            @if($surat->created_by === Auth::id() && in_array($surat->currentStatus->kode_status ?? \'\', [\'draft\', \'ditolak_kaprodi\']))
                                <x-table.action-button 
                                    type="edit" 
                                    :href="route(\'surat.edit\', $surat->id)" />
                            @endif
                        </div>
                    </x-table.td>
                </x-table.tr>
                @empty
                <x-table.empty 
                    colspan="9" 
                    message="Belum ada surat yang tersedia" />
                @endforelse
            </x-table.tbody>
        </x-table.table>
        
        @if($surats->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($surats->previousPageUrl())
                    <a href="{{ $surats->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                    @endif
                    @if($surats->nextPageUrl())
                    <a href="{{ $surats->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Menampilkan 
                            <span class="font-medium">{{ $surats->firstItem() }}</span> 
                            hingga 
                            <span class="font-medium">{{ $surats->lastItem() }}</span> 
                            dari 
                            <span class="font-medium">{{ $surats->total() }}</span> 
                            hasil
                        </p>
                    </div>
                    <div>
                        {{ $surats->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </x-table.wrapper>
</div>
@endsection

@push(\'scripts\')
<script>
function confirmApprove(id) {
    if (confirm(\'Apakah Anda yakin ingin menyetujui surat ini?\')) {
        window.location.href = `/surat/${id}/approve`;
    }
}

function confirmReject(id) {
    if (confirm(\'Apakah Anda yakin ingin menolak surat ini?\')) {
        window.location.href = `/surat/${id}/reject`;
    }
}
</script>
@endpush';

file_put_contents('resources/views/staff/surat/index.blade.php', $indexContent);
echo "✓ Updated staff/surat/index.blade.php with improved layout\n";

echo "\n=== IMPROVEMENTS APPLIED ===\n";
echo "1. ✓ Better text alignment in 'Dibuat Oleh' column\n";
echo "2. ✓ Improved status badges with consistent styling\n";
echo "3. ✓ Added tooltips to action buttons\n";
echo "4. ✓ Enhanced hover effects on buttons and rows\n";
echo "5. ✓ Better truncation for long text\n";
echo "6. ✓ Improved stats cards with icons\n";
echo "7. ✓ Consistent spacing and typography\n";

echo "\nBackup saved at: $backupDir\n";
echo "\nTo restore if needed:\n";
echo "cp $backupDir/* resources/views/\n";

echo "\n=== DONE ===\n";