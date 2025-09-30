{{-- resources/views/admin/master/prodi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Master Data Prodi')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto">
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Master Data Program Studi</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola data program studi</p>
                    </div>
                    <a href="{{ route('admin.prodi.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Prodi
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari nama atau kode prodi..."
                               class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-64">
                        <select name="fakultas_id" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Fakultas</option>
                            @foreach($fakultas as $f)
                                <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>
                                    {{ $f->nama_fakultas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.prodi.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </form>
            </div>

            <!-- Table -->
            @if($prodis->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Prodi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fakultas</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Jumlah User</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($prodis as $index => $prodi)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $prodis->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono text-xs">{{ $prodi->kode_prodi }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $prodi->nama_prodi }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $prodi->fakultas->nama_fakultas ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">{{ $prodi->users()->count() }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.prodi.edit', $prodi->id) }}" 
                                               class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs font-medium">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.prodi.destroy', $prodi->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Yakin hapus prodi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs font-medium">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t">
                    {{ $prodis->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-building fa-3x text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tidak ada data prodi</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection