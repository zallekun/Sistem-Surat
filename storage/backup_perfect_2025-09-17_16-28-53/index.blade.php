@extends('layouts.app')

@section('title', 'Daftar Surat')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Daftar Surat</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ Auth::user()->role->name === 'kaprodi' ? 'Kelola surat program studi' : 'Daftar surat yang Anda buat' }}
                </p>
            </div>
            @if(Auth::user()->hasRole('staff_prodi'))
            <a href="{{ route('staff.surat.create') }}" 
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
                                {{ $surats->where('currentStatus.kode_status', 'review_kaprodi')->count() }}
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
                                {{ $surats->whereIn('currentStatus.kode_status', ['disetujui_kaprodi', 'disetujui_fakultas'])->count() }}
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
                                {{ $surats->whereIn('currentStatus.kode_status', ['ditolak_kaprodi', 'ditolak_fakultas'])->count() }}
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
                        <span class="text-sm text-gray-900">{{ $surat->jenisSurat->nama_jenis ?? 'Surat Biasa' }}</span>
                    </x-table.td>
                    <x-table.td>
                        <span class="text-sm text-gray-900">{{ $surat->prodi->nama_prodi ?? 'Informatika' }}</span>
                    </x-table.td>
                    <x-table.td>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $surat->createdBy->nama ?? $surat->createdBy->name ?? 'Staff Prodi Informatika' }}
                            </span>
                            @if($surat->createdBy && $surat->createdBy->jabatan)
                            <span class="text-xs text-gray-500 mt-0.5">
                                {{ $surat->createdBy->jabatan->nama_jabatan ?? 'Staff Program Studi' }}
                            </span>
                            @else
                            <span class="text-xs text-gray-500 mt-0.5">Staff Program Studi</span>
                            @endif
                        </div>
                    </x-table.td>
                    <x-table.td>
                        <div class="flex flex-col">
                            <span class="text-sm text-gray-900">{{ $surat->created_at->format('d/m/Y') }}</span>
                            <span class="text-xs text-gray-500 mt-0.5">{{ $surat->created_at->format('H:i') }}</span>
                        </div>
                    </x-table.td>
                    <x-table.td align="center">
                        <x-table.status :status="$surat->currentStatus" />
                    </x-table.td>
                    <x-table.td align="center">
                        <div class="flex items-center justify-center gap-1">
                            <x-table.action-button 
                                type="view" 
                                :href="route('surat.show', $surat->id)" />
                            
                            @if(Auth::user()->hasRole('kaprodi') && in_array($surat->currentStatus->kode_status ?? '', ['review_kaprodi', 'diajukan']))
                                <x-table.action-button 
                                    type="approve" 
                                    onclick="confirmApprove({{ $surat->id }})" />
                                <x-table.action-button 
                                    type="reject" 
                                    onclick="confirmReject({{ $surat->id }})" />
                            @endif
                            
                            @if($surat->created_by === Auth::id() && in_array($surat->currentStatus->kode_status ?? '', ['draft', 'ditolak_kaprodi']))
                                <x-table.action-button 
                                    type="edit" 
                                    :href="route('surat.edit', $surat->id)" />
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

@push('scripts')
<script>
function confirmApprove(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui surat ini?')) {
        window.location.href = `/surat/${id}/approve`;
    }
}

function confirmReject(id) {
    if (confirm('Apakah Anda yakin ingin menolak surat ini?')) {
        window.location.href = `/surat/${id}/reject`;
    }
}
</script>
@endpush