<?php
// update_create_surat_livewire.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== UPDATING LIVEWIRE COMPONENT FOR DRAFT AND SUBMIT ===\n\n";

// Step 1: Update the Livewire Component Class
$componentFile = 'app/Livewire/CreateSuratForm.php';
if (!file_exists($componentFile)) {
    $componentFile = 'app/Http/Livewire/CreateSuratForm.php';
}

if (file_exists($componentFile)) {
    echo "1. UPDATING COMPONENT CLASS: $componentFile\n";
    $content = file_get_contents($componentFile);
    
    // Backup
    file_put_contents($componentFile . '.backup_' . date('YmdHis'), $content);
    
    // Check if methods already exist
    if (!str_contains($content, 'saveDraft')) {
        echo "   Adding saveDraft and submitForReview methods...\n";
        
        // Find the saveSurat method and add new methods after it
        $newMethods = '
    public function saveDraft()
    {
        $this->validate();

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($this->file_surat) {
                $filePath = $this->file_surat->store(\'surat_pdfs\', \'public\');
            }

            $draftStatus = StatusSurat::where(\'kode_status\', \'draft\')->firstOrFail();
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                \'nomor_surat\' => $this->nomor_surat,
                \'perihal\' => $this->perihal,
                \'tujuan_jabatan_id\' => $this->tujuan_jabatan_id,
                \'lampiran\' => $this->lampiran,
                \'prodi_id\' => $this->prodi_id,
                \'fakultas_id\' => $this->fakultas_id,
                \'jenis_id\' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                \'status_id\' => $draftStatus->id,
                \'created_by\' => $user->id,
                \'tanggal_surat\' => $this->tanggal_surat,
                \'sifat_surat\' => $this->sifat_surat,
                \'file_surat\' => $filePath,
            ]);

            DB::commit();
            session()->flash(\'success\', \'Surat berhasil disimpan sebagai draft!\');
            return redirect()->route(\'staff.surat.show\', $surat->id);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk(\'public\')->delete($filePath);
            }
            session()->flash(\'error\', \'Gagal menyimpan draft: \' . $e->getMessage());
        }
    }

    public function confirmSubmit()
    {
        // Emit browser event for confirmation
        $this->dispatch(\'show-submit-confirmation\');
    }

    public function submitForReview()
    {
        $this->validate();

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($this->file_surat) {
                $filePath = $this->file_surat->store(\'surat_pdfs\', \'public\');
            }

            // Use review_kaprodi status instead of draft
            $reviewStatus = StatusSurat::where(\'kode_status\', \'review_kaprodi\')->first();
            if (!$reviewStatus) {
                $reviewStatus = StatusSurat::where(\'kode_status\', \'diajukan\')->first();
            }
            if (!$reviewStatus) {
                $reviewStatus = StatusSurat::where(\'kode_status\', \'draft\')->firstOrFail();
            }
            
            $jenisSuratDefault = JenisSurat::first();

            $surat = Surat::create([
                \'nomor_surat\' => $this->nomor_surat,
                \'perihal\' => $this->perihal,
                \'tujuan_jabatan_id\' => $this->tujuan_jabatan_id,
                \'lampiran\' => $this->lampiran,
                \'prodi_id\' => $this->prodi_id,
                \'fakultas_id\' => $this->fakultas_id,
                \'jenis_id\' => $jenisSuratDefault ? $jenisSuratDefault->id : null,
                \'status_id\' => $reviewStatus->id,
                \'created_by\' => $user->id,
                \'tanggal_surat\' => $this->tanggal_surat,
                \'sifat_surat\' => $this->sifat_surat,
                \'file_surat\' => $filePath,
            ]);

            // Send notification to Kaprodi
            $kaprodiJabatan = Jabatan::where(\'nama_jabatan\', \'Ketua Program Studi\')->first();
            if ($kaprodiJabatan && $this->prodi_id) {
                $kaprodiUsers = User::where(\'jabatan_id\', $kaprodiJabatan->id)
                    ->where(\'prodi_id\', $this->prodi_id)
                    ->get();
                foreach ($kaprodiUsers as $kaprodiUser) {
                    $notification = new NewSuratNotification($surat);
                    $notificationData = $notification->toArray($kaprodiUser);

                    CustomNotification::create([
                        \'user_id\' => $kaprodiUser->id,
                        \'type\' => $notificationData[\'type\'],
                        \'title\' => $notificationData[\'perihal\'],
                        \'message\' => \'Surat baru memerlukan review Anda\',
                        \'data\' => $notificationData,
                        \'url\' => $notificationData[\'link\'],
                    ]);
                }
            }

            // Also send to Staff Fakultas
            $staffFakultasJabatan = Jabatan::where(\'nama_jabatan\', \'Staff Fakultas\')->first();
            if ($staffFakultasJabatan) {
                $staffFakultasUsers = User::where(\'jabatan_id\', $staffFakultasJabatan->id)->get();
                foreach ($staffFakultasUsers as $staffUser) {
                    $notification = new NewSuratNotification($surat);
                    $notificationData = $notification->toArray($staffUser);

                    CustomNotification::create([
                        \'user_id\' => $staffUser->id,
                        \'type\' => $notificationData[\'type\'],
                        \'title\' => $notificationData[\'perihal\'],
                        \'message\' => $notificationData[\'message\'],
                        \'data\' => $notificationData,
                        \'url\' => $notificationData[\'link\'],
                    ]);
                }
            }

            DB::commit();
            session()->flash(\'success\', \'Surat berhasil dikirim ke Kaprodi untuk review!\');
            return redirect()->route(\'staff.surat.show\', $surat->id);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk(\'public\')->delete($filePath);
            }
            session()->flash(\'error\', \'Gagal mengirim surat: \' . $e->getMessage());
        }
    }';
        
        // Insert before the render method
        $pattern = '/(public function render\(\))/';
        $content = preg_replace($pattern, $newMethods . "\n\n$1", $content);
        
        // Save the updated component
        file_put_contents($componentFile, $content);
        echo "   ✓ Methods added successfully\n";
    } else {
        echo "   Methods already exist\n";
    }
} else {
    echo "   ERROR: Component file not found!\n";
}

// Step 2: Update the Livewire View
echo "\n2. UPDATING LIVEWIRE VIEW:\n";
$viewFile = 'resources/views/livewire/create-surat-form.blade.php';

if (file_exists($viewFile)) {
    $viewContent = file_get_contents($viewFile);
    
    // Backup
    file_put_contents($viewFile . '.backup_' . date('YmdHis'), $viewContent);
    
    // Find and replace the submit button
    echo "   Looking for submit button...\n";
    
    // Look for the existing button
    $patterns = [
        '/<button[^>]*wire:click=["\']saveSurat["\'][^>]*>.*?<\/button>/si',
        '/<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>/si',
        '/<button[^>]*>.*?(BUAT|CREATE|SUBMIT|SIMPAN).*?<\/button>/si'
    ];
    
    $replaced = false;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $viewContent, $matches)) {
            echo "   Found button to replace\n";
            
            $newButtons = '
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 justify-end mt-6">
                    <!-- Cancel Button -->
                    <a href="{{ route(\'staff.surat.index\') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-500 text-white font-medium text-sm rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-4 focus:ring-gray-300 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                    
                    <!-- Save Draft Button -->
                    <button type="button"
                            wire:click="saveDraft"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white font-medium text-sm rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveDraft">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                            </svg>
                            Simpan Draft
                        </span>
                        <span wire:loading wire:target="saveDraft" class="inline-flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                    
                    <!-- Submit for Review Button -->
                    <button type="button"
                            wire:click="confirmSubmit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-medium text-sm rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitForReview">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim ke Kaprodi
                        </span>
                        <span wire:loading wire:target="submitForReview" class="inline-flex items-center">
                            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </div>';
            
            $viewContent = str_replace($matches[0], $newButtons, $viewContent);
            $replaced = true;
            echo "   ✓ Replaced button section\n";
            break;
        }
    }
    
    if (!$replaced) {
        echo "   No button found, adding at the end of form\n";
        // Add before closing </form> or </div>
        $newButtons = '
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 justify-end mt-6">
                    <a href="{{ route(\'staff.surat.index\') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-500 text-white font-medium text-sm rounded-lg hover:bg-gray-600 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                    
                    <button type="button"
                            wire:click="saveDraft"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2.5 bg-gray-600 text-white font-medium text-sm rounded-lg hover:bg-gray-700 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
                        </svg>
                        Simpan Draft
                    </button>
                    
                    <button type="button"
                            wire:click="confirmSubmit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white font-medium text-sm rounded-lg hover:bg-blue-700 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim ke Kaprodi
                    </button>
                </div>
            </div>
        </div>';
        
        // Insert before last closing div
        $lastDiv = strrpos($viewContent, '</div>');
        $viewContent = substr($viewContent, 0, $lastDiv) . $newButtons . "\n" . substr($viewContent, $lastDiv);
    }
    
    // Add JavaScript for confirmation if not exists
    if (!str_contains($viewContent, 'show-submit-confirmation')) {
        echo "   Adding confirmation JavaScript...\n";
        
        $confirmScript = '

@push(\'scripts\')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener(\'livewire:init\', () => {
        Livewire.on(\'show-submit-confirmation\', () => {
            Swal.fire({
                title: \'Konfirmasi Pengiriman\',
                html: `
                    <div class="text-left">
                        <p class="mb-3">Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?</p>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Perhatian:</strong> Surat yang sudah dikirim tidak dapat diedit kembali.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                icon: \'warning\',
                showCancelButton: true,
                confirmButtonColor: \'#2563eb\',
                cancelButtonColor: \'#6b7280\',
                confirmButtonText: \'Ya, Kirim!\',
                cancelButtonText: \'Batal\',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: \'Mengirim Surat\',
                        text: \'Mohon tunggu...\',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Call Livewire method
                    @this.submitForReview();
                }
            });
        });
    });
</script>
@endpush';
        
        $viewContent .= $confirmScript;
        echo "   ✓ Added confirmation script\n";
    }
    
    // Save the updated view
    file_put_contents($viewFile, $viewContent);
    echo "   ✓ View file updated successfully\n";
} else {
    echo "   ERROR: View file not found!\n";
}

echo "\n=== SUMMARY ===\n";
echo "✓ Updated Livewire component with saveDraft and submitForReview methods\n";
echo "✓ Updated view with 3 buttons: Batal, Simpan Draft, Kirim ke Kaprodi\n";
echo "✓ Added SweetAlert2 confirmation for submit\n";
echo "✓ Added loading states for buttons\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Clear all caches:\n";
echo "   php artisan view:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan livewire:discover\n";
echo "\n2. Hard refresh browser (Ctrl+F5)\n";
echo "\n3. Test the new buttons:\n";
echo "   - Batal: Should redirect to index page\n";
echo "   - Simpan Draft: Save with draft status\n";
echo "   - Kirim ke Kaprodi: Show confirmation then submit for review\n";

echo "\n=== DONE ===\n";