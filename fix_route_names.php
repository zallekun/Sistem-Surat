<?php
/**
 * Fix Regex Error in Controller
 * Run: php fix_regex_error.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== FIX REGEX ERROR ===\n\n";

if (!file_exists('artisan')) {
    die("ERROR: Run from Laravel root!\n");
}

// 1. READ CONTROLLER
$controllerPath = 'app/Http/Controllers/SuratController.php';
if (!file_exists($controllerPath)) {
    echo "[ERROR] SuratController not found\n";
    exit(1);
}

$content = file_get_contents($controllerPath);
$lines = explode("\n", $content);

echo "[OK] Found SuratController\n";
echo "[CHECK] Line 119 content:\n";
echo "Line 119: " . ($lines[118] ?? 'Not found') . "\n\n";

// 2. BACKUP
$backupPath = $controllerPath . '.backup_regex_' . date('Ymd_His');
copy($controllerPath, $backupPath);
echo "[BACKUP] Created: " . basename($backupPath) . "\n";

// 3. FIX THE REGEX PATTERN
echo "\n[FIX] Fixing regex pattern\n";

// The problem is likely this line - missing ending delimiter
$badPattern = "if (\$lastSurat && preg_match('/(\d+)\/', \$lastSurat->nomor_surat, \$matches)) {";
$goodPattern = "if (\$lastSurat && preg_match('/(\d+)\\\\//', \$lastSurat->nomor_surat, \$matches)) {";

if (strpos($content, "preg_match('/(\d+)/'") !== false) {
    // Missing delimiter case
    $content = str_replace(
        "preg_match('/(\d+)/'", 
        "preg_match('/(\d+)/'", 
        $content
    );
    echo "[FIXED] Added missing delimiter\n";
} elseif (strpos($content, "preg_match('/(\d+)\/'") !== false) {
    // Incorrect escape case
    $content = str_replace(
        "preg_match('/(\d+)\/'", 
        "preg_match('/(\d+)\\\\/'", 
        $content
    );
    echo "[FIXED] Fixed escape sequence\n";
}

// More comprehensive fix for the regex pattern
$patterns = [
    // Fix unescaped forward slash in regex
    "preg_match('/(\d+)\/', \$lastSurat->nomor_surat, \$matches)" => 
    "preg_match('/(\\d+)\\//', \$lastSurat->nomor_surat, \$matches)",
    
    // Alternative fix using different delimiter
    "preg_match('/(\d+)/', \$lastSurat->nomor_surat, \$matches)" =>
    "preg_match('#(\\d+)/#', \$lastSurat->nomor_surat, \$matches)",
];

foreach ($patterns as $search => $replace) {
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        echo "[FIXED] Pattern: $search\n";
    }
}

// 4. SAVE FIXED FILE
if (file_put_contents($controllerPath, $content)) {
    echo "[SAVED] Controller fixed\n";
} else {
    echo "[ERROR] Failed to save controller\n";
    exit(1);
}

// 5. SHOW THE CORRECT PATTERN
echo "\n[INFO] Correct regex patterns for nomor surat parsing:\n";
echo "Option 1: preg_match('/(\\d+)\\//', \$lastSurat->nomor_surat, \$matches)\n";
echo "Option 2: preg_match('#(\\d+)/#', \$lastSurat->nomor_surat, \$matches)\n";
echo "Option 3: preg_match('~(\\d+)/~', \$lastSurat->nomor_surat, \$matches)\n";

echo "\n=== FIX COMPLETED ===\n";