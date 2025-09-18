<div>
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

    <form wire:submit.prevent="saveSurat" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nomor Surat -->
            <div>
                <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat (Otomatis)</label>
                <input type="text" wire:model="nomor_surat" id="nomor_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-gray-100" readonly>
                <p class="text-sm text-gray-500 mt-1">Nomor surat akan dibuat secara otomatis berdasarkan Fakultas yang dipilih.</p>
            </div>

            <!-- Perihal -->
            <div>
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" wire:model="perihal" id="perihal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                @error('perihal')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tujuan Jabatan -->
            <div>
                <label for="tujuan_jabatan_id" class="block text-sm font-medium text-gray-700">Tujuan Jabatan</label>
                <select wire:model="tujuan_jabatan_id" id="tujuan_jabatan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <option value="">Pilih Tujuan Jabatan</option>
                    @foreach($tujuanJabatanOptions as $jabatan)
                        <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                    @endforeach
                </select>
                @error('tujuan_jabatan_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Lampiran (Opsional) -->
            <div>
                <label for="lampiran" class="block text-sm font-medium text-gray-700">Lampiran (Opsional)</label>
                <input type="text" wire:model="lampiran" id="lampiran" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('lampiran')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fakultas -->
            <div>
                <label for="fakultas_id" class="block text-sm font-medium text-gray-700">Fakultas</label>
                <input type="text" wire:model="fakultas_name" id="fakultas_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-gray-100" readonly>
                @error('fakultas_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(!$isStaffFakultas)
            <!-- Prodi -->
            <div>
                <label for="prodi_id" class="block text-sm font-medium text-gray-700">Prodi</label>
                <input type="text" wire:model="prodi_name" id="prodi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-gray-100" readonly>
                @error('prodi_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <!-- Tanggal Surat -->
            <div>
                <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                <input type="date" wire:model="tanggal_surat" id="tanggal_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                @error('tanggal_surat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sifat Surat -->
            <div>
                <label for="sifat_surat" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                <select wire:model="sifat_surat" id="sifat_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    <option value="">Pilih Sifat Surat</option>
                    @foreach($sifatSuratOptions as $sifat)
                        <option value="{{ $sifat }}">{{ $sifat }}</option>
                    @endforeach
                </select>
                @error('sifat_surat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- File PDF -->
            <div class="md:col-span-2">
                <label for="file_surat" class="block text-sm font-medium text-gray-700">Upload File PDF (Wajib)</label>
                <input type="file" wire:model="file_surat" id="file_surat" class="mt-1 block w-full text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                @error('file_surat')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                Buat Surat
            </button>
        </div>
    </form>
</div>