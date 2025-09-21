<?php
/**
 * Debug Views Structure
 * Run: php debug_views.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG VIEWS STRUCTURE ===\n\n";

if (!file_exists('artisan')) {
    die("ERROR: Run from Laravel root!\n");
}

// 1. CHECK VIEWS DIRECTORY
echo "1. CHECKING VIEWS DIRECTORY\n";
echo "============================\n";

$viewsPath = resource_path('views');
if (!is_dir($viewsPath)) {
    echo "✗ Views directory not found: $viewsPath\n";
    exit(1);
}

echo "✓ Views directory found: $viewsPath\n";

// 2. SCAN ALL VIEW DIRECTORIES
function scanDirectory($dir, $prefix = '') {
    $items = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            $viewName = $prefix . $file;
            
            if (is_dir($fullPath)) {
                echo "📁 $viewName/\n";
                $items = array_merge($items, scanDirectory($fullPath, $viewName . '.'));
            } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $viewKey = $prefix . pathinfo($file, PATHINFO_FILENAME);
                echo "📄 $viewKey\n";
                $items[] = $viewKey;
            }
        }
    }
    return $items;
}

echo "\n2. ALL AVAILABLE VIEWS\n";
echo "======================\n";
$allViews = scanDirectory($viewsPath);

// 3. CHECK SPECIFIC MISSING VIEW
echo "\n3. CHECKING MISSING VIEW: surat.index\n";
echo "======================================\n";

$suratIndexPaths = [
    resource_path('views/surat/index.blade.php'),
    resource_path('views/surat/index.php'),
    resource_path('views/surats/index.blade.php'),
    resource_path('views/surats/index.php'),
];

$found = false;
foreach ($suratIndexPaths as $path) {
    if (file_exists($path)) {
        echo "✓ Found: $path\n";
        $found = true;
    } else {
        echo "✗ Not found: $path\n";
    }
}

// 4. CHECK SURAT RELATED VIEWS
echo "\n4. SURAT RELATED VIEWS\n";
echo "======================\n";

$suratViews = array_filter($allViews, function($view) {
    return strpos($view, 'surat') !== false;
});

if (empty($suratViews)) {
    echo "✗ No surat-related views found\n";
} else {
    echo "Found surat-related views:\n";
    foreach ($suratViews as $view) {
        echo "  ✓ $view\n";
    }
}

// 5. CHECK STAFF VIEWS
echo "\n5. STAFF RELATED VIEWS\n";
echo "======================\n";

$staffViews = array_filter($allViews, function($view) {
    return strpos($view, 'staff') !== false;
});

if (empty($staffViews)) {
    echo "✗ No staff-related views found\n";
} else {
    echo "Found staff-related views:\n";
    foreach ($staffViews as $view) {
        echo "  ✓ $view\n";
    }
}

// 6. SUGGEST SOLUTION
echo "\n6. SOLUTION SUGGESTIONS\n";
echo "========================\n";

if (!$found) {
    echo "PROBLEM: View 'surat.index' not found\n\n";
    echo "SOLUTIONS:\n";
    echo "1. Create missing view file\n";
    echo "2. Check if view uses different name\n";
    echo "3. Change controller to use existing view\n\n";
    
    // Check if there are similar views
    $similarViews = array_filter($allViews, function($view) {
        return strpos($view, 'index') !== false;
    });
    
    if (!empty($similarViews)) {
        echo "Similar index views found (could be used as template):\n";
        foreach ($similarViews as $view) {
            echo "  - $view\n";
        }
    }
    
    // Check if layouts exist
    echo "\nChecking for layouts:\n";
    $layoutViews = array_filter($allViews, function($view) {
        return strpos($view, 'layout') !== false || strpos($view, 'app') !== false;
    });
    
    foreach ($layoutViews as $layout) {
        echo "  ✓ $layout\n";
    }
}

// 7. GENERATE MISSING VIEW
echo "\n7. GENERATING MISSING VIEW\n";
echo "==========================\n";

$suratDir = resource_path('views/surat');
if (!is_dir($suratDir)) {
    echo "Creating surat views directory...\n";
    if (mkdir($suratDir, 0755, true)) {
        echo "✓ Directory created: $suratDir\n";
    } else {
        echo "✗ Failed to create directory: $suratDir\n";
    }
}

$indexViewPath = $suratDir . '/index.blade.php';

// Determine layout to extend
$layout = 'app';
if (in_array('layouts.app', $allViews)) {
    $layout = 'layouts.app';
} elseif (in_array('layout.app', $allViews)) {
    $layout = 'layout.app';
} elseif (in_array('layouts.main', $allViews)) {
    $layout = 'layouts.main';
} elseif (in_array('layouts.admin', $allViews)) {
    $layout = 'layouts.admin';
}

$viewContent = '@extends(\'' . $layout . '\')

@section(\'title\', \'Daftar Surat\')

@section(\'content\')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Surat</h3>
                    <div class="card-tools">
                        <a href="{{ route(\'staff.surat.create\') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Surat Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($surats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nomor Surat</th>
                                        <th>Tanggal</th>
                                        <th>Perihal</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($surats as $index => $surat)
                                    <tr>
                                        <td>{{ $surats->firstItem() + $index }}</td>
                                        <td>{{ $surat->nomor_surat ?? \'-\' }}</td>
                                        <td>{{ $surat->tanggal_surat ? $surat->tanggal_surat->format(\'d/m/Y\') : \'-\' }}</td>
                                        <td>{{ Str::limit($surat->perihal, 50) }}</td>
                                        <td>
                                            @if($surat->jenisSurat)
                                                <span class="badge badge-info">{{ $surat->jenisSurat->nama }}</span>
                                            @else
                                                <span class="badge badge-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($surat->currentStatus)
                                                <span class="badge" style="background-color: {{ $surat->currentStatus->warna_status ?? \'#6c757d\' }}">
                                                    {{ $surat->currentStatus->nama ?? $surat->status }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($surat->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route(\'staff.surat.show\', $surat->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route(\'staff.surat.edit\', $surat->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $surats->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada surat</h5>
                            <p class="text-muted">Silakan buat surat baru untuk memulai.</p>
                            <a href="{{ route(\'staff.surat.create\') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Surat Baru
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
';

if (file_put_contents($indexViewPath, $viewContent)) {
    echo "✓ Created view: $indexViewPath\n";
    echo "  Layout used: $layout\n";
} else {
    echo "✗ Failed to create view: $indexViewPath\n";
}

echo "\n=== DEBUG COMPLETED ===\n";
echo "\nNEXT STEPS:\n";
echo "1. Test the application: php artisan serve\n";
echo "2. Visit: http://localhost:8000/staff/surat\n";
echo "3. If layout issues, check available layouts and update view\n";