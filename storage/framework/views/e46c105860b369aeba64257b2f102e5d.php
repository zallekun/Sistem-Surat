<div class="create-surat-form">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">Buat Surat Baru</h2>
            
            <form wire:submit.prevent="saveSurat">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="space-y-4">
                        
                        <div>
                            <label for="nomor_surat" class="block text-sm font-medium text-gray-700 mb-1">
                                Nomor Surat
                            </label>
                            <input type="text" 
                                   wire:model="nomor_surat" 
                                   id="nomor_surat"
                                   class="w-full rounded-md border-gray-300 bg-gray-50"
                                   readonly>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['nomor_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        
                        <div>
                            <label for="perihal" class="block text-sm font-medium text-gray-700 mb-1">
                                Perihal <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   wire:model="perihal" 
                                   id="perihal"
                                   class="w-full rounded-md border-gray-300"
                                   placeholder="Masukkan perihal surat"
                                   required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['perihal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        
                        <div>
                            <label for="tujuan_jabatan_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Tujuan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="tujuan_jabatan_id" 
                                    id="tujuan_jabatan_id"
                                    class="w-full rounded-md border-gray-300"
                                    required>
                                <option value="">Pilih Tujuan</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $tujuanJabatanOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jabatan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($jabatan->id); ?>"><?php echo e($jabatan->nama_jabatan); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['tujuan_jabatan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        
                        <div>
                            <label for="lampiran" class="block text-sm font-medium text-gray-700 mb-1">
                                Lampiran
                            </label>
                            <input type="text" 
                                   wire:model="lampiran" 
                                   id="lampiran"
                                   class="w-full rounded-md border-gray-300"
                                   placeholder="Contoh: 1 bendel (opsional)">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['lampiran'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    
                    <div class="space-y-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fakultas
                            </label>
                            <input type="text" 
                                   value="<?php echo e($fakultas_name); ?>" 
                                   class="w-full rounded-md border-gray-300 bg-gray-50"
                                   readonly>
                        </div>

                        
                        <!--[if BLOCK]><![endif]--><?php if(!$isStaffFakultas): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Program Studi
                            </label>
                            <input type="text" 
                                   value="<?php echo e($prodi_name); ?>" 
                                   class="w-full rounded-md border-gray-300 bg-gray-50"
                                   readonly>
                        </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        
                        <div>
                            <label for="tanggal_surat" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Surat <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   wire:model="tanggal_surat" 
                                   id="tanggal_surat"
                                   class="w-full rounded-md border-gray-300"
                                   required>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['tanggal_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        
                        <div>
                            <label for="sifat_surat" class="block text-sm font-medium text-gray-700 mb-1">
                                Sifat Surat <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="sifat_surat" 
                                    id="sifat_surat"
                                    class="w-full rounded-md border-gray-300"
                                    required>
                                <option value="">Pilih Sifat Surat</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sifatSuratOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sifat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sifat); ?>"><?php echo e($sifat); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['sifat_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                
                <div class="mt-6">
                    <label for="file_surat" class="block text-sm font-medium text-gray-700 mb-1">
                        File Surat (PDF) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" 
                           wire:model="file_surat" 
                           id="file_surat"
                           accept="application/pdf"
                           class="w-full text-sm text-gray-500 
                                  file:mr-4 file:py-2 file:px-4 
                                  file:rounded-full file:border-0 
                                  file:text-sm file:font-semibold 
                                  file:bg-indigo-50 file:text-indigo-700 
                                  hover:file:bg-indigo-100"
                           required>
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['file_surat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                    
                    <div wire:loading wire:target="file_surat" class="mt-2">
                        <span class="text-sm text-gray-500">Sedang mengupload...</span>
                    </div>
                </div>

                

                
            
                
            
                <!-- Action Buttons -->
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: flex-end; gap: 12px;">
                        <a href="<?php echo e(route('staff.surat.index')); ?>" 
                           style="padding: 8px 16px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 6px; display: inline-block;">
                            Batal
                        </a>
                        
                        <button type="button"
                                wire:click="saveDraft"
                                style="padding: 8px 16px; background-color: #4b5563; color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Simpan Draft
                        </button>
                        
                        <button type="button"
                                wire:click="confirmSubmit"
                                style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Kirim ke Kaprodi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:init', function() {
            Livewire.on('show-submit-confirmation', function() {
                Swal.fire({
                    title: 'Konfirmasi Pengiriman',
                    text: 'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review? Surat yang sudah dikirim tidak dapat diedit kembali.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4F46E5',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Kirim!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').submitForReview();
                    }
                });
            });
        });
    </script>
</div><?php /**PATH C:\laragon\www\sistem-surat\resources\views/livewire/create-surat-form.blade.php ENDPATH**/ ?>