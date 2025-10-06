@extends('layouts.app')

@section('title', 'Detail Arsip Surat')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('fakultas.arsip.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Arsip
            </a>
        </div>

        <!-- Header Card -->
        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Detail Arsip Surat</h2>
                        <p class="text-sm text-white/90 mt-1">{{ $pengajuan->jenisSurat->nama_jenis ?? 'Surat' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            <i class="fas fa-check-circle mr-1.5"></i>
                            Selesai
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pengajuan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Pengajuan</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tracking Token</label>
                                <p class="text-sm font-mono text-blue-600 font-medium mt-1">{{ $pengajuan->tracking_token }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Durasi Proses</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    @if($pengajuan->completed_at)
                                        {{ $pengajuan->created_at->diffForHumans($pengajuan->completed_at, true) }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Pengajuan</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->created_at->format('d F Y, H:i') }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Selesai</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->completed_at?->format('d F Y, H:i') ?? '-' }}</p>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Data Mahasiswa</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">NIM</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->nim ?? $pengajuan->nim }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nama Lengkap</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->nama ?? $pengajuan->nama_mahasiswa }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Email</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->email ?? $pengajuan->email ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">No. Telepon</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->mahasiswa->phone ?? $pengajuan->phone ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Keperluan -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Keperluan</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500 uppercase">Jenis Surat</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->jenisSurat->nama_jenis ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase">Keperluan</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->keperluan ?? '-' }}</p>
                        </div>
                        
                        @if($additionalData && !empty($additionalData))
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="text-xs font-medium text-gray-500 uppercase mb-3 block">Data Tambahan</label>

                                {{-- First, show all nested data (orang tua, dosen wali, etc) --}}
                                @foreach($additionalData as $key => $value)
                                    @if(!in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa']) && is_array($value))
                                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                            <h4 class="text-xs font-semibold text-gray-700 uppercase mb-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($value as $subKey => $subValue)
                                                    <div>
                                                        <label class="text-xs font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $subKey)) }}</label>
                                                        @if(is_array($subValue))
                                                            @php
                                                                $displayItems = [];
                                                                foreach ($subValue as $item) {
                                                                    if (is_array($item) || is_object($item)) {
                                                                        $displayItems[] = json_encode($item);
                                                                    } else {
                                                                        $displayItems[] = $item ?? '-';
                                                                    }
                                                                }
                                                            @endphp
                                                            <p class="text-sm text-gray-900 mt-1">{{ implode(', ', $displayItems) }}</p>
                                                        @else
                                                            <p class="text-sm text-gray-900 mt-1">{{ $subValue ?? '-' }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                {{-- Then, show all simple data in a single grid --}}
                                @php
                                    $simpleData = array_filter($additionalData, function($value, $key) {
                                        return !in_array($key, ['_token', 'mahasiswa_id', 'nim', 'nama_mahasiswa']) && !is_array($value);
                                    }, ARRAY_FILTER_USE_BOTH);
                                @endphp

                                @if(!empty($simpleData))
                                    <div class="grid grid-cols-2 gap-4">
                                        @foreach($simpleData as $key => $value)
                                            <div>
                                                <label class="text-xs font-medium text-gray-500 uppercase">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                                <p class="text-sm text-gray-900 mt-1">{{ $value ?? '-' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Approval -->
                @if($pengajuan->approvalHistories && $pengajuan->approvalHistories->count() > 0)
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Timeline Approval</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($pengajuan->approvalHistories->sortBy('created_at') as $index => $history)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                    {{ in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'bg-green-500' : 
                                                       (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'bg-red-500' : 'bg-blue-500') }}">
                                                    <i class="fas {{ in_array($history->action, ['approved_prodi', 'approved_fakultas', 'completed']) ? 'fa-check' : 
                                                                    (in_array($history->action, ['rejected_prodi', 'rejected_fakultas']) ? 'fa-times' : 'fa-circle') }} 
                                                              text-white text-xs"></i>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm">
                                                        <span class="font-medium text-gray-900">{{ $history->action_label }}</span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-gray-500">
                                                        {{ $history->created_at->format('d F Y, H:i') }}
                                                        @if($history->performedBy)
                                                            • oleh {{ $history->performedBy->nama }}
                                                        @endif
                                                    </p>
                                                </div>
                                                @if($history->notes)
                                                    <div class="mt-2 text-sm text-gray-700">
                                                        <p class="italic">{{ $history->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Aksi</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($pengajuan->surat_pengantar_url)
                            <a href="{{ $pengajuan->surat_pengantar_url }}" 
                               target="_blank"
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                                <i class="fas fa-download mr-2"></i>
                                Download Surat
                            </a>
                        @endif
                        
                        <a href="{{ route('fakultas.arsip.index') }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>

                <!-- Info Status -->
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Status</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Selesai
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Diproses oleh</span>
                            <span class="text-sm font-medium text-gray-900">{{ $pengajuan->prodi->nama_prodi ?? '-' }}</span>
                        </div>
                        @if($pengajuan->completed_by_user)
                            <div class="flex items-center justify-between py-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Diselesaikan oleh</span>
                                <span class="text-sm font-medium text-gray-900">{{ $pengajuan->completed_by_user->nama ?? '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- File Info -->
                @if($pengajuan->surat_generated_id || $pengajuan->surat_pengantar_url)
                <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">File Surat</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($pengajuan->surat_pengantar_nomor)
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Nomor Surat</label>
                                <p class="text-sm font-mono text-gray-900 mt-1">{{ $pengajuan->surat_pengantar_nomor }}</p>
                            </div>
                        @endif
                        @if($pengajuan->surat_pengantar_generated_at)
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase">Tanggal Generate</label>
                                <p class="text-sm text-gray-900 mt-1">{{ $pengajuan->surat_pengantar_generated_at->format('d F Y, H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection