@extends('layouts.app')

@section('title', 'Daftar Surat Fakultas')

@section('content')
@php
    if (!isset($pengajuans)) {
        $pengajuans = isset($pengajuan) ? collect([$pengajuan]) : collect([]);
    }
@endphp

<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-envelope mr-2"></i>Daftar Surat Fakultas
            </h1>
            <p class="text-gray-600 text-sm mt-1">{{ Auth::user()->prodi->fakultas->nama_fakultas ?? 'Fakultas Sains dan Informatika' }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm p-5 border-l-4 border-gray-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Item</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $paginationInfo->total ?? 0 }}</p>
                    </div>
                    <i class="fas fa-list text-gray-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-yellow-50/95 backdrop-blur-sm rounded-xl shadow-sm p-5 border-l-4 border-yellow-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Perlu Review</p>
                        <p class="text-2xl font-bold text-yellow-900 mt-1">
                            {{ $items->where('status_class', 'bg-yellow-100 text-yellow-800')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-clock text-yellow-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-blue-50/95 backdrop-blur-sm rounded-xl shadow-sm p-5 border-l-4 border-blue-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-800">Dalam Proses</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">
                            {{ $items->where('status_class', 'bg-blue-100 text-blue-800')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-cogs text-blue-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-green-50/95 backdrop-blur-sm rounded-xl shadow-sm p-5 border-l-4 border-green-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-800">Disetujui</p>
                        <p class="text-2xl font-bold text-green-900 mt-1">
                            {{ $items->where('status_class', 'bg-green-100 text-green-800')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-check-circle text-green-300 text-2xl"></i>
                </div>
            </div>

            <div class="bg-red-50/95 backdrop-blur-sm rounded-xl shadow-sm p-5 border-l-4 border-red-400">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-800">Ditolak</p>
                        <p class="text-2xl font-bold text-red-900 mt-1">
                            {{ $items->where('status_class', 'bg-red-100 text-red-800')->count() }}
                        </p>
                    </div>
                    <i class="fas fa-times-circle text-red-300 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-filter mr-2"></i>Filter & Pencarian
                </h3>
                <button type="button" onclick="toggleFilter()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <span id="filterToggleText">Sembunyikan</span>
                    <i id="filterToggleIcon" class="fas fa-chevron-up ml-1"></i>
                </button>
            </div>
            
            <div id="filterSection" class="px-6 py-4 bg-gray-50">
                <form method="GET" action="{{ route('fakultas.surat.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Pencarian</label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nomor, perihal, atau nama..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Program Studi</label>
                            <select name="prodi_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Prodi</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                            <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Tipe</option>
                                <option value="surat" {{ request('type') == 'surat' ? 'selected' : '' }}>Surat</option>
                                <option value="pengajuan" {{ request('type') == 'pengajuan' ? 'selected' : '' }}>Pengajuan</option>
                            </select>
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->nama_status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Dari Tanggal</label>
                            <input type="date" 
                                   name="tanggal_dari" 
                                   value="{{ request('tanggal_dari') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sampai Tanggal</label>
                            <input type="date" 
                                   name="tanggal_sampai" 
                                   value="{{ request('tanggal_sampai') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                <i class="fas fa-search mr-1"></i>Cari
                            </button>
                            @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id', 'tanggal_dari', 'tanggal_sampai']))
                                <a href="{{ route('fakultas.surat.index') }}" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium text-center">
                                    <i class="fas fa-redo mr-1"></i>Reset
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Active Filters -->
                    @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id']))
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-gray-700">Filter aktif:</span>
                                
                                @if(request('search'))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        "{{ request('search') }}"
                                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-1.5 hover:text-blue-900">×</a>
                                    </span>
                                @endif
                                
                                @if(request('prodi_id'))
                                    @php $selectedProdi = $prodis->find(request('prodi_id')) @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $selectedProdi->nama_prodi ?? 'N/A' }}
                                        <a href="{{ request()->fullUrlWithQuery(['prodi_id' => null]) }}" class="ml-1.5 hover:text-green-900">×</a>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-sm overflow-hidden">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 m-4">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-2"></i>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 m-4">
                    <div class="flex">
                        <i class="fas fa-times-circle text-red-400 mr-2"></i>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Table -->
            @if($items->count() > 0)
                <div class="overflow-hidden">
                    <!-- Fixed Header -->
                    <div class="bg-gray-50 border-b-2 border-gray-200">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-12">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-20">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Nomor/ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Perihal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Prodi</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Dibuat Oleh</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-28">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    
                    <!-- Scrollable Body -->
                    <div class="overflow-y-auto scroll-smooth" style="max-height: 600px; will-change: scroll-position;">
                        <table class="min-w-full">
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-blue-50">
                                    <td class="px-4 py-4 text-center w-12">
                                        <span class="text-sm font-medium text-gray-700">{{ $paginationInfo->from + $index }}</span>
                                    </td>
                                    <td class="px-4 py-4 w-20">
                                        @if($item->type === 'pengajuan')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-file-import mr-1"></i>
                                                Pengajuan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-file-alt mr-1"></i>
                                                Surat
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 w-32">
                                        @if($item->type === 'pengajuan')
                                            <div class="text-sm text-blue-600 font-mono">{{ $item->nomor_surat }}</div>
                                            <div class="text-xs text-gray-500">NIM: {{ $item->original_pengajuan->nim ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-sm text-gray-900 font-mono">{{ $item->nomor_surat }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900 line-clamp-2">{{ $item->perihal }}</div>
                                        @if($item->type === 'pengajuan')
                                            <div class="text-xs text-gray-500 mt-1">{{ $item->nama_mahasiswa ?? 'N/A' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 w-32">
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                            {{ $item->prodi->nama_prodi ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 w-40">
                                        <div class="text-sm text-gray-900">{{ $item->createdBy->nama ?? 'N/A' }}</div>
                                        @if($item->type === 'surat' && isset($item->original_surat->createdBy->jabatan))
                                            <div class="text-xs text-gray-500">{{ $item->original_surat->createdBy->jabatan->nama_jabatan }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 w-28">
                                        <div class="text-sm text-gray-900">{{ $item->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-4 w-32">
                                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $item->status_class }}">
                                            {{ $item->status_display }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 w-32 text-center">
                                        @if($item->type === 'pengajuan')
                                            @php 
                                                $pengajuan = $item->original_pengajuan;
                                                $needsPengantar = $pengajuan->needsSuratPengantar();
                                                $hasPengantar = $pengajuan->hasSuratPengantar();
                                            @endphp
                                            
                                            @if($pengajuan->status === 'approved_prodi' && $needsPengantar && !$hasPengantar)
                                                <span class="text-yellow-600 text-xs">
                                                    <i class="fas fa-exclamation-triangle"></i> Menunggu Pengantar
                                                </span>
                                            @else
                                                <a href="{{ route('fakultas.surat.show', $pengajuan->id) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                    <i class="fas fa-eye mr-1.5"></i>
                                                    Detail
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('fakultas.surat.show', $item->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                <i class="fas fa-eye mr-1.5"></i>
                                                Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if($paginationInfo->has_pages ?? false)
                <div class="px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span class="font-medium">{{ $paginationInfo->from }}</span> sampai 
                            <span class="font-medium">{{ $paginationInfo->to }}</span> dari 
                            <span class="font-medium">{{ $paginationInfo->total }}</span> item
                        </div>
                        <div class="flex gap-1">
                            @if($paginationInfo->current_page > 1)
                                <a href="?page={{ $paginationInfo->current_page - 1 }}&{{ http_build_query(request()->except('page')) }}" 
                                   class="px-3 py-2 text-sm bg-white border rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif
                            
                            @for($i = max(1, $paginationInfo->current_page - 2); $i <= min($paginationInfo->last_page, $paginationInfo->current_page + 2); $i++)
                                <a href="?page={{ $i }}&{{ http_build_query(request()->except('page')) }}" 
                                   class="px-3 py-2 text-sm border rounded-lg {{ $i == $paginationInfo->current_page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-gray-50' }}">
                                    {{ $i }}
                                </a>
                            @endfor
                            
                            @if($paginationInfo->current_page < $paginationInfo->last_page)
                                <a href="?page={{ $paginationInfo->current_page + 1 }}&{{ http_build_query(request()->except('page')) }}" 
                                   class="px-3 py-2 text-sm bg-white border rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data</h3>
                    <p class="text-gray-500 text-sm">
                        @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id']))
                            Coba ubah filter atau <a href="{{ route('fakultas.surat.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        @else
                            Belum ada surat atau pengajuan yang perlu diproses
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleFilter() {
    const filterSection = document.getElementById('filterSection');
    const toggleText = document.getElementById('filterToggleText');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    if (filterSection.classList.contains('hidden')) {
        filterSection.classList.remove('hidden');
        toggleText.textContent = 'Sembunyikan';
        toggleIcon.className = 'fas fa-chevron-up ml-1';
    } else {
        filterSection.classList.add('hidden');
        toggleText.textContent = 'Tampilkan';
        toggleIcon.className = 'fas fa-chevron-down ml-1';
    }
}
</script>
@endsection