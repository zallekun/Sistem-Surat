@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Detail Surat</h1>
            <a href="{{ route('dashboard') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Surat</label>
                    <p class="text-gray-900">{{ $surat->nomor_surat ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        {{ ($surat->currentStatus->kode_status ?? '') === 'review_kaprodi' ? 'bg-yellow-100 text-yellow-800' : 
                           (($surat->currentStatus->kode_status ?? '') === 'disetujui_kaprodi' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                        {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                    </span>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Surat</label>
                    <p class="text-gray-900">{{ $surat->jenisSurat->nama_jenis ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Jabatan</label>
                    <p class="text-gray-900">{{ $surat->tujuanJabatan->nama_jabatan ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dibuat Oleh</label>
                    <p class="text-gray-900">{{ $surat->createdBy->nama ?? '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dibuat</label>
                    <p class="text-gray-900">{{ $surat->created_at ? $surat->created_at->format('d/m/Y H:i') : '-' }}</p>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                <p class="text-gray-900 bg-gray-50 p-4 rounded">{{ $surat->perihal ?? '-' }}</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Isi Surat</label>
                <div class="text-gray-900 bg-gray-50 p-4 rounded whitespace-pre-wrap">{{ $surat->isi_surat ?? '-' }}</div>
            </div>
            
            @if(Auth::user()->hasRole('staff_prodi') && 
                $surat->created_by === Auth::user()->id && 
                in_array($surat->currentStatus->kode_status ?? '', ['draft', 'ditolak_umum', 'ditolak_kaprodi']))
                <div class="mt-6">
                    <a href="{{ route('surat.edit', $surat->id) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Surat
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection