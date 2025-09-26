<?php
/**
 * TARGETED LAYOUT FIX
 * 
 * Fix spesifik berdasarkan layout app.blade.php yang sebenarnya
 * 
 * File: targeted_layout_fix.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class TargetedLayoutFixer
{
    private $backupDir;
    
    public function __construct()
    {
        $this->backupDir = storage_path('backups/targeted_fix_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
        
        echo "🎯 TARGETED LAYOUT FIX\n";
        echo "======================\n\n";
    }
    
    public function fix()
    {
        $this->backupFiles();
        $this->fixLayoutStructure();
        $this->fixShowViewModal();
        $this->addViewportMeta();
        $this->optimizeCSS();
        $this->clearCaches();
        
        echo "\n✅ TARGETED FIX COMPLETED!\n";
        echo "Backup: {$this->backupDir}\n\n";
        
        echo "🧪 TEST STEPS:\n";
        echo "1. Hard refresh browser (Ctrl+Shift+R)\n";
        echo "2. Check navbar is on top\n";
        echo "3. Test modal functionality\n";
        echo "4. Check responsive design\n";
    }
    
    private function backupFiles()
    {
        echo "📦 Creating targeted backups...\n";
        
        $files = [
            'resources/views/layouts/app.blade.php',
            'resources/views/staff/pengajuan/show.blade.php'
        ];
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $backup = $this->backupDir . '/' . basename($file);
                File::copy($file, $backup);
                echo "  ✅ {$file}\n";
            }
        }
    }
    
    private function fixLayoutStructure()
    {
        echo "\n🏗️ Fixing layout structure...\n";
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = File::get($layoutPath);
        
        // Replace the existing problematic CSS with optimized version
        $oldCSS = '/* Z-INDEX HIERARCHY FIX */
nav, .navbar {
    z-index: 1030 !important;
    position: relative !important;
}

/* Modal z-index should be lower than navbar */
.modal, [id*=\'Modal\'] {
    z-index: 1020 !important;
}

/* Ensure proper stacking */
main, .main-content {
    z-index: 1 !important;
    position: relative !important;
}';

        $newCSS = '/* OPTIMIZED LAYOUT FIX */
:root {
    --z-navbar: 1030;
    --z-modal: 1025;
    --z-toast: 1050;
    --z-dropdown: 1020;
}

/* Navbar always on top */
nav {
    z-index: var(--z-navbar);
    position: relative;
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
}

/* Modal positioning */
.modal, 
[id*="Modal"], 
[id$="modal"], 
.fixed.inset-0 {
    z-index: var(--z-modal);
}

/* Toast notifications above modals */
.fixed.bottom-4.right-4 {
    z-index: var(--z-toast);
}

/* Dropdown menus */
[x-data*="dropdown"], 
.dropdown-menu {
    z-index: var(--z-dropdown);
}

/* Main content normal flow */
main {
    position: relative;
    z-index: 1;
}

/* Prevent layout shifts */
body {
    overflow-x: hidden;
}

/* Fix Alpine.js flickering */
[x-cloak] { 
    display: none !important; 
}';

        if (strpos($content, 'Z-INDEX HIERARCHY FIX') !== false) {
            $content = str_replace($oldCSS, $newCSS, $content);
            echo "  ✅ Replaced old CSS with optimized version\n";
        } else {
            // Add new CSS before </head>
            $content = str_replace('</head>', $newCSS . "\n</style>\n</head>", $content);
            echo "  ✅ Added optimized CSS\n";
        }
        
        File::put($layoutPath, $content);
    }
    
    private function fixShowViewModal()
    {
        echo "\n🖼️ Fixing modal in show view...\n";
        
        $showPath = resource_path('views/staff/pengajuan/show.blade.php');
        if (!File::exists($showPath)) {
            echo "  ❌ Show view not found\n";
            return;
        }
        
        $content = File::get($showPath);
        
        // Fix modal z-index values
        $modalFixes = [
            'z-[9999]' => 'z-[1025]',
            'z-index: 9999' => 'z-index: 1025',
            'z-9999' => 'z-[1025]'
        ];
        
        $fixedCount = 0;
        foreach ($modalFixes as $old => $new) {
            $count = substr_count($content, $old);
            if ($count > 0) {
                $content = str_replace($old, $new, $content);
                $fixedCount += $count;
            }
        }
        
        echo "  ✅ Fixed {$fixedCount} modal z-index values\n";
        
        // Optimize modal backdrop
        $backdropFix = 'backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    background-color: rgba(0, 0, 0, 0.6);';
        
        if (strpos($content, 'backdrop-filter: blur(8px)') === false) {
            $content = str_replace(
                'background-color: rgba(0, 0, 0, 0.6);',
                $backdropFix,
                $content
            );
            echo "  ✅ Added backdrop blur optimization\n";
        }
        
        // Remove excessive !important declarations
        $unnecessaryImportant = [
            'display: inline-flex !important;' => 'display: inline-flex;',
            'align-items: center !important;' => 'align-items: center;',
            'padding: 8px 16px !important;' => 'padding: 8px 16px;',
            'border-radius: 6px !important;' => 'border-radius: 6px;',
            'font-size: 0.875rem !important;' => 'font-size: 0.875rem;',
            'font-weight: 500 !important;' => 'font-weight: 500;'
        ];
        
        $removedImportant = 0;
        foreach ($unnecessaryImportant as $old => $new) {
            $count = substr_count($content, $old);
            if ($count > 0) {
                $content = str_replace($old, $new, $content);
                $removedImportant += $count;
            }
        }
        
        echo "  ✅ Removed {$removedImportant} unnecessary !important\n";
        
        File::put($showPath, $content);
    }
    
    private function addViewportMeta()
    {
        echo "\n📱 Adding viewport meta...\n";
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = File::get($layoutPath);
        
        // Check if viewport already exists
        if (strpos($content, 'name="viewport"') !== false) {
            echo "  ✅ Viewport meta already exists\n";
            return;
        }
        
        // Find the head section and add viewport after charset
        $viewportMeta = '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        
        // Look for existing meta tags and add after them
        if (preg_match('/<head[^>]*>/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPos = $matches[0][1] + strlen($matches[0][0]);
            $content = substr_replace($content, "\n    " . $viewportMeta, $insertPos, 0);
            echo "  ✅ Added viewport meta tag\n";
        }
        
        File::put($layoutPath, $content);
    }
    
    private function optimizeCSS()
    {
        echo "\n🎨 CSS optimization...\n";
        
        // Create optimized CSS file
        $optimizedCSS = '/* SISTEM PERSURATAN - OPTIMIZED STYLES */

/* CSS Custom Properties for consistent theming */
:root {
    --primary-color: #2563eb;
    --success-color: #16a34a;
    --danger-color: #dc2626;
    --warning-color: #d97706;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    
    --transition-fast: 150ms ease-in-out;
    --transition-normal: 300ms ease-in-out;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
}

/* Layout stability */
html {
    scroll-behavior: smooth;
}

body {
    overflow-x: hidden;
    line-height: 1.6;
}

/* Button consistency */
.btn-primary {
    background-color: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    transition: var(--transition-fast);
}

.btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

.btn-success {
    background-color: var(--success-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    transition: var(--transition-fast);
}

.btn-success:hover {
    background-color: #15803d;
    transform: translateY(-1px);
}

.btn-danger {
    background-color: var(--danger-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    font-weight: 500;
    transition: var(--transition-fast);
}

.btn-danger:hover {
    background-color: #b91c1c;
    transform: translateY(-1px);
}

/* Modal improvements */
.modal-backdrop {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.modal-content {
    box-shadow: var(--shadow-lg);
    border-radius: 0.75rem;
    overflow: hidden;
}

/* Loading states */
.loading-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Focus improvements */
.focus-visible {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .modal-content {
        margin: 1rem;
        width: calc(100% - 2rem);
    }
    
    .btn-primary,
    .btn-success,
    .btn-danger {
        width: 100%;
        justify-content: center;
    }
}';

        $cssPath = public_path('css/optimized.css');
        if (!File::exists(dirname($cssPath))) {
            File::makeDirectory(dirname($cssPath), 0755, true);
        }
        
        File::put($cssPath, $optimizedCSS);
        echo "  ✅ Created optimized CSS file\n";
        
        // Add reference to layout if not exists
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = File::get($layoutPath);
        
        if (strpos($content, 'optimized.css') === false) {
            $cssLink = '<link rel="stylesheet" href="{{ asset(\'css/optimized.css\') }}">';
            $content = str_replace(
                '<link rel="stylesheet" href="https://cdnjs.cloudflare.com',
                $cssLink . "\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com",
                $content
            );
            
            File::put($layoutPath, $content);
            echo "  ✅ Added optimized CSS reference to layout\n";
        }
    }
    
    private function clearCaches()
    {
        echo "\n🗑️ Clearing caches...\n";
        
        $commands = [
            'view:clear',
            'config:clear', 
            'route:clear',
            'cache:clear'
        ];
        
        foreach ($commands as $command) {
            try {
                \Artisan::call($command);
                echo "  ✅ {$command}\n";
            } catch (Exception $e) {
                echo "  ❌ {$command}: {$e->getMessage()}\n";
            }
        }
        
        // Clear browser cache instruction
        echo "\n💡 BROWSER CACHE:\n";
        echo "Press Ctrl+Shift+R (hard refresh) to clear browser cache\n";
    }
}

// Execute
try {
    $fixer = new TargetedLayoutFixer();
    $fixer->fix();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>