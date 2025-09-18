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
        <table class="min-w-full divide-y divide-gray-200">
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

                            @if($surat->currentStatus?->kode_status !== 'arsip')
                                @if(Auth::user()->jabatan?->nama_jabatan === 'Dekan' && $surat->currentStatus?->kode_status === 'menunggu_ttd_dekan')
                                    <button wire:click="tandaTanganSurat({{ $surat->id }})" class="text-purple-600 hover:text-purple-900 ml-2 px-2 py-1 rounded-md" title="Tanda Tangan Surat"><i class="fa-solid fa-signature"></i> Tanda Tangan</button>
                                @elseif(in_array(Auth::user()->jabatan?->nama_jabatan, ['Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan']) && $surat->currentStatus?->kode_status === 'disposisi_pimpinan')
                                    <button wire:click="openDisposisiModal({{ $surat->id }})" class="text-indigo-600 hover:text-indigo-900 ml-2 px-2 py-1 rounded-md" title="Disposisi Surat"><i class="fa-solid fa-share"></i> Disposisi</button>
                                @elseif(str_contains(Auth::user()->jabatan?->nama_jabatan, 'Wakil Dekan') && $surat->currentStatus?->kode_status === 'disposisi_paralel')
                                    <button wire:click="completeParallelDisposisi({{ $surat->id }})" class="text-green-600 hover:text-green-900 ml-2 px-2 py-1 rounded-md" title="Selesaikan Disposisi Paralel"><i class="fa-solid fa-check"></i> Selesaikan Disposisi</button>
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

    <!-- Disposisi Modal -->
    <div x-data="{ open: false, currentSuratId: null, instruksi: '', tujuanDisposisiId: '', selectedWds: [] }" x-show="open" @open-disposisi-modal.window="open = true; currentSuratId = $event.detail.suratId" @close-disposisi-modal.window="open = false; currentSuratId = null; instruksi = ''; tujuanDisposisiId = ''; selectedWds = [];" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Disposisi Surat</h3>
            <div class="mt-2">
                <label for="instruksi" class="block text-sm font-medium text-gray-700">Instruksi</label>
                <textarea x-model="instruksi" id="instruksi" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
            </div>
            @if(Auth::user()->jabatan?->nama_jabatan === 'Dekan')
                <div class="mt-4">
                    <label for="selected_wds" class="block text-sm font-medium text-gray-700">Disposisi Paralel ke Wakil Dekan</label>
                    <select x-model="selectedWds" id="selected_wds" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach($wakilDekans as $wd)
                            <option value="{{ $wd->id }}">{{ $wd->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pilih satu atau lebih Wakil Dekan untuk disposisi paralel.</p>
                </div>
                <div class="mt-4">
                    <label for="tujuan_jabatan_disposisi_single" class="block text-sm font-medium text-gray-700">Atau Disposisi Tunggal ke</label>
                    <select x-model="tujuanDisposisiId" id="tujuan_jabatan_disposisi_single" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Pilih Tujuan</option>
                        @foreach($tujuanJabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pilih satu tujuan jika bukan disposisi paralel.</p>
                </div>
            @else
                <div class="mt-4">
                    <label for="tujuan_jabatan_disposisi" class="block text-sm font-medium text-gray-700">Tujuan Disposisi</label>
                    <select x-model="tujuanDisposisiId" id="tujuan_jabatan_disposisi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Pilih Tujuan</option>
                        @foreach($tujuanJabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="mt-5 sm:mt-6">
                <button @click="$wire.disposeSurat(); open = false;" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                    Submit Disposisi
                </button>
                <button @click="open = false" type="button" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>