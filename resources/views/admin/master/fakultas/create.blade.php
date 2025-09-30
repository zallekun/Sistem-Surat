{{-- resources/views/admin/master/fakultas/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Fakultas')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.fakultas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-500 to-indigo-600">
                <h2 class="text-xl font-bold text-white">Tambah Fakultas</h2>
            </div>

            <form action="{{ route('admin.fakultas.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Fakultas <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="kode_fakultas" 
                               value="{{ old('kode_fakultas') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 @error('kode_fakultas') border-red-500 @enderror"
                               placeholder="FSI"
                               maxlength="10"
                               required>
                        @error('kode_fakultas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Fakultas <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama_fakultas" 
                               value="{{ old('nama_fakultas') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 @error('nama_fakultas') border-red-500 @enderror"
                               placeholder="Fakultas Sains dan Informatika"
                               required>
                        @error('nama_fakultas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                    <a href="{{ route('admin.fakultas.index') }}" class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium text-center">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection