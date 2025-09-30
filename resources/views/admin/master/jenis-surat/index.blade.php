{{-- resources/views/admin/master/jenis-surat/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Jenis Surat')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.jenis-surat.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-500 to-purple-600">
                <h2 class="text-xl font-bold text-white">Tambah Jenis Surat</h2>
            </div>

            <form action="{{ route('admin.jenis-surat.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Surat <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="kode_surat" 
                               value="{{ old('kode_surat') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 @error('kode_surat') border-red-500 @enderror"
                               placeholder="Contoh: KP, TA, SK"
                               maxlength="10"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Kode singkat untuk identifikasi surat (maksimal 10 karakter)</p>
                        @error('kode_surat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jenis <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama_jenis" 
                               value="{{ old('nama_jenis') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 @error('nama_jenis') border-red-500 @enderror"
                               placeholder="Contoh: Surat Pengantar Kerja Praktek"
                               required>
                        @error('nama_jenis')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" 
                                  rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 @error('deskripsi') border-red-500 @enderror"
                                  placeholder="Deskripsi penggunaan jenis surat ini (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <a href="{{ route('admin.jenis-surat.index') }}" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium text-center">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection