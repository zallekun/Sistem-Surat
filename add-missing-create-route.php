<?php
// add-missing-create-route.php
// Jalankan: php add-missing-create-route.php

echo "=== ADDING MISSING CREATE ROUTE ===\n\n";

echo "1. Checking Current Routes Structure\n";
echo str_repeat("-", 50) . "\n";

$webRoutesFile = 'routes/web.php';
$content = file_get_contents($webRoutesFile);

// Check if staff.surat.create route exists
if (strpos($content, 'staff.surat.create') !== false) {
    echo "staff.surat.create route already exists\n";
} else {
    echo "staff.surat.create route is missing\n";
}

// Check for other create-related routes
$createRoutes = [];
$lines = explode("\n", $content);
foreach ($lines as $index => $line) {
    if (strpos($line, 'create') !== false && strpos($line, 'Route::') !== false) {
        $createRoutes[] = "Line " . ($index + 1) . ": " . trim($line);
    }
}

if (!empty($createRoutes)) {
    echo "Found existing create routes:\n";
    foreach ($createRoutes as $route) {
        echo "  " . $route . "\n";
    }
} else {
    echo "No create routes found\n";
}

echo "\n2. Adding Missing Routes\n";
echo str_repeat("-", 50) . "\n";

// Backup current routes
$backup = $webRoutesFile . '.createfix.' . date('YmdHis');
copy($webRoutesFile, $backup);
echo "Backup created: $backup\n";

// Read current content
$lines = explode("\n", $content);

// Find the staff routes section
$staffSectionStart = -1;
$staffSectionEnd = -1;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], "Route::prefix('staff')") !== false) {
        $staffSectionStart = $i;
        
        // Find the end of this group
        $braceCount = 0;
        $foundOpenBrace = false;
        
        for ($j = $i; $j < count($lines); $j++) {
            if (strpos($lines[$j], '{') !== false) {
                $foundOpenBrace = true;
            }
            
            if ($foundOpenBrace) {
                $braceCount += substr_count($lines[$j], '{') - substr_count($lines[$j], '}');
                
                if ($braceCount == 0 && $j > $i) {
                    $staffSectionEnd = $j;
                    break;
                }
            }
        }
        break;
    }
}

if ($staffSectionStart >= 0) {
    echo "Found staff routes section from line " . ($staffSectionStart + 1) . " to " . ($staffSectionEnd + 1) . "\n";
    
    // Show current staff section
    echo "Current staff routes:\n";
    for ($i = $staffSectionStart; $i <= $staffSectionEnd; $i++) {
        echo "  " . ($i + 1) . ": " . $lines[$i] . "\n";
    }
    
    // Add missing routes to staff section
    $newStaffRoutes = [
        "    // Staff routes",
        "    Route::prefix('staff')->name('staff.')->group(function () {",
        "        // Surat management",
        "        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');",
        "        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');",
        "        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');",
        "        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');",
        "        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');",
        "    });"
    ];
    
    // Replace the staff section
    array_splice($lines, $staffSectionStart, $staffSectionEnd - $staffSectionStart + 1, $newStaffRoutes);
    
    echo "\nAdded complete staff routes section\n";
    
} else {
    echo "Staff routes section not found, adding new section\n";
    
    // Find a good place to add staff routes (before admin routes)
    $insertPosition = -1;
    
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], '// Admin routes') !== false || 
            strpos($lines[$i], 'Route::resource') !== false) {
            $insertPosition = $i;
            break;
        }
    }
    
    if ($insertPosition < 0) {
        // Add before the end
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) !== '' && strpos($lines[$i], '?>') === false) {
                $insertPosition = $i + 1;
                break;
            }
        }
    }
    
    $newStaffSection = [
        "",
        "// Staff routes",
        "Route::middleware(['auth'])->group(function () {",
        "    Route::prefix('staff')->name('staff.')->group(function () {",
        "        // Surat management",
        "        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');",
        "        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');",
        "        Route::post('/surat/{id}/submit', [SuratController::class, 'submit'])->name('surat.submit');",
        "        Route::get('/surat/{id}/tracking', [SuratController::class, 'tracking'])->name('surat.tracking');",
        "        Route::get('/surat/{id}/download', [SuratController::class, 'download'])->name('surat.download');",
        "    });",
        "});",
        ""
    ];
    
    array_splice($lines, $insertPosition, 0, $newStaffSection);
    echo "Added new staff routes section at line " . ($insertPosition + 1) . "\n";
}

// Save updated routes
file_put_contents($webRoutesFile, implode("\n", $lines));
echo "✓ Routes file updated\n";

echo "\n3. Adding Missing Controller Methods\n";
echo str_repeat("-", 50) . "\n";

$controllerFile = 'app/Http/Controllers/SuratController.php';
$controllerContent = file_get_contents($controllerFile);

// Check if create and store methods exist
$missingMethods = [];
if (strpos($controllerContent, 'public function create') === false) {
    $missingMethods[] = 'create';
}
if (strpos($controllerContent, 'public function store') === false) {
    $missingMethods[] = 'store';
}

if (!empty($missingMethods)) {
    echo "Adding missing methods: " . implode(', ', $missingMethods) . "\n";
    
    // Backup controller
    $controllerBackup = $controllerFile . '.createfix.' . date('YmdHis');
    copy($controllerFile, $controllerBackup);
    
    // Add methods before the closing brace
    $controllerLines = explode("\n", $controllerContent);
    
    // Find the last closing brace of the class
    for ($i = count($controllerLines) - 1; $i >= 0; $i--) {
        if (trim($controllerLines[$i]) === '}') {
            $insertPosition = $i;
            break;
        }
    }
    
    $newMethods = [];
    
    if (in_array('create', $missingMethods)) {
        $newMethods = array_merge($newMethods, [
            "",
            "    public function create()",
            "    {",
            "        \$user = Auth::user();",
            "        ",
            "        // Check authorization - only staff_prodi can create surat",
            "        if (!\$user->hasRole('staff_prodi')) {",
            "            abort(403, 'Unauthorized to create surat');",
            "        }",
            "        ",
            "        \$jenisSurat = \\App\\Models\\JenisSurat::all();",
            "        \$jabatan = \\App\\Models\\Jabatan::all();",
            "        ",
            "        return view('surat.create', compact('jenisSurat', 'jabatan'));",
            "    }"
        ]);
    }
    
    if (in_array('store', $missingMethods)) {
        $newMethods = array_merge($newMethods, [
            "",
            "    public function store(Request \$request)",
            "    {",
            "        \$user = Auth::user();",
            "        ",
            "        // Check authorization",
            "        if (!\$user->hasRole('staff_prodi')) {",
            "            abort(403, 'Unauthorized to create surat');",
            "        }",
            "        ",
            "        // Validate request",
            "        \$request->validate([",
            "            'perihal' => 'required|string|max:255',",
            "            'isi_surat' => 'required|string',",
            "            'jenis_surat_id' => 'required|exists:jenis_surat,id',",
            "            'tujuan_jabatan_id' => 'required|exists:jabatan,id',",
            "        ]);",
            "        ",
            "        // Get draft status",
            "        \$draftStatus = StatusSurat::where('kode_status', 'draft')->first();",
            "        ",
            "        if (!\$draftStatus) {",
            "            return back()->with('error', 'Status draft tidak ditemukan');",
            "        }",
            "        ",
            "        // Create surat",
            "        \$surat = Surat::create([",
            "            'perihal' => \$request->perihal,",
            "            'isi_surat' => \$request->isi_surat,",
            "            'jenis_surat_id' => \$request->jenis_surat_id,",
            "            'tujuan_jabatan_id' => \$request->tujuan_jabatan_id,",
            "            'prodi_id' => \$user->prodi_id,",
            "            'created_by' => \$user->id,",
            "            'status_id' => \$draftStatus->id",
            "        ]);",
            "        ",
            "        Log::info('New surat created', ['surat_id' => \$surat->id, 'user_id' => \$user->id]);",
            "        ",
            "        return redirect()->route('dashboard')->with('success', 'Surat berhasil dibuat');",
            "    }"
        ]);
    }
    
    // Insert the new methods
    array_splice($controllerLines, $insertPosition, 0, $newMethods);
    
    // Save updated controller
    file_put_contents($controllerFile, implode("\n", $controllerLines));
    echo "✓ Added missing controller methods\n";
    
} else {
    echo "✓ All required methods already exist\n";
}

echo "\n4. Creating Create View\n";
echo str_repeat("-", 50) . "\n";

$createViewFile = 'resources/views/surat/create.blade.php';
if (!file_exists($createViewFile)) {
    echo "Creating surat create view...\n";
    
    $createViewContent = <<<'VIEW'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Buat Surat Baru</h1>
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="bg-white shadow-lg rounded-lg p-6">
            <form action="{{ route('staff.surat.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="perihal" class="block text-sm font-medium text-gray-700 mb-2">
                        Perihal
                    </label>
                    <input type="text" 
                           id="perihal" 
                           name="perihal" 
                           value="{{ old('perihal') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('perihal') border-red-500 @enderror"
                           required>
                    @error('perihal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="jenis_surat_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Surat
                    </label>
                    <select id="jenis_surat_id" 
                            name="jenis_surat_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenis_surat_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Jenis Surat</option>
                        @foreach($jenisSurat as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('jenis_surat_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_surat_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="tujuan_jabatan_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tujuan Jabatan
                    </label>
                    <select id="tujuan_jabatan_id" 
                            name="tujuan_jabatan_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tujuan_jabatan_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Tujuan Jabatan</option>
                        @foreach($jabatan as $jab)
                            <option value="{{ $jab->id }}" {{ old('tujuan_jabatan_id') == $jab->id ? 'selected' : '' }}>
                                {{ $jab->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('tujuan_jabatan_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="isi_surat" class="block text-sm font-medium text-gray-700 mb-2">
                        Isi Surat
                    </label>
                    <textarea id="isi_surat" 
                              name="isi_surat" 
                              rows="10"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('isi_surat') border-red-500 @enderror"
                              required>{{ old('isi_surat') }}</textarea>
                    @error('isi_surat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Simpan Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
VIEW;

    file_put_contents($createViewFile, $createViewContent);
    echo "✓ Created surat create view\n";
} else {
    echo "✓ Create view already exists\n";
}

echo "\n5. Testing New Routes\n";
echo str_repeat("-", 50) . "\n";

$testScript = <<<'TEST'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing staff routes:\n";

$routes = [
    'staff.surat.create',
    'staff.surat.store', 
    'staff.surat.submit',
    'staff.surat.tracking',
    'staff.surat.download'
];

foreach ($routes as $routeName) {
    try {
        if ($routeName === 'staff.surat.store') {
            $url = route($routeName);
        } else {
            $url = route($routeName, ['id' => 1]);
        }
        echo "✓ $routeName: $url\n";
    } catch (Exception $e) {
        echo "✗ $routeName: " . $e->getMessage() . "\n";
    }
}
TEST;

file_put_contents('test-staff-routes.php', $testScript);
$testOutput = shell_exec('php test-staff-routes.php 2>&1');
echo $testOutput;

echo "\n6. Clear Cache\n";
echo str_repeat("-", 50) . "\n";

$commands = [
    'php artisan route:clear',
    'php artisan optimize:clear'
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    $output = shell_exec("$command 2>&1");
    if ($output && strpos($output, 'successfully') !== false) {
        echo "✓ Success\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✓ Added staff.surat.create route\n";
echo "✓ Added staff.surat.store route\n";
echo "✓ Added create and store methods to SuratController\n";
echo "✓ Created surat create view\n";
echo "✓ Cleared route cache\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Restart server: php artisan serve\n";
echo "2. Test staff surat create functionality\n";
echo "3. Access: http://localhost:8000/staff/surat/create\n";

echo "\nRoute staff.surat.create should now be available!\n";

// Cleanup
unlink('test-staff-routes.php');