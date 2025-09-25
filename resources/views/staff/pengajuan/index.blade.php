@extends('layouts.app')

@section('title', 'Daftar Pengajuan Surat')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Daftar Pengajuan Surat Mahasiswa</h2>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                        Total: {{ $pengajuans->total() }} pengajuan
                    </span>
                </div>

                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-yellow-600">Perlu Review</p>
                                <p class="text-2xl font-bold text-yellow-800">
                                    {{ $pengajuans->where('status', 'pending')->count() }}
                                </p>
                            </div>
                            <i class="fas fa-clock text-yellow-400 text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-blue-600">Diproses</p>
                                <p class="text-2xl font-bold text-blue-800">
                                    {{ $pengajuans->whereIn('status', ['processed', 'approved_prodi'])->count() }}
                                </p>
                            </div>
                            <i class="fas fa-check text-blue-400 text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-green-600">Selesai</p>
                                <p class="text-2xl font-bold text-green-800">
                                    {{ $pengajuans->whereIn('status', ['approved_fakultas', 'completed'])->count() }}
                                </p>
                            </div>
                            <i class="fas fa-check-double text-green-400 text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <p class="text-sm text-red-600">Ditolak</p>
                                <p class="text-2xl font-bold text-red-800">
                                    {{ $pengajuans->whereIn('status', ['rejected', 'rejected_prodi'])->count() }}
                                </p>
                            </div>
                            <i class="fas fa-times text-red-400 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                @if($pengajuans->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        NIM
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Mahasiswa
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jenis Surat
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pengajuans as $pengajuan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pengajuan->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pengajuan->nim }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pengajuan->nama_mahasiswa }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $pengajuan->jenisSurat->nama_jenis ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'processed' => 'bg-blue-100 text-blue-800',
                                                'approved_prodi' => 'bg-blue-100 text-blue-800',
                                                'rejected_prodi' => 'bg-red-100 text-red-800',
                                                'approved_fakultas' => 'bg-green-100 text-green-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Menunggu Review',
                                                'processed' => 'Disetujui Prodi',
                                                'approved_prodi' => 'Disetujui Prodi',
                                                'rejected_prodi' => 'Ditolak Prodi',
                                                'approved_fakultas' => 'Disetujui Fakultas',
                                                'completed' => 'Selesai',
                                                'rejected' => 'Ditolak',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$pengajuan->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$pengajuan->status] ?? ucfirst($pengajuan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
    @if($pengajuan->status === 'pending')
        <a href="{{ route('staff.pengajuan.show', $pengajuan->id) }}" 
           style="display: inline-flex; align-items: center; padding: 6px 16px; background-color: #f59e0b; color: white; font-size: 0.875rem; font-weight: 500; border-radius: 6px; text-decoration: none; transition: all 0.2s;"
           onmouseover="this.style.backgroundColor='#d97706'" 
           onmouseout="this.style.backgroundColor='#f59e0b'">
            <svg style="width: 16px; height: 16px; margin-right: 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Review
        </a>
    @else
        <a href="{{ route('staff.pengajuan.show', $pengajuan->id) }}" 
           style="display: inline-flex; align-items: center; padding: 6px 16px; background-color: #3b82f6; color: white; font-size: 0.875rem; font-weight: 500; border-radius: 6px; text-decoration: none; transition: all 0.2s;"
           onmouseover="this.style.backgroundColor='#2563eb'" 
           onmouseout="this.style.backgroundColor='#3b82f6'">
            <svg style="width: 16px; height: 16px; margin-right: 6px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Detail
        </a>
    @endif
</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $pengajuans->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Belum ada pengajuan surat</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection