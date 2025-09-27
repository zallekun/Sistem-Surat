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
        .content ol { margin-left: 20px; }
        .content li { margin-bottom: 8px; }
        
        .mahasiswa-table { margin: 15px 0; width: 100%; border-collapse: collapse; }
        .mahasiswa-table th { border: 1px solid #000; padding: 5px; background: #f0f0f0; }
        .mahasiswa-table td { border: 1px solid #000; padding: 5px; }
        
        .tujuan-box { margin: 15px 0; padding: 10px; border: 1px solid #000; }
        .tujuan-box p { margin: 5px 0; }
        
        .signature { margin-top: 40px; text-align: right; }
        .signature-content { display: inline-block; text-align: center; min-width: 200px; }
        .signature-space { height: 60px; margin: 10px 0; }
        .ttd-image { max-width: 150px; max-height: 60px; }
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
                    <div class="line4">PROGRAM STUDI S1 {{ strtoupper($prodi->nama_prodi) }}</div>
                    <div class="alamat">
                        Terakreditasi UNGGUL<br>
                        Berdasarkan Keputusan LAMSAMA Nomor 190/SK/LAMSAMA/Akred/S/VII/2025 Tanggal 3 Juli 2025<br>
                        Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6631556 Fax. (022) 6631556
                    </div>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo-unjani.png') }}" alt="Logo UNJANI">
                </td>
            </tr>
        </table>
    </div>

    <!-- JUDUL -->
    <div class="judul">NOTA-DINAS</div>
    <div class="nomor">Nomor: {{ $nomor_surat }}</div>

    <!-- METADATA -->
    <div class="metadata">
        <table>
            <tr>
                <td class="label">Yth.</td>
                <td class="colon">:</td>
                <td>Wakil Dekan I Fakultas Sains Dan Informatika</td>
            </tr>
            <tr>
                <td class="label">Dari</td>
                <td class="colon">:</td>
                <td>Ketua Program Studi {{ $prodi->nama_prodi }}</td>
            </tr>
            <tr>
                <td class="label">Lampiran</td>
                <td class="colon">:</td>
                <td>{{ $surat_data['jumlah_lampiran'] ?? '1 (Satu) lembar' }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="colon">:</td>
                <td>{{ $pengajuan->jenisSurat->kode_surat === 'KP' ? 'Permohonan Surat Pengantar Izin Kerja Praktek (KP)' : 'Permohonan Surat Pengantar Tugas Akhir (TA)' }}</td>
            </tr>
        </table>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <ol>
            <li>
                <strong>Dasar:</strong>
                <ol type="a">
                    <li>{{ $surat_data['dasar_1'] ?? 'Program Kerja Program Studi ' . $prodi->nama_prodi . ' Fakultas Sains dan Informatika UNJANI Tahun Akademik 2024/2025 Bidang Akademik.' }}</li>
                    <li>{{ $surat_data['dasar_2'] ?? 'Pengambilan Mata Kuliah Wajib ' . ($pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek (KP)' : 'Tugas Akhir (TA)') . ' 1 SKS bagi Mahasiswa Program Studi ' . $prodi->nama_prodi . ' pada Semester Genap Tahun Akademik 2024/2025.' }}</li>
                    <li>{{ $surat_data['dasar_3'] ?? 'Permohonan penertiban Surat Pengantar Izin tempat untuk melakukan ' . ($pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek (KP)' : 'Tugas Akhir (TA)') . ' di ' . ($surat_data['tempat'] ?? 'perusahaan/instansi') . '.' }}</li>
                </ol>
            </li>
            
            <li>
                Atas dasar tersebut di atas, bersama ini kami sampaikan mahasiswa Program Studi {{ $prodi->nama_prodi }} di bawah ini:
                
                <table class="mahasiswa-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIM</th>
                            <th>Semester</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center;">1.</td>
                            <td>{{ $pengajuan->nama_mahasiswa }}</td>
                            <td>{{ $pengajuan->nim }}</td>
                            <td style="text-align: center;">{{ $surat_data['semester'] ?? '6' }}</td>
                            <td>{{ $surat_data['keterangan'] ?? 'Reguler Sore' }}</td>
                        </tr>
                    </tbody>
                </table>

                <p>
                    Mahasiswa bersangkutan sedang mengambil Mata Kuliah {{ $pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek (KP)' : 'Tugas Akhir (TA)' }} dalam memenuhi 
                    syarat pengambilan mata kuliah {{ $pengajuan->jenisSurat->kode_surat }} diwajibkan untuk melaksanakan {{ $pengajuan->jenisSurat->kode_surat }} di salah satu 
                    Perusahaan/Instansi. Untuk itu mohon dapat diterbitkan surat pengantar izin untuk melaksanakan 
                    {{ $pengajuan->jenisSurat->kode_surat === 'KP' ? 'Kerja Praktek (KP)' : 'Tugas Akhir (TA)' }} mulai pada tanggal {{ $surat_data['tanggal_mulai'] ?? '19 Mei' }} s.d {{ $surat_data['tanggal_selesai'] ?? '19 Juni' }} 2025, yang ditujukan:
                </p>

                <div class="tujuan-box">
                    <p><strong>Kepada Yth:</strong> {{ $surat_data['tujuan_jabatan'] ?? 'HR Manager' }}</p>
                    <p><strong>Instansi:</strong> {{ $surat_data['nama_instansi'] ?? 'PT. Company Name' }}</p>
                    <p><strong>Alamat:</strong> {{ $surat_data['alamat_instansi'] ?? 'Alamat lengkap instansi' }}</p>
                    @if(isset($surat_data['kp_departemen']))
                        <p><strong>KP di departemen:</strong> {{ $surat_data['kp_departemen'] }}</p>
                    @endif
                </div>
            </li>

            <li>Demikian kami sampaikan, atas perhatian dan realisasinya diucapkan terima kasih.</li>
        </ol>
    </div>

    <!-- SIGNATURE -->
    <div class="signature">
        <div class="signature-content">
            <p>Cimahi, {{ $tanggal_surat }}</p>
            <p>Ketua Program Studi {{ $prodi->nama_prodi }}</p>
            <div class="signature-space">
                @if($ttd_kaprodi)
                    <img src="{{ $ttd_kaprodi }}" class="ttd-image" alt="TTD Kaprodi">
                @endif
            </div>
            <p style="text-decoration: underline; font-weight: bold;">{{ $kaprodi->nama ?? 'Nama Kaprodi' }}</p>
            <p>NID. {{ $kaprodi->nip ?? 'NIP Kaprodi' }}</p>
        </div>
    </div>
</body>
</html>