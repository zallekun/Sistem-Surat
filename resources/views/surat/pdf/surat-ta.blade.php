<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { 
            margin: 15mm 20mm;
            size: A4;
        }
        body { 
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* KOP Surat */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-surat td {
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .kop-logo-left {
            width: 15%;
        }

        .kop-logo-left img {
            width: 50px;
            height: 50px;
        }

        .kop-text {
            width: 70%;
            font-weight: bold;
            line-height: 1.2;
        }

        .kop-text .line1 {
            font-size: 11pt;
            margin-bottom: 1px;
        }

        .kop-text .line2 {
            font-size: 11pt;
            margin-bottom: 1px;
        }

        .kop-text .line3 {
            font-size: 11pt;
            margin-bottom: 1px;
        }

        .kop-text .line4 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kop-text .alamat {
            font-size: 9pt;
            font-weight: normal;
        }

        .kop-logo-right {
            width: 15%;
        }

        .kop-logo-right img {
            width: 55px;
            height: 55px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .letter-header td {
            padding: 2px 5px;
            vertical-align: top;
            font-size: 11pt;
        }

        .content {
            text-align: justify;
            margin: 20px 0;
            font-size: 11pt;
            line-height: 1.3;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .student-table th, .student-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .student-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .student-table td:first-child {
            text-align: center;
            width: 50px;
        }

        .student-table td:nth-child(3) {
            text-align: center;
            width: 120px;
        }

        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .tembusan {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .signature {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .tembusan-title {
            text-decoration: underline;
            font-size: 11pt;
            margin-bottom: 5px;
        }

        .tembusan-list {
            margin: 5px 0;
            padding-left: 20px;
            font-size: 11pt;
        }

        ol {
            margin-left: 20px;
            padding-left: 0;
        }

        ol li {
            margin-bottom: 15px;
        }

        .pagebreak {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <table class="kop-surat">
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
                <div class="line4">(FSI)</div>
                <div class="alamat">
                    Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6650646
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

    <!-- HEADER SURAT -->
    <div style="display: table; width: 100%; margin-bottom: 20px;">
        <!-- Kiri: Detail Surat -->
        <div style="display: table-cell; width: 50%; vertical-align: top;">
            <table class="letter-header">
                <tr>
                    <td style="width: 80px;">Nomor</td>
                    <td style="width: 20px;">:</td>
                    <td>{{ $displayData['nomor_surat'] ?? $nomorSurat }}</td>
                </tr>
                <tr>
                    <td>Sifat</td>
                    <td>:</td>
                    <td>{{ $displayData['sifat'] ?? 'Biasa' }}</td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td>{{ $displayData['lampiran'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>:</td>
                    <td>Permohonan Izin Determinasi</td>
                </tr>
            </table>
        </div>
        
        <!-- Kanan: Tanggal dan Penerima -->
        <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 50px;">
            <div style="text-align: right; margin-bottom: 20px;">
                Cimahi, {{ $displayData['tanggal_surat'] ?? $tanggalSurat }}
            </div>
            
            <div style="margin-top: 20px;">
                <p style="margin: 0;">Kepada :</p>
                <p style="margin: 5px 0;">
                    <strong>Yth. {{ $displayData['kepada_jabatan'] ?? 'Bapak/Ibu Pimpinan' }}</strong>
                </p>
                <p style="margin: 5px 0;">
                    {{ $displayData['nama_perusahaan'] ?? $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'PT. [Nama Perusahaan]' }}
                </p>
                <p style="margin: 5px 0;">
                    {{ $displayData['alamat_perusahaan'] ?? $additionalData['kerja_praktek']['alamat_perusahaan'] ?? '[Alamat Perusahaan]' }}
                </p>
            </div>
        </div>
    </div>

    <!-- ISI SURAT -->
    <div class="content">
        <p style="margin-bottom: 15px;">Dengan hormat,</p>
        
        <ol>
            <li>
                Dasar: Nota Dinas Ketua Program Studi 
                {{ $displayData['prodi_nota'] ?? $pengajuan->prodi->nama_prodi }}
                Nomor: {{ $displayData['nomor_nota'] ?? 'ND/98/KI-FSI/IV/2025' }}
                tanggal {{ $displayData['tanggal_nota'] ?? date('d F Y') }}
                perihal Surat Pengantar.
            </li>
            
            <li>
                Atas dasar tersebut di atas, kami sampaikan mahasiswa :
                
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Ambil data mahasiswa dari displayData atau additionalData
                            $mahasiswaList = [];
                            if (isset($displayData['mahasiswa_kp']) && !empty($displayData['mahasiswa_kp'])) {
                                $mahasiswaList = $displayData['mahasiswa_kp'];
                            } elseif (isset($additionalData['kerja_praktek']['mahasiswa_kp']) && !empty($additionalData['kerja_praktek']['mahasiswa_kp'])) {
                                $mahasiswaList = $additionalData['kerja_praktek']['mahasiswa_kp'];
                            } else {
                                // Default: gunakan data pengaju
                                $mahasiswaList = [[
                                    'nama' => $pengajuan->nama_mahasiswa,
                                    'nim' => $pengajuan->nim,
                                    'prodi' => $pengajuan->prodi->nama_prodi
                                ]];
                            }
                        @endphp
                        
                        @foreach($mahasiswaList as $index => $mhs)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mhs['nama'] ?? '' }}</td>
                            <td>{{ $mhs['nim'] ?? '' }}</td>
                            <td style="text-align: center;">{{ $mhs['prodi'] ?? $pengajuan->prodi->nama_prodi }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </li>

            <li>Saat ini yang bersangkutan sedang melaksanakan Penelitian Tugas Akhir, untuk terlaksanakanya kegiatan tersebut, dengan ini kami mengajukan Permohonan Izin Determinasi di SITH Institut Teknologi Bandung (ITB). Kami mohon Bapak/Ibu berkenan memberikan izin untuk maksud tersebut di atas.</li>
            
            <li>
                Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.
            </li>
        </ol>
    </div>

    <!-- TANDA TANGAN DAN TEMBUSAN -->
    <div class="signature-section">
        <!-- Tembusan -->
        <div class="tembusan">
            <p class="tembusan-title">Tembusan Yth :</p>
            <ol class="tembusan-list">
                <li>Dekan F.SI (sebagai laporan)</li>
                <li style="text-decoration: underline;">
                    Ketua Program Studi {{ $displayData['prodi_tembusan'] ?? $pengajuan->prodi->nama_prodi }} FSI Unjani
                </li>
            </ol>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="signature">
            <p>a.n. Dekan</p>
            <p>Wakil Dekan I</p>
            
            @if(isset($ttd_elektronik) && $ttd_elektronik)
            <div style="margin: 20px 0;">
                <img src="{{ $ttd_elektronik }}" style="height: 60px;" alt="TTD">
            </div>
            @else
            <div style="margin: 60px 0;">
                <!-- Space untuk TTD -->
            </div>
            @endif
            
            <p style="font-weight: bold; text-decoration: underline;">
                {{ $displayData['ttd_nama'] ?? $penandatangan['nama'] ?? 'Dr. Arie Hardian, S.Si., M.Si.' }}
            </p>
            <p>NID. {{ $displayData['ttd_nid'] ?? $penandatangan['nid'] ?? '412185787' }}</p>
        </div>
    </div>
</body>
</html>
