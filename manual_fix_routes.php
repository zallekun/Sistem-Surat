<?php
/**
 * MANUAL FIX ROUTES FILE
 * 
 * Script untuk memperbaiki routes/web.php secara manual
 * Mengatasi unmatched brace di line 116
 * 
 * File: manual_fix_routes.php
 */

// Tidak perlu bootstrap Laravel untuk fix ini
$routesFile = __DIR__ . '/routes/web.php';

if (!file_exists($routesFile)) {
    die("❌ Routes file not found: {$routesFile}\n");
}

echo "🔧 MANUAL ROUTES FIX\n";
echo "====================\n\n";

// Backup file
$backupFile = $routesFile . '.backup.' . date('Y-m-d_H-i-s');
copy($routesFile, $backupFile);
echo "📦 Backup created: {$backupFile}\n";

// Read content
$content = file_get_contents($routesFile);

// Fix the problematic section
// The issue is around line 56-60 where there's a malformed route group

// First, let's fix the Pengajuan Mahasiswa section that has wrong structure
$content = preg_replace(
    '/\/\/ Pengajuan Mahasiswa\s+Route::get\(\'\/staff\/pengajuan\'.*?\}\);/s',
    '// Pengajuan Mahasiswa routes are handled in the staff section below',
    $content
);

// Now let's properly structure the staff routes section
$staffRoutesPattern = '/Route::middleware\(\[\'role:staff_prodi,staff_fakultas\'\]\)->prefix\(\'staff\'\)->name\(\'staff\.\'\)->group\(function \(\) \{.*?\}\);/s';

$staffRoutesReplacement = 'Route::middleware([\'role:staff_prodi,staff_fakultas\'])->prefix(\'staff\')->name(\'staff.\')->group(function () {
        Route::resource(\'surat\', SuratController::class)->except([\'index\']);
        Route::get(\'surat\', [SuratController::class, \'staffIndex\'])->name(\'surat.index\');

        // Pengajuan routes for staff
        Route::prefix(\'pengajuan\')->name(\'pengajuan.\')->group(function () {
            Route::get(\'/\', [App\Http\Controllers\StaffPengajuanController::class, \'index\'])->name(\'index\');
            Route::get(\'/{id}\', [App\Http\Controllers\StaffPengajuanController::class, \'show\'])->name(\'show\');
            Route::post(\'/{id}/process\', [SuratController::class, \'processProdiPengajuan\'])->name(\'process\');
        });

        // Staff Prodi only
        Route::middleware([\'role:staff_prodi\'])->group(function () {
            Route::get(\'surat/create-from-pengajuan/{id}\', [SuratController::class, \'createFromPengajuan\'])
                ->name(\'surat.create-from-pengajuan\');
        });
    })';

$content = preg_replace($staffRoutesPattern, $staffRoutesReplacement, $content);

// Remove duplicate route definitions
$content = preg_replace('/Route::get\(\'\/staff\/pengajuan\'.*?\)->name\(\'staff\.pengajuan\.index\'\);/', '', $content);

// Clean up any extra closing braces
$openBraces = substr_count($content, '{');
$closeBraces = substr_count($content, '}');

echo "Brace count - Open: {$openBraces}, Close: {$closeBraces}\n";

if ($closeBraces > $openBraces) {
    $diff = $closeBraces - $openBraces;
    echo "Removing {$diff} extra closing brace(s)\n";
    
    // Remove extra closing braces - be careful to only remove extras
    for ($i = 0; $i < $diff; $i++) {
        // Find isolated closing brace on its own line
        $content = preg_replace('/\n}\);\s*\n}\);/', "\n});", $content, 1);
    }
}

// Write the fixed content
file_put_contents($routesFile, $content);
echo "✅ Routes file updated\n";

// Verify syntax
echo "\n🔍 Verifying syntax...\n";
$output = shell_exec("php -l " . escapeshellarg($routesFile) . " 2>&1");

if (strpos($output, 'No syntax errors') !== false) {
    echo "✅ Syntax check passed!\n";
    echo "\n🎉 SUCCESS! Routes file has been fixed.\n";
    echo "\nNext steps:\n";
    echo "1. Run: php artisan route:clear\n";
    echo "2. Run: php artisan route:list\n";
    echo "3. Test the application\n";
} else {
    echo "⚠️ There might still be issues:\n";
    echo $output . "\n";
    echo "\nPlease check the file manually or restore from backup:\n";
    echo "cp {$backupFile} {$routesFile}\n";
}
?>