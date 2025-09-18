@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Buat Surat Baru</h1>
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="bg-white shadow-lg rounded-lg p-6">
            <form action="{{ route('staff.surat.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="perihal" class="block text-sm font-medium text-gray-700 mb-2">
                        Perihal
                    </label>
                    <input type="text" 
                           id="perihal" 
                           name="perihal" 
                           value="{{ old('perihal') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('perihal') border-red-500 @enderror"
                           required>
                    @error('perihal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="jenis_surat_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Surat
                    </label>
                    <select id="jenis_surat_id" 
                            name="jenis_surat_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenis_surat_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Jenis Surat</option>
                        @foreach($jenisSurat as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_surat_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_surat_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="tujuan_jabatan_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tujuan Jabatan
                    </label>
                    <select id="tujuan_jabatan_id" 
                            name="tujuan_jabatan_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tujuan_jabatan_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Tujuan Jabatan</option>
                        @foreach($jabatan as $jab)
                            <option value="{{ $jab->id }}" {{ old('tujuan_jabatan_id') == $jab->id ? 'selected' : '' }}>
                                {{ $jab->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('tujuan_jabatan_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="isi_surat" class="block text-sm font-medium text-gray-700 mb-2">
                        Isi Surat
                    </label>
                    <textarea id="isi_surat" 
                              name="isi_surat" 
                              rows="10"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('isi_surat') border-red-500 @enderror"
                              required>{{ old('isi_surat') }}</textarea>
                    @error('isi_surat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Simpan Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection