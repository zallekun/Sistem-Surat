@extends('layouts.app')

@section('title', 'Daftar Pengajuan Surat')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <a href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600">Staff</a>
        </div>
    </li>
    <li aria-current="page">
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <span class="text-sm font-medium text-gray-500">Pengajuan</span>
        </div>
    </li>
@endsection

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Token, NIM, atau Nama..."
                                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved_prodi" {{ request('status') == 'approved_prodi' ? 'selected' : '' }}>Disetujui Prodi</option>
                                <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Sudah Diproses</option>
                                <option value="rejected_prodi" {{ request('status') == 'rejected_prodi' ? 'selected' : '' }}>Ditolak Prodi</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>

                        <!-- Jenis Surat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Surat</label>
                            <select name="jenis_surat" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Jenis</option>
                                @if(isset($jenisSurat))
                                    @foreach($jenisSurat as $jenis)
                                        <option value="{{ $jenis->id }}" {{ request('jenis_surat') == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rentang Tanggal</label>
                            <div class="flex gap-2">
                                <input type="date" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}" 
                                       class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="date" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}" 
                                       class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                                <i class="fas fa-search mr-1"></i>Filter
                            </button>
                            <a href="{{ route('staff.pengajuan.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                                <i class="fas fa-redo mr-1"></i>Reset
                            </a>
                        </div>
                        
                        <!-- Quick Filters -->
                        <div class="flex gap-2">
                            <a href="{{ route('staff.pengajuan.index', ['status' => 'pending']) }}" 
                               class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium hover:bg-yellow-200 transition">
                                Pending ({{ $pendingCount ?? 0 }})
                            </a>
                            <a href="{{ route('staff.pengajuan.index', ['status' => 'approved_prodi']) }}" 
                               class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-xs font-medium hover:bg-green-200 transition">
                                Disetujui ({{ $approvedCount ?? 0 }})
                            </a>
                            <a href="{{ route('staff.pengajuan.index', ['status' => 'completed']) }}" 
                               class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium hover:bg-blue-200 transition">
                                Selesai ({{ $completedCount ?? 0 }})
                            </a>
                        </div>
                    </div>

                    <!-- Applied Filters -->
                    @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-blue-800">Filter aktif:</span>
                                    
                                    @if(request('search'))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            "{{ request('search') }}"
                                            <a href="{{ route('staff.pengajuan.index', request()->except('search')) }}" class="ml-1.5 hover:text-blue-900">×</a>
                                        </span>
                                    @endif

                                    @if(request('status'))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                                            <a href="{{ route('staff.pengajuan.index', request()->except('status')) }}" class="ml-1.5 hover:text-green-900">×</a>
                                        </span>
                                    @endif
                                </div>
                                
                                <a href="{{ route('staff.pengajuan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Hapus semua
                                </a>
                            </div>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Table Section with Fixed Header -->
            @if(isset($pengajuans) && $pengajuans->count() > 0)
                <div class="relative">
                    <!-- Table Container - Fixed height with scroll -->
                    <div class="overflow-hidden">
                        <!-- Fixed Header -->
                        <div class="bg-gray-50 border-b-2 border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Token Tracking</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data Mahasiswa</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jenis Surat</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tanggal Pengajuan</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="overflow-y-auto scroll-smooth" style="max-height: 500px; will-change: scroll-position;">
                            <table class="min-w-full">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($pengajuans as $index => $pengajuan)
                                        <tr class="hover:bg-blue-50">
                                            <td class="px-4 py-4 text-center">
                                                <span class="text-sm font-medium text-gray-700">{{ $index + 1 }}</span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="text-xs font-mono text-blue-600 font-medium">{{ $pengajuan->tracking_token }}</span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $pengajuan->nama_mahasiswa }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $pengajuan->nim }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-900">{{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $pengajuan->jenisSurat->kode_surat ?? '' }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                @php
                                                    $statusConfig = [
                                                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                                                        'processed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Diproses'],
                                                        'approved_prodi' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui'],
                                                        'rejected_prodi' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                                                        'completed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Selesai'],
                                                    ];
                                                    $status = $statusConfig[$pengajuan->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($pengajuan->status)];
                                                @endphp
                                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="text-sm text-gray-900">{{ $pengajuan->created_at->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $pengajuan->created_at->format('H:i') }}</div>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('staff.pengajuan.show', $pengajuan->id) }}" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium">
                                                        <i class="fas fa-eye mr-1.5"></i>
                                                        Detail
                                                    </a>
                                                    
                                                    @if($pengajuan->status === 'pending')
                                                        <button onclick="processAction({{ $pengajuan->id }}, 'approve')" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-xs font-medium">
                                                            <i class="fas fa-check mr-1.5"></i>
                                                            Setuju
                                                        </button>
                                                        <button onclick="rejectAction({{ $pengajuan->id }})" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-xs font-medium">
                                                            <i class="fas fa-times mr-1.5"></i>
                                                            Tolak
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if(method_exists($pengajuans, 'links'))
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $pengajuans->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <i class="fas fa-inbox fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                            Tidak Ada Data yang Cocok
                        @else
                            Tidak Ada Pengajuan
                        @endif
                    </h3>
                    <p class="text-gray-500 text-sm">
                        @if(request()->hasAny(['search', 'status', 'jenis_surat', 'date_from', 'date_to']))
                            Coba ubah filter atau <a href="{{ route('staff.pengajuan.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">reset pencarian</a>
                        @else
                            Belum ada pengajuan surat yang masuk
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function processAction(id, action) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
    
    fetch(`/staff/pengajuan/${id}/process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ action: 'approve' })
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

function rejectAction(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (!reason || reason.trim() === '') {
        alert('Alasan penolakan harus diisi!');
        return;
    }
    
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