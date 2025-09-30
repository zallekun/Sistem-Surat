{{-- resources/views/surat/pdf/pengantar-universal.blade.php --}}
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
        
        /* KOP Surat */
        .kop-surat {
            border-bottom: 3px double #000;
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
            width: 15%;
            text-align: center;
        }
        .logo-left img, .logo-right img {
            width: 60px;
            height: 60px;
        }
        .kop-text {
            width: 70%;
            text-align: center;
            font-weight: bold;
            line-height: 1.3;
        }
        .kop-text .line1 { font-size: 13pt; }
        .kop-text .line2 { font-size: 13pt; }
        .kop-text .line3 { font-size: 13pt; }
        .kop-text .line4 { font-size: 13pt; font-weight: bold; }
        .kop-text .alamat { font-size: 9pt; font-weight: normal; margin-top: 5px; }
        
        /* Content styles */
        .judul { 
            text-align: center; 
            font-weight: bold; 
            margin: 20px 0 10px 0; 
            font-size: 14pt;
            text-decoration: underline;
        }
        .nomor { 
            text-align: center; 
            margin-bottom: 20px; 
            font-size: 12pt;
        }
        
        .metadata { margin: 20px 0; }
        .metadata table { border-collapse: collapse; }
        .metadata td { padding: 2px 0; vertical-align: top; }
        .metadata .label { width: 80px; }
        .metadata .colon { width: 20px; text-align: center; }
        
        .content { text-align: justify; margin: 15px 0; }
        
        /* Table mahasiswa */
        .mahasiswa-table { 
            width: 100%; 
            margin: 20px 0; 
            border-collapse: collapse; 
            border: 1px solid #000;
        }
        .mahasiswa-table th { 
            background-color: #ffff99; 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: center; 
            font-weight: bold;
        }
        .mahasiswa-table td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left;
        }
        .mahasiswa-table td.center { text-align: center; }
        
        /* Instansi info */
        .instansi-info {
            margin: 20px 0;
            font-weight: bold;
        }
        
        /* Signature - FIXED POSITIONING */
        .signature { 
            margin-top: 40px; 
            text-align: right; 
        }
        .signature-content { 
            display: inline-block; 
            text-align: center; 
            min-width: 250px; 
        }
        .signature-date { 
            margin-bottom: 5px; 
        }
        .signature-title { 
            font-weight: bold; 
            text-decoration: underline; 
            margin-bottom: 5px;
        }
        .signature-unit { 
            margin-bottom: 10px;
        }
        .signature-space { 
            height: 80px; 
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .ttd-image { 
            max-width: 150px; 
            max-height: 80px;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }
        .signature-name { 
            font-weight: bold; 
            text-decoration: underline;
            margin-top: 5px;
        }
        .signature-nid { 
            margin-top: 5px;
        }
        
        /* Print specific styles */
        @media print {
            .signature-space {
                page-break-inside: avoid;
            }
            .ttd-image {
                position: static;
                transform: none;
                display: block;
                margin: 0 auto;
            }
        }
    </style>
</head>
@php
    $logoYkepPath = public_path('images/logo-ykep.png');
    $logoYkep = file_exists($logoYkepPath) 
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoYkepPath))
        : null;
    
    $logoUnjaniPath = public_path('images/logo-unjani.png');
    $logoUnjani = file_exists($logoUnjaniPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnjaniPath))
        : null;
@endphp
<body>
    <!-- KOP SURAT -->
    <div class="kop-surat">
        <table>
            <tr>
            <td style="width: 15%; text-align: center;">
                @if($logoYkep)
                    <img src="{{ $logoYkep }}" style="width: 60px; height: 60px;">
                @else
                    <div style="width: 60px; height: 60px; border: 1px solid #000; margin: 0 auto;"></div>
                @endif
            </td>
                <td class="kop-text">
                    <div class="line1">YAYASAN KARTIKA EKA PAKSI</div>
                    <div class="line2">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                    <div class="line3">FAKULTAS SAINS DAN INFORMATIKA</div>
                    <div class="line4">PROGRAM STUDI S1 {{ strtoupper($pengajuan->prodi->nama_prodi ?? 'SISTEM INFORMASI') }}</div>
                    <div class="alamat">
                        Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO. BOX 148 Telp. (022) 6631556 Fax. (022) 6631556
                    </div>
                </td>
            <td style="width: 15%; text-align: center;">
                @if($logoUnjani)
                    <img src="{{ $logoUnjani }}" style="width: 65px; height: 65px;">
                @else
                    <div style="width: 65px; height: 65px; border: 1px solid #000; margin: 0 auto;"></div>
                @endif
            </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL -->
    <div class="judul">NOTA DINAS</div>
    <div class="nomor">Nomor : {{ $surat_data['nomor_nota'] ?? $nomor_surat ?? 'ND-70/SI-F.SI/VII/2025' }}</div>

    <!-- METADATA -->
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Kepada</td>
                <td class="colon">:</td>
                <td>{{ $surat_data['kepada'] ?? 'Wakil Dekan I FSI' }}</td>
            </tr>
            <tr>
                <td class="label">Dari</td>
                <td class="colon">:</td>
                <td>{{ $surat_data['dari'] ?? 'Ketua Program Studi ' . ($pengajuan->prodi->nama_prodi ?? 'Sistem Informasi') }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="colon">:</td>
                <td>{{ $surat_data['perihal'] ?? 'Permohonan Pengantar Kerja Praktik' }}</td>
            </tr>
        </table>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <ol style="margin-left: 0px; padding-left: 20px;">
            <li style="margin-bottom: 15px;">
                <strong>Dasar :</strong>
                <ol type="a" style="margin-left: 20px; margin-top: 8px; padding-left: 0px; list-style-type: lower-alpha;">
                    <li style="margin-bottom: 8px; text-align: justify;">
                        {{ $surat_data['dasar_a'] ?? 'Program Kerja Prodi ' . ($pengajuan->prodi->nama_prodi ?? 'Sistem Informasi') . ' 2025, tentang Kalender Kegiatan Akademik Unjani TA. 2024/2025.' }}
                    </li>
                    <li style="margin-bottom: 8px; text-align: justify;">
                        {{ $surat_data['dasar_b'] ?? 'Form pengajuan Pembuatan surat pengantar ke instansi/lembaga/perusahaan dari mahasiswa.' }}
                    </li>
                    <li style="margin-bottom: 8px; text-align: justify;">
                        {{ $surat_data['dasar_c'] ?? 'Memperhatikan saran, arahan dan pandangan dari pimpinan jurusan, berkaitan dengan kegiatan :' }}<br>
                        <strong>{{ $surat_data['kegiatan'] ?? 'Pengumpulan data dan survey guna memenuhi tugas besar mata kuliah Kerja Praktik' }}</strong>
                    </li>
                </ol>
            </li>
            
            <li style="margin-bottom: 15px; text-align: justify;">
                Atas dasar tersebut di atas, dengan ini kami sampaikan permohonan kepada Wakil Dekan I, untuk berkenan kiranya menerbitkan surat 
                pengantar bagi mahasiswa yang tersebut dalam lampiran, yang ditujukan kepada :

                <!-- Dynamic Table Mahasiswa -->
                <table class="mahasiswa-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">No</th>
                            <th style="width: 25%;">N I M</th>
                            <th style="width: 65%;">NAMA LENGKAP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $mahasiswaList = [];
                            
                            // Priority: surat_data > additionalData > pengajuan default
                            if (isset($surat_data['mahasiswa_list']) && is_array($surat_data['mahasiswa_list'])) {
                                $mahasiswaList = $surat_data['mahasiswa_list'];
                            } elseif (isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp'])) {
                                $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'];
                            }
                            
                            // Default if empty
                            if (empty($mahasiswaList) || (count($mahasiswaList) == 1 && empty($mahasiswaList[0]['nama']))) {
                                $mahasiswaList = [
                                    ['nim' => $pengajuan->nim, 'nama' => $pengajuan->nama_mahasiswa]
                                ];
                            }
                        @endphp
                        
                        @foreach($mahasiswaList as $index => $mhs)
                        <tr>
                            <td class="center">{{ $index + 1 }}.</td>
                            <td class="center">{{ $mhs['nim'] ?? '' }}</td>
                            <td>{{ $mhs['nama'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Instansi Info -->
                <div class="instansi-info">
                    {{ $surat_data['nama_instansi'] ?? $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Badan Kesatuan Bangsa dan Politik Kota Cimahi (Bakesbangpol Cimahi)' }}
                </div>
                <div style="margin: 10px 0;">
                    {{ $surat_data['alamat_instansi'] ?? $additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Gedung C, Jl. Raden Demang Hardjakusumah Lantai 1, Cibabat, Kec. Cimahi Utara, Kota Cimahi, Jawa Barat 40513' }}
                </div>
            </li>

            <li>Demikian surat permohonan ini kami sampaikan, atas perhatian dan Kerjasamanya, diucapkan terima kasih.</li>
        </ol>
    </div>



    <!-- SIGNATURE WITH FIXED POSITIONING -->
{{-- Di bagian signature, update menjadi dynamic berdasarkan prodi --}}
@php
    // Data Kaprodi per Prodi
    $kaprodiData = [
        'si' => [
            'nama' => 'Takbir Hendro Pudjiantoro, S.Si., M.T',
            'nid' => '412166969'
        ],
        'ki' => [
            'nama' => 'Dr. Drs. Jasmansyah, M.Si.',
            'nid' => '412116964'
        ],
        'if' => [
            'nama' => 'Puspita Nurul, S.Kom., M.T.',
            'nid' => '412190585'
        ]
    ];
    
    $kodeProdi = strtolower($pengajuan->prodi->kode_prodi);
    $currentKaprodi = $kaprodiData[$kodeProdi] ?? $kaprodiData['si'];
@endphp

<!-- SIGNATURE WITH FIXED POSITIONING -->
<div class="signature">
    <div class="signature-content">
        <div class="signature-date">
            {{ $surat_data['tempat_tanggal'] ?? 'Cimahi, ' . ($tanggal_surat ?? now()->locale('id')->isoFormat('D MMMM Y')) }}
        </div>
        <div class="signature-title">
            {{ $surat_data['jabatan_ttd'] ?? 'Ketua Prodi' }}
        </div>
        <div class="signature-unit">
            {{ $surat_data['unit_ttd'] ?? $pengajuan->prodi->nama_prodi }}
        </div>
        
        <!-- TTD Space with proper positioning -->
        <div class="signature-space">
            @if(isset($ttd_kaprodi) && $ttd_kaprodi)
                <img src="{{ $ttd_kaprodi }}" class="ttd-image" alt="TTD Kaprodi">
            @endif
        </div>
        
        <div class="signature-name">
            {{ $surat_data['nama_ttd'] ?? $currentKaprodi['nama'] }}
        </div>
        <div class="signature-nid">
            NID. {{ $surat_data['nid_ttd'] ?? $currentKaprodi['nid'] }}
        </div>
    </div>
</div>
</body>
</html>