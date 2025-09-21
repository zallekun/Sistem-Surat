@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Detail Pengajuan Surat</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        {{ $pengajuan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($pengajuan->status === 'approved' ? 'bg-green-100 text-green-800' : 
                            'bg-red-100 text-red-800') }}">
                        {{ ucfirst($pengajuan->status) }}
                    </span>
                </div>
                
                <!-- Basic Info -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi Pengajuan
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Token Tracking:</strong> 
                                <span class="font-mono bg-blue-100 px-2 py-1 rounded">{{ $pengajuan->tracking_token }}</span>
                            </div>
                            <div><strong>Tanggal Pengajuan:</strong> {{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                            <div><strong>Jenis Surat:</strong> 
                                <span class="font-medium">{{ $pengajuan->jenisSurat->nama_jenis ?? 'N/A' }}</span>
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded ml-1">{{ $pengajuan->jenisSurat->kode_surat ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-800 mb-3">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Data Mahasiswa
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                            <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                            <div><strong>Program Studi:</strong> {{ $pengajuan->prodi->nama_prodi ?? 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $pengajuan->email }}</div>
                            <div><strong>Phone:</strong> {{ $pengajuan->phone }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Keperluan -->
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Keperluan Surat
                    </h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700">{{ $pengajuan->keperluan }}</p>
                    </div>
                </div>
                
                <!-- Additional Data berdasarkan Jenis Surat -->
                @if($pengajuan->additional_data)
                    @php
                        $additionalData = json_decode($pengajuan->additional_data, true);
                        $jenisSurat = $pengajuan->jenisSurat;
                    @endphp
                    
                    @if($additionalData && is_array($additionalData))
                        <div class="mb-8">
                            <h3 class="font-semibold text-gray-800 mb-3">
                                <i class="fas fa-list-alt mr-2"></i>
                                Data Tambahan - {{ $jenisSurat->nama_jenis ?? 'Unknown' }}
                            </h3>
                            
                            {{-- SURAT MAHASISWA AKTIF --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'MA')
                                <!-- Data Akademik -->
                                @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']))
                                    <div class="bg-green-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-green-800 mb-3">
                                            <i class="fas fa-graduation-cap mr-2"></i>
                                            Data Akademik
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            @if($additionalData['semester'] ?? false)
                                                <div><strong>Semester:</strong> {{ $additionalData['semester'] }}</div>
                                            @endif
                                            @if($additionalData['tahun_akademik'] ?? false)
                                                <div><strong>Tahun Akademik:</strong> {{ $additionalData['tahun_akademik'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Biodata Orang Tua -->
                                @if(isset($additionalData['orang_tua']))
                                    <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-yellow-800 mb-3">
                                            <i class="fas fa-users mr-2"></i>
                                            Biodata Orang Tua
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Nama:</strong> {{ $additionalData['orang_tua']['nama'] ?? '-' }}</div>
                                            <div><strong>Tempat Lahir:</strong> {{ $additionalData['orang_tua']['tempat_lahir'] ?? '-' }}</div>
                                            <div><strong>Tanggal Lahir:</strong> {{ $additionalData['orang_tua']['tanggal_lahir'] ?? '-' }}</div>
                                            <div><strong>Pekerjaan:</strong> {{ $additionalData['orang_tua']['pekerjaan'] ?? '-' }}</div>
                                            <div><strong>NIP:</strong> {{ $additionalData['orang_tua']['nip'] ?? '-' }}</div>
                                            <div><strong>Jabatan:</strong> {{ $additionalData['orang_tua']['jabatan'] ?? '-' }}</div>
                                            <div><strong>Pangkat/Golongan:</strong> {{ $additionalData['orang_tua']['pangkat_golongan'] ?? '-' }}</div>
                                            <div><strong>Pendidikan Terakhir:</strong> {{ $additionalData['orang_tua']['pendidikan_terakhir'] ?? '-' }}</div>
                                        </div>
                                        
                                        @if($additionalData['orang_tua']['alamat_instansi'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Instansi:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['orang_tua']['alamat_instansi'] }}</p>
                                            </div>
                                        @endif
                                        
                                        @if($additionalData['orang_tua']['alamat_rumah'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Rumah:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['orang_tua']['alamat_rumah'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT KERJA PRAKTEK --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'KP')
                                @if(isset($additionalData['kerja_praktek']))
                                    <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-blue-800 mb-3">
                                            <i class="fas fa-briefcase mr-2"></i>
                                            Data Kerja Praktek
                                        </h4>
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Nama Perusahaan:</strong> {{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? '-' }}</div>
                                            <div><strong>Bidang Kerja:</strong> {{ $additionalData['kerja_praktek']['bidang_kerja'] ?? '-' }}</div>
                                            <div><strong>Periode Mulai:</strong> {{ $additionalData['kerja_praktek']['periode_mulai'] ?? '-' }}</div>
                                            <div><strong>Periode Selesai:</strong> {{ $additionalData['kerja_praktek']['periode_selesai'] ?? '-' }}</div>
                                        </div>
                                        
                                        @if($additionalData['kerja_praktek']['alamat_perusahaan'] ?? false)
                                            <div class="mt-3">
                                                <strong>Alamat Perusahaan:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT TUGAS AKHIR --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'TA')
                                @if(isset($additionalData['tugas_akhir']))
                                    <div class="bg-purple-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-purple-800 mb-3">
                                            <i class="fas fa-book mr-2"></i>
                                            Data Tugas Akhir
                                        </h4>
                                        
                                        @if($additionalData['tugas_akhir']['judul_ta'] ?? false)
                                            <div class="mb-3">
                                                <strong>Judul Tugas Akhir:</strong>
                                                <p class="mt-1 p-2 bg-white rounded border">{{ $additionalData['tugas_akhir']['judul_ta'] }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="grid md:grid-cols-2 gap-4 text-sm">
                                            <div><strong>Dosen Pembimbing 1:</strong> {{ $additionalData['tugas_akhir']['dosen_pembimbing1'] ?? '-' }}</div>
                                            <div><strong>Dosen Pembimbing 2:</strong> {{ $additionalData['tugas_akhir']['dosen_pembimbing2'] ?? '-' }}</div>
                                            <div><strong>Lokasi Penelitian:</strong> {{ $additionalData['tugas_akhir']['lokasi_penelitian'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- SURAT KETERANGAN --}}
                            @if(($jenisSurat->kode_surat ?? '') === 'SKM')
                                @if(isset($additionalData['keterangan_khusus']))
                                    <div class="bg-orange-50 p-4 rounded-lg mb-4">
                                        <h4 class="font-medium text-orange-800 mb-3">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            Keterangan Khusus
                                        </h4>
                                        <div class="p-2 bg-white rounded border">
                                            {{ $additionalData['keterangan_khusus'] }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                @else
                    <div class="mb-8">
                        <div class="bg-gray-100 p-4 rounded-lg text-center">
                            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-600">Tidak ada data tambahan untuk pengajuan ini.</p>
                        </div>
                    </div>
                @endif
                
                <!-- Actions -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a href="{{ route('staff.pengajuan.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    
                    <div class="flex space-x-3">
                        @if($pengajuan->status === 'pending')
                            <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Status
                            </button>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Approve
                            </button>
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Generate Surat
                            </button>
                        @elseif($pengajuan->status === 'approved')
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Download Surat
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection