<?php
// fix_relation_null_error.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIXING RELATION NULL ERROR ===\n\n";

// 1. Fix SuratController - remove problematic eager loading
echo "1. FIXING SURAT CONTROLLER EAGER LOADING\n";
$controllerFile = 'app/Http/Controllers/SuratController.php';
$content = file_get_contents($controllerFile);

// Replace the problematic with() statement
$oldWith = "Surat::with(['pengirim', 'tujuan_jabatan', 'status', 'fakultas', 'prodi'])";
$newWith = "Surat::with(['pengirim', 'status'])";

$content = str_replace($oldWith, $newWith, $content);
file_put_contents($controllerFile, $content);
echo "   ✓ Simplified eager loading to avoid null relations\n";

// 2. Update index method completely to avoid relation issues
echo "\n2. UPDATING INDEX METHOD\n";

$newIndexMethod = '
    public function index()
    {
        $user = auth()->user();
        $query = Surat::query();
        
        if ($user->hasRole(\'kaprodi\')) {
            // Get kaprodi\'s jabatan
            $jabatan = null;
            if ($user->jabatan_id) {
                $jabatan = \App\Models\Jabatan::find($user->jabatan_id);
            }
            
            if ($jabatan && $jabatan->prodi_id) {
                // Kaprodi sees non-draft letters from their prodi
                $query->where(\'prodi_id\', $jabatan->prodi_id)
                      ->where(\'status_id\', \'!=\', 1); // Exclude drafts
            }
            
        } elseif ($user->hasRole(\'staff_prodi\')) {
            // Staff prodi sees their own letters
            if ($user->jabatan_id) {
                $query->where(\'pengirim_jabatan_id\', $user->jabatan_id);
            }
            
        } elseif ($user->hasRole(\'staff_fakultas\')) {
            // Staff fakultas sees faculty letters
            if ($user->jabatan_id) {
                $jabatan = \App\Models\Jabatan::find($user->jabatan_id);
                if ($jabatan && $jabatan->fakultas_id) {
                    $query->where(\'fakultas_id\', $jabatan->fakultas_id);
                }
            }
        }
        
        // Get surats with only essential relations
        $surats = $query->with([\'pengirim\', \'status\'])
                       ->orderBy(\'created_at\', \'desc\')
                       ->paginate(10);
        
        // Use different views based on role
        if ($user->hasRole(\'kaprodi\')) {
            return view(\'kaprodi.surat.index\', compact(\'surats\'));
        } elseif ($user->hasRole(\'staff_prodi\')) {
            return view(\'staff.surat.index\', compact(\'surats\'));
        } elseif ($user->hasRole(\'staff_fakultas\')) {
            return view(\'staff_fakultas.surat.index\', compact(\'surats\'));
        } else {
            return view(\'surat.index\', compact(\'surats\'));
        }
    }';

// Replace the entire index method
$pattern = '/public function index\(\).*?^\s{4}}/ms';
if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, trim($newIndexMethod), $content);
    file_put_contents($controllerFile, $content);
    echo "   ✓ Index method updated\n";
}

// 3. Check and fix model relations
echo "\n3. CHECKING MODEL RELATIONS\n";

// Check if relations exist in database
$tables = ['fakultas', 'prodi', 'jabatan', 'status_surat'];
foreach ($tables as $table) {
    $exists = DB::select("SHOW TABLES LIKE '{$table}'");
    if (empty($exists)) {
        echo "   ⚠ Table '{$table}' does not exist\n";
    } else {
        echo "   ✓ Table '{$table}' exists\n";
    }
}

// 4. Create a simple test query
echo "\n4. TESTING QUERY\n";
try {
    $testQuery = DB::table('surat')
        ->select('id', 'nomor_surat', 'perihal', 'status_id', 'pengirim_jabatan_id')
        ->limit(1)
        ->first();
    
    if ($testQuery) {
        echo "   ✓ Basic query works\n";
        echo "   Sample surat: {$testQuery->nomor_surat}\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Query error: {$e->getMessage()}\n";
}

// 5. Update views to handle null relations safely
echo "\n5. UPDATING VIEWS FOR SAFE NULL HANDLING\n";
$viewFiles = [
    'resources/views/kaprodi/surat/index.blade.php',
    'resources/views/staff/surat/index.blade.php'
];

foreach ($viewFiles as $viewFile) {
    if (file_exists($viewFile)) {
        $viewContent = file_get_contents($viewFile);
        
        // Replace unsafe relation calls with safe ones
        $viewContent = str_replace(
            '{{ $surat->pengirim->nama_jabatan ?? \'-\' }}',
            '{{ optional($surat->pengirim)->nama_jabatan ?? \'-\' }}',
            $viewContent
        );
        
        $viewContent = str_replace(
            '{{ $surat->status->nama_status }}',
            '{{ optional($surat->status)->nama_status ?? \'Unknown\' }}',
            $viewContent
        );
        
        file_put_contents($viewFile, $viewContent);
        echo "   ✓ Updated $viewFile for safe null handling\n";
    }
}

echo "\n=== DONE ===\n";
echo "Run: php artisan cache:clear && php artisan config:clear\n";
echo "\nThe error should be fixed. Try accessing /surat again.\n";