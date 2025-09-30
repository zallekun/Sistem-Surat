{{-- resources/views/admin/pengajuan/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.pengajuan.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar Pengajuan
            </a>
        </div>

        <!-- Alert if Stuck -->
        @if($isStuck)
            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-orange-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800">Pengajuan Stuck!</h3>
                        <p class="text-sm text-orange-700 mt-1">
                            Pengajuan ini sudah {{ $stuckDays }} hari tanpa progress di status "{{ $pengajuan->status }}".
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alert if Deleted -->
        @if($pengajuan->trashed())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-trash text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Pengajuan Telah Dihapus</h3>
                            <p class="text-sm text-red-700 mt-1">
                                Dihapus pada {{ $pengajuan->deleted_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                    <button onclick="restorePengajuan({{ $pengajuan->id }})" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                        <i class="fas fa-undo mr-1"></i>Pulihkan
                    </button>
                </div>
            </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Detail Pengajuan</h2>
                        <p class="text-sm text-white/90 mt-1">{{ $pengajuan->jenisSurat->nama_jenis ?? 'Surat' }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $statusBadge = match($pengajuan->status) {
                                'pending' => ['bg' => 'bg-yellow-500', 'icon' => 'fa-clock'],
                                'approved_prodi' => ['bg' => 'bg-blue-500', 'icon' => 'fa-check'],
                                'approved_fakultas' => ['bg' => 'bg-indigo-500', 'icon' => 'fa-check-double'],
                                'completed' => ['bg' => 'bg-green-500', 'icon' => 'fa-check-circle'],
                                'rejected_prodi', 'rejected_fakultas' => ['bg' => 'bg-red-500', 'icon' => 'fa-times-circle'],
                                default => ['bg' => 'bg-gray-500', 'icon' => 'fa-circle']
                            };
                        @endphp
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            <i class="fas {{ $statusBadge['icon'] }} mr-1.5"></i>
                            {{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pengajuan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Pengajuan</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tracking Token</label>
                                <p class="text-sm font-mono text-blue-600 font-medium mt-1">{{ $pengajuan->tracking_token }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Program Studi</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->prodi->nama_prodi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Pengajuan</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->created_at->format('d F Y, H:i') }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Last Update</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->updated_at->format('d F Y, H:i') }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $pengajuan->updated_at->diffForHumans() }}</p>
                            </div>
                            @if($pengajuan->completed_at)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Selesai</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->completed_at->format('d F Y, H:i') }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase">Processing Time</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->created_at->diffForHumans($pengajuan->completed_at, true) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Data Mahasiswa</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">NIM</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->nim ?? $pengajuan->nim }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nama Lengkap</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Email</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->email ?? $pengajuan->email ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">No. Telepon</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->phone ?? $pengajuan->phone ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Keperluan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Keperluan</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase">Jenis Surat</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->jenisSurat->nama_jenis ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Keperluan</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->keperluan ?? '-' }}</p>
                        </div>
                        
                        @if($additionalData && !empty($additionalData))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-medium text-gray-500 uppercase mb-2 block">Data Tambahan</label>
                                <div class="space-y-2">
                                    @foreach($additionalData as $key => $value)
                                        @if(!in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa']))
                                            <div class="flex justify-between py-2 border-b border-gray-100">
                                                <span class="text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                <span class="text-sm text-gray-900 font-medium">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Approval -->
                @if($pengajuan->approvalHistories && $pengajuan->approvalHistories->count() > 0)
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Timeline Approval</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($pengajuan->approvalHistories->sortBy('created_at') as $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $iconColor = in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'bg-green-500' : 
                                                                (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'bg-red-500' : 'bg-blue-500');
                                                    $icon = in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'fa-check' : 
                                                           (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'fa-times' : 'fa-circle');
                                                @endphp
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $iconColor }}">
                                                    <i class="fas {{ $icon }} text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">{{ $history->action_label }}</span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-gray-500">
                                                        {{ $history->created_at->format('d F Y, H:i') }}
                                                        @if($history->performedBy)
                                                            • oleh {{ $history->performedBy->nama }}
                                                        @endif
                                                    </p>
                                                </div>
                                                @if($history->notes)
                                                    <div class="mt-2 text-sm text-gray-700">
                                                        <p class="italic">{{ $history->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <!-- Admin Actions -->
                @if(!$pengajuan->trashed())
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Admin Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($pengajuan->surat_pengantar_url)
                            <a href="{{ $pengajuan->surat_pengantar_url }}" 
                               target="_blank"
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                                <i class="fas fa-download mr-2"></i>Download Surat
                            </a>
                        @endif
                        
                        <button onclick="deletePengajuan({{ $pengajuan->id }})" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                            <i class="fas fa-trash mr-2"></i>Hapus Pengajuan
                        </button>
                        
                        <a href="{{ route('admin.pengajuan.index') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>
                @endif

                <!-- Status Info -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Status Info</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-600">Current Status</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $pengajuan->status)) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Prodi</span>
                            <span class="text-sm font-medium text-gray-900">{{ $pengajuan->prodi->nama_prodi ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Fakultas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $pengajuan->prodi->fakultas->nama_fakultas ?? '-' }}</span>
                        </div>
                        @if($isStuck)
                            <div class="flex items-center justify-between py-2 border-t border-gray-100">
                                <span class="text-sm text-orange-600 font-medium">Stuck Duration</span>
                                <span class="text-sm font-bold text-orange-600">{{ $stuckDays }} hari</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Hapus Pengajuan</h3>
            <p class="text-sm text-gray-600 mb-4">
                Anda yakin ingin menghapus pengajuan ini? Data masih bisa dipulihkan nanti.
            </p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penghapusan (wajib)</label>
                <textarea id="deleteReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" placeholder="Masukkan alasan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmDelete()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deleteId = null;

function deletePengajuan(id) {
    deleteId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteReason').value = '';
}

function confirmDelete() {
    const reason = document.getElementById('deleteReason').value.trim();
    
    if (!reason) {
        alert('Alasan penghapusan wajib diisi');
        return;
    }
    
    fetch(`/admin/pengajuan/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("admin.pengajuan.index") }}';
        } else {
            alert(data.message || 'Gagal menghapus pengajuan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function restorePengajuan(id) {
    if (!confirm('Yakin ingin memulihkan pengajuan ini?')) return;
    
    fetch(`/admin/pengajuan/${id}/restore`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal memulihkan pengajuan');
        }
    });
}
</script>
@endpush
@endsection