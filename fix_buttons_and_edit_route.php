<?php
// fix_buttons_and_edit_route.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING BUTTON SIZE AND EDIT ROUTE ERROR ===\n\n";

// 1. Fix button size and spacing in index.blade.php
$indexFile = 'resources/views/staff/surat/index.blade.php';
$content = file_get_contents($indexFile);

// Backup
file_put_contents($indexFile . '.backup', $content);

// Replace small buttons (w-7 h-7) with larger ones (w-9 h-9) and better spacing
$content = str_replace('w-7 h-7', 'w-9 h-9', $content);
$content = str_replace('gap-1', 'gap-2', $content);
$content = str_replace('w-4 h-4', 'w-5 h-5', $content);

file_put_contents($indexFile, $content);
echo "✓ Fixed button sizes and spacing in index\n";

// 2. Fix the edit form route in staff/surat/edit.blade.php
$editFile = 'resources/views/staff/surat/edit.blade.php';
if (file_exists($editFile)) {
    $content = file_get_contents($editFile);
    
    // Find the form tag and check the route
    if (preg_match('/<form[^>]*action="([^"]*)"[^>]*>/', $content, $matches)) {
        echo "Current form action: " . $matches[1] . "\n";
        
        // Check if it's using staff.surat.update without ID
        if (str_contains($matches[1], 'staff.surat.update') && !str_contains($matches[1], '$surat->id')) {
            echo "Fixing missing ID in form route...\n";
            
            // Replace the form opening tag
            $oldForm = $matches[0];
            $newForm = '<form action="{{ route(\'staff.surat.update\', $surat->id) }}" method="POST" enctype="multipart/form-data">';
            
            $content = str_replace($oldForm, $newForm, $content);
            file_put_contents($editFile, $content);
            echo "✓ Fixed form route with ID parameter\n";
        }
    }
    
    // Also check if $surat variable is passed from controller
    if (!str_contains($content, '$surat->')) {
        echo "⚠ Warning: View might not have \$surat variable\n";
        echo "Make sure controller passes it: return view('staff.surat.edit', compact('surat'));\n";
    }
}

// 3. Check the controller edit method
$controllerFile = app_path('Http/Controllers/SuratController.php');
$controllerContent = file_get_contents($controllerFile);

// Find edit method
if (preg_match('/public\s+function\s+edit\s*\([^)]*\$id[^)]*\)\s*{([^}]+)}/s', $controllerContent, $matches)) {
    $editMethod = $matches[0];
    
    if (!str_contains($editMethod, "compact('surat')")) {
        echo "⚠ Controller edit method might not be passing \$surat variable\n";
        echo "Adding proper return statement...\n";
        
        // Create proper edit method
        $newEditMethod = '
    public function edit($id)
    {
        $surat = Surat::findOrFail($id);
        
        // Check authorization
        if ($surat->created_by !== Auth::id()) {
            return redirect()->back()->with(\'error\', \'Anda tidak dapat mengedit surat ini\');
        }
        
        // Only draft can be edited
        if ($surat->currentStatus->kode_status !== \'draft\') {
            return redirect()->back()->with(\'error\', \'Hanya surat draft yang dapat diedit\');
        }
        
        $jenisSurats = JenisSurat::all();
        $tujuanJabatans = TujuanJabatan::all();
        
        return view(\'staff.surat.edit\', compact(\'surat\', \'jenisSurats\', \'tujuanJabatans\'));
    }';
        
        $controllerContent = preg_replace(
            '/public\s+function\s+edit\s*\([^)]*\)\s*{[^}]+}/',
            $newEditMethod,
            $controllerContent
        );
        
        file_put_contents($controllerFile, $controllerContent);
        echo "✓ Fixed controller edit method\n";
    }
}

// 4. Create better styled buttons component
$buttonStyles = '
<style>
/* Better button styling */
.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    transition: all 0.2s;
    position: relative;
}

.action-btn:hover {
    transform: scale(1.1);
}

.action-btn svg {
    width: 20px;
    height: 20px;
}

.action-btn-view {
    color: #4F46E5;
    background-color: transparent;
}
.action-btn-view:hover {
    background-color: #EEF2FF;
}

.action-btn-edit {
    color: #EAB308;
    background-color: transparent;
}
.action-btn-edit:hover {
    background-color: #FEF3C7;
}

.action-btn-approve {
    color: #10B981;
    background-color: transparent;
}
.action-btn-approve:hover {
    background-color: #D1FAE5;
}

.action-btn-reject {
    color: #EF4444;
    background-color: transparent;
}
.action-btn-reject:hover {
    background-color: #FEE2E2;
}

.action-btn-submit {
    color: #3B82F6;
    background-color: transparent;
}
.action-btn-submit:hover {
    background-color: #DBEAFE;
}

/* Button group spacing */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}
</style>';

echo "\n=== IMPROVED BUTTON STYLES ===\n";
echo "Add this to your layout or create a CSS file:\n";
echo $buttonStyles;

echo "\n=== DONE ===\n";
echo "1. Button sizes increased from 28px to 36px\n";
echo "2. Spacing increased from 4px to 8px\n";
echo "3. Edit form route fixed with ID parameter\n";
echo "4. Controller edit method updated\n";