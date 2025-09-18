<?php
// fix_route_error.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING ROUTE ERROR ===\n\n";

// Check available routes
echo "Checking available surat routes:\n";
$routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
    return str_contains($route->getName() ?? '', 'surat');
});

foreach ($routes as $route) {
    if ($route->getName()) {
        echo "- " . $route->getName() . " (" . implode('|', $route->methods()) . " " . $route->uri() . ")\n";
    }
}

// Fix the view file
$file = 'resources/views/staff/surat/index.blade.php';
$content = file_get_contents($file);

// Remove or comment out the print route
$content = str_replace(
    "{{-- Print/Download --}}
                                        <div class=\"border-t border-gray-100\"></div>
                                        <a href=\"{{ route('surat.print', \$surat->id) }}\" 
                                           target=\"_blank\"
                                           class=\"flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100\">
                                            <svg class=\"h-4 w-4 mr-3 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z\"/>
                                            </svg>
                                            Cetak
                                        </a>",
    "{{-- Print feature - uncomment when route is available
                                        <div class=\"border-t border-gray-100\"></div>
                                        <a href=\"#\" 
                                           onclick=\"alert('Fitur cetak belum tersedia'); return false;\"
                                           class=\"flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100\">
                                            <svg class=\"h-4 w-4 mr-3 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                                                <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z\"/>
                                            </svg>
                                            Cetak
                                        </a> --}}",
    $content
);

file_put_contents($file, $content);

echo "\n✓ Fixed route error by commenting out print feature\n";
echo "✓ Print feature can be enabled later when route is available\n";

echo "\n=== DONE ===\n";