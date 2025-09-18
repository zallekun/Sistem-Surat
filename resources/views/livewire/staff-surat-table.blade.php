<div class="container mx-auto px-4">
    <!-- Search and Filters -->
    <div class="mb-4 flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
        <input type="text" wire:model.live="search" 
               class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-grow" 
               placeholder="Cari nomor surat atau perihal...">
        
        <select wire:model.live="statusFilter" 
                class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Status</option>
            @foreach(($allStatuses ?? []) as $status)
                <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterTujuanJabatan" 
                class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Tujuan Jabatan</option>
            @foreach(($tujuanJabatans ?? []) as $jabatan)
                <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
            @endforeach
        </select>

        <button wire:click="exportUsers" 
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Export Excel
        </button>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        <button wire:click="sortBy('nomor_surat')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Nomor Surat</span>
                            @if(($sortField ?? 'created_at') === 'nomor_surat')
                                @if(($sortDirection ?? 'desc') === 'asc')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 10l5-5 5 5H5z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 10l-5 5-5-5h10z"/>
                                    </svg>
                                @endif
                            @else
                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12l5-5 5 5H5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Perihal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        Jenis Surat
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        Tujuan
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        <button wire:click="sortBy('status_id')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Status</span>
                            @if(($sortField ?? 'created_at') === 'status_id')
                                @if(($sortDirection ?? 'desc') === 'asc')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 10l5-5 5 5H5z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 10l-5 5-5-5h10z"/>
                                    </svg>
                                @endif
                            @else
                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12l5-5 5 5H5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Tanggal Dibuat</span>
                            @if(($sortField ?? 'created_at') === 'created_at')
                                @if(($sortDirection ?? 'desc') === 'asc')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 10l5-5 5 5H5z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M15 10l-5 5-5-5h10z"/>
                                    </svg>
                                @endif
                            @else
                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 12l5-5 5 5H5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                        Pengaju
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse(($surats ?? []) as $surat)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $surat->nomor_surat ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="max-w-xs truncate" title="{{ $surat->perihal ?? '-' }}">
                            {{ $surat->perihal ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $surat->jenisSurat->nama_jenis ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $surat->tujuanJabatan->nama_jabatan ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusCode = $surat->currentStatus->kode_status ?? '';
                            $badgeClasses = 'inline-flex px-2 py-1 text-xs font-semibold rounded-full';
                            
                            if ($statusCode === 'review_kaprodi') {
                                $badgeClasses .= ' bg-yellow-100 text-yellow-800';
                            } elseif ($statusCode === 'disetujui_kaprodi') {
                                $badgeClasses .= ' bg-green-100 text-green-800';
                            } elseif ($statusCode === 'ditolak_kaprodi') {
                                $badgeClasses .= ' bg-red-100 text-red-800';
                            } elseif ($statusCode === 'draft') {
                                $badgeClasses .= ' bg-blue-100 text-blue-800';
                            } else {
                                $badgeClasses .= ' bg-gray-100 text-gray-800';
                            }
                        @endphp
                        <span class="{{ $badgeClasses }}">
                            {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $surat->created_at ? $surat->created_at->format('d/m/Y') : '-' }}
                        <br>
                        <span class="text-xs text-gray-400">
                            {{ $surat->created_at ? $surat->created_at->format('H:i') : '' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $surat->createdBy->nama ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <!-- View Button -->
                            <a href="{{ route('surat.show', $surat->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            
                            <!-- Approve/Reject untuk Kaprodi -->
                            @if(Auth::user()->role->name === 'kaprodi' && ($surat->currentStatus->kode_status ?? '') === 'review_kaprodi')
                                <button type="button" 
                                        onclick="approveSurat({{ $surat->id }})" 
                                        class="text-green-600 hover:text-green-900 inline-flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button type="button" 
                                        onclick="rejectSurat({{ $surat->id }})" 
                                        class="text-red-600 hover:text-red-900 inline-flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif

                            <!-- Edit untuk Staff Prodi -->
                            @if(Auth::user()->role->name === 'staff_prodi' && 
                                in_array($surat->currentStatus->kode_status ?? '', ['draft', 'ditolak_umum']))
                                <a href="{{ route('surat.edit', $surat->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 inline-flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada surat ditemukan</h3>
                            <p class="text-gray-500">Coba ubah filter atau buat surat baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($surats) && $surats->hasPages())
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Menampilkan {{ $surats->firstItem() ?? 0 }} - {{ $surats->lastItem() ?? 0 }} 
            dari {{ $surats->total() ?? 0 }} surat
        </div>
        <div>
            {{ $surats->links() }}
        </div>
    </div>
    @endif

    <!-- JavaScript -->
    <script>
    window.approveSurat = function(id) {
        if (confirm('Apakah Anda yakin ingin menyetujui surat ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/surat/${id}/approve`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    }

    window.rejectSurat = function(id) {
        const reason = prompt('Masukkan alasan penolakan:');
        if (reason && reason.trim() !== '') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/surat/${id}/reject`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'keterangan';
            reasonInput.value = reason;
            
            form.appendChild(csrfToken);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</div>