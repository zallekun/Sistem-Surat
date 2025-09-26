
<!-- ISI SURAT MAHASISWA AKTIF -->
<div style="text-align: justify; font-size: 12pt; line-height: 1.4; margin: 20px 0;">
    <p style="margin: 8px 0; text-indent: 30px;">Yang bertanda tangan di bawah ini:</p>
    
    <table style="margin: 15px 0; width: 100%; font-size: 12pt;">
        <tr>
            <td style="width: 160px; padding: 2px 0;">Nama</td>
            <td style="width: 15px; text-align: center;">:</td>
            <td><span id="preview_ttd_nama" class="editable-field" data-input="edit_ttd_nama"><?php echo e($penandatangan['nama']); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Pangkat/Golongan</td>
            <td style="text-align: center;">:</td>
            <td><span id="preview_ttd_pangkat" class="editable-field" data-input="edit_ttd_pangkat"><?php echo e($penandatangan['pangkat']); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Jabatan</td>
            <td style="text-align: center;">:</td>
            <td><span id="preview_ttd_jabatan" class="editable-field" data-input="edit_ttd_jabatan"><?php echo e($penandatangan['jabatan']); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
    </table>
    
    <p style="margin: 15px 0; text-indent: 30px;">Dengan ini menerangkan bahwa:</p>
    
    <table style="margin: 15px 0; width: 100%; font-size: 12pt;">
        <tr>
            <td style="width: 160px; padding: 2px 0;">Nama</td>
            <td style="width: 15px; text-align: center;">:</td>
            <td style="font-weight: bold;"><span id="preview_nama_mahasiswa" class="editable-field" data-input="edit_nama_mahasiswa"><?php echo e(strtoupper($pengajuan->nama_mahasiswa)); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">N I M</td>
            <td style="text-align: center;">:</td>
            <td style="font-weight: bold;"><span id="preview_nim" class="editable-field" data-input="edit_nim"><?php echo e($pengajuan->nim); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Program Studi</td>
            <td style="text-align: center;">:</td>
            <td><span id="preview_prodi" class="editable-field" data-input="edit_prodi"><?php echo e($pengajuan->prodi->nama_prodi ?? 'Tidak ada data'); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Fakultas</td>
            <td style="text-align: center;">:</td>
            <td>Fakultas Sains dan Informatika</td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Jenjang</td>
            <td style="text-align: center;">:</td>
            <td>Sarjana (S1)</td>
        </tr>
    </table>
    
    <!-- Data orang tua - display all fields from additional_data -->
    <div id="parent_data_section">
        <?php if(isset($additionalData['orang_tua']) && is_array($additionalData['orang_tua'])): ?>
        <?php
            $orangTua = $additionalData['orang_tua'];
            $displayFields = [];
            
            // Collect all available fields
            if (isset($orangTua['nama']) && !empty($orangTua['nama'])) {
                $displayFields[] = ['label' => 'Nama Orang Tua/Wali', 'value' => $orangTua['nama'], 'id' => 'nama'];
            }
            if (isset($orangTua['nama_ayah']) && !empty($orangTua['nama_ayah'])) {
                $displayFields[] = ['label' => 'Nama Ayah', 'value' => $orangTua['nama_ayah'], 'id' => 'nama_ayah'];
            }
            if (isset($orangTua['nama_ibu']) && !empty($orangTua['nama_ibu'])) {
                $displayFields[] = ['label' => 'Nama Ibu', 'value' => $orangTua['nama_ibu'], 'id' => 'nama_ibu'];
            }
            if (isset($orangTua['tempat_lahir']) && !empty($orangTua['tempat_lahir'])) {
                $displayFields[] = ['label' => 'Tempat Lahir', 'value' => $orangTua['tempat_lahir'], 'id' => 'tempat_lahir'];
            }
            if (isset($orangTua['tanggal_lahir']) && !empty($orangTua['tanggal_lahir'])) {
                $displayFields[] = ['label' => 'Tanggal Lahir', 'value' => $orangTua['tanggal_lahir'], 'id' => 'tanggal_lahir'];
            }
            if (isset($orangTua['pekerjaan']) && !empty($orangTua['pekerjaan'])) {
                $displayFields[] = ['label' => 'Pekerjaan', 'value' => $orangTua['pekerjaan'], 'id' => 'pekerjaan'];
            }
            if (isset($orangTua['nip']) && !empty($orangTua['nip'])) {
                $displayFields[] = ['label' => 'NIP', 'value' => $orangTua['nip'], 'id' => 'nip'];
            }
            if (isset($orangTua['pangkat_golongan']) && !empty($orangTua['pangkat_golongan'])) {
                $displayFields[] = ['label' => 'Pangkat/Golongan', 'value' => $orangTua['pangkat_golongan'], 'id' => 'pangkat_golongan'];
            }
            if (isset($orangTua['instansi']) && !empty($orangTua['instansi'])) {
                $displayFields[] = ['label' => 'Instansi', 'value' => $orangTua['instansi'], 'id' => 'instansi'];
            }
            if (isset($orangTua['alamat_instansi']) && !empty($orangTua['alamat_instansi'])) {
                $displayFields[] = ['label' => 'Alamat Instansi', 'value' => $orangTua['alamat_instansi'], 'id' => 'alamat_instansi'];
            }
            if (isset($orangTua['alamat_rumah']) && !empty($orangTua['alamat_rumah'])) {
                $displayFields[] = ['label' => 'Alamat Rumah', 'value' => $orangTua['alamat_rumah'], 'id' => 'alamat_rumah'];
            }
        ?>
        
        <?php if(count($displayFields) > 0): ?>
            <p style="margin: 15px 0; text-indent: 30px;">Dengan nama Orang Tua/Wali:</p>
            
            <table style="margin: 15px 0; width: 100%; font-size: 12pt;">
                <?php $__currentLoopData = $displayFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="width: 160px; padding: 2px 0;"><?php echo e($field['label']); ?></td>
                    <td style="width: 15px; text-align: center;">:</td>
                    <td><span id="preview_<?php echo e($field['id']); ?>" class="editable-field" data-input="edit_<?php echo e($field['id']); ?>"><?php echo e($field['value']); ?><span class="tooltip-edit">Klik untuk edit</span></span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        <?php endif; ?>
        <?php else: ?>
        <p style="margin: 15px 0; text-indent: 30px;">Dengan nama Orang Tua/Wali:</p>
        
        <table style="margin: 15px 0; width: 100%; font-size: 12pt;">
            <tr>
                <td style="width: 160px; padding: 2px 0;">Nama Ayah</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td><span id="preview_nama_ayah" class="editable-field" data-input="edit_nama_ayah">-<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
            <tr>
                <td style="padding: 2px 0;">Nama Ibu</td>
                <td style="text-align: center;">:</td>
                <td><span id="preview_nama_ibu" class="editable-field" data-input="edit_nama_ibu">-<span class="tooltip-edit">Klik untuk edit</span></span></td>
            </tr>
        </table>
        <?php endif; ?>
    </div>
    
    <p style="margin: 15px 0; text-indent: 30px;">
        Adalah benar mahasiswa aktif pada Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani 
        Semester <span id="preview_semester" class="editable-field" data-input="edit_semester"><?php echo e($additionalData['semester'] ?? 'Ganjil'); ?><span class="tooltip-edit">Klik untuk edit</span></span> 
        Tahun Akademik <span id="preview_tahun_akademik" class="editable-field" data-input="edit_tahun_akademik"><?php echo e($additionalData['tahun_akademik'] ?? '2024/2025'); ?><span class="tooltip-edit">Klik untuk edit</span></span>.
    </p>
    
    <p style="margin: 15px 0; text-indent: 30px;">
        Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </p>
</div>

<!-- TANDA TANGAN -->
<div style="margin-top: 40px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                <div style="margin-bottom: 5px;">
                    Cimahi, <span id="preview_tanggal" class="editable-field" data-input="edit_tanggal_surat"><?php echo e($tanggalSurat); ?><span class="tooltip-edit">Klik untuk edit</span></span>
                </div>
                <div style="margin-bottom: 5px;">An. Dekan</div>
                <div style="margin-bottom: 60px;">Wakil Dekan III FSI</div>
                <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px;">
                    <span id="preview_ttd_nama_bottom" class="editable-field" data-input="edit_ttd_nama"><?php echo e($penandatangan['nama']); ?><span class="tooltip-edit">Klik untuk edit</span></span>
                </div>
                <div>NID. <span id="preview_ttd_nid" class="editable-field" data-input="edit_ttd_nid"><?php echo e($penandatangan['nid']); ?><span class="tooltip-edit">Klik untuk edit</span></span></div>
            </td>
        </tr>
    </table>
</div><?php /**PATH C:\laragon\www\sistem-surat\resources\views/surat/templates/ma-content.blade.php ENDPATH**/ ?>