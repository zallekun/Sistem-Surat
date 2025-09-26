<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Permohonan Izin Kerja Praktek - {{ $pengajuan->nim ?? 'Multiple' }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        /* KOP Surat - Same as MA template */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .kop-surat td {
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }
        
        .kop-logo-left {
            width: 15%;
        }
        
        .kop-logo-left .logo-box {
            width: 50px;
            height: 50px;
            margin: 0 auto;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            background: #f9f9f9;
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
        
        .kop-logo-right .logo-box {
            width: 55px;
            height: 55px;
            margin: 0 auto;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            background: #f9f9f9;
        }
        
        /* Document Info */
        .document-info {
            margin-bottom: 20px;
        }
        
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .document-info td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 12pt;
        }
        
        .document-info .label {
            width: 80px;
        }
        
        .document-info .colon {
            width: 15px;
        }
        
        /* Recipient */
        .recipient {
            margin-bottom: 20px;
            font-size: 12pt;
        }
        
        .recipient-line {
            margin-bottom: 2px;
        }
        
        /* Content */
        .content {
            text-align: justify;
            margin-bottom: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }
        
        .content p {
            margin-bottom: 12px;
            text-indent: 0;
        }
        
        /* Mahasiswa Table */
        .mahasiswa-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .mahasiswa-table th,
        .mahasiswa-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            font-size: 11pt;
        }
        
        .mahasiswa-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .mahasiswa-table .no {
            text-align: center;
            width: 40px;
        }
        
        .mahasiswa-table .nim {
            width: 120px;
        }
        
        .mahasiswa-table .nama {
            width: auto;
        }
        
        .mahasiswa-table .prodi {
            width: 120px;
        }
        
        /* Signature Area - Same as MA */
        .signature-area {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature-content {
            display: inline-block;
            text-align: center;
            width: 200px;
            margin: 0;
        }
        
        .signature-content p {
            margin: 4px 0;
            font-size: 11pt;
        }
        
        .signature-space {
            height: 50px;
            margin: 10px 0;
            position: relative;
        }
        
        .barcode-image {
            max-width: 120px;
            max-height: 40px;
            margin: 0 auto;
        }
        
        .name-underline {
            text-decoration: underline;
            font-weight: bold;
            font-size: 10pt;
            margin: 2px 0;
        }
        
        .nid {
            font-size: 10pt;
            margin: 2px 0;
        }
        
        /* Tembusan */
        .tembusan {
            margin-top: 30px;
            font-size: 11pt;
        }
        
        .tembusan-list {
            margin-left: 0;
            padding-left: 20px;
        }
        
        /*  fields */
        .editable {
            background-color: #fff3cd;
            border: 1px dashed #856404;
            padding: 2px 4px;
            min-height: 18px;
            display: inline-block;
            min-width: 100px;
        }
        
        .editable:focus {
            outline: 2px solid #007bff;
            background-color: #ffffff;
        }
        
        .print-hide {
            display: block;
        }
        
        @media print {
            . {
                background-color: transparent !important;
                border: none !important;
            }
            
            .print-hide {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- KOP SURAT - Same style as MA -->
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

    <!-- Document Info -->
    <div class="document-info">
        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="colon">:</td>
                <td><span class="" content="true">{{ $displayData['nomor_surat'] ?? 'BI/FSI-UNJANI/XI/2024' }}</span></td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>:</td>
                <td><span class="" content="true">{{ $displayData['sifat'] ?? 'Biasa' }}</span></td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td><span class="" content="true">{{ $displayData['lampiran'] ?? '-' }}</span></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>{{ $displayData['perihal'] ?? 'Permohonan Izin Melaksanakan Kerja Praktik' }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Recipient -->
    <div class="recipient">
        <div class="recipient-line">
            Kepada: <span class="" content="true">{{ $displayData['kepada_yth'] ?? 'Yth' }}</span>
        </div>
        <div class="recipient-line">
            <span class="" content="true">{{ $displayData['kepada_nama'] ?? $additionalData['kerja_praktek']['nama_perusahaan'] ?? 'Nama Perusahaan' }}</span>
        </div>
        <div class="recipient-line">
            <span class="" content="true">{{ $displayData['kepada_alamat_1'] ?? 'Alamat Baris 1' }}</span>
        </div>
        <div class="recipient-line">
            <span class="" content="true">{{ $displayData['kepada_alamat_2'] ?? 'Alamat Baris 2' }}</span>
        </div>
        <div class="recipient-line">
            <span class="" content="true">{{ $displayData['kepada_tempat'] ?? 'Di Tempat' }}</span>
        </div>
    </div>

    <!-- Greeting -->
    <div style="margin-bottom: 15px;">
        <p><span class="" content="true">{{ $displayData['salam_pembuka'] ?? 'Dengan hormat,' }}</span></p>
    </div>

    <!-- Content -->
    <div class="content">
        <p>
            <span class="" content="true">
                {{ $displayData['paragraph_1'] ?? 'Dasar : Nota Dinas Ketua Program Studi Kimia Nomor: ND/373KI-FSI/XI/2024 tanggal 20 November 2024 perihal Permohonan Surat Pengantar Kerja Praktik (KP).' }}
            </span>
        </p>
        
        <p>
            <span class="" content="true">
                {{ $displayData['paragraph_2'] ?? 'Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan Izin untuk melaksanakan Kerja Praktik pada tanggal ' . ($additionalData['kerja_praktek']['periode_mulai'] ?? '14 Juli') . ' s.d ' . ($additionalData['kerja_praktek']['periode_selesai'] ?? '16 Agustus 2025') . ' di Instansi/Perusahaan yang Bapak/Ibu Pimpin kepada mahasiswa sebagai berikut :' }}
            </span>
        </p>

        <!-- Tabel Mahasiswa - Dynamic -->
        <table class="mahasiswa-table">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="nama">Nama</th>
                    <th class="nim">NIM</th>
                    <th class="prodi">Program Studi</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($additionalData['kerja_praktek']['mahasiswa_kp']) && is_array($additionalData['kerja_praktek']['mahasiswa_kp']))
                    @foreach($additionalData['kerja_praktek']['mahasiswa_kp'] as $index => $mhs)
                    <tr>
                        <td class="no">{{ $index + 1 }}</td>
                        <td class="nama"><span class="" content="true">{{ $mhs['nama'] ?? '' }}</span></td>
                        <td class="nim"><span class="" content="true">{{ $mhs['nim'] ?? '' }}</span></td>
                        <td class="prodi"><span class="" content="true">{{ $mhs['prodi'] ?? '' }}</span></td>
                    </tr>
                    @endforeach
                    
                    <!-- Add empty rows if less than 5 students for consistent formatting -->
                    @if(count($additionalData['kerja_praktek']['mahasiswa_kp']) < 5)
                        @for($i = count($additionalData['kerja_praktek']['mahasiswa_kp']); $i < 5; $i++)
                        <tr>
                            <td class="no">{{ $i + 1 }}</td>
                            <td class="nama"><span class="" content="true"></span></td>
                            <td class="nim"><span class="" content="true"></span></td>
                            <td class="prodi"><span class="" content="true"></span></td>
                        </tr>
                        @endfor
                    @endif
                @else
                    <!-- Default 5 empty rows if no data -->
                    @for($i = 1; $i <= 5; $i++)
                    <tr>
                        <td class="no">{{ $i }}</td>
                        <td class="nama"><span class="" content="true"></span></td>
                        <td class="nim"><span class="" content="true"></span></td>
                        <td class="prodi"><span class="" content="true"></span></td>
                    </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <p>
            <span class="" content="true">
                {{ $displayData['paragraph_3'] ?? 'Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.' }}
            </span>
        </p>
    </div>

    <!-- Signature Area - Same as MA -->
    <div class="signature-area">
        <div class="signature-content">
            <p><span class="" content="true">{{ $displayData['tempat_tanggal'] ?? ('Cimahi, ' . date('d F Y')) }}</span></p>
            <p><span class="" content="true">{{ $displayData['jabatan_penandatangan'] ?? 'An. Dekan' }}</span></p>
            <p><span class="" content="true">{{ $displayData['jabatan_wakil'] ?? 'Wakil Dekan I - FSI' }}</span></p>
            
            <div class="signature-space">
                @if(isset($barcodeImage) && $barcodeImage)
                    <img src="data:image/png;base64,{{ $barcodeImage }}" class="barcode-image" alt="Barcode Signature">
                @else
                    <div style="width: 120px; height: 40px; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #666;">
                        BARCODE TTD
                    </div>
                @endif
            </div>
            
            <p class="name-underline"><span class="" content="true">{{ $displayData['nama_penandatangan'] ?? $penandatangan['nama'] ?? 'Dr. Arie Handian, S.Si., M.Si' }}</span></p>
            <p class="nid"><span class="" content="true">{{ $displayData['nip_penandatangan'] ?? $penandatangan['nid'] ?? 'NIP. 4121 857 87' }}</span></p>
        </div>
    </div>

    <!-- Tembusan -->
    <div class="tembusan">
        <strong>Tembusan Yth :</strong>
        <ol class="tembusan-list">
            <li><span class="" content="true">{{ $displayData['tembusan_1'] ?? 'Dekan FSI (sebagai laporan)' }}</span></li>
            <li><span class="" content="true">{{ $displayData['tembusan_2'] ?? 'Ketua Program Studi ' . ($pengajuan->prodi->nama_prodi ?? 'Kimia') . ' FSI UNJANI' }}</span></li>
        </ol>
    </div>

    <!-- Edit Controls (Hidden when printing)
    <div class="print-hide" style="position: fixed; top: 20px; right: 20px; background: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000;">
        <h4 style="margin: 0 0 10px 0; font-size: 12pt;">Edit Controls</h4>
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin: 2px; font-size: 10pt;">Print</button><br>
        <button onclick="saveDraft()" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin: 2px; font-size: 10pt;">Save Draft</button><br>
        <button onclick="resetToOriginal()" style="background: #ffc107; color: black; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin: 2px; font-size: 10pt;">Reset</button><br>
        <button onclick="toggleEditMode()" id="editToggle" style="background: #17a2b8; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin: 2px; font-size: 10pt;">Edit Mode: ON</button>
    </div> -->

    <!-- <script>
        let editMode = true;
        let originalData = {};

        document.addEventListener('DOMContentLoaded', function() {
            saveOriginalData();
            console.log('Surat KP loaded with', {{ count($additionalData['kerja_praktek']['mahasiswa_kp'] ?? []) }}, 'mahasiswa');
        });

        function saveOriginalData() {
            const s = document.querySelectorAll('.');
            s.forEach((element, index) => {
                originalData[index] = element.textContent;
            });
        }

        function toggleEditMode() {
            editMode = !editMode;
            const s = document.querySelectorAll('.');
            const button = document.getElementById('editToggle');
            
            if (editMode) {
                s.forEach(el => el.content = true);
                button.textContent = 'Edit Mode: ON';
                button.style.background = '#17a2b8';
            } else {
                s.forEach(el => el.content = false);
                button.textContent = 'Edit Mode: OFF';
                button.style.background = '#6c757d';
            }
        }

        function saveDraft() {
            const data = {};
            const s = document.querySelectorAll('.');
            s.forEach((element, index) => {
                data[index] = element.textContent;
            });
            
            localStorage.setItem('surat_kp_draft_{{ $pengajuan->id ?? "new" }}', JSON.stringify(data));
            alert('Draft berhasil disimpan!');
        }

        function loadDraft() {
            const saved = localStorage.getItem('surat_kp_draft_{{ $pengajuan->id ?? "new" }}');
            if (saved) {
                const data = JSON.parse(saved);
                const s = document.querySelectorAll('.');
                s.forEach((element, index) => {
                    if (data[index] !== undefined) {
                        element.textContent = data[index];
                    }
                });
            }
        }

        function resetToOriginal() {
            if (confirm('Reset semua perubahan ke data asli?')) {
                const s = document.querySelectorAll('.');
                s.forEach((element, index) => {
                    if (originalData[index] !== undefined) {
                        element.textContent = originalData[index];
                    }
                });
            }
        }

        // Auto-save every 30 seconds
        setInterval(saveDraft, 30000);
        loadDraft();

        // Prevent accidental page reload
        window.addEventListener('beforeunload', function(e) {
            if (editMode) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script> -->
</body>
</html>