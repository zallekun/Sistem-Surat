<?php
/**
 * Debug Route Error - Fixed Version
 * Run: php debug_routes.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG ROUTE ERROR ===\n\n";

if (!file_exists('artisan')) {
    die("ERROR: Run from Laravel root!\n");
}

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

// 1. CHECK CURRENT ROUTES
echo "1. CHECKING ROUTES WITH SURATCONTROLLER\n";
echo "========================================\n";

$routes = Route::getRoutes();
$problemFound = false;

foreach ($routes as $route) {
    $action = $route->getActionName();
    
    // Check if route uses SuratController
    if (strpos($action, 'SuratController') !== false) {
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        
        echo "$methods $uri -> $action\n";
        
        // Check for staffIndex
        if (strpos($action, 'staffIndex') !== false) {
            echo "  *** PROBLEM: staffIndex method called but doesn't exist!\n";
            $problemFound = true;
        }
    }
}

// 2. CHECK SURATCONTROLLER METHODS
echo "\n2. EXISTING METHODS IN SURATCONTROLLER\n";
echo "=======================================\n";

if (class_exists('App\Http\Controllers\SuratController')) {
    $reflection = new ReflectionClass('App\Http\Controllers\SuratController');
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach ($methods as $method) {
        $name = $method->getName();
        if (!in_array($name, ['__construct', '__call', '__destruct'])) {
            echo "  - $name()\n";
        }
    }
    
    // Check if staffIndex exists
    if (!$reflection->hasMethod('staffIndex')) {
        echo "\n  *** staffIndex() method NOT FOUND!\n";
    }
} else {
    echo "ERROR: SuratController class not found!\n";
}

// 3. CHECK ROUTES FILE
echo "\n3. CHECKING ROUTES/WEB.PHP\n";
echo "==========================\n";

$webRoutesPath = base_path('routes/web.php');
if (file_exists($webRoutesPath)) {
    $lines = file($webRoutesPath);
    $lineNumber = 0;
    
    foreach ($lines as $line) {
        $lineNumber++;
        if (strpos($line, 'staffIndex') !== false) {
            echo "Found 'staffIndex' at line $lineNumber:\n";
            echo "  " . trim($line) . "\n";
            $problemFound = true;
        }
    }
} else {
    echo "ERROR: routes/web.php not found!\n";
}

// 4. GENERATE FIX
if ($problemFound) {
    echo "\n4. SOLUTION\n";
    echo "===========\n";
    
    echo "Add this method to app/Http/Controllers/SuratController.php:\n\n";
    
    $fixCode = <<<'PHP'
    public function staffIndex()
    {
        $user = auth()->user();
        
        // Staff melihat surat yang mereka buat
        $query = Surat::with(['pengirim', 'tujuan_jabatan', 'status', 'fakultas', 'prodi']);
        
        // Filter berdasarkan role
        if ($user->hasRole('staff_prodi')) {
            // Staff prodi melihat surat prodi mereka
            if ($user->jabatan && $user->jabatan->prodi_id) {
                $query->where('prodi_id', $user->jabatan->prodi_id);
            } else {
                $query->where('created_by', $user->id);
            }
        } elseif ($user->hasRole('staff_fakultas')) {
            // Staff fakultas melihat surat fakultas
            if ($user->jabatan && $user->jabatan->fakultas_id) {
                $query->where('fakultas_id', $user->jabatan->fakultas_id);
            } else {
                $query->where('created_by', $user->id);
            }
        } else {
            // Default: user hanya melihat surat yang mereka buat
            $query->where('created_by', $user->id);
        }
        
        $surats = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('surat.index', compact('surats'));
    }
PHP;
    
    echo $fixCode . "\n";
    
    // Save to file
    file_put_contents('add_to_suratcontroller.txt', "<?php\n\n" . $fixCode);
    echo "\nSaved to: add_to_suratcontroller.txt\n";
    echo "Copy the method above and paste it into your SuratController.php\n";
}

// 5. CHECK STAFF ROUTES
echo "\n5. ALL STAFF ROUTES\n";
echo "===================\n";

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'staff') !== false) {
        $action = $route->getActionName();
        $methods = implode('|', $route->methods());
        echo "$methods $uri -> " . str_replace('App\Http\Controllers\\', '', $action) . "\n";
    }
}

// 6. SUMMARY
echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if ($problemFound) {
    echo "PROBLEM FOUND: Route calling SuratController@staffIndex\n";
    echo "              but staffIndex() method doesn't exist\n\n";
    echo "TO FIX:\n";
    echo "1. Open app/Http/Controllers/SuratController.php\n";
    echo "2. Add the staffIndex() method from add_to_suratcontroller.txt\n";
    echo "3. Save and test again\n";
} else {
    echo "No route problems found.\n";
    echo "The error might be cached. Try:\n";
    echo "  php artisan route:clear\n";
    echo "  php artisan cache:clear\n";
    echo "  php artisan config:clear\n";
}

echo "\n=== DEBUG COMPLETED ===\n";