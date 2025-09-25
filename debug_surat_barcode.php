<?php
/**
 * Debug Script untuk Implementasi Surat dengan Barcode TTD Fakultas
 * File: debug_surat_barcode.php
 * 
 * Instruksi:
 * 1. Simpan file ini di root Laravel project
 * 2. Jalankan: php debug_surat_barcode.php
 * 3. Script akan otomatis backup, create files, dan setup database
 * 4. Untuk restore: php debug_surat_barcode.php restore
 */

// Configuration
$config = [
    'backup_dir' => 'storage/debug_backups_' . date('Y-m-d_H-i-s'),
    'log_file' => 'storage/logs/debug_surat_barcode.log',
];

// ANSI Colors
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

class SuratBarcodeDebugger {
    private $config;
    private $backupFiles = [];
    private $createdFiles = [];
    private $errors = [];
    
    public function __construct($config) {
        $this->config = $config;
        $this->createBackupDir();
    }
    
    public function run() {
        $this->printHeader();
        
        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === 'restore') {
            $this->restoreBackups();
            return;
        }
        
        try {
            // Run all steps
            $this->step1_CheckEnvironment();
            $this->step2_BackupExistingFiles();
            $this->step3_CreateMigrations();
            $this->step4_CreateModels();
            $this->step5_CreateControllers();
            $this->step6_CreateViews();
            $this->step7_UpdateRoutes();
            $this->step8_CreateStorageDirectories();
            $this->step9_RunMigrations();
            $this->step10_SeedTestData();
            
            $this->printSuccess("\n✅ INSTALLATION COMPLETED SUCCESSFULLY!");
            $this->printInstructions();
            
        } catch (Exception $e) {
            $this->printError("\n❌ ERROR: " . $e->getMessage());
            $this->printInfo("\n🔄 Running rollback...");
            $this->restoreBackups();
        }
    }
    
    private function printHeader() {
        echo Colors::CYAN . Colors::BOLD . "\n";
        echo "=====================================\n";
        echo "   SURAT BARCODE TTD DEBUG SCRIPT   \n";
        echo "=====================================\n";
        echo Colors::RESET . "\n";
    }
    
    private function printStep($step, $title) {
        echo Colors::BLUE . Colors::BOLD . "\n[$step] " . Colors::RESET . Colors::YELLOW . $title . Colors::RESET . "\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    private function printInfo($message) {
        echo Colors::CYAN . "[INFO] " . Colors::RESET . $message . "\n";
    }
    
    private function printSuccess($message) {
        echo Colors::GREEN . "[SUCCESS] " . Colors::RESET . $message . "\n";
    }
    
    private function printError($message) {
        echo Colors::RED . "[ERROR] " . Colors::RESET . $message . "\n";
        $this->errors[] = $message;
    }
    
    private function printWarning($message) {
        echo Colors::YELLOW . "[WARNING] " . Colors::RESET . $message . "\n";
    }
    
    private function createBackupDir() {
        if (!is_dir($this->config['backup_dir'])) {
            mkdir($this->config['backup_dir'], 0755, true);
        }
    }
    
    private function backupFile($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $backupPath = $this->config['backup_dir'] . '/' . str_replace('/', '_', $filePath);
        
        if (copy($filePath, $backupPath)) {
            $this->backupFiles[$filePath] = $backupPath;
            $this->printSuccess("Backed up: " . basename($filePath));
            return true;
        }
        
        return false;
    }
    
    // STEP 1: Check Environment
    private function step1_CheckEnvironment() {
        $this->printStep(1, "Checking Environment");
        
        // Check Laravel
        if (!file_exists('artisan')) {
            throw new Exception("Not in Laravel root directory!");
        }
        $this->printSuccess("Laravel root detected");
        
        // Check composer packages
        $composerJson = json_decode(file_get_contents('composer.json'), true);
        $requiredPackages = [
            'barryvdh/laravel-dompdf' => 'PDF generation',
            'simplesoftwareio/simple-qrcode' => 'QR Code generation (optional)'
        ];
        
        foreach ($requiredPackages as $package => $description) {
            if (isset($composerJson['require'][$package])) {
                $this->printSuccess("Package found: $package");
            } else {
                $this->printWarning("Package not found: $package ($description)");
                $this->printInfo("Install with: composer require $package");
            }
        }
        
        // Check database connection
        try {
            require_once 'vendor/autoload.php';
            $app = require_once 'bootstrap/app.php';
            $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->bootstrap();
            
            \DB::connection()->getPdo();
            $this->printSuccess("Database connection OK");
        } catch (Exception $e) {
            $this->printWarning("Database connection failed: " . $e->getMessage());
        }
    }
    
    // STEP 2: Backup Existing Files
    private function step2_BackupExistingFiles() {
        $this->printStep(2, "Backing Up Existing Files");
        
        $filesToBackup = [
            'routes/web.php',
            'app/Http/Controllers/FakultasStaffController.php',
            'app/Http/Controllers/PublicSuratController.php',
        ];
        
        foreach ($filesToBackup as $file) {
            if (file_exists($file)) {
                $this->backupFile($file);
            }
        }
    }
    
    // STEP 3: Create Migrations
    private function step3_CreateMigrations() {
        $this->printStep(3, "Creating Migration Files");
        
        // Migration for barcode_signatures
        $migrationBarcode = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarcodeSignaturesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable(\'barcode_signatures\')) {
            Schema::create(\'barcode_signatures\', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger(\'fakultas_id\')->nullable();
                $table->string(\'pejabat_nama\');
                $table->string(\'pejabat_nid\')->nullable();
                $table->string(\'pejabat_jabatan\');
                $table->string(\'pejabat_pangkat\')->nullable();
                $table->string(\'barcode_path\');
                $table->boolean(\'is_active\')->default(true);
                $table->text(\'description\')->nullable();
                $table->timestamps();
                
                $table->index(\'fakultas_id\');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists(\'barcode_signatures\');
    }
}';

        // Migration for surat_generated
        $migrationSurat = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratGeneratedTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable(\'surat_generated\')) {
            Schema::create(\'surat_generated\', function (Blueprint $table) {
                $table->id();
                $table->foreignId(\'pengajuan_id\')->constrained(\'pengajuan_surat\');
                $table->string(\'nomor_surat\')->unique();
                $table->foreignId(\'barcode_signature_id\')->nullable();
                $table->string(\'file_path\')->nullable();
                $table->foreignId(\'generated_by\')->constrained(\'users\');
                $table->string(\'signed_by\')->nullable();
                $table->timestamp(\'signed_at\')->nullable();
                $table->enum(\'status\', [\'draft\', \'generated\', \'signed\', \'completed\'])->default(\'generated\');
                $table->timestamps();
                
                $table->index(\'pengajuan_id\');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists(\'surat_generated\');
    }
}';

        // Save migrations
        $timestamp = date('Y_m_d_His');
        $migrationPath1 = "database/migrations/{$timestamp}_create_barcode_signatures_table.php";
        $migrationPath2 = "database/migrations/{$timestamp}_create_surat_generated_table.php";
        
        file_put_contents($migrationPath1, $migrationBarcode);
        file_put_contents($migrationPath2, $migrationSurat);
        
        $this->createdFiles[] = $migrationPath1;
        $this->createdFiles[] = $migrationPath2;
        
        $this->printSuccess("Created migration: barcode_signatures");
        $this->printSuccess("Created migration: surat_generated");
    }
    
    // STEP 4: Create Models
    private function step4_CreateModels() {
        $this->printStep(4, "Creating Model Files");
        
        // BarcodeSignature Model
        $modelBarcode = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BarcodeSignature extends Model
{
    protected $table = \'barcode_signatures\';
    
    protected $fillable = [
        \'fakultas_id\',
        \'pejabat_nama\',
        \'pejabat_nid\',
        \'pejabat_jabatan\',
        \'pejabat_pangkat\',
        \'barcode_path\',
        \'is_active\',
        \'description\'
    ];
    
    protected $casts = [
        \'is_active\' => \'boolean\'
    ];
    
    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }
    
    public function suratGenerated()
    {
        return $this->hasMany(SuratGenerated::class);
    }
    
    public function getBarcodeUrlAttribute()
    {
        return $this->barcode_path ? Storage::url($this->barcode_path) : null;
    }
}';

        // SuratGenerated Model
        $modelSurat = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SuratGenerated extends Model
{
    protected $table = \'surat_generated\';
    
    protected $fillable = [
        \'pengajuan_id\',
        \'nomor_surat\',
        \'barcode_signature_id\',
        \'file_path\',
        \'generated_by\',
        \'signed_by\',
        \'signed_at\',
        \'status\'
    ];
    
    protected $dates = [\'signed_at\', \'created_at\', \'updated_at\'];
    
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanSurat::class);
    }
    
    public function barcodeSignature()
    {
        return $this->belongsTo(BarcodeSignature::class);
    }
    
    public function generator()
    {
        return $this->belongsTo(User::class, \'generated_by\');
    }
    
    public function getDownloadUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}';

        // Save models
        $modelPath1 = "app/Models/BarcodeSignature.php";
        $modelPath2 = "app/Models/SuratGenerated.php";
        
        if (!file_exists($modelPath1)) {
            file_put_contents($modelPath1, $modelBarcode);
            $this->createdFiles[] = $modelPath1;
            $this->printSuccess("Created model: BarcodeSignature");
        } else {
            $this->printWarning("Model already exists: BarcodeSignature");
        }
        
        if (!file_exists($modelPath2)) {
            file_put_contents($modelPath2, $modelSurat);
            $this->createdFiles[] = $modelPath2;
            $this->printSuccess("Created model: SuratGenerated");
        } else {
            $this->printWarning("Model already exists: SuratGenerated");
        }
    }
    
    // STEP 5: Create Controllers
    private function step5_CreateControllers() {
        $this->printStep(5, "Creating Controller Files");
        
        // Directly use the controller template
        $controllerContent = $this->getControllerTemplate();
        
        $controllerPath = "app/Http/Controllers/SuratFSIController.php";
        
        if (!file_exists($controllerPath)) {
            file_put_contents($controllerPath, $controllerContent);
            $this->createdFiles[] = $controllerPath;
            $this->printSuccess("Created controller: SuratFSIController");
        } else {
            $this->printWarning("Controller already exists: SuratFSIController");
        }
    }
    
    // STEP 6: Create Views
    private function step6_CreateViews() {
        $this->printStep(6, "Creating View Files");
        
        // Create directories
        $viewDirs = [
            'resources/views/surat',
            'resources/views/surat/fsi',
            'resources/views/surat/pdf',
            'resources/views/admin/barcode-signatures'
        ];
        
        foreach ($viewDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->printSuccess("Created directory: $dir");
            }
        }
        
        // Create preview view
        $previewView = $this->getPreviewViewTemplate();
        $viewPath = "resources/views/surat/fsi/preview-with-signature.blade.php";
        
        if (!file_exists($viewPath)) {
            file_put_contents($viewPath, $previewView);
            $this->createdFiles[] = $viewPath;
            $this->printSuccess("Created view: preview-with-signature");
        }
        
        // Create PDF template
        $pdfView = $this->getPdfViewTemplate();
        $pdfPath = "resources/views/surat/pdf/fsi-surat-final.blade.php";
        
        if (!file_exists($pdfPath)) {
            file_put_contents($pdfPath, $pdfView);
            $this->createdFiles[] = $pdfPath;
            $this->printSuccess("Created view: fsi-surat-final");
        }
    }
    
    // STEP 7: Update Routes
    private function step7_UpdateRoutes() {
        $this->printStep(7, "Updating Routes");
        
        $routesPath = "routes/web.php";
        $routesContent = file_get_contents($routesPath);
        
        $newRoutes = "
// FSI Surat Routes for Fakultas
Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas/surat/fsi')->group(function () {
    Route::get('/preview/{id}', [App\Http\Controllers\SuratFSIController::class, 'preview'])
        ->name('fakultas.surat.fsi.preview');
    Route::post('/generate-pdf/{id}', [App\Http\Controllers\SuratFSIController::class, 'generatePdf'])
        ->name('fakultas.surat.fsi.generate-pdf');
});

// Admin Barcode Management
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('barcode-signatures', App\Http\Controllers\Admin\BarcodeSignatureController::class);
});
";
        
        // Check if routes already exist
        if (strpos($routesContent, 'fakultas/surat/fsi') === false) {
            // Add routes before the last closing
            $routesContent = str_replace(
                "});", 
                "});\n" . $newRoutes . "\n", 
                $routesContent
            );
            
            file_put_contents($routesPath, $routesContent);
            $this->printSuccess("Routes added successfully");
        } else {
            $this->printWarning("Routes already exist");
        }
    }
    
    // STEP 8: Create Storage Directories
    private function step8_CreateStorageDirectories() {
        $this->printStep(8, "Creating Storage Directories");
        
        $directories = [
            'storage/app/public/barcode-signatures',
            'storage/app/public/surat/generated',
            'storage/app/public/signatures'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->printSuccess("Created directory: $dir");
                
                // Create .gitignore
                file_put_contents($dir . '/.gitignore', "*\n!.gitignore");
            }
        }
        
        // Create storage link
        if (!file_exists('public/storage')) {
            exec('php artisan storage:link', $output, $returnCode);
            if ($returnCode === 0) {
                $this->printSuccess("Storage link created");
            } else {
                $this->printWarning("Failed to create storage link");
            }
        }
    }
    
    // STEP 9: Run Migrations
    private function step9_RunMigrations() {
        $this->printStep(9, "Running Migrations");
        
        $this->printInfo("Running: php artisan migrate");
        exec('php artisan migrate --force', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->printSuccess("Migrations completed");
            foreach ($output as $line) {
                $this->printInfo($line);
            }
        } else {
            $this->printWarning("Migration might have failed or tables already exist");
        }
    }
    
    // STEP 10: Seed Test Data
    private function step10_SeedTestData() {
        $this->printStep(10, "Creating Test Data");
        
        try {
            // Create test barcode signature
            $testBarcode = \App\Models\BarcodeSignature::firstOrCreate([
                'pejabat_nama' => 'AGUS KOMARUDIN, S.Kom., M.T.'
            ], [
                'fakultas_id' => 1,
                'pejabat_nid' => '4121 758 78',
                'pejabat_jabatan' => 'WAKIL DEKAN III',
                'pejabat_pangkat' => 'PENATA MUDA TK.I – III/B',
                'barcode_path' => 'barcode-signatures/test_barcode.png',
                'is_active' => true,
                'description' => 'Test barcode for FSI UNJANI'
            ]);
            
            $this->printSuccess("Test barcode signature created");
            
            // Create dummy barcode image
            $dummyBarcode = $this->createDummyBarcodeImage();
            if ($dummyBarcode) {
                $this->printSuccess("Dummy barcode image created");
            }
            
        } catch (Exception $e) {
            $this->printWarning("Could not create test data: " . $e->getMessage());
        }
    }
    
    // Helper: Create dummy barcode image
    private function createDummyBarcodeImage() {
        $path = storage_path('app/public/barcode-signatures/test_barcode.png');
        
        if (!file_exists($path) && extension_loaded('gd')) {
            $img = imagecreate(200, 100);
            $bg = imagecolorallocate($img, 255, 255, 255);
            $text_color = imagecolorallocate($img, 0, 0, 0);
            imagestring($img, 5, 50, 40, "BARCODE", $text_color);
            
            // Draw some lines to simulate barcode
            for ($i = 10; $i < 190; $i += 5) {
                imageline($img, $i, 20, $i, 80, $text_color);
            }
            
            imagepng($img, $path);
            imagedestroy($img);
            return true;
        }
        
        return false;
    }
    
    // Restore backups
    private function restoreBackups() {
        $this->printInfo("\n🔄 Restoring backups...\n");
        
        foreach ($this->backupFiles as $originalFile => $backupFile) {
            if (file_exists($backupFile)) {
                if (copy($backupFile, $originalFile)) {
                    $this->printSuccess("Restored: " . basename($originalFile));
                }
            }
        }
        
        // Delete created files
        foreach ($this->createdFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
                $this->printInfo("Removed: " . basename($file));
            }
        }
        
        $this->printSuccess("\n✅ Restore completed");
    }
    
    // Print final instructions
    private function printInstructions() {
        echo Colors::CYAN . Colors::BOLD . "\n";
        echo "=====================================\n";
        echo "         NEXT STEPS                  \n";
        echo "=====================================\n";
        echo Colors::RESET;
        
        echo Colors::GREEN . "\n✅ Installation successful! Here's what to do next:\n\n" . Colors::RESET;
        
        echo Colors::YELLOW . "1. Upload Barcode Images:\n" . Colors::RESET;
        echo "   - Place barcode images in: storage/app/public/barcode-signatures/\n";
        echo "   - Format: PNG or JPG\n";
        echo "   - Recommended size: 200x100 pixels\n\n";
        
        echo Colors::YELLOW . "2. Test the System:\n" . Colors::RESET;
        echo "   - Login as staff fakultas\n";
        echo "   - Go to pengajuan list\n";
        echo "   - Click 'Preview Surat' on approved pengajuan\n";
        echo "   - Select barcode and generate PDF\n\n";
        
        echo Colors::YELLOW . "3. Configure Barcode Signatures:\n" . Colors::RESET;
        echo "   - Access admin panel: /admin/barcode-signatures\n";
        echo "   - Add barcode for each pejabat\n";
        echo "   - Assign to appropriate fakultas\n\n";
        
        echo Colors::YELLOW . "4. Required Packages (if not installed):\n" . Colors::RESET;
        echo "   composer require barryvdh/laravel-dompdf\n";
        echo "   composer require simplesoftwareio/simple-qrcode (optional)\n\n";
        
        if (count($this->errors) > 0) {
            echo Colors::RED . "⚠️  Some errors occurred:\n" . Colors::RESET;
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
        }
        
        echo Colors::GREEN . "\n🎉 System is ready to use!\n" . Colors::RESET;
        echo Colors::CYAN . "For issues or rollback, run: php debug_surat_barcode.php restore\n\n" . Colors::RESET;
    }
    
    // Template methods
    private function getControllerTemplate() {
        return '<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use App\Models\SuratGenerated;
use App\Models\BarcodeSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuratFSIController extends Controller
{
    public function preview($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole("staff_fakultas")) {
            abort(403, "Unauthorized");
        }
        
        $pengajuan = PengajuanSurat::with(["jenisSurat", "prodi.fakultas"])
            ->findOrFail($id);
        
        $barcodeSignatures = BarcodeSignature::where("is_active", true)->get();
        
        $nomorSurat = $this->generateNomorSurat($pengajuan);
        
        $additionalData = json_decode($pengajuan->additional_data, true);
        
        $data = [
            "pengajuan" => $pengajuan,
            "additionalData" => $additionalData,
            "nomorSurat" => $nomorSurat,
            "tanggalSurat" => now()->locale("id")->isoFormat("D MMMM Y"),
            "barcodeSignatures" => $barcodeSignatures,
            "isPreview" => true
        ];
        
        return view("surat.fsi.preview-with-signature", $data);
    }
    
    public function generatePdf(Request $request, $id)
    {
        $request->validate([
            "barcode_signature_id" => "required|exists:barcode_signatures,id"
        ]);
        
        $user = Auth::user();
        
        if (!$user->hasRole("staff_fakultas")) {
            abort(403);
        }
        
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanSurat::findOrFail($id);
            $additionalData = json_decode($pengajuan->additional_data, true);
            
            $barcodeSignature = BarcodeSignature::findOrFail($request->barcode_signature_id);
            
            $nomorSurat = $this->generateNomorSurat($pengajuan);
            
            $suratGenerated = SuratGenerated::create([
                "pengajuan_id" => $pengajuan->id,
                "nomor_surat" => $nomorSurat,
                "barcode_signature_id" => $barcodeSignature->id,
                "file_path" => null,
                "generated_by" => $user->id,
                "signed_by" => $barcodeSignature->pejabat_nama,
                "signed_at" => now(),
                "status" => "completed"
            ]);
            
            $data = [
                "pengajuan" => $pengajuan,
                "additionalData" => $additionalData,
                "nomorSurat" => $nomorSurat,
                "tanggalSurat" => now()->locale("id")->isoFormat("D MMMM Y"),
                "barcodeImage" => $this->getBarcodeImage($barcodeSignature),
                "penandatangan" => [
                    "nama" => $barcodeSignature->pejabat_nama,
                    "pangkat" => $barcodeSignature->pejabat_pangkat,
                    "jabatan" => $barcodeSignature->pejabat_jabatan,
                    "nid" => $barcodeSignature->pejabat_nid
                ]
            ];
            
            $pdf = PDF::loadView("surat.pdf.fsi-surat-final", $data);
            $pdf->setPaper("A4", "portrait");
            
            $fileName = "surat_" . $pengajuan->nim . "_" . time() . ".pdf";
            $filePath = "surat/generated/" . $fileName;
            Storage::put("public/" . $filePath, $pdf->output());
            
            $suratGenerated->update(["file_path" => $filePath]);
            
            $pengajuan->update([
                "status" => "completed",
                "completed_at" => now()
            ]);
            
            DB::commit();
            
            return $pdf->download("Surat_Pernyataan_" . $pengajuan->nim . ".pdf");
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with("error", "Gagal generate surat: " . $e->getMessage());
        }
    }
    
    private function getBarcodeImage($barcodeSignature)
    {
        if ($barcodeSignature->barcode_path && Storage::exists($barcodeSignature->barcode_path)) {
            return base64_encode(Storage::get($barcodeSignature->barcode_path));
        }
        return null;
    }
    
    private function generateNomorSurat($pengajuan)
    {
        $lastNumber = SuratGenerated::whereYear("created_at", date("Y"))->count();
        $nomorUrut = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
        $bulanRomawi = $this->getRomanMonth(date("n"));
        $tahun = date("Y");
        
        return "P/{$nomorUrut}/FSI-UNJANI/{$bulanRomawi}/{$tahun}";
    }
    
    private function getRomanMonth($month)
    {
        $romans = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        return $romans[$month - 1];
    }
}';
    }
    
    private function getPreviewViewTemplate() {
        return '@extends("layouts.app")

@section("content")
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Preview Surat Pernyataan Masih Kuliah</h1>
                <a href="{{ route(\'fakultas.surat.index\') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
        
        @if($barcodeSignatures->count() > 0)
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="font-semibold text-lg mb-4">Pilih Barcode Tanda Tangan</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($barcodeSignatures as $barcode)
                    <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer barcode-option" 
                         data-id="{{ $barcode->id }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">{{ $barcode->pejabat_nama }}</p>
                                <p class="text-sm text-gray-600">{{ $barcode->pejabat_jabatan }}</p>
                            </div>
                            <input type="radio" name="barcode_signature_id" value="{{ $barcode->id }}">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <div class="mt-6 flex justify-end space-x-3">
            <button onclick="generatePDF()" class="btn btn-primary" id="generateBtn">
                <i class="fas fa-file-pdf mr-2"></i>Generate PDF
            </button>
        </div>
    </div>
</div>

<script>
function generatePDF() {
    const selectedBarcode = document.querySelector(\'input[name="barcode_signature_id"]:checked\');
    
    if (!selectedBarcode) {
        alert(\'Pilih barcode tanda tangan terlebih dahulu!\');
        return;
    }
    
    fetch(`/fakultas/surat/fsi/generate-pdf/{{ $pengajuan->id }}`, {
        method: \'POST\',
        headers: {
            \'Content-Type\': \'application/json\',
            \'X-CSRF-TOKEN\': \'{{ csrf_token() }}\'
        },
        body: JSON.stringify({
            barcode_signature_id: selectedBarcode.value
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement(\'a\');
        a.href = url;
        a.download = \'Surat_Pernyataan.pdf\';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    });
}
</script>
@endsection';
    }
    
    private function getPdfViewTemplate() {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $nomorSurat }}</title>
    <style>
        @page {
            size: A4;
            margin: 2cm 2.5cm 2cm 2.5cm;
        }
        
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        /* KOP Surat dengan 3 kolom */
        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .kop-surat td {
            vertical-align: middle;
            text-align: center;
        }
        
        .kop-logo-left {
            width: 15%;
        }
        
        .kop-text {
            width: 70%;
            font-weight: bold;
            line-height: 1.3;
        }
        
        .kop-logo-right {
            width: 15%;
        }
        
        .judul-surat {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        
        .judul-surat h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .nomor-surat {
            margin-top: 5px;
            font-size: 12pt;
        }
        
        .content {
            text-align: justify;
            margin-top: 30px;
        }
        
        .content p {
            margin: 10px 0;
        }
        
        .data-table {
            margin: 15px 0 15px 30px;
        }
        
        .data-table tr td {
            padding: 2px 0;
            vertical-align: top;
        }
        
        .data-table tr td:first-child {
            width: 180px;
        }
        
        .data-table tr td:nth-child(2) {
            width: 20px;
            text-align: center;
        }
        
        .signature-area {
            margin-top: 50px;
            float: right;
            text-align: center;
            width: 250px;
        }
        
        .signature-area p {
            margin: 5px 0;
        }
        
        .barcode-ttd {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT UNJANI FSI -->
    <table class="kop-surat">
        <tr>
            <td class="kop-logo-left">
                <!-- Logo YKEP jika ada -->
            </td>
            <td class="kop-text">
                <div style="font-size: 14pt;">YAYASAN KARTIKA EKA PAKSI</div>
                <div style="font-size: 14pt;">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                <div style="font-size: 14pt;">FAKULTAS SAINS DAN INFORMATIKA</div>
                <div style="font-size: 14pt; font-weight: bold;">(FSI)</div>
                <div style="font-size: 11pt; font-weight: normal; margin-top: 8px;">
                    Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6650646
                </div>
            </td>
            <td class="kop-logo-right">
                <!-- Logo UNJANI jika ada -->
            </td>
        </tr>
    </table>
    
    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        <h3>SURAT PERNYATAAN MASIH KULIAH</h3>
        <div class="nomor-surat">
            NOMOR: {{ $nomorSurat }}
        </div>
    </div>
    
    <!-- ISI SURAT -->
    <div class="content">
        <p>Yang bertanda tangan di bawah ini :</p>
        
        <table class="data-table">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ $penandatangan["nama"] }}</td>
            </tr>
            <tr>
                <td>Pangkat/Golongan</td>
                <td>:</td>
                <td>{{ $penandatangan["pangkat"] }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $penandatangan["jabatan"] }} FAKULTAS SAINS DAN INFORMATIKA UNJANI</td>
            </tr>
        </table>
        
        <p>Dengan ini menyatakan :</p>
        
        <table class="data-table">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td><strong>{{ strtoupper($pengajuan->nama_mahasiswa) }}</strong></td>
            </tr>
            <tr>
                <td>N I M</td>
                <td>:</td>
                <td>{{ $pengajuan->nim }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>{{ $pengajuan->prodi->nama_prodi ?? "TEKNIK INFORMATIKA" }}</td>
            </tr>
            <tr>
                <td>Program</td>
                <td>:</td>
                <td>{{ $additionalData["program"] ?? "S1" }}</td>
            </tr>
        </table>
        
        <p>Nama Orang Tua/Wali dari Mahasiswa tersebut adalah :</p>
        
        <table class="data-table">
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ strtoupper($additionalData["orang_tua"]["nama"] ?? "") }}</td>
            </tr>
            <tr>
                <td>Tempat/Tanggal Lahir</td>
                <td>:</td>
                <td>{{ ($additionalData["orang_tua"]["tempat_lahir"] ?? "") }} / {{ ($additionalData["orang_tua"]["tanggal_lahir"] ?? "") }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["nip"] ?? "-" }}</td>
            </tr>
            <tr>
                <td>Pangkat/Golongan</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["pangkat_golongan"] ?? "-" }}</td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["pekerjaan"] ?? "" }}</td>
            </tr>
            <tr>
                <td>Instansi</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["instansi"] ?? "-" }}</td>
            </tr>
            <tr>
                <td>Alamat Kantor</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["alamat_instansi"] ?? "-" }}</td>
            </tr>
            <tr>
                <td>Alamat Rumah</td>
                <td>:</td>
                <td>{{ $additionalData["orang_tua"]["alamat_rumah"] ?? "" }}</td>
            </tr>
        </table>
        
        <p>Merupakan Mahasiswa Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani dan 
        <strong>Aktif</strong> pada Semester {{ $additionalData["semester"] ?? "Genap" }} 
        Tahun Akademik {{ $additionalData["tahun_akademik"] ?? "2024/2025" }}.</p>
        
        <p>Demikian surat pernyataan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
    </div>
    
    <!-- TANDA TANGAN -->
    <div class="signature-area">
        <p>Cimahi, {{ $tanggalSurat }}</p>
        <p>An. Dekan</p>
        <p>Wakil Dekan III – FSI</p>
        
        @if($barcodeImage)
        <div class="barcode-ttd">
            <img src="data:image/png;base64,{{ $barcodeImage }}" style="height: 80px; width: auto;">
        </div>
        @endif
        
        <p style="text-decoration: underline; font-weight: bold;">{{ $penandatangan["nama"] }}</p>
        <p>NID. {{ $penandatangan["nid"] }}</p>
    </div>
    
    <div style="clear: both;"></div>
</body>
</html>';
    }
}

// Main execution
try {
    echo Colors::GREEN . "\nSurat Barcode TTD Debug Script\n" . Colors::RESET;
    echo Colors::YELLOW . "================================\n\n" . Colors::RESET;
    
    if (!file_exists('artisan')) {
        throw new Exception("Script must be run from Laravel root directory!");
    }
    
    $debugger = new SuratBarcodeDebugger($config);
    $debugger->run();
    
} catch (Exception $e) {
    echo Colors::RED . "\n❌ FATAL ERROR: " . $e->getMessage() . Colors::RESET . "\n";
    echo Colors::YELLOW . "\nMake sure:\n" . Colors::RESET;
    echo "1. You are in Laravel root directory\n";
    echo "2. Database is properly configured\n";
    echo "3. You have write permissions\n\n";
    exit(1);
}
?>