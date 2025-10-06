{{-- resources/views/admin/master/fakultas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Master Data Fakultas')

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
        <span class="text-sm font-medium text-gray-500">Fakultas</span>
    </div>
</li>
@endsection

@section('content')
<div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Master Data Fakultas</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola data fakultas</p>
                    </div>
                    <a href="{{ route('admin.fakultas.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah Fakultas
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
                               placeholder="Cari nama atau kode fakultas..."
                               class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                    <a href="{{ route('admin.fakultas.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </a>
                </form>
            </div>

            <!-- Table -->
            @if($fakultas->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-16">No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Fakultas</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Jumlah Prodi</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($fakultas as $index => $f)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $fakultas->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full font-mono text-xs font-semibold">{{ $f->kode_fakultas }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $f->nama_fakultas }}</td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                            {{ $f->prodi()->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.fakultas.edit', $f->id) }}" 
                                               class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs font-medium">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.fakultas.destroy', $f->id) }}"
                                                  method="POST"
                                                  onsubmit="return handleDelete(event, 'Yakin hapus fakultas ini?')">
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
                    {{ $fakultas->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-university fa-3x text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tidak ada data fakultas</p>
                </div>
            @endif
</div>
@endsection