<div>
    <div class="flex flex-col md:flex-row justify-between mb-4 space-y-4 md:space-y-0 md:space-x-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari surat berdasarkan nomor, perihal, atau pengirim..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-grow">

        <select wire:model.live="filterStatus" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Status</option>
            @foreach($allStatuses as $status)
                <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
            @endforeach
        </select>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-[120px]" wire:click="sortBy('nomor_surat')">
                        Nomor Surat
                        @if($sortField === 'nomor_surat')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-[180px]" wire:click="sortBy('perihal')">
                        Perihal
                        @if($sortField === 'perihal')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">
                        Pengirim
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-[180px]">
                        Status
                    </th>
                    <th scope="col" class="pl-8 pr-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-[140px]" wire:click="sortBy('tanggal_surat')">
                        Tanggal Surat
                        @if($sortField === 'tanggal_surat')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="relative px-6 py-3 text-center w-[250px]">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($surats as $surat)
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                        <td class="px-6 py-4 text-center text-sm font-medium text-gray-900">
                            {{ $surat->nomor_surat }}
                        </td>
                        <td class="px-6 py-4 text-left text-sm text-gray-500">
                            {{ $surat->perihal }}
                        </td>
                        <td class="px-6 py-4 text-left text-sm text-gray-500">
                            {{ $surat->createdBy?->nama ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-center min-w-[150px]">
                            <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full whitespace-nowrap"
                                        style="background-color: {{ $surat->currentStatus->warna_status ?? '#ffffff' }}; color: {{ app(\App\Helpers\StatusHelper::class)->getTextColor($surat->currentStatus->warna_status ?? '#ffffff') }};">
                                {{ $surat->currentStatus?->nama_status ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="pl-8 pr-6 py-4 text-center text-sm text-gray-500">
                            {{ $surat->tanggal_surat->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-medium whitespace-nowrap">
                            <a href="{{ route('surat.show', $surat->id) }}" class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded-md" title="Lihat Surat"><i class="fa-solid fa-eye"></i></a>

                            @if(str_contains(Auth::user()->jabatan?->nama_jabatan, 'Divisi'))
                                @if($surat->currentStatus?->kode_status === 'proses_divisi')
                                    <button wire:click="$dispatch('openCompleteModal', { suratId: {{ $surat->id }} })" class="text-green-600 hover:text-green-900 ml-2 px-2 py-1 rounded-md" title="Selesaikan Surat"><i class="fa-solid fa-check"></i> Selesaikan</button>
                                    <button wire:click="$dispatch('openReturnModal', { suratId: {{ $surat->id }} })" class="text-red-600 hover:text-red-900 ml-2 px-2 py-1 rounded-md" title="Kembalikan ke Kabag TU"><i class="fa-solid fa-arrow-left"></i> Kembalikan</button>
                                @elseif($surat->currentStatus?->kode_status === 'selesai')
                                    <button wire:click="archiveSurat({{ $surat->id }})" wire:confirm="Apakah Anda yakin ingin mengarsipkan surat ini?" class="text-gray-600 hover:text-gray-900 ml-2 px-2 py-1 rounded-md" title="Arsipkan Surat"><i class="fa-solid fa-archive"></i> Arsipkan</button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $surats->links() }}
    </div>

    <!-- Complete Surat Modal -->
    <div x-data="{ open: false, currentSuratId: null }" x-show="open" @open-complete-modal.window="open = true; currentSuratId = $event.detail.suratId" @close-complete-modal.window="open = false; currentSuratId = null; $wire.finalDocument = null;" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Selesaikan Surat</h3>
            <div class="mt-2">
                <label for="finalDocument" class="block text-sm font-medium text-gray-700">Upload Dokumen Final (PDF)</label>
                <input type="file" wire:model="finalDocument" id="finalDocument" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                @error('finalDocument') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="mt-5 sm:mt-6">
                <button @click="$wire.completeSurat(currentSuratId); open = false;" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                    Selesaikan
                </button>
                <button @click="open = false" type="button" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Return to Kabag Modal -->
    <div x-data="{ open: false, currentSuratId: null, keterangan: '' }" x-show="open" @open-return-modal.window="open = true; currentSuratId = $event.detail.suratId" @close-return-modal.window="open = false; currentSuratId = null; keterangan = '';" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Kembalikan Surat ke Kabag TU</h3>
            <div class="mt-2">
                <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea x-model="keterangan" id="keterangan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                @error('keterangan') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="mt-5 sm:mt-6">
                <button @click="$wire.returnToKabag(currentSuratId, keterangan); open = false;" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                    Kembalikan
                </button>
                <button @click="open = false" type="button" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>