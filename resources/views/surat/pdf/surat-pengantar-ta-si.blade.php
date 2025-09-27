{{-- resources/views/surat/pdf/pengantar-kp-si.blade.php --}}
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
        .metadata .label { width: 80px; }
        .metadata .colon { width: 20px; text-align: center; }
        
        .content { text-align: justify; margin: 15px 0; }
        .content ol { margin-left: 20px; }
        .content li { margin-bottom: 8px; }
        
        .instansi-table { margin: 15px 30px; width: calc(100% - 60px); border-collapse: collapse; }
        .instansi-table th { border: none; padding: 5px; text-align: left; }
        .instansi-table td { border: none; padding: 5px; text-align: left; }
        
        .instansi-box { margin: 15px 0; padding: 10px; }
        .instansi-box p { margin: 5px 0; }
        
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
                <td class="logo-left">
                    <img src="{{ public_path('images/logo-ykep.png') }}" alt="Logo YKEP">
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
                <td class="logo-right">
                    <img src="{{ public_path('images/logo-unjani.png') }}" alt="Logo UNJANI">
                </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL -->
    <div class="judul">NOTA DINAS</div>
    <div class="nomor">Nomor: <span id="preview_nomor_pengantar" class="editable-field" data-input="edit_nomor_pengantar">{{ $nomor_surat }}<span class="tooltip-edit">Klik untuk edit</span></span></div>

    <!-- METADATA -->
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Kepada</td>
                <td class="colon">:</td>
                <td><span id="preview_tujuan_pengantar" class="editable-field" data-input="edit_tujuan_pengantar">Wakil Dekan I FSI<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
            <tr>
                <td class="label">Dari</td>
                <td class="colon">:</td>
                <td><span id="preview_dari_pengantar" class="editable-field" data-input="edit_dari_pengantar">Ketua Program Studi {{ $prodi->nama_prodi }}<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="colon">:</td>
                <td><span id="preview_perihal_pengantar" class="editable-field" data-input="edit_perihal_pengantar">Permohonan Pengantar Kerja Praktik<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
        </table>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <ol>
            <li>
                <strong>Dasar:</strong>
                <ol type="a">
                    <li><span id="preview_dasar_1" class="editable-field" data-input="edit_dasar_1">Program Kerja Prodi {{ $prodi->nama_prodi }} 2025, tentang Kalender Kegiatan Akademik Unjani TA. 2024/2025.<span class="tooltip-edit">Klik untuk edit</span></span></li>
                    <li><span id="preview_dasar_2" class="editable-field" data-input="edit_dasar_2">Form pengajuan Pembuatan surat pengantar ke instansi/lembaga/perusahaan dari mahasiswa.<span class="tooltip-edit">Klik untuk edit</span></span></li>
                    <li><span id="preview_dasar_3" class="editable-field" data-input="edit_dasar_3">Memperhatikan saran, arahan dan pandangan dari pimpinan jurusan, berkaitan dengan kegiatan:<span class="tooltip-edit">Klik untuk edit</span></span></li>
                </ol>
                <p style="margin-left: 60px; font-weight: bold;"><span id="preview_jenis_kegiatan" class="editable-field" data-input="edit_jenis_kegiatan">Pengumpulan data dan survey guna memenuhi tugas besar mata kuliah Kerja Praktik<span class="tooltip-edit">Klik untuk edit</span></span></p>
            </li>
            
            <li>
                Atas dasar tersebut di atas, dengan ini kami sampaikan permohonan kepada Wakil Dekan I, untuk berkenan kiranya menerbitkan surat 
                pengantar bagi mahasiswa yang tersebut dalam lampiran, yang ditujukan kepada:

                <!-- Dynamic Table for Multiple Students -->
                <table style="width: 100%; margin: 15px 0; border-collapse: collapse; border: 2px solid #000;">
                    <thead>
                        <tr style="background-color: #ffeb3b;">
                            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 50px;">NO</th>
                            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 120px;">N I M</th>
                            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">NAMA LENGKAP</th>
                        </tr>
                    </thead>
                    <tbody id="mahasiswa-table-body">
                        @php
                            $mahasiswaList = [];
                            // Get from additional_data if exists
                            if (isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp'])) {
                                $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'];
                            }
                            
                            // If empty or only has current student, use current student data
                            if (empty($mahasiswaList) || (count($mahasiswaList) == 1 && empty($mahasiswaList[0]['nama']))) {
                                $mahasiswaList = [
                                    [
                                        'nim' => $pengajuan->nim,
                                        'nama' => $pengajuan->nama_mahasiswa
                                    ]
                                ];
                            }
                        @endphp
                        
                        @foreach($mahasiswaList as $index => $mhs)
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center;">{{ $index + 1 }}.</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                                <span id="preview_mhs_{{ $index }}_nim" class="editable-field" data-input="edit_mhs_{{ $index }}_nim">{{ $mhs['nim'] ?? '' }}<span class="tooltip-edit">Klik untuk edit</span></span>
                            </td>
                            <td style="border: 1px solid #000; padding: 8px;">
                                <span id="preview_mhs_{{ $index }}_nama" class="editable-field" data-input="edit_mhs_{{ $index }}_nama">{{ $mhs['nama'] ?? '' }}<span class="tooltip-edit">Klik untuk edit</span></span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <p style="margin-top: 15px;"><strong><span id="preview_nama_instansi_pengantar" class="editable-field" data-input="edit_nama_instansi_pengantar">{{ $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Badan Kesatuan Bangsa dan Politik Kota Cimahi (Bakesbangpol Cimahi)' }}<span class="tooltip-edit">Klik untuk edit</span></span></strong></p>
                <p><span id="preview_alamat_instansi_pengantar" class="editable-field" data-input="edit_alamat_instansi_pengantar">{{ $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Gedung C, Jl. Raden Demang Hardjakusumah Lantai 1, Cibabat, Kec. Cimahi Utara, Kota Cimahi, Jawa Barat 40513' }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            </li>

            <li>Demikian surat permohonan ini kami sampaikan, atas perhatian dan Kerjasamanya, diucapkan terima kasih.</li>
        </ol>
    </div>

    <!-- SIGNATURE -->
    <div class="signature">
        <div class="signature-content">
            <p>Cimahi, <span id="preview_tanggal_pengantar" class="editable-field" data-input="edit_tanggal_pengantar">{{ $tanggal_surat }}<span class="tooltip-edit">Klik untuk edit</span></span></p>
            <p><strong>Ketua Prodi</strong></p>
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