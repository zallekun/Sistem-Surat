{{-- resources/views/admin/master/dosen-wali/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Master Data Dosen Wali')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <a href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600">Master Data</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Dosen Wali</span>
    </div>
</li>
@endsection

@section('content')
<div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Master Data Dosen Wali</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola data dosen wali</p>
                    </div>
                    <a href="{{ route('admin.dosen-wali.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Dosen Wali
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
                               placeholder="Cari nama atau NID dosen wali..."
                               class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-64">
                        <select name="prodi_id" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Prodi</option>
                            @foreach($prodis as $p)
                                <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.dosen-wali.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </form>
            </div>

            <!-- Table -->
            @if($dosenWalis->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Dosen</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">NID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prodi</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($dosenWalis as $index => $dosenWali)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $dosenWalis->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $dosenWali->nama }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono text-xs">{{ $dosenWali->nid }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $dosenWali->prodi->nama_prodi ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        @if($dosenWali->is_active)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.dosen-wali.edit', $dosenWali->id) }}" 
                                               class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs font-medium">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.dosen-wali.destroy', $dosenWali->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Anda yakin ingin menghapus Dosen Wali ini?')">
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
                    {{ $dosenWalis->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-user-tie fa-3x text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tidak ada data Dosen Wali</p>
                </div>
            @endif
</div>
@endsection
