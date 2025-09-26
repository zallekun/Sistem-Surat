@extends('layouts.app')

@section('title', 'Daftar Pengajuan Surat')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Daftar Pengajuan Surat</h2>
                <span class="text-sm text-gray-500">
                    Total: {{ isset($pengajuans) ? $pengajuans->count() : 0 }} pengajuan
                </span>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('staff.pengajuan.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search by Token/NIM/Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Token, NIM, atau Nama..."
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Filter by Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved_prodi" {{ request('status') == 'approved_prodi' ? 'selected' : '' }}>Disetujui Prodi</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Sudah Diproses</option>
                            <option value="rejected_prodi" {{ request('status') == 'rejected_prodi' ? 'selected' : '' }}>Ditolak Prodi</option>
                            <option value="sedang_ditandatangani" {{ request('status') == 'sedang_ditandatangani' ? 'selected' : '' }}>Sedang Ditandatangani</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <!-- Filter by Jenis Surat -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Surat</label>
                        <select name="jenis_surat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Semua Jenis</option>
                            @if(isset($jenisSurat))
                                @foreach($jenisSurat as $jenis)
                                    <option value="{{ $jenis->id }}" {{ request('jenis_surat') == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama_jenis }} ({{ $jenis->kode_surat }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Filter by Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Tanggal</label>
                        <div class="flex space-x-2">
                            <input type="date" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <span class="flex items-center text-gray-500">s/d</span>
                            <input type="date" 
                                   name="date_to" 
                                   value="{{ request('date_to') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('staff.pengajuan.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                    
                    <!-- Quick Filters -->
                    <div class="flex space-x-2">
                        <a href="{{ route('staff.pengajuan.index', ['status' => 'pending']) }}" 
                           class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs hover:bg-yellow-200 transition {{ request('status') == 'pending' ? 'bg-yellow-200' : '' }}">
                            Pending ({{ $pendingCount ?? 0 }})
                        </a>
                        <a href="{{ route('staff.pengajuan.index', ['status' => 'approved_prodi']) }}" 
                           class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs hover:bg-green-200 transition {{ request('status') == 'approved_prodi' ? 'bg-green-200' : '' }}">
                            Disetujui ({{ $approvedCount ?? 0 }})
                        </a>
                        <a href="{{ route('staff.pengajuan.index', ['status' => 'completed']) }}" 
                           class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs hover:bg-blue-200 transition {{ request('status') == 'completed' ? 'bg-blue-200' : '' }}">
                            Selesai ({{ $completedCount ?? 0 }})
                        </a>
                    </div>
                </div>

                <!-- Applied Filters Display -->
                @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                
                                @if(request('search'))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Pencarian: "{{ request('search') }}"
                                        <a href="{{ route('staff.pengajuan.index', request()->except('search')) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('status'))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                                        <a href="{{ route('staff.pengajuan.index', request()->except('status')) }}" class="ml-1 text-green-600 hover:text-green-800">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('jenis_surat'))
                                    @php
                                        $selectedJenis = collect($jenisSurat ?? [])->where('id', request('jenis_surat'))->first();
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Jenis: {{ $selectedJenis->nama_jenis ?? 'Unknown' }}
                                        <a href="{{ route('staff.pengajuan.index', request()->except('jenis_surat')) }}" class="ml-1 text-purple-600 hover:text-purple-800">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('date_from') || request('date_to'))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Tanggal: {{ request('date_from') ? request('date_from') : '...' }} s/d {{ request('date_to') ? request('date_to') : '...' }}
                                        <a href="{{ route('staff.pengajuan.index', request()->except(['date_from', 'date_to'])) }}" class="ml-1 text-orange-600 hover:text-orange-800">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif
                            </div>
                            
                            <a href="{{ route('staff.pengajuan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Hapus semua filter
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- Content -->
        <div class="p-6">
            @if(isset($pengajuans) && $pengajuans->count() > 0)
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'tracking_token', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex">
                                        Token Tracking
                                        @if(request('sort') == 'tracking_token')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50 group-hover:opacity-100"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_mahasiswa', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex">
                                        Mahasiswa
                                        @if(request('sort') == 'nama_mahasiswa')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50 group-hover:opacity-100"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis Surat
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex">
                                        Status
                                        @if(request('sort') == 'status')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50 group-hover:opacity-100"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex">
                                        Tanggal
                                        @if(request('sort') == 'created_at')
                                            <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                        @else
                                            <i class="fas fa-sort ml-1 opacity-50 group-hover:opacity-100"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pengajuans as $pengajuan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-blue-600">
                                        {{ $pengajuan->tracking_token }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $pengajuan->nama_mahasiswa }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            NIM: {{ $pengajuan->nim }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $pengajuan->jenisSurat->kode_surat ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processed' => 'bg-blue-100 text-blue-800', 
                                                'approved_prodi' => 'bg-green-100 text-green-800',
                                                'rejected_prodi' => 'bg-red-100 text-red-800',
                                                'sedang_ditandatangani' => 'bg-orange-100 text-orange-800',
                                                'completed' => 'bg-purple-100 text-purple-800'
                                            ];
                                            $statusColor = $statusColors[$pengajuan->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        @php
                                            $statusLabels = [
                                                'pending' => 'Menunggu Persetujuan',
                                                'approved_prodi' => 'Disetujui Prodi',
                                                'processed' => 'Sudah Diproses',
                                                'rejected_prodi' => 'Ditolak Prodi',
                                                'sedang_ditandatangani' => 'Sedang Ditandatangani',
                                                'completed' => 'Selesai',
                                                'rejected_fakultas' => 'Ditolak Fakultas'
                                            ];
                                            $statusLabel = $statusLabels[$pengajuan->status] ?? ucfirst(str_replace('_', ' ', $pengajuan->status));
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $pengajuan->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('staff.pengajuan.show', $pengajuan->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        
                                        @if($pengajuan->status === 'pending' && auth()->user()->hasRole(['staff_prodi', 'kaprodi']))
                                            <button onclick="processAction({{ $pengajuan->id }}, 'approve')" 
                                                    class="text-green-600 hover:text-green-900 mr-2">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button onclick="rejectAction({{ $pengajuan->id }})" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($pengajuans, 'links'))
                    <div class="mt-6">
                        {{ $pengajuans->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                            Tidak Ada Pengajuan yang Cocok
                        @else
                            Tidak Ada Pengajuan
                        @endif
                    </h3>
                    <p class="text-gray-500">
                        @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                            Coba ubah filter pencarian atau <a href="{{ route('staff.pengajuan.index') }}" class="text-blue-600 hover:text-blue-800">reset semua filter</a>.
                        @else
                            Belum ada pengajuan surat yang masuk.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function processAction(id, action) {
    if (action === 'approve') {
        if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
        
        // Use the same route as show.blade.php
        fetch(`/staff/pengajuan/${id}/process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan');
        });
    }
}

function rejectAction(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (!reason || reason.trim() === '') {
        alert('Alasan penolakan harus diisi!');
        return;
    }
    
    // Use the same route as show.blade.php
    fetch(`/staff/pengajuan/${id}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action: 'reject',
            rejection_reason: reason.trim()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan');
    });
}
</script>
@endsection