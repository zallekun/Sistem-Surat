{{-- resources/views/surat/pdf/pengantar-kp-default.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Dinas - {{ $pengajuan->tracking_token }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        
        /* Editable fields styling for preview */
        .editable-field {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid transparent;
            display: inline-block;
            min-width: 50px;
            position: relative;
            transition: all 0.2s;
        }

        .editable-field:hover {
            background: #fde047;
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }

        .tooltip-edit {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }

        .editable-field:hover .tooltip-edit {
            opacity: 1;
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-surat table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-surat td {
            vertical-align: middle;
        }
        .logo-left, .logo-right {
            width: 80px;
        }
        .logo-left img, .logo-right img {
            width: 60px;
            height: 60px;
        }
        .kop-text {
            font-weight: bold;
            line-height: 1.3;
        }
        .kop-text .line1 { font-size: 11pt; }
        .kop-text .line2 { font-size: 12pt; }
        .kop-text .line3 { font-size: 13pt; }
        .kop-text .line4 { font-size: 14pt; font-weight: bold; }
        .kop-text .alamat { font-size: 9pt; font-weight: normal; margin-top: 5px; }
        
        .judul { text-align: center; font-weight: bold; margin: 20px 0; }
        .nomor { text-align: center; margin-bottom: 20px; }
        
        .metadata { margin: 20px 0; }
        .metadata table { border-collapse: collapse; }
        .metadata td { padding: 2px 0; vertical-align: top; }
        .metadata .label { width: 100px; }
        .metadata .colon { width: 20px; text-align: center; }
        
        .content { text-align: justify; margin: 15px 0; }
        .content p { margin-bottom: 8px; }
        
        .mahasiswa-info { margin: 15px 0; padding: 10px; border: 1px solid #000; }
        .mahasiswa-info p { margin: 5px 0; }
        
        .instansi-info { margin: 15px 0; padding: 10px; }
        .instansi-info p { margin: 5px 0; }
        
        .signature { margin-top: 40px; text-align: right; }
        .signature-content { display: inline-block; text-align: center; min-width: 200px; }
        .signature-space { height: 60px; margin: 10px 0; }
        .ttd-image { max-width: 150px; max-height: 60px; }
        
        /* Print styles */
        @media print {
            .editable-field {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
            }
            .tooltip-edit {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <div class="kop-surat">
        <table>
            <tr>
            <td class="kop-logo-left">
            @if(file_exists(public_path('images/logo-ykep.png')))
                <img src="{{ public_path('images/logo-ykep.png') }}" style="width: 50px; height: 50px;">
            @else
                <div class="logo-box">LOGO<br>YKEP</div>
            @endif
            </td>
                <td class="kop-text">
                    <div class="line1">YAYASAN KARTIKA EKA PAKSI</div>
                    <div class="line2">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                    <div class="line3">FAKULTAS SAINS DAN INFORMATIKA</div>
                    <div class="line4">PROGRAM STUDI S1 <span id="preview_prodi_pengantar" class="editable-field" data-input="edit_prodi_pengantar">{{ strtoupper($prodi->nama_prodi) }}<span class="tooltip-edit">Klik untuk edit</span></span></div>
                    <div class="alamat">
                        Kampus Cimahi : <span id="preview_alamat_kampus" class="editable-field" data-input="edit_alamat_kampus">Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6631556 Fax. (022) 6631556<span class="tooltip-edit">Klik untuk edit</span></span>
                    </div>
                </td>
            <td class="kop-logo-right">
            @if(file_exists(public_path('images/logo-unjani.png')))
                <img src="{{ public_path('images/logo-unjani.png') }}" style="width: 55px; height: 55px;">
            @else
                <div class="logo-box">LOGO<br>UNJANI</div>
            @endif
            </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL -->
    <div class="judul">SURAT PENGANTAR</div>
    <div class="nomor">Nomor: <span id="preview_nomor_pengantar" class="editable-field" data-input="edit_nomor_pengantar">{{ $nomor_surat }}<span class="tooltip-edit">Klik untuk edit</span></span></div>

    <!-- METADATA -->
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Kepada</td>
                <td class="colon">:</td>
                <td><span id="preview_tujuan_pengantar" class="editable-field" data-input="edit_tujuan_pengantar">Yth. Pimpinan Instansi/Perusahaan<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
            <tr>
                <td class="label">Dari</td>
                <td class="colon">:</td>
                <td><span id="preview_dari_pengantar" class="editable-field" data-input="edit_dari_pengantar">Ketua Program Studi {{ $prodi->nama_prodi }}<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="colon">:</td>
                <td><span id="preview_perihal_pengantar" class="editable-field" data-input="edit_perihal_pengantar">Permohonan {{ $pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek' : 'Penelitian Tugas Akhir' }}<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
        </table>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <p>Dengan hormat,</p>
        
        <p>Berdasarkan program akademik Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani, dengan ini kami mohon izin untuk mahasiswa kami dapat melaksanakan <span id="preview_jenis_kegiatan" class="editable-field" data-input="edit_jenis_kegiatan">{{ $pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek' : 'Penelitian Tugas Akhir' }}<span class="tooltip-edit">Klik untuk edit</span></span> di instansi yang Bapak/Ibu pimpin.</p>

        <div class="mahasiswa-info">
            <p><strong>Data Mahasiswa:</strong></p>
            <p>Nama : <span id="preview_nama_mhs_pengantar" class="editable-field" data-input="edit_nama_mhs_pengantar">{{ $pengajuan->nama_mahasiswa }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>NIM : <span id="preview_nim_pengantar" class="editable-field" data-input="edit_nim_pengantar">{{ $pengajuan->nim }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>Program Studi : {{ $prodi->nama_prodi }}</p>
            <p>Semester : <span id="preview_semester_pengantar" class="editable-field" data-input="edit_semester_pengantar">6<span class="tooltip-edit">Klik untuk edit</span></span></p>
        </div>

        <div class="instansi-info">
            <p><strong>Tujuan:</strong></p>
            <p>Instansi : <span id="preview_nama_instansi_pengantar" class="editable-field" data-input="edit_nama_instansi_pengantar">{{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Nama Perusahaan' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>Alamat : <span id="preview_alamat_instansi_pengantar" class="editable-field" data-input="edit_alamat_instansi_pengantar">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Alamat Perusahaan' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>Periode : <span id="preview_tanggal_mulai_pengantar" class="editable-field" data-input="edit_tanggal_mulai_pengantar">{{ $additionalData['kerja_praktek']['periode_mulai'] ?? '19 Mei' }}<span class="tooltip-edit">Klik untuk edit</span></span> s.d <span id="preview_tanggal_selesai_pengantar" class="editable-field" data-input="edit_tanggal_selesai_pengantar">{{ $additionalData['kerja_praktek']['periode_selesai'] ?? '19 Juni' }}<span class="tooltip-edit">Klik untuk edit</span></span> 2025</p>
        </div>

        <p>Demikian surat pengantar ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
    </div>

    <!-- SIGNATURE -->
    <div class="signature">
        <div class="signature-content">
            <p>Cimahi, <span id="preview_tanggal_pengantar" class="editable-field" data-input="edit_tanggal_pengantar">{{ $tanggal_surat }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>Ketua Program Studi</p>
            <p><span id="preview_prodi_ttd" class="editable-field" data-input="edit_prodi_ttd">{{ $prodi->nama_prodi }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <div class="signature-space">
                @if($ttd_kaprodi)
                    <img src="{{ $ttd_kaprodi }}" class="ttd-image" alt="TTD Kaprodi">
                @endif
            </div>
            <p style="text-decoration: underline; font-weight: bold;"><span id="preview_nama_kaprodi_pengantar" class="editable-field" data-input="edit_nama_kaprodi_pengantar">{{ $kaprodi->nama ?? 'Nama Kaprodi' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p>NID. <span id="preview_nip_kaprodi_pengantar" class="editable-field" data-input="edit_nip_kaprodi_pengantar">{{ $kaprodi->nip ?? 'NIP Kaprodi' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
        </div>
    </div>
</body>
</html>