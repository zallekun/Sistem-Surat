@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section dengan spacing yang lebih baik -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Surat Saya</h1>
                    <p class="mt-2 text-sm text-gray-600">Kelola dan pantau status surat Anda dengan mudah</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('staff.surat.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buat Surat Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages dengan styling yang lebih baik -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Data Table dengan shadow yang lebih baik -->
        <div class="bg-white shadow-lg rounded-lg border border-gray-200 overflow-hidden">
            <!-- Header Section dengan filter yang dipisah -->
            <div class="px-6 py-6 bg-white border-b border-gray-200">
                <div class="flex flex-col space-y-4">
                    <!-- Title dan Counter -->
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Data Surat</h2>
                            <p class="text-sm text-gray-500 mt-1">Total: <span id="totalCount" class="font-medium">{{ $surats->total() ?? 0 }}</span> surat ditemukan</p>
                        </div>
                    </div>
                    
                    <!-- Filter Section dengan layout terpisah -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">
                        <!-- Search Bar -->
                        <div class="lg:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                            <div class="relative">
                                <input type="text" id="search" placeholder="Cari berdasarkan nomor surat atau perihal..." 
                                       class="w-full pl-10 pr-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors duration-200">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filters dan Reset -->
                        <div class="flex items-end space-x-3">
                            <div class="flex-1">
                                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="statusFilter" class="w-full px-3 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Semua Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="review_kaprodi">Review</option>
                                    <option value="disetujui_kaprodi">Disetujui</option>
                                    <option value="ditolak_kaprodi">Ditolak</option>
                                </select>
                            </div>
                            
                            <div class="flex-1">
                                <label for="jenisFilter" class="block text-sm font-medium text-gray-700 mb-2">Jenis</label>
                                <select id="jenisFilter" class="w-full px-3 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Semua Jenis</option>
                                    @foreach(\App\Models\JenisSurat::all() as $jenis)
                                        <option value="{{ $jenis->nama_jenis }}">{{ $jenis->nama_jenis }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Reset Button -->
                            <button onclick="clearFilters()" 
                                    class="p-3 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 flex-shrink-0"
                                    title="Reset semua filter">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table dengan layout responsif tanpa horizontal scroll -->
            <div class="overflow-hidden">
                <table id="suratTable" class="w-full divide-y divide-gray-200 table-fixed">
                    <thead class="bg-gray-50">
                        <tr>
                            <!-- Kolom dengan persentase width yang optimal -->
                            <th scope="col" class="w-[15%] px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button class="flex items-center hover:text-gray-700 focus:outline-none transition-colors duration-200" onclick="sortTable(0)">
                                    <span class="truncate">Nomor</span>
                                    <svg class="w-3 h-3 ml-1 opacity-50 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="w-[30%] px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="truncate">Perihal Surat</span>
                            </th>
                            <th scope="col" class="w-[15%] px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                <button class="flex items-center hover:text-gray-700 focus:outline-none transition-colors duration-200" onclick="sortTable(2)">
                                    <span class="truncate">Jenis</span>
                                    <svg class="w-3 h-3 ml-1 opacity-50 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="w-[15%] px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="truncate">Status</span>
                            </th>
                            <th scope="col" class="w-[12%] px-3 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                <button class="flex items-center hover:text-gray-700 focus:outline-none transition-colors duration-200" onclick="sortTable(4)">
                                    <span class="truncate">Tanggal</span>
                                    <svg class="w-3 h-3 ml-1 opacity-50 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="w-[13%] px-3 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="truncate">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($surats as $index => $surat)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-200" 
                            data-status="{{ $surat->currentStatus->kode_status ?? '' }}"
                            data-jenis="{{ $surat->jenisSurat->nama_jenis ?? '' }}"
                            data-tanggal="{{ $surat->created_at->format('Y-m-d') }}">
                            
                            <!-- Nomor Surat dengan ellipsis untuk text yang panjang -->
                            <td class="px-3 py-4">
                                <div class="text-sm font-semibold text-gray-900 truncate" title="{{ $surat->nomor_surat ?? 'Belum ada nomor' }}">
                                    {{ $surat->nomor_surat ?? 'Belum ada nomor' }}
                                </div>
                            </td>
                            
                            <!-- Perihal dengan multi-line truncate -->
                            <td class="px-3 py-4">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium leading-5 line-clamp-2 break-words" title="{{ $surat->perihal }}">
                                        {{ $surat->perihal }}
                                    </div>
                                    <!-- Mobile: Show jenis dan tanggal di bawah perihal -->
                                    <div class="mt-1 space-y-1 md:hidden">
                                        <div class="text-xs text-gray-600">{{ $surat->jenisSurat->nama_jenis ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 sm:hidden">{{ $surat->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Jenis Surat - hidden di mobile -->
                            <td class="px-3 py-4 hidden md:table-cell">
                                <div class="text-sm text-gray-700 font-medium truncate" title="{{ $surat->jenisSurat->nama_jenis ?? '-' }}">
                                    {{ $surat->jenisSurat->nama_jenis ?? '-' }}
                                </div>
                            </td>
                            
                            <!-- Status dengan ukuran yang lebih compact -->
                            <td class="px-3 py-4">
                                @php
                                    $statusCode = $surat->currentStatus->kode_status ?? '';
                                    $statusConfig = [
                                        'draft' => ['bg-blue-100 text-blue-800 border border-blue-300', 'Draft'],
                                        'review_kaprodi' => ['bg-amber-100 text-amber-800 border border-amber-300', 'Review'],
                                        'disetujui_kaprodi' => ['bg-green-100 text-green-800 border border-green-300', 'Disetujui'],
                                        'ditolak_kaprodi' => ['bg-red-100 text-red-800 border border-red-300', 'Ditolak'],
                                    ];
                                    $config = $statusConfig[$statusCode] ?? ['bg-gray-100 text-gray-800 border border-gray-300', 'N/A'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $config[0] }} truncate max-w-full" title="{{ $config[1] }}">
                                    {{ $config[1] }}
                                </span>
                            </td>
                            
                            <!-- Tanggal - hidden di mobile -->
                            <td class="px-3 py-4 text-sm hidden sm:table-cell">
                                <div class="font-medium text-gray-900 text-xs">{{ $surat->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $surat->created_at->format('H:i') }}</div>
                            </td>
                            
                            <!-- Actions dengan spacing yang lebih compact -->
                            <td class="px-3 py-4">
                                <div class="flex items-center justify-center space-x-1">
                                    <!-- View dengan ukuran yang lebih kecil -->
                                    <div class="group relative">
                                        <a href="{{ route('surat.show', $surat->id) }}" 
                                           class="inline-flex items-center justify-center w-7 h-7 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-full transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <!-- Tooltip dengan posisi yang lebih baik -->
                                        <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 transition-opacity duration-200 whitespace-nowrap z-20">
                                            Lihat
                                        </div>
                                    </div>
                                    
                                    <!-- Edit -->
                                    @if(in_array($statusCode, ['draft', 'ditolak_kaprodi']))
                                        <div class="group relative">
                                            <a href="{{ route('surat.edit', $surat->id) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-full transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 transition-opacity duration-200 whitespace-nowrap z-20">
                                                Edit
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Submit -->
                                    @if($statusCode === 'draft')
                                        <div class="group relative">
                                            <form action="{{ route('staff.surat.submit', $surat->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center justify-center w-7 h-7 text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 rounded-full transition-all duration-200"
                                                        onclick="return confirm('Submit surat untuk review Kaprodi?')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 transition-opacity duration-200 whitespace-nowrap z-20">
                                                Submit
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Tracking -->
                                    <div class="group relative">
                                        <a href="{{ route('staff.surat.tracking', $surat->id) }}" 
                                           class="inline-flex items-center justify-center w-7 h-7 text-purple-600 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 rounded-full transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                        </a>
                                        <div class="opacity-0 group-hover:opacity-100 absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 transition-opacity duration-200 whitespace-nowrap z-20">
                                            Track
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyStateRow">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada surat</h3>
                                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Mulai dengan membuat surat baru untuk keperluan administrasi Anda. Klik tombol "Buat Surat Baru" di atas.</p>
                                <div class="mt-6">
                                    <a href="{{ route('staff.surat.create') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Buat Surat Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination dengan styling yang lebih baik -->
            @if($surats->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($surats->onFirstPage())
                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                Sebelumnya
                            </span>
                        @else
                            <a href="{{ $surats->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Sebelumnya
                            </a>
                        @endif

                        @if($surats->hasMorePages())
                            <a href="{{ $surats->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Selanjutnya
                            </a>
                        @else
                            <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md">
                                Selanjutnya
                            </span>
                        @endif
                    </div>

                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Menampilkan
                                <span class="font-medium">{{ $surats->firstItem() ?? 0 }}</span>
                                sampai
                                <span class="font-medium">{{ $surats->lastItem() ?? 0 }}</span>
                                dari
                                <span class="font-medium">{{ $surats->total() ?? 0 }}</span>
                                hasil
                            </p>
                        </div>
                        <div>
                            {{ $surats->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
let sortDirection = {};
let currentSortColumn = -1;

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('statusFilter');
    const jenisFilter = document.getElementById('jenisFilter');
    
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (statusFilter) statusFilter.addEventListener('change', filterTable);
    if (jenisFilter) jenisFilter.addEventListener('change', filterTable);
    
    // Initialize table filtering
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('search')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    const jenisFilter = document.getElementById('jenisFilter')?.value || '';
    
    const table = document.getElementById('suratTable');
    if (!table) return;
    
    const tbody = table.getElementsByTagName('tbody')[0];
    if (!tbody) return;
    
    const rows = tbody.getElementsByTagName('tr');
    let visibleCount = 0;
    let hasDataRows = false;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        
        // Skip empty state row
        if (row.id === 'emptyStateRow' || row.children.length === 1) {
            continue;
        }
        
        hasDataRows = true;
        let showRow = true;
        
        // Search filter
        if (searchTerm) {
            const nomorSurat = row.children[0]?.textContent.toLowerCase() || '';
            const perihal = row.children[1]?.textContent.toLowerCase() || '';
            if (!nomorSurat.includes(searchTerm) && !perihal.includes(searchTerm)) {
                showRow = false;
            }
        }
        
        // Status filter
        if (statusFilter && row.dataset.status !== statusFilter) {
            showRow = false;
        }
        
        // Jenis filter
        if (jenisFilter && row.dataset.jenis !== jenisFilter) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
        if (showRow) {
            visibleCount++;
            // Update zebra striping for visible rows
            const isEven = visibleCount % 2 === 0;
            row.className = row.className.replace(/bg-(white|gray-50)/, isEven ? 'bg-gray-50' : 'bg-white');
        }
    }
    
    // Update total count
    const totalCountElement = document.getElementById('totalCount');
    if (totalCountElement) {
        totalCountElement.textContent = visibleCount;
    }
}

function clearFilters() {
    const searchInput = document.getElementById('search');
    const statusFilter = document.getElementById('statusFilter');
    const jenisFilter = document.getElementById('jenisFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (jenisFilter) jenisFilter.value = '';
    
    filterTable();
}

function sortTable(columnIndex) {
    const table = document.getElementById('suratTable');
    if (!table) return;
    
    const tbody = table.getElementsByTagName('tbody')[0];
    if (!tbody) return;
    
    const rows = Array.from(tbody.getElementsByTagName('tr')).filter(row => 
        row.id !== 'emptyStateRow' && row.children.length > 1
    );
    
    if (rows.length === 0) return;
    
    // Reset other column indicators
    const sortIcons = table.querySelectorAll('th button svg');
    sortIcons.forEach((icon, idx) => {
        if (idx !== currentSortColumn) {
            icon.classList.add('opacity-50');
            icon.classList.remove('rotate-180');
        }
    });
    
    // Determine sort direction
    const isCurrentColumn = currentSortColumn === columnIndex;
    const currentDirection = sortDirection[columnIndex] || 'asc';
    const newDirection = isCurrentColumn && currentDirection === 'asc' ? 'desc' : 'asc';
    
    sortDirection[columnIndex] = newDirection;
    currentSortColumn = columnIndex;
    
    // Sort rows
    rows.sort((a, b) => {
        let aValue, bValue;
        
        if (columnIndex === 4) { // Date column
            const aDateText = a.children[columnIndex].children[0].textContent.trim();
            const bDateText = b.children[columnIndex].children[0].textContent.trim();
            
            // Convert DD/MM/YYYY to YYYY-MM-DD for proper sorting
            const parseDate = (dateStr) => {
                const [day, month, year] = dateStr.split('/');
                return new Date(year, month - 1, day);
            };
            
            aValue = parseDate(aDateText);
            bValue = parseDate(bDateText);
        } else {
            aValue = a.children[columnIndex].textContent.trim();
            bValue = b.children[columnIndex].textContent.trim();
        }
        
        let comparison = 0;
        if (aValue instanceof Date && bValue instanceof Date) {
            comparison = aValue.getTime() - bValue.getTime();
        } else {
            comparison = aValue.localeCompare(bValue);
        }
        
        return newDirection === 'asc' ? comparison : -comparison;
    });
    
    // Rebuild the tbody with sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort icon
    const currentSortIcon = table.querySelectorAll('th button svg')[columnIndex];
    if (currentSortIcon) {
        currentSortIcon.classList.remove('opacity-50');
        currentSortIcon.classList.toggle('rotate-180', newDirection === 'desc');
    }
    
    // Re-apply filtering and zebra striping
    filterTable();
}
</script>

@endsection