<!-- fakultas.surat.index -->



@extends('layouts.app')

@section('title', 'Daftar Surat Fakultas')

@section('content')
   @php
       if (!isset($pengajuans)) {
           $pengajuans = isset($pengajuan) ? collect([$pengajuan]) : collect([]);
       }
   @endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            <i class="fas fa-envelope mr-2"></i>Daftar Surat Fakultas - {{ Auth::user()->prodi->fakultas->nama_fakultas ?? 'Fakultas Sains dan Informatika' }}
        </h1>
        <p class="text-gray-600 text-sm mt-1">Kelola dan proses surat dari berbagai program studi</p>
    </div>

    <!-- Enhanced Filters Section -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-filter mr-2"></i>Filter & Pencarian
                </h3>
                <button type="button" onclick="toggleFilter()" class="text-sm text-indigo-600 hover:text-indigo-500">
                    <span id="filterToggleText">Sembunyikan Filter</span>
                    <i id="filterToggleIcon" class="fas fa-chevron-up ml-1"></i>
                </button>
            </div>
            
            <div id="filterSection">
                <form method="GET" action="{{ route('fakultas.surat.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                    <!-- Search Input -->
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                        <input type="text" 
                               name="search" 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                               placeholder="Nomor surat, perihal, atau nama..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- Prodi Filter -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                        <select name="prodi_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Semua Prodi</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                        <select name="type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Semua Tipe</option>
                            <option value="surat" {{ request('type') == 'surat' ? 'selected' : '' }}>Surat</option>
                            <option value="pengajuan" {{ request('type') == 'pengajuan' ? 'selected' : '' }}>Pengajuan</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->nama_status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                        <input type="date" 
                               name="tanggal_dari" 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                               value="{{ request('tanggal_dari') }}">
                    </div>
                    
                    <!-- Date To -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                        <input type="date" 
                               name="tanggal_sampai" 
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                               value="{{ request('tanggal_sampai') }}">
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="sm:col-span-1 flex flex-col justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mb-2">
                            <i class="fas fa-search mr-1"></i>Cari
                        </button>
                        @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id', 'tanggal_dari', 'tanggal_sampai']))
                            <a href="{{ route('fakultas.surat.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sync mr-1"></i>Reset
                            </a>
                        @endif
                    </div>
                </form>
                
                <!-- Active Filters Display -->
                @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id', 'tanggal_dari', 'tanggal_sampai']))
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="text-sm font-medium text-gray-700">Filter aktif:</span>
                        
                        @if(request('search'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Pencarian: "{{ request('search') }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-1 text-blue-600 hover:text-blue-500">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </span>
                        @endif
                        
                        @if(request('prodi_id'))
                            @php $selectedProdi = $prodis->find(request('prodi_id')) @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Prodi: {{ $selectedProdi->nama_prodi ?? 'Unknown' }}
                                <a href="{{ request()->fullUrlWithQuery(['prodi_id' => null]) }}" class="ml-1 text-green-600 hover:text-green-500">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </span>
                        @endif
                        
                        @if(request('type'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Tipe: {{ ucfirst(request('type')) }}
                                <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="ml-1 text-purple-600 hover:text-purple-500">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-gray-400">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Item</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $paginationInfo->total ?? 0 }}</dd>
                    </div>
                    <i class="fas fa-list text-gray-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 overflow-hidden shadow rounded-lg border-l-4 border-yellow-400">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dt class="text-sm font-medium text-yellow-800 truncate">Perlu Review</dt>
                        <dd class="mt-1 text-2xl font-semibold text-yellow-900">
                            {{ $items->where('status_class', 'bg-yellow-100 text-yellow-800')->count() }}
                        </dd>
                    </div>
                    <i class="fas fa-clock text-yellow-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 overflow-hidden shadow rounded-lg border-l-4 border-blue-400">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dt class="text-sm font-medium text-blue-800 truncate">Dalam Proses</dt>
                        <dd class="mt-1 text-2xl font-semibold text-blue-900">
                            {{ $items->where('status_class', 'bg-blue-100 text-blue-800')->count() }}
                        </dd>
                    </div>
                    <i class="fas fa-cogs text-blue-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-green-50 overflow-hidden shadow rounded-lg border-l-4 border-green-400">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dt class="text-sm font-medium text-green-800 truncate">Disetujui</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-900">{{ $items->where('status_class', 'bg-green-100 text-green-800')->count() }}</dd>
                    </div>
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-red-50 overflow-hidden shadow rounded-lg border-l-4 border-red-400">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dt class="text-sm font-medium text-red-800 truncate">Ditolak</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-900">{{ $items->where('status_class', 'bg-red-100 text-red-800')->count() }}</dd>
                    </div>
                    <i class="fas fa-times-circle text-red-400 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Table Section -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th scope="col" class="w-16 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor/ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prodi</th>
                        <th scope="col" class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $paginationInfo->from + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            @if($item->type === 'pengajuan')
                                <div class="text-blue-600 font-mono">{{ $item->nomor_surat }}</div>
                                <div class="text-xs text-gray-500">NIM: {{ $item->original_pengajuan->nim ?? 'N/A' }}</div>
                            @else
                                <div class="text-gray-900 font-mono">{{ $item->nomor_surat }}</div>
                                @if($item->created_at)
                                    <div class="text-xs text-gray-500">{{ $item->created_at->format('Y') }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $item->perihal }}">
                                {{ $item->perihal }}
                            </div>
                            @if($item->type === 'pengajuan')
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="font-medium">{{ $item->nama_mahasiswa ?? 'N/A' }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                {{ $item->prodi->nama_prodi ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ $item->createdBy->nama ?? $item->createdBy->name ?? 'N/A' }}</div>
                            @if($item->type === 'surat' && isset($item->original_surat) && $item->original_surat->createdBy && $item->original_surat->createdBy->jabatan)
                                <div class="text-xs text-gray-500">{{ $item->original_surat->createdBy->jabatan->nama_jabatan }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $item->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $item->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->status_class }}">
                                {{ $item->status_display }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
    <!-- Hanya tombol Detail untuk SEMUA item (surat dan pengajuan) -->
    <a href="{{ route('fakultas.surat.show', $item->id) }}" 
       style="display: inline-flex; align-items: center; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 0.75rem; font-weight: 500; border-radius: 4px; text-decoration: none;"
       onmouseover="this.style.backgroundColor='#2563eb'" 
       onmouseout="this.style.backgroundColor='#3b82f6'">
        <i class="fas fa-eye mr-1"></i>
        Detail
    </a>
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                <p class="text-gray-500 text-lg mb-2">Tidak ada data ditemukan</p>
                                <p class="text-gray-400 text-sm">
                                    @if(request()->hasAny(['search', 'prodi_id', 'type', 'status_id', 'tanggal_dari', 'tanggal_sampai']))
                                        Coba ubah filter atau 
                                        <a href="{{ route('fakultas.surat.index') }}" class="text-indigo-600 hover:text-indigo-500">reset pencarian</a>
                                    @else
                                        Belum ada surat atau pengajuan yang perlu diproses
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Enhanced Pagination -->
        @if($paginationInfo->has_pages ?? false)
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan 
                    <span class="font-medium">{{ $paginationInfo->from }}</span> 
                    sampai 
                    <span class="font-medium">{{ $paginationInfo->to }}</span> 
                    dari 
                    <span class="font-medium">{{ $paginationInfo->total }}</span> 
                    item
                </div>
                <div class="flex space-x-1">
                    @if($paginationInfo->current_page > 1)
                        <a href="?page={{ $paginationInfo->current_page - 1 }}&{{ http_build_query(request()->except('page')) }}" 
                           class="inline-flex items-center px-3 py-2 text-sm bg-white border rounded hover:bg-gray-50 transition-colors">
                            <i class="fas fa-chevron-left mr-1"></i>Previous
                        </a>
                    @endif
                    
                    @for($i = max(1, $paginationInfo->current_page - 2); $i <= min($paginationInfo->last_page, $paginationInfo->current_page + 2); $i++)
                        <a href="?page={{ $i }}&{{ http_build_query(request()->except('page')) }}" 
                           class="px-3 py-2 text-sm border rounded transition-colors {{ $i == $paginationInfo->current_page ? 'bg-indigo-500 text-white border-indigo-500' : 'bg-white hover:bg-gray-50' }}">
                            {{ $i }}
                        </a>
                    @endfor
                    
                    @if($paginationInfo->current_page < $paginationInfo->last_page)
                        <a href="?page={{ $paginationInfo->current_page + 1 }}&{{ http_build_query(request()->except('page')) }}" 
                           class="inline-flex items-center px-3 py-2 text-sm bg-white border rounded hover:bg-gray-50 transition-colors">
                            Next<i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Keep existing modals... -->
@foreach($items as $item)
    @if($item->type === 'surat' && $item->currentStatus->kode_status === 'disetujui_kaprodi')
    <!-- Approve Modal -->
    <div id="approveModal{{ $item->id }}" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('fakultas.surat.approve', $item->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center mb-4">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <i class="fas fa-check text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 text-center">Setujui Surat</h3>
                        <div class="bg-blue-50 p-4 rounded-md mb-4">
                            <p class="text-sm"><strong>Nomor:</strong> {{ $item->nomor_surat }}</p>
                            <p class="text-sm"><strong>Perihal:</strong> {{ $item->perihal }}</p>
                            <p class="text-sm"><strong>Prodi:</strong> {{ $item->prodi->nama_prodi ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                            <textarea name="catatan" rows="3" class="shadow-sm focus:ring-green-500 focus:border-green-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Tambahkan catatan persetujuan..."></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            <i class="fas fa-check mr-2"></i>Setujui Surat
                        </button>
                        <button type="button" onclick="closeModal('approveModal{{ $item->id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal{{ $item->id }}" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('fakultas.surat.reject', $item->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center mb-4">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <i class="fas fa-times text-red-600 text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 text-center">Tolak Surat</h3>
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Perhatian</p>
                                    <p class="text-sm text-yellow-700 mt-1">Surat ini akan ditolak dan dikembalikan ke prodi untuk diperbaiki.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-md mb-4">
                            <p class="text-sm"><strong>Nomor:</strong> {{ $item->nomor_surat }}</p>
                            <p class="text-sm"><strong>Perihal:</strong> {{ $item->perihal }}</p>
                            <p class="text-sm"><strong>Prodi:</strong> {{ $item->prodi->nama_prodi ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="keterangan" rows="4" required class="shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Jelaskan alasan penolakan secara detail..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Alasan ini akan dikirim ke prodi untuk perbaikan</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            <i class="fas fa-times mr-2"></i>Tolak Surat
                        </button>
                        <button type="button" onclick="closeModal('rejectModal{{ $item->id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
function toggleFilter() {
    const filterSection = document.getElementById('filterSection');
    const toggleText = document.getElementById('filterToggleText');
    const toggleIcon = document.getElementById('filterToggleIcon');
    
    if (filterSection.classList.contains('hidden')) {
        filterSection.classList.remove('hidden');
        toggleText.textContent = 'Sembunyikan Filter';
        toggleIcon.className = 'fas fa-chevron-up ml-1';
    } else {
        filterSection.classList.add('hidden');
        toggleText.textContent = 'Tampilkan Filter';
        toggleIcon.className = 'fas fa-chevron-down ml-1';
    }
}

function openApproveModal(id) {
    document.getElementById('approveModal' + id).classList.remove('hidden');
}

function openRejectModal(id) {
    document.getElementById('rejectModal' + id).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Enhanced functions for pengajuan
function processAction(id, action) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
    
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
    
    fetch(`/pengajuan/${id}/fakultas/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: 'approve' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            successAlert.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>${data.message}</span>
                </div>
            `;
            document.body.appendChild(successAlert);
            
            setTimeout(() => {
                successAlert.remove();
                window.location.reload();
            }, 2000);
        } else {
            alert(data.message || 'Terjadi kesalahan');
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

function generateSurat(id) {
    if (!confirm('Generate surat untuk pengajuan ini? Surat akan dibuat dan dapat diedit selanjutnya.')) return;
    
    // Show loading state
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generating...';
    
    fetch(`/pengajuan/${id}/fakultas/generate-surat`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const successAlert = document.createElement('div');
            successAlert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg z-50 max-w-md';
            successAlert.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                    <div>
                        <p class="font-medium">${data.message}</p>
                        ${data.edit_url ? '<p class="text-sm mt-1">Surat akan dibuka di tab baru untuk diedit</p>' : ''}
                    </div>
                </div>
            `;
            document.body.appendChild(successAlert);
            
            if (data.edit_url) {
                window.open(data.edit_url, '_blank');
            }
            
            setTimeout(() => {
                successAlert.remove();
                window.location.reload();
            }, 3000);
        } else {
            alert(data.message || 'Terjadi kesalahan');
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}


</script>
@endpush