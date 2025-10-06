{{-- resources/views/admin/master/dosen-wali/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Dosen Wali')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.dosen-wali.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-blue-500 to-blue-600">
                <h2 class="text-xl font-bold text-white">Edit Dosen Wali</h2>
            </div>

            <form action="{{ route('admin.dosen-wali.update', $dosenWali->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Dosen <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama" 
                               value="{{ old('nama', $dosenWali->nama) }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror"
                               placeholder="Contoh: Dr. John Doe, M.Kom."
                               required>
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NID (Nomor Induk Dosen) <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nid" 
                               value="{{ old('nid', $dosenWali->nid) }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('nid') border-red-500 @enderror"
                               placeholder="Contoh: 0412345678"
                               required>
                        @error('nid')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prodi <span class="text-red-500">*</span></label>
                        <select name="prodi_id" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('prodi_id') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Prodi</option>
                            @foreach($prodis as $p)
                                <option value="{{ $p->id }}" {{ old('prodi_id', $dosenWali->prodi_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                        @error('prodi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1" 
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ old('is_active', $dosenWali->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif</label>
                        </div>
                         @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.dosen-wali.index') }}" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium text-center">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
