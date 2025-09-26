<!-- surat.pdf.fsi-surat -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Masih Kuliah - <?php echo e($pengajuan->nim); ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
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
        
        /* Judul Surat */
        .judul-surat {
            text-align: center;
            margin: 12px 0;
        }
        
        .judul-surat h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .nomor-surat {
            margin-top: 6px;
            font-size: 11pt;
        }
        
        /* Content */
        .content {
            text-align: justify;
            margin-top: 16px;
            font-size: 11pt;
            line-height: 1.3;
        }
        
        .content p {
            margin: 6px 0;
        }
        
        .data-table {
            margin: 8px 0;
            border-collapse: collapse;
            width: 100%;
        }
        
        .data-table td {
            padding: 1px 0;
            vertical-align: top;
            border: none;
        }
        
        .data-table .label {
            width: 160px;
        }
        
        .data-table .colon {
            width: 15px;
            text-align: center;
        }
        
        .data-table .value {
            font-weight: normal;
        }
        
        /* Signature */
        .signature-area {
            margin-top: 25px;
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
            height: 40px;
            margin: 8px 0;
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
        
        /* Text emphasis */
        .bold {
            font-weight: bold;
        }
        
        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT -->
    <table class="kop-surat">
        <tr>
            <td class="kop-logo-left">
            <?php if(file_exists(public_path('images/logo-ykep.png'))): ?>
                <img src="<?php echo e(public_path('images/logo-ykep.png')); ?>" style="width: 50px; height: 50px;">
            <?php else: ?>
                <div class="logo-box">LOGO<br>YKEP</div>
            <?php endif; ?>
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
            <?php if(file_exists(public_path('images/logo-unjani.png'))): ?>
                <img src="<?php echo e(public_path('images/logo-unjani.png')); ?>" style="width: 55px; height: 55px;">
            <?php else: ?>
                <div class="logo-box">LOGO<br>UNJANI</div>
            <?php endif; ?>
        </td>
        </tr>
    </table>
    
    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h3>SURAT PERNYATAAN MASIH KULIAH</h3>
        <div class="nomor-surat">
            NOMOR: <?php echo e($nomorSurat); ?>

        </div>
    </div>
    
    <!-- ISI SURAT -->
    <div class="content">
        <p>Yang bertanda tangan di bawah ini :</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($penandatangan['nama']); ?></td>
            </tr>
            <tr>
                <td class="label">Pangkat/Golongan</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($penandatangan['pangkat'] ?: 'PENATA MUDA TK.I – III/B'); ?></td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($penandatangan['jabatan']); ?> FAKULTAS SAINS DAN INFORMATIKA UNJANI</td>
            </tr>
        </table>
        
        <p>Dengan ini menyatakan :</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value uppercase bold">
                    <?php echo e(isset($displayData['nama']) ? $displayData['nama'] : strtoupper($pengajuan->nama_mahasiswa)); ?>

                </td>
            </tr>
            <tr>
                <td class="label">N I M</td>
                <td class="colon">:</td>
                <td class="value">
                    <?php echo e(isset($displayData['nim']) ? $displayData['nim'] : $pengajuan->nim); ?>

                </td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($pengajuan->prodi->nama_prodi); ?></td>
            </tr>
            <tr>
                <td class="label">Program</td>
                <td class="colon">:</td>
                <td class="value">S1</td>
            </tr>
        </table>
        
        <?php if(isset($additionalData['orang_tua']) && !empty($additionalData['orang_tua'])): ?>
        <p>Nama Orang Tua/Wali dari Mahasiswa tersebut adalah :</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value uppercase"><?php echo e($additionalData['orang_tua']['nama'] ?? '-'); ?></td>
            </tr>
            <?php if(isset($additionalData['orang_tua']['tempat_lahir']) || isset($additionalData['orang_tua']['tanggal_lahir'])): ?>
            <tr>
                <td class="label">Tempat/Tanggal Lahir</td>
                <td class="colon">:</td>
                <td class="value">
                    <?php echo e(($additionalData['orang_tua']['tempat_lahir'] ?? '')); ?>

                    <?php if(isset($additionalData['orang_tua']['tanggal_lahir'])): ?>
                        / <?php echo e($additionalData['orang_tua']['tanggal_lahir']); ?>

                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['nip']) && !empty($additionalData['orang_tua']['nip'])): ?>
            <tr>
                <td class="label">NIP</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['nip']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['pangkat_golongan']) && !empty($additionalData['orang_tua']['pangkat_golongan'])): ?>
            <tr>
                <td class="label">Pangkat/Golongan</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['pangkat_golongan']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['pekerjaan']) && !empty($additionalData['orang_tua']['pekerjaan'])): ?>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['pekerjaan']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['instansi']) && !empty($additionalData['orang_tua']['instansi'])): ?>
            <tr>
                <td class="label">Instansi</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['instansi']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['alamat_instansi']) && !empty($additionalData['orang_tua']['alamat_instansi'])): ?>
            <tr>
                <td class="label">Alamat Kantor</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['alamat_instansi']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if(isset($additionalData['orang_tua']['alamat_rumah']) && !empty($additionalData['orang_tua']['alamat_rumah'])): ?>
            <tr>
                <td class="label">Alamat Rumah</td>
                <td class="colon">:</td>
                <td class="value"><?php echo e($additionalData['orang_tua']['alamat_rumah']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <?php endif; ?>
        
        <p>
            Merupakan Mahasiswa Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani dan 
            <span class="bold">Aktif</span> pada Semester <?php echo e($additionalData['semester'] ?? 'Genap'); ?> 
            Tahun Akademik <?php echo e($additionalData['tahun_akademik'] ?? '2024/2025'); ?>.
        </p>
        
        <p>Demikian surat pernyataan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
    </div>
    
    <!-- TANDA TANGAN -->
    <div class="signature-area">
        <div class="signature-content">
            <p>Cimahi, <?php echo e($tanggalSurat); ?></p>
            <p>An. Dekan</p>
            <p><?php echo e($penandatangan['jabatan']); ?> – FSI</p>
            <div class="signature-space">
                <?php if($barcodeImage): ?>
                    <img src="data:image/png;base64,<?php echo e($barcodeImage); ?>" class="barcode-image" alt="Barcode Signature">
                <?php else: ?>
                    <div style="width: 120px; height: 40px; border: 1px dashed #999; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #666;">
                        BARCODE TTD
                    </div>
                <?php endif; ?>
            </div>
            <p class="name-underline"><?php echo e($penandatangan['nama']); ?></p>
            <p class="nid">NID. <?php echo e($penandatangan['nid'] ?: '4121 758 78'); ?></p>
        </div>
    </div>
</body>
</html><?php /**PATH C:\laragon\www\sistem-surat\resources\views/surat/pdf/surat-ma.blade.php ENDPATH**/ ?>