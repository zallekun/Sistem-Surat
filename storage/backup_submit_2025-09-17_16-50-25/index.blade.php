@extends('layouts.app')

@section('title', 'Daftar Surat')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Daftar Surat</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ Auth::user()->role->name === 'kaprodi' ? 'Kelola surat program studi' : 'Daftar surat yang Anda buat' }}
                    </p>
                </div>
                @if(Auth::user()->hasRole('staff_prodi'))
                <a href="{{ route('staff.surat.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Surat
                </a>
                @endif
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-lg p-2">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Total</p>
                        <p class="text-xl font-semibold text-gray-900">{{ $surats->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-lg p-2">
                        <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Menunggu</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $surats->whereIn('currentStatus.kode_status', ['review_kaprodi', 'diajukan'])->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-lg p-2">
                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Disetujui</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $surats->whereIn('currentStatus.kode_status', ['disetujui_kaprodi', 'disetujui_fakultas'])->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-lg p-2">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Ditolak</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ $surats->whereIn('currentStatus.kode_status', ['ditolak_kaprodi', 'ditolak_fakultas'])->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table with optimized spacing --}}
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- Optimized column spacing --}}
                        <th class="w-8 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nomor
                        </th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Perihal
                        </th>
                        <th class="hidden lg:table-cell px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jenis
                        </th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tujuan
                        </th>
                        <th class="hidden md:table-cell px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dibuat
                        </th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($surats as $index => $surat)
                    @php
                        $statusCode = $surat->currentStatus->kode_status ?? 'draft';
                        $canApprove = Auth::user()->hasRole('kaprodi') && in_array($statusCode, ['review_kaprodi', 'diajukan']);
                        $canEdit = $surat->created_by === Auth::id() && in_array($statusCode, ['draft', 'ditolak_kaprodi']);
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        {{-- # --}}
                        <td class="px-2 py-3 text-sm text-gray-500">
                            {{ $surats->firstItem() + $index }}
                        </td>
                        
                        {{-- Nomor Surat --}}
                        <td class="px-2 py-3">
                            <div class="text-sm font-medium text-gray-900 truncate max-w-[130px]" title="{{ $surat->nomor_surat }}">
                                {{ $surat->nomor_surat }}
                            </div>
                        </td>
                        
                        {{-- Perihal --}}
                        <td class="px-2 py-3">
                            <div class="text-sm text-gray-900 truncate max-w-[180px] lg:max-w-[220px]" title="{{ $surat->perihal }}">
                                {{ $surat->perihal }}
                            </div>
                        </td>
                        
                        {{-- Jenis --}}
                        <td class="hidden lg:table-cell px-2 py-3">
                            <div class="text-sm text-gray-900 truncate max-w-[90px]">
                                {{ $surat->jenisSurat->nama_jenis ?? 'Surat Biasa' }}
                            </div>
                        </td>
                        
                        {{-- Tujuan --}}
                        <td class="px-2 py-3">
                            <div class="text-sm text-gray-900 truncate max-w-[90px]" title="{{ $surat->tujuanJabatan->nama_jabatan ?? 'Dekan' }}">
                                {{ $surat->tujuanJabatan->nama_jabatan ?? 'Dekan' }}
                            </div>
                        </td>
                        
                        {{-- Dibuat --}}
                        <td class="hidden md:table-cell px-2 py-3">
                            <div class="text-sm text-gray-900 truncate max-w-[110px]" title="{{ $surat->createdBy->nama ?? $surat->createdBy->name }}">
                                {{ $surat->createdBy->nama ?? $surat->createdBy->name ?? 'Staff' }}
                            </div>
                            <div class="text-xs text-gray-500 truncate max-w-[110px]">
                                {{ Str::limit($surat->createdBy->jabatan->nama_jabatan ?? 'Staff', 18) }}
                            </div>
                        </td>
                        
                        {{-- Tanggal --}}
                        <td class="px-2 py-3">
                            <div class="text-sm text-gray-900">
                                {{ $surat->created_at->format('d/m/y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $surat->created_at->format('H:i') }}
                            </div>
                        </td>
                        
                        {{-- Status --}}
                        <td class="px-2 py-3 text-center">
                            @php
                                $statusConfig = [
                                    'draft' => ['bg-gray-100 text-gray-700', 'Draft'],
                                    'diajukan' => ['bg-yellow-100 text-yellow-700', 'Diajukan'],
                                    'review_kaprodi' => ['bg-yellow-100 text-yellow-700', 'Review'],
                                    'disetujui_kaprodi' => ['bg-blue-100 text-blue-700', 'Disetujui'],
                                    'ditolak_kaprodi' => ['bg-red-100 text-red-700', 'Ditolak'],
                                    'diproses_fakultas' => ['bg-indigo-100 text-indigo-700', 'Proses'],
                                    'disetujui_fakultas' => ['bg-green-100 text-green-700', 'Selesai'],
                                ];
                                $config = $statusConfig[$statusCode] ?? ['bg-gray-100 text-gray-700', 'Unknown'];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $config[0] }}">
                                {{ $config[1] }}
                            </span>
                        </td>
                        
                        {{-- Aksi - Direct buttons with optimized spacing --}}
                        <td class="px-2 py-3">
                            <div class="flex items-center justify-center gap-1">
                                {{-- View button - always visible --}}
                                <a href="{{ route('surat.show', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-indigo-600 hover:bg-indigo-50 rounded transition-colors duration-150 group relative"
                                   title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{-- Tooltip --}}
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Lihat
                                    </span>
                                </a>
                                
                                {{-- Edit button - if owner --}}
                                @if($canEdit)
                                <a href="{{ route('surat.edit', $surat->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 text-yellow-600 hover:bg-yellow-50 rounded transition-colors duration-150 group relative"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Edit
                                    </span>
                                </a>
                                @endif
                                
                                {{-- Approve button - for kaprodi --}}
                                @if($canApprove)
                                <button onclick="confirmApprove({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-7 h-7 text-green-600 hover:bg-green-50 rounded transition-colors duration-150 group relative"
                                        title="Setujui">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Setujui
                                    </span>
                                </button>
                                
                                <button onclick="confirmReject({{ $surat->id }})" 
                                        class="inline-flex items-center justify-center w-7 h-7 text-red-600 hover:bg-red-50 rounded transition-colors duration-150 group relative"
                                        title="Tolak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                                        Tolak
                                    </span>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm font-medium text-gray-900">Belum ada surat</p>
                            <p class="mt-1 text-sm text-gray-500">Mulai buat surat baru untuk melihat data di sini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Pagination --}}
            @if($surats->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-700 mb-2 sm:mb-0">
                        Menampilkan 
                        <span class="font-medium">{{ $surats->firstItem() }}</span>-<span class="font-medium">{{ $surats->lastItem() }}</span>
                        dari 
                        <span class="font-medium">{{ $surats->total() }}</span>
                    </div>
                    <div class="flex justify-center">
                        {{ $surats->withQueryString()->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom max-width classes */
    .max-w-\[90px\] { max-width: 90px; }
    .max-w-\[110px\] { max-width: 110px; }
    .max-w-\[130px\] { max-width: 130px; }
    .max-w-\[180px\] { max-width: 180px; }
    .max-w-\[220px\] { max-width: 220px; }
    
    /* Ensure action buttons stay compact */
    .gap-1 { gap: 0.25rem; }
    
    /* Smaller padding on mobile */
    @media (max-width: 768px) {
        .px-2 { padding-left: 0.375rem; padding-right: 0.375rem; }
    }
</style>
@endpush

@push('scripts')
<script>
function confirmApprove(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui surat ini?')) {
        window.location.href = `/surat/${id}/approve`;
    }
}

function confirmReject(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/surat/${id}/reject`;
        
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        
        form.appendChild(token);
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush