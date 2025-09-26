<?php
/**
 * QUICK LAYOUT FIX
 * 
 * Script untuk memperbaiki masalah layout berdasarkan hasil debug
 * 
 * File: quick_layout_fix.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class QuickLayoutFixer
{
    private $backupDir;
    
    public function __construct()
    {
        $this->backupDir = storage_path('backups/layout_fix_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
        
        echo "🔧 QUICK LAYOUT FIX\n";
        echo "===================\n\n";
    }
    
    public function fixLayout()
    {
        $this->backupFiles();
        $this->fixViewportMeta();
        $this->fixZIndexHierarchy();
        $this->reduceImportantUsage();
        $this->createMissingJsDirectory();
        $this->clearCaches();
        
        echo "\n✅ LAYOUT FIX COMPLETED!\n";
        echo "Backup location: {$this->backupDir}\n\n";
        
        echo "🔄 NEXT STEPS:\n";
        echo "1. Refresh your browser (Ctrl+F5)\n";
        echo "2. Test navbar positioning\n";
        echo "3. Test modal functionality\n";
        echo "4. If issues persist, restore from backup\n";
    }
    
    private function backupFiles()
    {
        echo "📦 Creating backups...\n";
        
        $filesToBackup = [
            'resources/views/layouts/app.blade.php',
            'resources/views/staff/pengajuan/show.blade.php'
        ];
        
        foreach ($filesToBackup as $file) {
            if (File::exists($file)) {
                $backupFile = $this->backupDir . '/' . str_replace(['/', '\\'], '_', $file);
                File::copy($file, $backupFile);
                echo "  ✅ Backed up: {$file}\n";
            }
        }
    }
    
    private function fixViewportMeta()
    {
        echo "\n🔧 Fixing viewport meta tag...\n";
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        if (!File::exists($layoutPath)) {
            echo "  ❌ Layout file not found\n";
            return;
        }
        
        $content = File::get($layoutPath);
        
        // Check if viewport already exists
        if (stripos($content, 'name="viewport"') !== false) {
            echo "  ✅ Viewport meta already exists\n";
            return;
        }
        
        // Add viewport meta after charset
        $viewportMeta = '<meta name="viewport" content="width=device-width, initial-scale=1">';
        
        if (stripos($content, 'charset=') !== false) {
            $content = preg_replace(
                '/<meta\s+charset="[^"]*"[^>]*>/i',
                '$0' . "\n    " . $viewportMeta,
                $content
            );
        } else {
            // Add after <head>
            $content = str_replace('<head>', "<head>\n    " . $viewportMeta, $content);
        }
        
        File::put($layoutPath, $content);
        echo "  ✅ Viewport meta tag added\n";
    }
    
    private function fixZIndexHierarchy()
    {
        echo "\n🏗️ Fixing z-index hierarchy...\n";
        
        $showPath = resource_path('views/staff/pengajuan/show.blade.php');
        if (!File::exists($showPath)) {
            echo "  ❌ Show view not found\n";
            return;
        }
        
        $content = File::get($showPath);
        
        // Replace problematic z-index values
        $zIndexFixes = [
            'z-[9999]' => 'z-[1045]',
            'z-index: 9999' => 'z-index: 1045',
            'z-9999' => 'z-[1045]'
        ];
        
        foreach ($zIndexFixes as $old => $new) {
            if (stripos($content, $old) !== false) {
                $content = str_ireplace($old, $new, $content);
                echo "  ✅ Fixed: {$old} → {$new}\n";
            }
        }
        
        // Add navbar z-index fix to layout
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $layoutContent = File::get($layoutPath);
        
        $navbarFix = "
<style>
/* Z-INDEX HIERARCHY FIX */
nav, .navbar {
    z-index: 1030 !important;
    position: relative !important;
}

/* Modal z-index should be lower than navbar */
.modal, [id*='Modal'] {
    z-index: 1020 !important;
}

/* Ensure proper stacking */
main, .main-content {
    z-index: 1 !important;
    position: relative !important;
}
</style>";
        
        // Add before closing </head>
        if (stripos($layoutContent, 'Z-INDEX HIERARCHY FIX') === false) {
            $layoutContent = str_replace('</head>', $navbarFix . "\n</head>", $layoutContent);
            File::put($layoutPath, $layoutContent);
            echo "  ✅ Added navbar z-index fix to layout\n";
        }
        
        File::put($showPath, $content);
    }
    
    private function reduceImportantUsage()
    {
        echo "\n🎨 Reducing !important usage...\n";
        
        $showPath = resource_path('views/staff/pengajuan/show.blade.php');
        $content = File::get($showPath);
        
        // Remove unnecessary !important from common properties
        $unnecessaryImportant = [
            'display: inline-flex !important' => 'display: inline-flex',
            'align-items: center !important' => 'align-items: center',
            'padding: 8px 16px !important' => 'padding: 8px 16px',
            'border-radius: 6px !important' => 'border-radius: 6px',
            'font-size: 0.875rem !important' => 'font-size: 0.875rem',
            'font-weight: 500 !important' => 'font-weight: 500',
        ];
        
        $removedCount = 0;
        foreach ($unnecessaryImportant as $old => $new) {
            $oldCount = substr_count($content, $old);
            if ($oldCount > 0) {
                $content = str_replace($old, $new, $content);
                $removedCount += $oldCount;
            }
        }
        
        File::put($showPath, $content);
        echo "  ✅ Removed {$removedCount} unnecessary !important declarations\n";
    }
    
    private function createMissingJsDirectory()
    {
        echo "\n📁 Creating missing JS directory...\n";
        
        $jsDir = public_path('js');
        if (!File::exists($jsDir)) {
            File::makeDirectory($jsDir, 0755, true);
            echo "  ✅ Created public/js directory\n";
            
            // Create a dummy file to prevent 404s
            File::put($jsDir . '/app.js', "// Laravel app.js\nconsole.log('App JS loaded');");
            echo "  ✅ Created dummy app.js file\n";
        } else {
            echo "  ✅ JS directory already exists\n";
        }
    }
    
    private function clearCaches()
    {
        echo "\n🗑️ Clearing caches...\n";
        
        $commands = [
            'view:clear' => 'View cache',
            'config:clear' => 'Config cache',
            'route:clear' => 'Route cache',
            'cache:clear' => 'Application cache'
        ];
        
        foreach ($commands as $command => $description) {
            try {
                \Artisan::call($command);
                echo "  ✅ Cleared: {$description}\n";
            } catch (Exception $e) {
                echo "  ❌ Failed to clear {$description}: {$e->getMessage()}\n";
            }
        }
    }
}

// === MAIN EXECUTION ===
if (php_sapi_name() === 'cli') {
    try {
        $fixer = new QuickLayoutFixer();
        $fixer->fixLayout();
        
    } catch (Exception $e) {
        echo "\n❌ FIX ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    header('Content-Type: text/plain; charset=utf-8');
    try {
        $fixer = new QuickLayoutFixer();
        $fixer->fixLayout();
        
    } catch (Exception $e) {
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>