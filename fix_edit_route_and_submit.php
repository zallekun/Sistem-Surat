<?php
// fix_edit_route_and_submit.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING EDIT ROUTE ERROR AND ADDING SUBMIT BUTTON TO DETAIL ===\n\n";

// 1. Fix edit form route in edit.blade.php
$editViewFiles = [
    'resources/views/surat/edit.blade.php',
    'resources/views/staff/surat/edit.blade.php'
];

foreach ($editViewFiles as $file) {
    if (file_exists($file)) {
        echo "Checking $file...\n";
        $content = file_get_contents($file);
        
        // Fix the form action route
        if (str_contains($content, "route('staff.surat.update')")) {
            $content = str_replace(
                "route('staff.surat.update')",
                "route('staff.surat.update', \$surat->id)",
                $content
            );
            file_put_contents($file, $content);
            echo "✓ Fixed route in $file\n";
        } elseif (str_contains($content, "route('surat.update')")) {
            // Make sure it has the ID parameter
            if (!str_contains($content, "route('surat.update', \$surat")) {
                $content = str_replace(
                    "route('surat.update')",
                    "route('surat.update', \$surat->id)",
                    $content
                );
                file_put_contents($file, $content);
                echo "✓ Fixed route in $file\n";
            }
        } else {
            echo "⚠ Form route might be missing or different in $file\n";
        }
    }
}

// 2. Add submit button to detail view
$showViewFile = 'resources/views/surat/show.blade.php';
if (!file_exists($showViewFile)) {
    $showViewFile = 'resources/views/staff/surat/show.blade.php';
}

if (file_exists($showViewFile)) {
    echo "\nAdding submit button to detail view...\n";
    $content = file_get_contents($showViewFile);
    
    // Check if submit button already exists
    if (!str_contains($content, 'surat.submit') && !str_contains($content, 'confirmSubmit')) {
        // Find the action buttons section
        $pattern = '/(@if\s*\(\$surat->created_by\s*===\s*Auth::id\(\)[^@]*@endif)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $existingButtons = $matches[0];
            
            // Add submit button
            $submitButton = '
            {{-- Submit button for draft --}}
            @if($surat->created_by === Auth::id() && $surat->currentStatus->kode_status === \'draft\')
            <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim ke Kaprodi
                </button>
            </form>
            @endif
            ';
            
            // Insert after existing buttons
            $content = str_replace($existingButtons, $existingButtons . "\n" . $submitButton, $content);
            file_put_contents($showViewFile, $content);
            echo "✓ Added submit button to detail view\n";
        } else {
            echo "⚠ Could not find action buttons section, adding at a different location...\n";
            
            // Alternative: Add before closing of card-body or similar
            if (str_contains($content, '</div>{{-- End of action buttons --}}')) {
                $content = str_replace(
                    '</div>{{-- End of action buttons --}}',
                    '
            @if($surat->created_by === Auth::id() && $surat->currentStatus->kode_status === \'draft\')
            <form action="{{ route(\'surat.submit\', $surat->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm(\'Apakah Anda yakin ingin mengirim surat ini ke Kaprodi untuk review?\')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim ke Kaprodi
                </button>
            </form>
            @endif
            </div>{{-- End of action buttons --}}',
                    $content
                );
                file_put_contents($showViewFile, $content);
                echo "✓ Added submit button to detail view (alternative location)\n";
            }
        }
    } else {
        echo "✓ Submit button already exists in detail view\n";
    }
}

// 3. Check routes and fix if needed
echo "\n=== CHECKING ROUTES ===\n";
$routes = collect(\Illuminate\Support\Facades\Route::getRoutes());

// Check for staff.surat.update route
$updateRoute = $routes->first(function ($route) {
    return $route->getName() === 'staff.surat.update';
});

if (!$updateRoute) {
    echo "⚠ Route 'staff.surat.update' not found\n";
    echo "You may need to add this route in routes/web.php:\n";
    echo "Route::put('/staff/surat/{id}', [SuratController::class, 'update'])->name('staff.surat.update');\n";
} else {
    echo "✓ Route 'staff.surat.update' exists\n";
}

// Check for surat.submit route
$submitRoute = $routes->first(function ($route) {
    return $route->getName() === 'surat.submit';
});

if (!$submitRoute) {
    echo "⚠ Route 'surat.submit' not found\n";
    echo "Adding route to routes/web.php...\n";
    
    $routesFile = base_path('routes/web.php');
    $routesContent = file_get_contents($routesFile);
    
    if (!str_contains($routesContent, 'surat.submit')) {
        // Add submit route
        $submitRouteCode = "\nRoute::post('/surat/{id}/submit', [App\Http\Controllers\SuratController::class, 'submit'])->name('surat.submit');";
        
        // Find a good place to insert (after surat routes)
        if (str_contains($routesContent, "Route::resource('surat'")) {
            $routesContent = str_replace(
                "Route::resource('surat', SuratController::class);",
                "Route::resource('surat', SuratController::class);" . $submitRouteCode,
                $routesContent
            );
        } else {
            // Add at the end of auth middleware group
            $routesContent = str_replace(
                "});",
                $submitRouteCode . "\n});",
                $routesContent
            );
        }
        
        file_put_contents($routesFile, $routesContent);
        echo "✓ Added surat.submit route\n";
    }
} else {
    echo "✓ Route 'surat.submit' exists\n";
}

echo "\n=== QUICK FIX FOR EDIT ROUTE ===\n";
echo "If the error persists, check your edit form and ensure it has:\n";
echo "<form action=\"{{ route('surat.update', \$surat->id) }}\" method=\"POST\">\n";
echo "NOT just: route('surat.update') without the ID parameter\n";

echo "\n=== DONE ===\n";