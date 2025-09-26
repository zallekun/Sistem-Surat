@extends('layouts.app')

@section('title', 'Detail Pengajuan - Staff Fakultas')

@section('content')
@php
    // Parse pengajuan data properly
    $pengajuan = null;
    $jenisSurat = null;
    $additionalData = null;
    
    if (isset($surat) && is_object($surat)) {
        if (isset($surat->original_pengajuan)) {
            $pengajuan = $surat->original_pengajuan;
        }
    }
    
    if ($pengajuan) {
        $jenisSurat = $pengajuan->jenisSurat ?? null;
        
        // Parse additional data
        if ($pengajuan->additional_data) {
            if (is_string($pengajuan->additional_data)) {
                $additionalData = json_decode($pengajuan->additional_data, true);
            } elseif (is_array($pengajuan->additional_data)) {
                $additionalData = $pengajuan->additional_data;
            }
        }
    }
@endphp

<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    Detail Pengajuan Surat
                    <span class="text-sm font-normal text-blue-600 ml-2">(Staff Fakultas)</span>
                </h2>
                <div class="flex items-center gap-4">
                    @if($pengajuan)
                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                            @if($pengajuan->status == 'completed') bg-green-100 text-green-800
                            @elseif($pengajuan->status == 'sedang_ditandatangani') bg-orange-100 text-orange-800
                            @elseif($pengajuan->status == 'approved_prodi') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucwords(str_replace('_', ' ', $pengajuan->status)) }}
                        </span>
                    @endif
                    <a href="{{ route('fakultas.surat.index') }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        @if($pengajuan)
        <div class="p-6">
            <!-- Basic Info Grid -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <!-- Informasi Pengajuan -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-800 mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Pengajuan
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>Token:</strong> <span class="font-mono bg-white px-2 py-1 rounded">{{ $pengajuan->tracking_token }}</span></div>
                        <div><strong>Tanggal:</strong> {{ $pengajuan->created_at->format('d/m/Y H:i') }}</div>
                        <div><strong>Jenis Surat:</strong> {{ $jenisSurat->nama_jenis ?? 'N/A' }} ({{ $jenisSurat->kode_surat ?? '' }})</div>
                    </div>
                </div>

                <!-- Data Mahasiswa -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="font-semibold text-green-800 mb-3">
                        <i class="fas fa-user-graduate mr-2"></i>Data Mahasiswa
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><strong>NIM:</strong> {{ $pengajuan->nim }}</div>
                        <div><strong>Nama:</strong> {{ $pengajuan->nama_mahasiswa }}</div>
                        <div><strong>Prodi:</strong> {{ $pengajuan->prodi->nama_prodi ?? 'N/A' }}</div>
                        <div><strong>Email:</strong> {{ $pengajuan->email }}</div>
                    </div>
                </div>
            </div>

            <!-- Keperluan -->
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-2">
                    <i class="fas fa-clipboard-list mr-2"></i>Keperluan
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    {{ $pengajuan->keperluan }}
                </div>
            </div>

            <!-- Additional Data Section -->
            @if($additionalData)
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">
                        <i class="fas fa-list-alt mr-2"></i>Data Tambahan
                    </h3>

                    {{-- SURAT MAHASISWA AKTIF (MA) --}}
                    @if($jenisSurat && $jenisSurat->kode_surat === 'MA')
                        @if(isset($additionalData['orang_tua']))
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h4 class="font-medium text-yellow-800 mb-3">Data Orang Tua</h4>
                                <div class="grid md:grid-cols-2 gap-3 text-sm">
                                    @foreach(['nama' => 'Nama', 'tempat_lahir' => 'Tempat Lahir', 'tanggal_lahir' => 'Tanggal Lahir', 
                                             'pekerjaan' => 'Pekerjaan', 'alamat_rumah' => 'Alamat'] as $key => $label)
                                        @if(isset($additionalData['orang_tua'][$key]))
                                            <div><strong>{{ $label }}:</strong> {{ $additionalData['orang_tua'][$key] }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- SURAT KERJA PRAKTEK (KP) --}}
                    @if($jenisSurat && $jenisSurat->kode_surat === 'KP')
                        @if(isset($additionalData['kerja_praktek']))
                            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                <h4 class="font-medium text-blue-800 mb-3">Data Perusahaan</h4>
                                <div class="grid md:grid-cols-2 gap-3 text-sm">
                                    <div><strong>Nama:</strong> {{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? '-' }}</div>
                                    <div><strong>Periode:</strong> {{ $additionalData['kerja_praktek']['periode_mulai'] ?? '' }} - {{ $additionalData['kerja_praktek']['periode_selesai'] ?? '' }}</div>
                                    <div><strong>Alamat:</strong> {{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? '-' }}</div>
                                    <div><strong>Jumlah Mahasiswa:</strong> {{ $additionalData['kerja_praktek']['jumlah_mahasiswa'] ?? 1 }}</div>
                                </div>
                            </div>
                            
                            @if(isset($additionalData['kerja_praktek']['mahasiswa_kp']))
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-3">Daftar Mahasiswa KP</h4>
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-2">No</th>
                                                <th class="text-left py-2">Nama</th>
                                                <th class="text-left py-2">NIM</th>
                                                <th class="text-left py-2">Prodi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($additionalData['kerja_praktek']['mahasiswa_kp'] as $index => $mhs)
                                                <tr class="border-b">
                                                    <td class="py-2">{{ $index + 1 }}</td>
                                                    <td class="py-2">{{ $mhs['nama'] ?? '-' }}</td>
                                                    <td class="py-2">{{ $mhs['nim'] ?? '-' }}</td>
                                                    <td class="py-2">{{ $mhs['prodi'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    @endif

                    {{-- Add other letter types (TA, SKM) as needed --}}
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="border-t pt-6 flex justify-end gap-3">
                @if(in_array($pengajuan->status, ['approved_prodi', 'processed']))
                    <button onclick="previewSurat({{ $pengajuan->id }})" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        <i class="fas fa-eye mr-2"></i>Preview & Edit
                    </button>
                    <button onclick="printSurat({{ $pengajuan->id }})" 
                            class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition">
                        <i class="fas fa-print mr-2"></i>Print untuk TTD
                    </button>
                @elseif($pengajuan->status === 'sedang_ditandatangani')
                    <button onclick="previewSurat({{ $pengajuan->id }})" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        <i class="fas fa-eye mr-2"></i>Lanjutkan Proses TTD
                    </button>
                    <button onclick="showUploadModal()" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-upload mr-2"></i>Upload Link Surat Signed
                    </button>
                @elseif($pengajuan->status === 'completed')
                    <a href="{{ $pengajuan->download_url ?? '#' }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                        <i class="fas fa-download mr-2"></i>Download Surat
                    </a>
                @endif
            </div>
        </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-exclamation-triangle text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600">Data Tidak Ditemukan</h3>
                <p class="text-gray-500">Pengajuan yang Anda cari tidak tersedia.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Upload Link -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Upload Link Surat Tertandatangan</h3>
        <input type="url" id="signedUrl" placeholder="https://drive.google.com/..." 
               class="w-full px-3 py-2 border rounded mb-3">
        <textarea id="uploadNotes" placeholder="Catatan (opsional)" 
                  class="w-full px-3 py-2 border rounded mb-4" rows="3"></textarea>
        <div class="flex justify-end gap-2">
            <button onclick="closeUploadModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
            <button onclick="submitSignedLink()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Upload</button>
        </div>
    </div>
</div>

<script>
function previewSurat(id) {
    window.open(`/fakultas/surat/fsi/preview/${id}`, '_blank');
}

function printSurat(id) {
    if (confirm('Print surat untuk proses tanda tangan?')) {
        window.location.href = `/fakultas/surat/fsi/print/${id}`;
    }
}

function showUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
}

function submitSignedLink() {
    const url = document.getElementById('signedUrl').value;
    const notes = document.getElementById('uploadNotes').value;
    
    if (!url) {
        alert('Link harus diisi!');
        return;
    }
    
    fetch(`/fakultas/surat/fsi/upload-signed/{{ $pengajuan->id ?? 0 }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ signed_url: url, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>
@endsection