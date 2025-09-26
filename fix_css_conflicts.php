<?php
/**
 * FIX CSS CONFLICTS & LAYOUT
 * 
 * Script untuk membersihkan duplikasi CSS dan memperbaiki layout issues
 * 
 * File: fix_css_conflicts.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class CSSConflictFixer
{
    private $backupDir;
    private $fixes = [];
    
    public function __construct()
    {
        $this->backupDir = storage_path('backups/css_fix_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
        
        echo "🔧 CSS CONFLICT FIXER\n";
        echo "====================\n\n";
    }
    
    public function fix()
    {
        $this->backupFiles();
        $this->fixLayoutFile();
        $this->fixShowView();
        $this->clearCaches();
        $this->showSummary();
    }
    
    private function backupFiles()
    {
        echo "📦 Creating backups...\n";
        
        $files = [
            'resources/views/layouts/app.blade.php',
            'resources/views/staff/pengajuan/show.blade.php'
        ];
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $backup = $this->backupDir . '/' . basename($file);
                File::copy($file, $backup);
                echo "  ✅ Backed up: " . basename($file) . "\n";
            }
        }
    }
    
    private function fixLayoutFile()
    {
        echo "\n🏗️ Fixing layout file...\n";
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = File::get($layoutPath);
        
        // 1. Add missing @stack('styles')
        if (strpos($content, "@stack('styles')") === false) {
            // Add before </head>
            $content = str_replace(
                '</head>',
                "    @stack('styles')\n</head>",
                $content
            );
            $this->fixes[] = "Added missing @stack('styles') to layout";
            echo "  ✅ Added missing @stack('styles')\n";
        }
        
        // 2. Update z-index hierarchy with better values
        $oldZIndexCSS = '/* OPTIMIZED LAYOUT FIX */
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

        $newZIndexCSS = '<style>
/* CLEAN Z-INDEX HIERARCHY */
:root {
    --z-content: 1;
    --z-dropdown: 100;
    --z-navbar: 200;
    --z-modal-backdrop: 300;
    --z-modal: 400;
    --z-toast: 500;
}

/* Navbar positioning */
nav {
    z-index: var(--z-navbar);
    position: sticky;
    top: 0;
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
}

/* Main content */
main {
    position: relative;
    z-index: var(--z-content);
}

/* Modal specific */
#approveModal, 
#rejectModal {
    z-index: var(--z-modal-backdrop);
}

#approveModal > div,
#rejectModal > div {
    z-index: var(--z-modal);
}

/* Toast notifications */
.fixed.bottom-4.right-4 {
    z-index: var(--z-toast);
}

/* Prevent horizontal scroll */
body {
    overflow-x: hidden;
}

/* Alpine.js cloak */
[x-cloak] {
    display: none !important;
}
</style>';

        // Replace old CSS with new
        if (strpos($content, 'OPTIMIZED LAYOUT FIX') !== false) {
            $content = preg_replace(
                '/<style>.*?OPTIMIZED LAYOUT FIX.*?<\/style>/s',
                $newZIndexCSS,
                $content
            );
            $this->fixes[] = "Updated z-index hierarchy";
            echo "  ✅ Updated z-index hierarchy\n";
        }
        
        File::put($layoutPath, $content);
    }
    
    private function fixShowView()
    {
        echo "\n📄 Fixing show.blade.php...\n";
        
        $showPath = resource_path('views/staff/pengajuan/show.blade.php');
        $content = File::get($showPath);
        
        // 1. Remove duplicate CSS blocks
        $cssPattern = '/@push\([\'"]styles[\'"]\)(.*?)@endpush/s';
        preg_match_all($cssPattern, $content, $matches);
        
        if (count($matches[0]) > 1) {
            // Keep only the first @push('styles') block
            $firstBlock = $matches[0][0];
            $content = preg_replace($cssPattern, '', $content);
            
            // Re-add the first block at the end
            $insertPosition = strpos($content, '@endsection');
            if ($insertPosition !== false) {
                $content = substr_replace($content, "\n" . $firstBlock . "\n", $insertPosition, 0);
            }
            
            $this->fixes[] = "Removed duplicate CSS blocks";
            echo "  ✅ Removed duplicate @push('styles') blocks\n";
        }
        
        // 2. Clean up the CSS content
        $cleanedCSS = '@push(\'styles\')
<style>
/* MODAL STYLES - CLEANED */
#approveModal, 
#rejectModal {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Modal animations */
#approveModal.flex > div,
#rejectModal.flex > div {
    animation: modalSlideIn 0.3s ease-out forwards;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Button states */
button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Loading spinner */
@keyframes spin {
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Scrollbar styling */
.modal-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.modal-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.modal-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

/* Mobile responsive */
@media (max-width: 640px) {
    #approveModal > div,
    #rejectModal > div {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-footer button {
        width: 100%;
    }
}

/* Action buttons */
.action-button {
    opacity: 1;
    visibility: visible;
    position: relative;
}
</style>
@endpush';

        // Replace the entire @push('styles') section
        $content = preg_replace($cssPattern, $cleanedCSS, $content);
        $this->fixes[] = "Cleaned and optimized CSS";
        echo "  ✅ Cleaned and optimized CSS\n";
        
        // 3. Fix body overflow hidden issue
        $jsPattern = '/document\.body\.style\.overflow\s*=\s*[\'"]hidden[\'"]/';
        if (preg_match($jsPattern, $content)) {
            // Make sure enableBodyScroll is called properly
            $content = preg_replace(
                '/function\s+closeApproveModal\(\)\s*{/',
                'function closeApproveModal() {
    enableBodyScroll();',
                $content
            );
            
            $content = preg_replace(
                '/function\s+closeRejectModal\(\)\s*{/',
                'function closeRejectModal() {
    enableBodyScroll();',
                $content
            );
            
            $this->fixes[] = "Fixed body overflow handling";
            echo "  ✅ Fixed body overflow handling\n";
        }
        
        // 4. Remove excessive !important
        $importantReplacements = [
            'opacity: 1 !important' => 'opacity: 1',
            'visibility: visible !important' => 'visibility: visible',
            'position: relative !important' => 'position: relative',
            'display: inline-flex !important' => 'display: inline-flex',
        ];
        
        foreach ($importantReplacements as $old => $new) {
            $count = substr_count($content, $old);
            if ($count > 0) {
                $content = str_replace($old, $new, $content);
                echo "  ✅ Removed {$count} unnecessary !important from '{$old}'\n";
            }
        }
        
        File::put($showPath, $content);
    }
    
    private function clearCaches()
    {
        echo "\n🗑️ Clearing caches...\n";
        
        $commands = ['view:clear', 'config:clear', 'route:clear'];
        
        foreach ($commands as $command) {
            try {
                \Artisan::call($command);
                echo "  ✅ Cleared: {$command}\n";
            } catch (Exception $e) {
                echo "  ❌ Failed: {$command}\n";
            }
        }
    }
    
    private function showSummary()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "✅ FIX COMPLETED\n";
        echo str_repeat("=", 50) . "\n\n";
        
        echo "Fixed issues:\n";
        foreach ($this->fixes as $fix) {
            echo "  • {$fix}\n";
        }
        
        echo "\nBackup location: {$this->backupDir}\n";
        
        echo "\n🔄 NEXT STEPS:\n";
        echo "1. Hard refresh browser (Ctrl+Shift+R)\n";
        echo "2. Test navbar positioning\n";
        echo "3. Test modal functionality\n";
        echo "4. Check for console errors\n";
        
        echo "\n💡 If issues persist:\n";
        echo "• Run: npm run build\n";
        echo "• Check browser console for errors\n";
        echo "• Restore from backup if needed\n";
    }
}

// Execute
try {
    $fixer = new CSSConflictFixer();
    $fixer->fix();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>