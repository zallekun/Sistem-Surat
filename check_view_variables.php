<?php
// check_view_variables.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK VIEW VARIABLES ===\n\n";

// Check SuratController to see what it's actually passing
$controllerFile = app_path('Http/Controllers/SuratController.php');
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    echo "1. SuratController Methods and Views:\n";
    
    // Find staffIndex method
    if (preg_match('/public\s+function\s+staffIndex.*?return\s+view\([^;]+;/s', $content, $match)) {
        echo "\n   staffIndex method found:\n";
        
        // Check what variables are being passed
        if (preg_match('/compact\s*\(\s*([^\)]+)\)/', $match[0], $vars)) {
            $variables = $vars[1];
            $variables = str_replace(["'", '"', ' '], '', $variables);
            $varArray = explode(',', $variables);
            
            echo "   Variables passed: " . implode(', ', $varArray) . "\n";
            
            // Check if 'surat' is passed instead of 'surats'
            if (in_array('surat', $varArray) && !in_array('surats', $varArray)) {
                echo "   ⚠️ WARNING: Passing 'surat' but view expects 'surats'\n";
                echo "   ✓ FIX: Change compact('surat', ...) to compact('surats', ...)\n";
            }
        }
    }
    
    // Find index method  
    if (preg_match('/public\s+function\s+index.*?return\s+view\([^;]+;/s', $content, $match)) {
        echo "\n   index method found:\n";
        
        if (preg_match('/compact\s*\(\s*([^\)]+)\)/', $match[0], $vars)) {
            $variables = $vars[1];
            $variables = str_replace(["'", '"', ' '], '', $variables);
            $varArray = explode(',', $variables);
            
            echo "   Variables passed: " . implode(', ', $varArray) . "\n";
        }
    }
    
    // Find approvalList method
    if (preg_match('/public\s+function\s+approvalList.*?return\s+view\([^;]+;/s', $content, $match)) {
        echo "\n   approvalList method found:\n";
        
        if (preg_match('/compact\s*\(\s*([^\)]+)\)/', $match[0], $vars)) {
            $variables = $vars[1];
            $variables = str_replace(["'", '"', ' '], '', $variables);
            $varArray = explode(',', $variables);
            
            echo "   Variables passed: " . implode(', ', $varArray) . "\n";
        }
    }
}

echo "\n2. View Variable Usage Check:\n";

// Check what each view expects
$viewsToCheck = [
    'staff.surat.index',
    'kaprodi.surat.index', 
    'kaprodi.surat.approval'
];

foreach ($viewsToCheck as $viewName) {
    if (view()->exists($viewName)) {
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        if (file_exists($viewPath)) {
            $content = file_get_contents($viewPath);
            
            echo "\n   View: $viewName\n";
            
            // Check for $surats usage
            if (preg_match_all('/\$surats/', $content, $matches)) {
                echo "   - Uses \$surats: " . count($matches[0]) . " times\n";
            }
            
            // Check for $surat usage (singular)
            if (preg_match_all('/\$surat(?![s])/', $content, $matches)) {
                // Filter out $surat within loops which is OK
                $nonLoopUsage = 0;
                foreach ($matches[0] as $match) {
                    if (!preg_match('/@foreach.*\$surat/', $content)) {
                        $nonLoopUsage++;
                    }
                }
                if ($nonLoopUsage > 0) {
                    echo "   - Uses \$surat (singular): $nonLoopUsage times\n";
                }
            }
        }
    }
}

echo "\n3. Quick Fix Suggestions:\n\n";

echo "Option A - Fix the Controller (RECOMMENDED):\n";
echo "----------------------------------------\n";
echo "In SuratController@staffIndex, change:\n";
echo "  FROM: \$surat = Surat::...->paginate(15);\n";
echo "  TO:   \$surats = Surat::...->paginate(15);\n";
echo "\n";
echo "  FROM: return view('staff.surat.index', compact('surat', ...));\n";
echo "  TO:   return view('staff.surat.index', compact('surats', ...));\n";

echo "\n";
echo "Option B - Update the View (NOT recommended):\n";
echo "----------------------------------------\n";
echo "In resources/views/staff/surat/index.blade.php:\n";
echo "  Change all occurrences of \$surats to \$surat\n";
echo "  But this breaks convention (collections should be plural)\n";

echo "\n=== END CHECK ===\n";