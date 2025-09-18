@extends('layouts.app')

@section('title', 'Daftar Surat Fakultas')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            <i class="fas fa-envelope mr-2"></i>Daftar Surat Fakultas - {{ Auth::user()->prodi->fakultas->nama_fakultas ?? 'Fakultas Sains dan Informatika' }}
        </h1>
    </div>

    <!-- Filters Section -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('fakultas.surat.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                <div class="sm:col-span-2">
                    <input type="text" 
                           name="search" 
                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                           placeholder="Cari nomor/perihal surat..." 
                           value="{{ request('search') }}">
                </div>
                
                <div>
                    <select name="prodi_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="">Semua Prodi</option>
                        @foreach($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->nama_prodi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <select name="status_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->nama_status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <input type="date" 
                           name="tanggal_dari" 
                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                           value="{{ request('tanggal_dari') }}"
                           placeholder="dd/mm/yyyy">
                </div>
                
                <div>
                    <input type="date" 
                           name="tanggal_sampai" 
                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                           value="{{ request('tanggal_sampai') }}"
                           placeholder="dd/mm/yyyy">
                </div>
                
                <div class="flex space-x-2 sm:col-span-6 lg:col-span-1">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-search mr-1"></i>
                    </button>
                    @if(request()->hasAny(['search', 'prodi_id', 'status_id', 'tanggal_dari', 'tanggal_sampai']))
                        <a href="{{ route('fakultas.surat.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-sync mr-1"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Surat</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $surats->total() }}</dd>
            </div>
        </div>

        <div class="bg-yellow-50 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-yellow-800 truncate">Menunggu Proses</dt>
                <dd class="mt-1 text-3xl font-semibold text-yellow-900">{{ $surats->where('currentStatus.kode_status', 'disetujui_kaprodi')->count() }}</dd>
            </div>
        </div>

        <div class="bg-green-50 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-green-800 truncate">Disetujui</dt>
                <dd class="mt-1 text-3xl font-semibold text-green-900">{{ $surats->where('currentStatus.kode_status', 'disetujui_fakultas')->count() }}</dd>
            </div>
        </div>

        <div class="bg-red-50 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-red-800 truncate">Ditolak</dt>
                <dd class="mt-1 text-3xl font-semibold text-red-900">{{ $surats->where('currentStatus.kode_status', 'ditolak_fakultas')->count() }}</dd>
            </div>
        </div>
    </div>

    <!-- Table Section -->
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
                        <th scope="col" class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prodi</th>
                        <th scope="col" class="w-40 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="w-32 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="w-32 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($surats as $index => $surat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $surats->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $surat->nomor_surat ?? '2025/FSI/IF/'.str_pad($surat->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $surat->perihal }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $surat->prodi->nama_prodi ?? 'Informatika' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ $surat->createdBy->nama ?? $surat->createdBy->name ?? 'Staff Prodi' }}</div>
                            @if($surat->createdBy && $surat->createdBy->jabatan)
                                <div class="text-xs text-gray-500">{{ $surat->createdBy->jabatan->nama_jabatan ?? 'Informatika' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $surat->created_at->format('d/m/Y H:i') ?? '16/09/2025 17:44' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = match($surat->currentStatus->kode_status ?? 'disetujui_kaprodi') {
                                    'disetujui_kaprodi' => 'bg-yellow-100 text-yellow-800',
                                    'diproses_fakultas' => 'bg-blue-100 text-blue-800',
                                    'disetujui_fakultas' => 'bg-green-100 text-green-800',
                                    'ditolak_fakultas' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ $surat->currentStatus->nama_status ?? 'Disetujui Kaprodi' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="{{ route('fakultas.surat.show', $surat->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-2"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if(($surat->currentStatus->kode_status ?? 'disetujui_kaprodi') === 'disetujui_kaprodi')
                                <button type="button" 
                                        onclick="openApproveModal({{ $surat->id }})"
                                        class="text-green-600 hover:text-green-900 mr-2"
                                        title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" 
                                        onclick="openRejectModal({{ $surat->id }})"
                                        class="text-red-600 hover:text-red-900"
                                        title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Tidak ada surat yang perlu diproses</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($surats->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan {{ $surats->firstItem() }} sampai {{ $surats->lastItem() }} dari {{ $surats->total() }} surat
                </div>
                <div>
                    {{ $surats->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modals -->
@foreach($surats as $surat)
    @if(($surat->currentStatus->kode_status ?? 'disetujui_kaprodi') === 'disetujui_kaprodi')
    <!-- Approve Modal -->
    <div id="approveModal{{ $surat->id }}" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('fakultas.surat.approve', $surat->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Setujui Surat</h3>
                        <div class="bg-blue-50 p-4 rounded-md mb-4">
                            <p class="text-sm"><strong>Nomor:</strong> {{ $surat->nomor_surat }}</p>
                            <p class="text-sm"><strong>Perihal:</strong> {{ $surat->perihal }}</p>
                            <p class="text-sm"><strong>Tujuan:</strong> {{ $surat->tujuanJabatan->nama_jabatan ?? 'Pimpinan' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                            <textarea name="catatan" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Setujui
                        </button>
                        <button type="button" onclick="closeModal('approveModal{{ $surat->id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal{{ $surat->id }}" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('fakultas.surat.reject', $surat->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Tolak Surat</h3>
                        <div class="bg-yellow-50 p-4 rounded-md mb-4">
                            <p class="text-sm"><strong>Nomor:</strong> {{ $surat->nomor_surat }}</p>
                            <p class="text-sm"><strong>Perihal:</strong> {{ $surat->perihal }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="keterangan" rows="3" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Tolak
                        </button>
                        <button type="button" onclick="closeModal('rejectModal{{ $surat->id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
function openApproveModal(id) {
    document.getElementById('approveModal' + id).classList.remove('hidden');
}

function openRejectModal(id) {
    document.getElementById('rejectModal' + id).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    var alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);
</script>
@endpush