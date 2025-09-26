
<!-- ISI SURAT KERJA PRAKTEK -->

<!-- Document Info untuk KP -->
<table style="margin-bottom: 20px; width: 100%; font-size: 12pt;">
    <tr>
        <td style="width: 80px; padding: 2px 0;">Nomor</td>
        <td style="width: 15px;">:</td>
        <td><span id="preview_nomor_surat_kp" class="editable-field" data-input="edit_nomor_surat"><?php echo e($nomorSurat); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
    </tr>
    <tr>
        <td>Sifat</td>
        <td>:</td>
        <td><span id="preview_sifat_kp" class="editable-field" data-input="edit_sifat">Biasa<span class="tooltip-edit">Klik untuk edit</span></span></td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>:</td>
        <td><span id="preview_lampiran_kp" class="editable-field" data-input="edit_lampiran">-<span class="tooltip-edit">Klik untuk edit</span></span></td>
    </tr>
    <tr>
        <td>Perihal</td>
        <td>:</td>
        <td><strong>Permohonan Izin Melaksanakan Kerja Praktik</strong></td>
    </tr>
</table>

<!-- Recipient untuk KP -->
<div style="margin-bottom: 20px; font-size: 12pt;">
    <div style="margin-bottom: 2px;">Kepada Yth:</div>
    <div style="margin-bottom: 2px;"><span id="preview_kepada_nama" class="editable-field" data-input="edit_kepada_nama"><?php echo e($additionalData['kerja_praktek']['nama_perusahaan'] ?? 'HRD Perusahaan'); ?><span class="tooltip-edit">Klik untuk edit</span></span></div>
    <div style="margin-bottom: 2px;"><span id="preview_kepada_alamat_1" class="editable-field" data-input="edit_kepada_alamat_1"><?php echo e($additionalData['kerja_praktek']['alamat_perusahaan'] ?? 'Alamat Perusahaan'); ?><span class="tooltip-edit">Klik untuk edit</span></span></div>
    <div style="margin-bottom: 2px;"><span id="preview_kepada_alamat_2" class="editable-field" data-input="edit_kepada_alamat_2"><span class="tooltip-edit">Klik untuk edit</span></span></div>
    <div>Di Tempat</div>
</div>

<!-- Content KP -->
<div style="text-align: justify; font-size: 12pt; line-height: 1.5;">
    <p style="margin-bottom: 12px;"><span id="preview_salam_pembuka" class="editable-field" data-input="edit_salam_pembuka">Dengan hormat,<span class="tooltip-edit">Klik untuk edit</span></span></p>
    
    <p style="margin-bottom: 12px;">
        <span id="preview_paragraph_1" class="editable-field" data-input="edit_paragraph_1">
            Dasar : Nota Dinas Ketua Program Studi <?php echo e($pengajuan->prodi->nama_prodi ?? 'Kimia'); ?> Nomor: ND/373KI-FSI/XI/2024 tanggal <?php echo e(date('d F Y')); ?> perihal Permohonan Surat Pengantar Kerja Praktik (KP).
            <span class="tooltip-edit">Klik untuk edit</span>
        </span>
    </p>
    
    <p style="margin-bottom: 12px;">
        <span id="preview_paragraph_2" class="editable-field" data-input="edit_paragraph_2">
            Atas dasar tersebut di atas, bersama ini kami sampaikan permohonan Izin untuk melaksanakan Kerja Praktik pada tanggal 
            <span id="preview_periode_mulai"><?php echo e($additionalData['kerja_praktek']['periode_mulai'] ?? '14 Juli'); ?></span> s.d 
            <span id="preview_periode_selesai"><?php echo e($additionalData['kerja_praktek']['periode_selesai'] ?? '16 Agustus 2025'); ?></span> 
            di Instansi/Perusahaan yang Bapak/Ibu Pimpin kepada mahasiswa sebagai berikut :
            <span class="tooltip-edit">Klik untuk edit</span>
        </span>
    </p>

    <!-- Tabel Mahasiswa KP -->
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid #000; padding: 6px 8px; text-align: center; width: 40px;">No</th>
                <th style="border: 1px solid #000; padding: 6px 8px; text-align: center;">Nama</th>
                <th style="border: 1px solid #000; padding: 6px 8px; text-align: center; width: 120px;">NIM</th>
                <th style="border: 1px solid #000; padding: 6px 8px; text-align: center; width: 150px;">Program Studi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($mahasiswa) && is_array($mahasiswa) && count($mahasiswa) > 0): ?>
                <?php $__currentLoopData = $mahasiswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mhs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;"><?php echo e($index + 1); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($index); ?>_nama" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_nama">
                            <?php echo e($mhs['nama'] ?? ''); ?>

                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($index); ?>_nim" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_nim">
                            <?php echo e($mhs['nim'] ?? ''); ?>

                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($index); ?>_prodi" class="editable-field" data-input="edit_mhs_<?php echo e($index); ?>_prodi">
                            <?php echo e($mhs['prodi'] ?? ''); ?>

                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <!-- Add empty rows untuk konsistensi format -->
                <?php for($i = count($mahasiswa); $i < 5; $i++): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;"><?php echo e($i + 1); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_nama" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_nama">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_nim" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_nim">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_prodi" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_prodi">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                </tr>
                <?php endfor; ?>
            <?php else: ?>
                <!-- Default 5 empty rows jika tidak ada data -->
                <?php for($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 6px 8px; text-align: center;"><?php echo e($i + 1); ?></td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_nama" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_nama">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_nim" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_nim">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                    <td style="border: 1px solid #000; padding: 6px 8px;">
                        <span id="preview_mhs_<?php echo e($i); ?>_prodi" class="editable-field" data-input="edit_mhs_<?php echo e($i); ?>_prodi">
                            <span class="tooltip-edit">Klik untuk edit</span>
                        </span>
                    </td>
                </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="margin-bottom: 12px;">
        <span id="preview_paragraph_3" class="editable-field" data-input="edit_paragraph_3">
            Demikian surat permohonan ini kami sampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.
            <span class="tooltip-edit">Klik untuk edit</span>
        </span>
    </p>
</div>

<!-- TANDA TANGAN KP -->
<div style="margin-top: 40px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div style="margin-bottom: 5px;">
                    <span id="preview_tempat_tanggal" class="editable-field" data-input="edit_tanggal_surat">
                        Cimahi, <?php echo e($tanggalSurat); ?>

                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </div>
                <div style="margin-bottom: 5px;">
                    <span id="preview_jabatan_penandatangan" class="editable-field" data-input="edit_jabatan_penandatangan">
                        An. Dekan
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </div>
                <div style="margin-bottom: 60px;">
                    <span id="preview_jabatan_wakil" class="editable-field" data-input="edit_jabatan_wakil">
                        Wakil Dekan I - FSI
                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </div>
                <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px;">
                    <span id="preview_ttd_nama_bottom_kp" class="editable-field" data-input="edit_ttd_nama">
                        <?php echo e($penandatangan['nama']); ?>

                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </div>
                <div>
                    NID. <span id="preview_ttd_nid_kp" class="editable-field" data-input="edit_ttd_nid">
                        <?php echo e($penandatangan['nid']); ?>

                        <span class="tooltip-edit">Klik untuk edit</span>
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- Tembusan untuk KP -->
<div style="margin-top: 30px; font-size: 11pt;">
    <strong>Tembusan Yth :</strong>
    <ol style="margin-left: 0; padding-left: 20px;">
        <li>
            <span id="preview_tembusan_1" class="editable-field" data-input="edit_tembusan_1">
                Dekan FSI (sebagai laporan)
                <span class="tooltip-edit">Klik untuk edit</span>
            </span>
        </li>
        <li>
            <span id="preview_tembusan_2" class="editable-field" data-input="edit_tembusan_2">
                Ketua Program Studi <?php echo e($pengajuan->prodi->nama_prodi ?? 'Kimia'); ?> FSI UNJANI
                <span class="tooltip-edit">Klik untuk edit</span>
            </span>
        </li>
    </ol>
</div><?php /**PATH C:\laragon\www\sistem-surat\resources\views/surat/templates/kp-preview.blade.php ENDPATH**/ ?>