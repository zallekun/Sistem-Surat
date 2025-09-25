{{-- Shared partial for displaying additional data --}}
{{-- Used by both staff prodi and staff fakultas views --}}

@if($additionalData && is_array($additionalData))
    
    {{-- Universal Academic Data --}}
    @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']) || isset($additionalData['dosen_wali']))
        <div class="bg-indigo-50 p-4 rounded-lg mb-4">
            <h4 class="font-medium text-indigo-800 mb-3">
                <i class="fas fa-graduation-cap mr-2"></i>
                Data Akademik
            </h4>
            <div class="grid md:grid-cols-3 gap-4 text-sm">
                @if(isset($additionalData['semester']))
                    <div><strong>Semester:</strong> {{ $additionalData['semester'] }}</div>
                @endif
                @if(isset($additionalData['tahun_akademik']))
                    <div><strong>Tahun Akademik:</strong> {{ $additionalData['tahun_akademik'] }}</div>
                @endif
                @if(isset($additionalData['dosen_wali']) && is_array($additionalData['dosen_wali']))
                    <div>
                        <strong>Dosen Wali:</strong> {{ $additionalData['dosen_wali']['nama'] ?? '-' }}
                        @if(isset($additionalData['dosen_wali']['nid']))
                            <br><span class="text-xs">NID: {{ $additionalData['dosen_wali']['nid'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Surat Mahasiswa Aktif (MA) --}}
    @if($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']))
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h4 class="font-medium text-yellow-800 mb-3">
                <i class="fas fa-users mr-2"></i>
                Biodata Orang Tua
            </h4>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                @foreach(['nama', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 'nip', 'jabatan', 'pangkat_golongan', 'pendidikan_terakhir'] as $field)
                    @if(isset($additionalData['orang_tua'][$field]) && !empty($additionalData['orang_tua'][$field]))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong> 
                            {{ $additionalData['orang_tua'][$field] }}
                        </div>
                    @endif
                @endforeach
            </div>
            
            @foreach(['alamat_instansi', 'alamat_rumah'] as $alamat)
                @if(isset($additionalData['orang_tua'][$alamat]) && !empty($additionalData['orang_tua'][$alamat]))
                    <div class="mt-3">
                        <strong>{{ ucwords(str_replace('_', ' ', $alamat)) }}:</strong>
                        <div class="mt-1 p-2 bg-white rounded border text-sm">
                            {{ $additionalData['orang_tua'][$alamat] }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        
    {{-- Surat Kerja Praktek (KP) --}}
    @elseif($jenisSurat && isset($jenisSurat->kode_surat) && $jenisSurat->kode_surat === 'KP' && isset($additionalData['kerja_praktek']))
        <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-medium text-blue-800 mb-3">
                <i class="fas fa-briefcase mr-2"></i>
                Data Kerja Praktek
            </h4>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
                @foreach(['nama_perusahaan', 'bidang_kerja', 'periode_mulai', 'periode_selesai'] as $field)
                    @if(isset($additionalData['kerja_praktek'][$field]))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                            {{ $additionalData['kerja_praktek'][$field] }}
                        </div>
                    @endif
                @endforeach
            </div>
            
            @if(isset($additionalData['kerja_praktek']['alamat_perusahaan']))
                <div class="mt-3">
                    <strong>Alamat Perusahaan:</strong>
                    <div class="mt-1 p-2 bg-white rounded border text-sm">
                        {{ $additionalData['kerja_praktek']['alamat_perusahaan'] }}
                    </div>
                </div>
            @endif
        </div>
        
    {{-- Generic display for other types --}}
    @else
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-800 mb-3">
                <i class="fas fa-list mr-2"></i>
                Data Lainnya
            </h4>
            <div class="space-y-2 text-sm">
                @foreach($additionalData as $key => $value)
                    @if(!in_array($key, ['semester', 'tahun_akademik', 'dosen_wali']) && !is_array($value))
                        <div>
                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                            {{ $value }}
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    
@else
    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
        <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
        <p class="text-gray-600 text-sm">
            Tidak ada data tambahan untuk pengajuan ini.
        </p>
    </div>
@endif