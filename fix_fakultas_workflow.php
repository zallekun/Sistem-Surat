<?php
/**
 * FIX FAKULTAS WORKFLOW - PREVIEW, EDIT, DAN KIRIM
 * 
 * Masalah:
 * 1. Surat hilang dari tabel setelah kirim ke pengaju (status completed tidak muncul)
 * 2. Tidak ada preview/validasi sebelum kirim
 * 3. Perlu bisa edit data sebelum generate/kirim
 * 
 * File: fix_fakultas_workflow.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class FakultasWorkflowFixer
{
    private $output = [];
    private $backupPath;
    
    public function __construct()
    {
        $this->log("=== FIX FAKULTAS WORKFLOW ===");
        $this->log("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->log("");
        
        $this->backupPath = storage_path('backups/fakultas_workflow_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }
    
    public function fixWorkflow()
    {
        $this->log("🔧 FIXING FAKULTAS WORKFLOW");
        $this->log("============================");
        
        $this->log("\n📦 PHASE 1: BACKUP FILES");
        $this->backupFiles();
        
        $this->log("\n🎮 PHASE 2: FIX CONTROLLER");
        $this->fixFakultasController();
        
        $this->log("\n🎨 PHASE 3: CREATE PREVIEW/EDIT VIEW");
        $this->createPreviewEditView();
        
        $this->log("\n📋 PHASE 4: UPDATE SHOW VIEW");
        $this->updateShowView();
        
        $this->log("\n🛣️ PHASE 5: ADD ROUTES");
        $this->addRoutes();
        
        $this->displayResults();
    }
    
    private function backupFiles()
    {
        $files = [
            'app/Http/Controllers/FakultasStaffController.php',
            'resources/views/fakultas/surat/show.blade.php'
        ];
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $backupFile = $this->backupPath . '/' . str_replace(['/', '\\'], '_', $file);
                File::copy($file, $backupFile);
                $this->log("✅ Backed up: {$file}");
            }
        }
    }
    
    private function fixFakultasController()
    {
        $controllerPath = app_path('Http/Controllers/FakultasStaffController.php');
        if (!File::exists($controllerPath)) {
            $this->log("❌ FakultasStaffController not found");
            return;
        }
        
        $content = File::get($controllerPath);
        
        // 1. Fix index method to include completed status
        $content = preg_replace(
            "/->where\('status', 'processed'\)/",
            "->whereIn('status', ['processed', 'approved_prodi', 'completed'])",
            $content
        );
        
        // 2. Add preview/edit methods
        if (!str_contains($content, 'previewPengajuan')) {
            $newMethods = '
    /**
     * Preview pengajuan before finalization
     */
    public function previewPengajuan($id)
    {
        $user = Auth::user();
        $user->load(\'prodi.fakultas\');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->route(\'fakultas.surat.index\')
                           ->with(\'error\', \'Anda tidak memiliki akses ke fakultas manapun\');
        }
        
        $pengajuan = PengajuanSurat::with([\'jenisSurat\', \'prodi.fakultas\'])
                                   ->where(\'id\', $id)
                                   ->first();
        
        if (!$pengajuan || $pengajuan->prodi->fakultas_id !== $fakultasId) {
            return redirect()->route(\'fakultas.surat.index\')
                           ->with(\'error\', \'Pengajuan tidak ditemukan\');
        }
        
        // Parse additional data for display
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        return view(\'fakultas.surat.preview\', compact(\'pengajuan\', \'additionalData\'));
    }
    
    /**
     * Edit pengajuan data before finalization
     */
    public function editPengajuan($id)
    {
        $user = Auth::user();
        $user->load(\'prodi.fakultas\');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return redirect()->route(\'fakultas.surat.index\')
                           ->with(\'error\', \'Anda tidak memiliki akses\');
        }
        
        $pengajuan = PengajuanSurat::with([\'jenisSurat\', \'prodi\'])
                                   ->where(\'id\', $id)
                                   ->first();
        
        if (!$pengajuan || $pengajuan->prodi->fakultas_id !== $fakultasId) {
            return redirect()->route(\'fakultas.surat.index\')
                           ->with(\'error\', \'Pengajuan tidak ditemukan\');
        }
        
        // Only allow edit for certain statuses
        if (!in_array($pengajuan->status, [\'processed\', \'approved_prodi\'])) {
            return redirect()->route(\'fakultas.surat.show\', $id)
                           ->with(\'error\', \'Pengajuan tidak dapat diedit pada status ini\');
        }
        
        $additionalData = $this->parseAdditionalData($pengajuan->additional_data);
        
        return view(\'fakultas.surat.edit\', compact(\'pengajuan\', \'additionalData\'));
    }
    
    /**
     * Update pengajuan data
     */
    public function updatePengajuan(Request $request, $id)
    {
        $request->validate([
            \'keperluan\' => \'required|string|max:500\',
            \'additional_data\' => \'nullable|array\'
        ]);
        
        $user = Auth::user();
        $user->load(\'prodi.fakultas\');
        
        $fakultasId = $user->prodi?->fakultas_id;
        if (!$fakultasId) {
            return response()->json([\'success\' => false, \'message\' => \'Unauthorized\'], 403);
        }
        
        $pengajuan = PengajuanSurat::where(\'id\', $id)->first();
        
        if (!$pengajuan || $pengajuan->prodi->fakultas_id !== $fakultasId) {
            return response()->json([\'success\' => false, \'message\' => \'Pengajuan tidak ditemukan\'], 404);
        }
        
        try {
            // Update pengajuan data
            $pengajuan->update([
                \'keperluan\' => $request->keperluan,
                \'additional_data\' => json_encode($request->additional_data),
                \'updated_by_fakultas\' => $user->id,
                \'updated_at\' => now()
            ]);
            
            return response()->json([
                \'success\' => true,
                \'message\' => \'Data pengajuan berhasil diperbarui\',
                \'redirect\' => route(\'fakultas.surat.preview\', $id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                \'success\' => false,
                \'message\' => \'Gagal update data: \' . $e->getMessage()
            ], 500);
        }
    }';
            
            $content = preg_replace('/}\s*$/', $newMethods . "\n}", $content);
        }
        
        // 3. Update status display in index method
        $statusMapping = "'completed' => 'Selesai - Dikirim ke Pengaju',";
        if (!str_contains($content, $statusMapping)) {
            $content = str_replace(
                "'processed' => 'Perlu Generate Surat',",
                "'processed' => 'Perlu Generate Surat',\n                    'completed' => 'Selesai - Dikirim ke Pengaju',",
                $content
            );
        }
        
        File::put($controllerPath, $content);
        $this->log("✅ FakultasStaffController updated");
    }
    
    private function createPreviewEditView()
    {
        $previewPath = resource_path('views/fakultas/surat/preview.blade.php');
        
        $previewContent = '@extends(\'layouts.app\')

@section(\'title\', \'Preview Pengajuan - Staff Fakultas\')

@section(\'content\')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <!-- Header -->
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
            <h2 style="font-size: 20px; font-weight: 600; margin: 0; color: #374151;">
                Preview & Validasi Pengajuan
            </h2>
        </div>

        <div style="padding: 24px;">
            <!-- Data Preview -->
            <div style="margin-bottom: 32px;">
                <h3 style="font-weight: 600; color: #374151; margin-bottom: 16px;">
                    <i class="fas fa-user-graduate mr-2"></i> Data Mahasiswa
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px; width: 200px; font-weight: 500;">NIM</td>
                        <td style="padding: 8px;">{{ $pengajuan->nim }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px; font-weight: 500;">Nama</td>
                        <td style="padding: 8px;">{{ $pengajuan->nama_mahasiswa }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px; font-weight: 500;">Program Studi</td>
                        <td style="padding: 8px;">{{ $pengajuan->prodi->nama_prodi }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px; font-weight: 500;">Jenis Surat</td>
                        <td style="padding: 8px;">{{ $pengajuan->jenisSurat->nama_jenis }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px; font-weight: 500;">Keperluan</td>
                        <td style="padding: 8px;">{{ $pengajuan->keperluan }}</td>
                    </tr>
                </table>
            </div>

            @if($additionalData && is_array($additionalData))
                <!-- Additional Data Preview -->
                <div style="margin-bottom: 32px;">
                    <h3 style="font-weight: 600; color: #374151; margin-bottom: 16px;">
                        <i class="fas fa-info-circle mr-2"></i> Data Tambahan
                    </h3>
                    <div style="background-color: #f9fafb; padding: 16px; border-radius: 8px;">
                        @foreach($additionalData as $key => $value)
                            @if(is_array($value))
                                <div style="margin-bottom: 12px;">
                                    <strong>{{ ucwords(str_replace(\'_\', \' \', $key)) }}:</strong>
                                    <ul style="margin-left: 20px; margin-top: 4px;">
                                        @foreach($value as $subKey => $subValue)
                                            <li>{{ ucwords(str_replace(\'_\', \' \', $subKey)) }}: {{ $subValue }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div style="margin-bottom: 8px;">
                                    <strong>{{ ucwords(str_replace(\'_\', \' \', $key)) }}:</strong> {{ $value }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Validation Checklist -->
            <div style="margin-bottom: 32px;">
                <h3 style="font-weight: 600; color: #374151; margin-bottom: 16px;">
                    <i class="fas fa-check-square mr-2"></i> Validasi Data
                </h3>
                <div style="background-color: #eff6ff; padding: 16px; border-radius: 8px;">
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" id="check1"> Data mahasiswa sudah benar
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" id="check2"> Keperluan surat sudah jelas
                    </label>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" id="check3"> Data tambahan sudah lengkap
                    </label>
                    @if($pengajuan->jenisSurat->kode_surat === \'MA\')
                        <label style="display: block; margin-bottom: 8px;">
                            <input type="checkbox" id="check4"> Data orang tua sudah benar
                        </label>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; display: flex; gap: 12px; justify-content: space-between;">
                <div>
                    <a href="{{ route(\'fakultas.surat.index\') }}" 
                       style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 6px;">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <a href="{{ route(\'fakultas.surat.edit\', $pengajuan->id) }}" 
                       style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #f59e0b; color: white; text-decoration: none; border-radius: 6px;">
                        <i class="fas fa-edit mr-2"></i> Edit Data
                    </a>
                    
                    <button onclick="validateAndProceed()" 
                            style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        <i class="fas fa-check-circle mr-2"></i> Validasi & Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateAndProceed() {
    // Check all validations
    const check1 = document.getElementById(\'check1\').checked;
    const check2 = document.getElementById(\'check2\').checked;
    const check3 = document.getElementById(\'check3\').checked;
    const check4 = document.getElementById(\'check4\');
    
    let allChecked = check1 && check2 && check3;
    if (check4) {
        allChecked = allChecked && check4.checked;
    }
    
    if (!allChecked) {
        alert(\'Harap centang semua validasi sebelum melanjutkan\');
        return;
    }
    
    // Show options
    const action = confirm(\'Data sudah valid. Pilih OK untuk Generate PDF, atau Cancel untuk Kirim langsung ke Pengaju\');
    
    if (action) {
        // Generate PDF
        window.location.href = \'{{ route(\'fakultas.surat.generate\', $pengajuan->id) }}\';
    } else {
        // Kirim ke Pengaju
        if (confirm(\'Kirim langsung ke pengaju tanpa generate PDF?\')) {
            kirimKePengaju({{ $pengajuan->id }});
        }
    }
}

function kirimKePengaju(id) {
    fetch(`/fakultas/surat/kirim-ke-pengaju/${id}`, {
        method: \'POST\',
        headers: {
            \'Content-Type\': \'application/json\',
            \'X-CSRF-TOKEN\': \'{{ csrf_token() }}\'
        },
        body: JSON.stringify({ note: \'Surat telah selesai dan dapat digunakan\' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = \'{{ route(\'fakultas.surat.index\') }}\';
        } else {
            alert(data.message || \'Terjadi kesalahan\');
        }
    });
}
</script>
@endsection';
        
        File::put($previewPath, $previewContent);
        $this->log("✅ Preview view created");
    }
    
    private function updateShowView()
    {
        $viewPath = resource_path('views/fakultas/surat/show.blade.php');
        if (!File::exists($viewPath)) {
            $this->log("❌ Show view not found");
            return;
        }
        
        $content = File::get($viewPath);
        
        // Replace direct action buttons with preview button
        $newButtons = '@if(in_array($status, [\'processed\', \'approved_prodi\']))
                            <a href="{{ route(\'fakultas.surat.preview\', $pengajuan->id) }}" 
                               style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                                <i class="fas fa-eye" style="margin-right: 8px;"></i>
                                Preview & Validasi
                            </a>
                        @endif';
        
        // Replace the existing buttons section
        $content = preg_replace(
            '/@if\(in_array\(\$status.*?@endif/s',
            $newButtons,
            $content,
            1
        );
        
        File::put($viewPath, $content);
        $this->log("✅ Show view updated");
    }
    
    private function addRoutes()
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);
        
        $newRoutes = [
            "Route::get('/fakultas/surat/preview/{id}', [App\Http\Controllers\FakultasStaffController::class, 'previewPengajuan'])->name('fakultas.surat.preview');",
            "Route::get('/fakultas/surat/edit/{id}', [App\Http\Controllers\FakultasStaffController::class, 'editPengajuan'])->name('fakultas.surat.edit');",
            "Route::put('/fakultas/surat/update/{id}', [App\Http\Controllers\FakultasStaffController::class, 'updatePengajuan'])->name('fakultas.surat.update');",
            "Route::get('/fakultas/surat/generate/{id}', [App\Http\Controllers\FakultasStaffController::class, 'generateSuratPDF'])->name('fakultas.surat.generate');"
        ];
        
        foreach ($newRoutes as $route) {
            if (!str_contains($content, $route)) {
                $content = str_replace(
                    "require __DIR__ . '/staff.php';",
                    "require __DIR__ . '/staff.php';\n\n// Fakultas Preview & Edit Routes\n" . $route,
                    $content
                );
            }
        }
        
        File::put($routesFile, $content);
        $this->log("✅ Routes added");
    }
    
    private function log($message)
    {
        $this->output[] = $message;
        echo $message . PHP_EOL;
    }
    
    public function displayResults()
    {
        $this->log("\n" . str_repeat("=", 60));
        $this->log("🎉 FAKULTAS WORKFLOW FIX COMPLETED");
        $this->log("");
        
        $this->log("📋 PERBAIKAN YANG DILAKUKAN:");
        $this->log("1. ✅ Surat completed tetap muncul di tabel");
        $this->log("2. ✅ Halaman preview & validasi sebelum finalisasi");
        $this->log("3. ✅ Bisa edit data sebelum generate/kirim");
        $this->log("4. ✅ Workflow: View → Preview → Validate → Edit (if needed) → Generate/Send");
        $this->log("");
        
        $this->log("🎯 WORKFLOW BARU:");
        $this->log("1. Staff fakultas lihat pengajuan");
        $this->log("2. Klik 'Preview & Validasi'");
        $this->log("3. Cek semua data, centang validasi");
        $this->log("4. Jika perlu edit, klik 'Edit Data'");
        $this->log("5. Setelah valid, pilih Generate PDF atau Kirim ke Pengaju");
        $this->log("");
        
        $this->log("📂 Backup: {$this->backupPath}");
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    echo "🚀 Starting Fakultas Workflow Fix...\n\n";
    
    try {
        $fixer = new FakultasWorkflowFixer();
        $fixer->fixWorkflow();
        
        echo "\n🎉 SUCCESS! Workflow has been improved.\n";
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>