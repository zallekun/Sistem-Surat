    <!-- fakultas.surat.show -->
    @extends('layouts.app')

    @section('title', 'Detail Pengajuan - Staff Fakultas')

    @section('content')
    @php
        // Initialize variables
        $statusColors = [
            'pending' => 'background-color: #fef3c7; color: #92400e;',
            'processed' => 'background-color: #dbeafe; color: #1e40af;',
            'approved_prodi' => 'background-color: #d1fae5; color: #065f46;',
            'rejected' => 'background-color: #fee2e2; color: #991b1b;',
            'completed' => 'background-color: #e0e7ff; color: #4338ca;'
        ];
        
        // Determine data source and extract pengajuan
        $pengajuan = null;
        $jenisSurat = null;
        $status = 'unknown';
        $additionalData = null;
        
        if (isset($surat) && is_object($surat)) {
            if (isset($surat->original_pengajuan)) {
                // This is a pengajuan wrapped in surat object
                $pengajuan = $surat->original_pengajuan;
            } else {
                // This is a regular surat
                // For now, we'll skip this case as we focus on pengajuan
            }
        } elseif (isset($pengajuan)) {
            // Direct pengajuan object
            // Already set
        }
        
        // Extract related data if pengajuan exists
        if ($pengajuan) {
            $jenisSurat = $pengajuan->jenisSurat ?? null;
            $status = $pengajuan->status ?? 'unknown';
            
            // Parse additional data
            if (isset($pengajuan->additional_data) && !empty($pengajuan->additional_data)) {
                if (is_string($pengajuan->additional_data)) {
                    try {
                        $additionalData = json_decode($pengajuan->additional_data, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $additionalData = null;
                        }
                    } catch (\Exception $e) {
                        $additionalData = null;
                    }
                } elseif (is_array($pengajuan->additional_data)) {
                    $additionalData = $pengajuan->additional_data;
                } elseif (is_object($pengajuan->additional_data)) {
                    $additionalData = (array) $pengajuan->additional_data;
                }
            }
        }
        
        $statusStyle = $statusColors[$status] ?? 'background-color: #f3f4f6; color: #374151;';
    @endphp

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-sm rounded-lg">
            <!-- Header -->
            <div style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 20px; font-weight: 600; margin: 0; color: #374151;">
                        Detail Pengajuan Surat
                        <span style="font-size: 14px; font-weight: normal; color: #3b82f6;">(Staff Fakultas)</span>
                    </h2>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <span style="padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; {{ $statusStyle }}">
                            {{ ucwords(str_replace('_', ' ', $status)) }}
                        </span>
                        <a href="{{ route('fakultas.surat.index') }}" 
                        style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 6px; font-size: 14px;">
                            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            @if($pengajuan)
            <div style="padding: 24px;">
                <!-- Data Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                    <!-- Informasi Pengajuan -->
                    <div style="background-color: #eff6ff; padding: 16px; border-radius: 8px;">
                        <h3 style="font-weight: 600; color: #1e40af; margin-bottom: 12px; font-size: 16px;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                            Informasi Pengajuan
                        </h3>
                        <div style="font-size: 14px; line-height: 1.6;">
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Token Tracking:</span>
                                <span style="font-weight: 500; font-family: monospace; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; margin-left: 8px;">
                                    {{ $pengajuan->tracking_token ?? 'N/A' }}
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Tanggal Pengajuan:</span>
                                <span style="font-weight: 500; margin-left: 8px;">
                                    {{ $pengajuan->created_at ? $pengajuan->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Jenis Surat:</span>
                                <div style="margin-left: 8px; margin-top: 4px;">
                                    <span style="font-weight: 600;">{{ $jenisSurat ? $jenisSurat->nama_jenis : 'N/A' }}</span>
                                    @if($jenisSurat && $jenisSurat->kode_surat)
                                        <br><span style="font-size: 12px; background: #e5e7eb; padding: 2px 6px; border-radius: 4px;">
                                            {{ $jenisSurat->kode_surat }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Mahasiswa -->
                    <div style="background-color: #f0fdf4; padding: 16px; border-radius: 8px;">
                        <h3 style="font-weight: 600; color: #166534; margin-bottom: 12px; font-size: 16px;">
                            <i class="fas fa-user-graduate" style="margin-right: 8px;"></i>
                            Data Mahasiswa
                        </h3>
                        <div style="font-size: 14px; line-height: 1.6;">
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">NIM:</span>
                                <span style="font-weight: 500; margin-left: 8px;">{{ $pengajuan->nim ?? 'N/A' }}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Nama:</span>
                                <span style="font-weight: 500; margin-left: 8px;">{{ $pengajuan->nama_mahasiswa ?? 'N/A' }}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Program Studi:</span>
                                <span style="font-weight: 500; margin-left: 8px;">{{ $pengajuan->prodi->nama_prodi ?? 'N/A' }}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="color: #6b7280;">Email:</span>
                                <span style="font-weight: 500; margin-left: 8px; font-size: 12px;">{{ $pengajuan->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keperluan -->
                <div style="margin-bottom: 32px;">
                    <h3 style="font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 16px;">
                        <i class="fas fa-clipboard-list" style="margin-right: 8px;"></i>
                        Keperluan Surat
                    </h3>
                    <div style="background-color: #f9fafb; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb;">
                        {{ $pengajuan->keperluan ?? 'Tidak ada keterangan keperluan' }}
                    </div>
                </div>

                <!-- Data Tambahan -->
                @if($additionalData && is_array($additionalData))
                    <!-- Data Akademik -->
                    @if(isset($additionalData['semester']) || isset($additionalData['tahun_akademik']))
                    <div style="margin-bottom: 24px;">
                        <h3 style="font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 16px;">
                            <i class="fas fa-graduation-cap" style="margin-right: 8px;"></i>
                            Data Akademik
                        </h3>
                        <div style="background-color: #f0f9ff; padding: 16px; border-radius: 8px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 14px;">
                                @if(isset($additionalData['semester']))
                                <div>
                                    <span style="color: #6b7280;">Semester:</span>
                                    <span style="font-weight: 500; margin-left: 8px;">{{ $additionalData['semester'] }}</span>
                                </div>
                                @endif
                                @if(isset($additionalData['tahun_akademik']))
                                <div>
                                    <span style="color: #6b7280;">Tahun Akademik:</span>
                                    <span style="font-weight: 500; margin-left: 8px;">{{ $additionalData['tahun_akademik'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Data Orang Tua (untuk MA) -->
                    @if($jenisSurat && $jenisSurat->kode_surat === 'MA' && isset($additionalData['orang_tua']))
                    <div style="margin-bottom: 24px;">
                        <h3 style="font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 16px;">
                            <i class="fas fa-users" style="margin-right: 8px;"></i>
                            Data Orang Tua
                        </h3>
                        <div style="background-color: #fefce8; padding: 16px; border-radius: 8px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; font-size: 14px;">
                                @php
                                    $orangTua = $additionalData['orang_tua'];
                                @endphp
                                
                                @foreach([
                                    'nama' => 'Nama',
                                    'tempat_lahir' => 'Tempat Lahir',
                                    'tanggal_lahir' => 'Tanggal Lahir',
                                    'pekerjaan' => 'Pekerjaan',
                                    'nip' => 'NIP',
                                    'pangkat_golongan' => 'Pangkat/Golongan',
                                    'instansi' => 'Instansi',
                                    'alamat_instansi' => 'Alamat Instansi',
                                    'alamat_rumah' => 'Alamat Rumah'
                                ] as $key => $label)
                                    @if(isset($orangTua[$key]) && !empty($orangTua[$key]))
                                    <div>
                                        <span style="color: #6b7280;">{{ $label }}:</span>
                                        <span style="font-weight: 500; margin-left: 8px;">{{ $orangTua[$key] }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endif

                <!-- Action Buttons -->
                <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 12px; color: #6b7280;">
                        Status: {{ $status }} | 
                        Jenis: {{ $jenisSurat ? $jenisSurat->kode_surat : 'N/A' }} |
                        @if(config('app.debug'))
                            Debug Mode Active
                        @endif
                    </div>
                    
                    <div style="display: flex; gap: 12px;">
                        @if(in_array($status, ['processed', 'approved_prodi']))
                            
                                
                                @if($pengajuan && in_array($pengajuan->status, ['processed', 'approved_prodi']))
                                    <button onclick="kirimKePengaju({{ $pengajuan->id }})" 
                                            style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 12px;">
                                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                                        Kirim ke Pengaju
                                    </button>
                                @endif
                            @if($pengajuan && $pengajuan->canGeneratePdf())
                                    <button onclick="generateSuratPDF({{ $pengajuan->id }})" 
                                            style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #7c3aed; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                                        <i class="fas fa-file-pdf" style="margin-right: 8px;"></i>
                                        Generate PDF Surat
                                    </button>
                                @endif
                            @if($jenisSurat && $jenisSurat->kode_surat === 'MA')
                                <!-- FSI Buttons untuk Surat Mahasiswa Aktif -->
                                <button onclick="previewSuratFSI({{ $pengajuan->id }})" 
                                        style="display: inline-flex; align-items: center; padding: 8px 16px; background-color: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-eye" style="margin-right: 8px;"></i>
                                    Preview Surat FSI
                                </button>
                                <button onclick="generateSuratFSI({{ $pengajuan->id }})" 
                                        style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #7c3aed; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                                    <i class="fas fa-file-pdf" style="margin-right: 8px;"></i>
                                    Generate PDF Surat
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @else
                <!-- No Data State -->
                <div style="padding: 48px; text-align: center;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                    <h3 style="color: #6b7280; margin: 0 0 8px 0;">Data Pengajuan Tidak Ditemukan</h3>
                    <p style="color: #9ca3af; margin: 0;">Pengajuan yang Anda cari tidak tersedia.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
    // FSI Functions
    function previewSuratFSI(id) {
        const previewUrl = `/fakultas/surat/fsi/preview/${id}`;
        window.open(previewUrl, '_blank');
    }

    function generateSuratFSI(id) {
        if (confirm('Generate PDF surat FSI UNJANI?')) {
            previewSuratFSI(id);
        }
    }
    
    // Generate PDF Surat
    function generateSuratPDF(id) {
        if (confirm('Generate PDF surat resmi FSI UNJANI? Proses ini akan menyelesaikan pengajuan.')) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating PDF...';
            
            fetch(`/fakultas/surat/generate-pdf/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (data.download_url) {
                        window.open(data.download_url, '_blank');
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    }

    // Kirim ke Pengaju Function
    function kirimKePengaju(id) {
        const note = prompt('Tambahkan catatan untuk pengaju (opsional):', 'Surat keterangan telah selesai dan dapat digunakan.');
        if (note === null) return; // User cancelled
        
        if (confirm('Kirim surat ke pengaju? Status akan berubah menjadi SELESAI dan pengaju akan bisa melihat hasilnya.')) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
            
            fetch(`/fakultas/surat/kirim-ke-pengaju/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ note: note })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    if (data.tracking_url) {
                        const openTracking = confirm('Apakah Anda ingin melihat halaman tracking untuk pengaju?');
                        if (openTracking) {
                            window.open(data.tracking_url, '_blank');
                        }
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
                button.disabled = false;
                button.innerHTML = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    }
</script>
    @endsection