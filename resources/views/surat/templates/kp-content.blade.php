{{-- resources/views/surat/templates/kp-content.blade.php --}}

<style>
    /* Style untuk header surat */
    .surat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px double #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .logo-left, .logo-right {
        width: 80px;
        height: 80px;
    }
    
    .logo-left img, .logo-right img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .header-text {
        text-align: center;
        flex-grow: 1;
        padding: 0 20px;
    }
    
    .header-text h1 {
        font-size: 14pt;
        margin: 5px 0;
        font-weight: bold;
    }
    
    .header-text p {
        margin: 3px 0;
        font-size: 11pt;
    }
    
    .letter-info {
        display: flex;
        margin-bottom: 20px;
    }
    
    .letter-details {
        flex: 1;
    }
    
    .recipient-section {
        flex: 1;
        padding-left: 50px;
    }
    
    .student-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    
    .student-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
    
    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
    }
    
    .tembusan {
        flex: 1;
    }
    
    .signature {
        flex: 1;
        text-align: center;
    }
</style>

<!-- HEADER SURAT -->
<div class="surat-header">
    <div class="logo-left">
        @if(isset($logo_kiri))
            <img src="{{ $logo_kiri }}" alt="Logo Universitas">
        @else
            <img src="{{ asset('images/logo-unjani.png') }}" alt="Logo Unjani" onerror="this.style.display='none'">
        @endif
    </div>
    <div class="header-text">
        <h1>YAYASAN KARTIKA EKA PAKSI</h1>
        <h1>UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</h1>
        <h1>FAKULTAS SAINS DAN INFORMATIKA</h1>
        <h1>(FSI)</h1>
        <p>Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6650646</p>
    </div>
    <div class="logo-right">
        @if(isset($logo_kanan))
            <img src="{{ $logo_kanan }}" alt="Logo FSI">
        @else
            <img src="{{ asset('images/logo-fsi.png') }}" alt="Logo FSI" onerror="this.style.display='none'">
        @endif
    </div>
</div>

<!-- INFO SURAT DAN PENERIMA -->
<div class="letter-info">
    <!-- Bagian Kiri: Info Surat -->
    <div class="letter-details">
        <table style="width: 100%; font-size: 12pt;">
            <tr>
                <td style="width: 80px; padding: 2px 5px; vertical-align: top;">Nomor</td>
                <td style="width: 20px; vertical-align: top;">:</td>
                <td>
                    <span id="preview_nomor_surat_kp" class="editable-field" data-input="edit_nomor_surat">
                        {{ $nomorSurat }}
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 5px; vertical-align: top;">Sifat</td>
                <td style="vertical-align: top;">:</td>
                <td>
                    <span id="preview_sifat_kp" class="editable-field" data-input="edit_sifat">
                        Biasa
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 5px; vertical-align: top;">Lampiran</td>
                <td style="vertical-align: top;">:</td>
                <td>
                    <span id="preview_lampiran_kp" class="editable-field" data-input="edit_lampiran">
                        -
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 5px; vertical-align: top;">Perihal</td>
                <td style="vertical-align: top;">:</td>
                <td>Permohonan Izin Melaksanakan Kerja Praktik</td>
            </tr>
        </table>
    </div>
    
    <!-- Bagian Kanan: Tanggal dan Penerima -->
    <div class="recipient-section">
        <!-- Tanggal Surat -->
        <div style="text-align: right; margin-bottom: 5px; font-size: 12pt;">
            <span id="preview_tempat_tanggal_kp" class="editable-field" data-input="edit_tanggal_surat">
                Cimahi, {{ $tanggalSurat }}
                <span class="tooltip-edit">Klik untuk edit</span>
            </span>
        </div>
        
        <!-- Box Penerima -->
        <div style="padding: 10px; margin-top: 20px;">
            <p style="margin: 0;">Kepada :</p>
            <p style="margin: 5px 0;">
                <strong>Yth. <span id="preview_kepada_jabatan" class="editable-field" data-input="edit_kepada_jabatan">
                    Bapak/Ibu Pimpinan
                    <span class="tooltip-edit">Klik untuk edit</span>
                </span></strong>
            </p>
            <p style="margin: 5px 0;">
                <span id="preview_kepada_nama" class="editable-field" data-input="edit_kepada_nama">
                    {{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'PT. JASAMARGA (Persero), Bagian Jasa Marga Learning Institute' }}
                    <span class="tooltip-edit">Klik untuk edit</span>
                </span>
            </p>
            <p style="margin: 5px 0;">
                <span id="preview_kepada_alamat" class="editable-field" data-input="edit_kepada_alamat">
                    {{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Plaza Tol Taman Mini Indonesia Indah Jakarta 13550' }}
                    <span class="tooltip-edit">Klik untuk edit</span>
                </span>
            </p>
        </div>
    </div>
</div>

<!-- ISI SURAT -->
<div style="text-align: justify; font-size: 12pt; line-height: 1.5; margin: 20px 0;">
    <p style="margin-bottom: 15px;">Dengan hormat,</p>
    
    <ol style="margin-left: 20px; padding-left: 0;">
        <li style="margin-bottom: 15px;">
            Dasar: Nota Dinas Ketua Program Studi <span id="preview_prodi_nota" class="editable-field" data-input="edit_prodi_nota">
                {{ $pengajuan->prodi->nama_prodi }}
                <span class="tooltip-edit">Klik untuk edit</span>
            </span> Nomor: <span id="preview_nomor_nota" class="editable-field" data-input="edit_nomor_nota">
                B-53/IF-FSI/VI/2025
                <span class="tooltip-edit">Klik untuk edit</span>
            </span> tanggal <span id="preview_tanggal_nota" class="editable-field" data-input="edit_tanggal_nota">
                23 Juni 2025
                <span class="tooltip-edit">Klik untuk edit</span>
            </span> perihal Surat Pengantar.
        </li>
        
        <li style="margin-bottom: 15px;">
            Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan izin untuk melaksanakan Kerja Praktik pada 
            <span id="preview_periode_kp" class="editable-field" data-input="edit_periode_kp">
                s.d.
                <span class="tooltip-edit">Klik untuk edit</span>
            </span> di Instansi/Perusahaan yang Bapak/Ibu Pimpin kepada mahasiswa sebagai berikut :
            
            <!-- Tabel Mahasiswa -->
            <table class="student-table" id="mahasiswa-table-kp" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000; padding: 8px; width: 50px;">No</th>
                        <th style="border: 1px solid #000; padding: 8px;">Nama</th>
                        <th style="border: 1px solid #000; padding: 8px; width: 120px;">NIM</th>
                        <th style="border: 1px solid #000; padding: 8px;">Program Studi</th>
                    </tr>
                </thead>
                <tbody id="mahasiswa-table-body-kp">
                    @php
                        $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'] ?? [];
                        if (empty($mahasiswaList)) {
                            $mahasiswaList = [
                                ['nama' => $pengajuan->nama_mahasiswa, 'nim' => $pengajuan->nim, 'prodi' => $pengajuan->prodi->nama_prodi]
                            ];
                        }
                    @endphp
                    
                    @foreach($mahasiswaList as $index => $mhs)
                    <tr>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #000; padding: 8px;">
                            <span id="preview_mhs_kp_{{ $index }}_nama" class="editable-field" data-input="edit_mhs_{{ $index }}_nama">
                                {{ $mhs['nama'] ?? '' }}
                                <span class="tooltip-edit">Klik untuk edit</span>
                            </span>
                        </td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                            <span id="preview_mhs_kp_{{ $index }}_nim" class="editable-field" data-input="edit_mhs_{{ $index }}_nim">
                                {{ $mhs['nim'] ?? '' }}
                                <span class="tooltip-edit">Klik untuk edit</span>
                            </span>
                        </td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                            <span id="preview_mhs_kp_{{ $index }}_prodi" class="editable-field" data-input="edit_mhs_{{ $index }}_prodi">
                                {{ $mhs['prodi'] ?? $pengajuan->prodi->nama_prodi }}
                                <span class="tooltip-edit">Klik untuk edit</span>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </li>
        
        <li style="margin-bottom: 15px;">
            Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.
        </li>
    </ol>
</div>

<!-- BAGIAN TANDA TANGAN DAN TEMBUSAN -->
<div class="signature-section">
    <!-- Tembusan (Kiri) -->
    <div class="tembusan">
        <p style="text-decoration: underline; margin-bottom: 5px; font-size: 11pt;">Tembusan Yth :</p>
        <ol style="margin: 5px 0; padding-left: 20px; font-size: 11pt;">
            <li>Dekan F.SI (sebagai laporan)</li>
            <li style="text-decoration: underline;">
                Ketua Program Studi <span id="preview_prodi_tembusan" class="editable-field" data-input="edit_prodi_tembusan">
                    {{ $pengajuan->prodi->nama_prodi }}
                    <span class="tooltip-edit">Klik untuk edit</span>
                </span> FSI Unjani
            </li>
        </ol>
    </div>
    
    <!-- Tanda Tangan (Kanan) -->
    <div class="signature">
        <p>a.n. Dekan</p>
        <p>Wakil Dekan I</p>
        
        @if(isset($ttd_elektronik) && $ttd_elektronik)
        <div style="margin-top: 20px; margin-bottom: 10px;">
            <img src="{{ $ttd_elektronik }}" style="height: 60px;" alt="TTD Elektronik">
        </div>
        @endif
        
        <p style="margin-top: 20px; font-weight: bold;">TT ELEKTRONIK</p>
        
        <p style="margin-top: 60px; font-weight: bold; text-decoration: underline;">
            <span id="preview_ttd_nama_kp" class="editable-field" data-input="edit_ttd_nama">
                {{ $penandatangan['nama'] ?? 'AGUS KOMARUDIN, S.Kom., M.T.' }}
                <span class="tooltip-edit">Klik untuk edit</span>
            </span>
        </p>
        <p style="margin-top: 5px;">
            NID. <span id="preview_ttd_nid_kp" class="editable-field" data-input="edit_ttd_nid">
                {{ $penandatangan['nid'] ?? '412175878' }}
                <span class="tooltip-edit">Klik untuk edit</span>
            </span>
        </p>
    </div>
</div>