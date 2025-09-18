@extends('layouts.app')

@section('title', 'Edit Surat')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Edit Surat: {{ $surat->nomor_surat }}</h1>

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

                <form action="{{ route('staff.surat.update', $surat->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nomor Surat -->
                        <div>
                            <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                            <input type="text" name="nomor_surat" id="nomor_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-gray-100" value="{{ old('nomor_surat', $surat->nomor_surat) }}" readonly>
                            <p class="text-sm text-gray-500 mt-1">Nomor surat akan otomatis direvisi jika ada perubahan yang memerlukan revisi.</p>
                        </div>

                        <!-- Perihal -->
                        <div>
                            <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                            <input type="text" name="perihal" id="perihal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('perihal', $surat->perihal) }}" required>
                            @error('perihal')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tujuan Jabatan -->
                        <div>
                            <label for="tujuan_jabatan_id" class="block text-sm font-medium text-gray-700">Tujuan Jabatan</label>
                            <select name="tujuan_jabatan_id" id="tujuan_jabatan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                <option value="">Pilih Tujuan Jabatan</option>
                                @foreach($tujuanJabatanOptions as $jabatan)
                                    <option value="{{ $jabatan->id }}" {{ (old('tujuan_jabatan_id') == $jabatan->id || $surat->tujuan_jabatan_id == $jabatan->id) ? 'selected' : '' }}>
                                        {{ $jabatan->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tujuan_jabatan_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lampiran (Opsional) -->
                        <div>
                            <label for="lampiran" class="block text-sm font-medium text-gray-700">Lampiran (Opsional)</label>
                            <input type="text" name="lampiran" id="lampiran" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('lampiran', $surat->lampiran) }}">
                            @error('lampiran')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fakultas -->
                        <div>
                            <label for="fakultas_id" class="block text-sm font-medium text-gray-700">Fakultas</label>
                            <select name="fakultas_id" id="fakultas_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $selectedFakultasId ? 'disabled' : '' }} required>
                                @foreach($fakultas as $fakultasItem)
                                    <option value="{{ $fakultasItem->id }}" {{ (old('fakultas_id') == $fakultasItem->id || $selectedFakultasId == $fakultasItem->id || $surat->createdBy?->prodi?->fakultas_id == $fakultasItem->id) ? 'selected' : '' }}>
                                        {{ $fakultasItem->nama_fakultas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fakultas_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prodi -->
                        <div>
                            <label for="prodi_id" class="block text-sm font-medium text-gray-700">Prodi</label>
                            <select name="prodi_id" id="prodi_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $selectedProdiId ? 'disabled' : '' }} required>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ (old('prodi_id') == $prodi->id || $selectedProdiId == $prodi->id || $surat->createdBy?->prodi_id == $prodi->id) ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prodi_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Surat -->
                        <div>
                            <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" id="tanggal_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('tanggal_surat', $surat->tanggal_surat->format('Y-m-d')) }}" required>
                            @error('tanggal_surat')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sifat Surat -->
                        <div>
                            <label for="sifat_surat" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                            <select name="sifat_surat" id="sifat_surat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @foreach($sifatSuratOptions as $sifat)
                                    <option value="{{ $sifat }}" {{ (old('sifat_surat') == $sifat || $surat->sifat_surat == $sifat) ? 'selected' : '' }}>{{ $sifat }}</option>
                                @endforeach
                            </select>
                            @error('sifat_surat')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File PDF -->
                        <div class="md:col-span-2">
                            <label for="file_surat" class="block text-sm font-medium text-gray-700">Upload File PDF (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="file" name="file_surat" id="file_surat" class="mt-1 block w-full text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('file_surat')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            @if($surat->file_surat)
                                <p class="text-sm text-gray-500 mt-2">File saat ini: <a href="{{ Storage::url($surat->file_surat) }}" target="_blank" class="text-blue-600 hover:underline">{{ basename($surat->file_surat) }}</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Update Surat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
