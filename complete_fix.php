<?php
/**
 * COMPLETE LAYOUT DIAGNOSIS & FIX
 * 
 * Script untuk diagnosa mendalam dan fix total
 * 
 * File: complete_fix.php
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

class CompleteFix
{
    private $issues = [];
    private $backupDir;
    
    public function __construct()
    {
        $this->backupDir = storage_path('backups/complete_fix_' . date('Y-m-d_H-i-s'));
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
        
        echo "🔍 COMPLETE LAYOUT DIAGNOSIS & FIX\n";
        echo "===================================\n\n";
    }
    
    public function diagnoseAndFix()
    {
        $this->checkAssetCompilation();
        $this->checkTailwindCSS();
        $this->backupFiles();
        $this->fixCompleteLayout();
        $this->fixShowView();
        $this->rebuildAssets();
        $this->clearEverything();
        $this->showResults();
    }
    
    private function checkAssetCompilation()
    {
        echo "📦 Checking asset compilation...\n";
        
        // Check if Vite is configured properly
        if (File::exists(base_path('vite.config.js'))) {
            echo "  ✅ vite.config.js found\n";
            
            // Check if build directory exists
            $buildDir = public_path('build');
            if (!File::exists($buildDir)) {
                $this->issues[] = "Build directory missing - assets not compiled";
                echo "  ❌ Build directory missing!\n";
            } else {
                $manifestPath = $buildDir . '/manifest.json';
                if (!File::exists($manifestPath)) {
                    $this->issues[] = "Manifest file missing - run npm run build";
                    echo "  ❌ Manifest file missing!\n";
                } else {
                    echo "  ✅ Build manifest found\n";
                }
            }
        }
        
        // Check package.json
        if (File::exists(base_path('package.json'))) {
            $package = json_decode(File::get(base_path('package.json')), true);
            if (isset($package['devDependencies']['tailwindcss'])) {
                echo "  ✅ Tailwind CSS in dependencies\n";
            } else {
                $this->issues[] = "Tailwind CSS not in dependencies";
                echo "  ❌ Tailwind CSS not found in package.json\n";
            }
        }
    }
    
    private function checkTailwindCSS()
    {
        echo "\n🎨 Checking Tailwind configuration...\n";
        
        // Check tailwind.config.js
        if (!File::exists(base_path('tailwind.config.js'))) {
            $this->issues[] = "tailwind.config.js missing";
            echo "  ❌ tailwind.config.js missing!\n";
            $this->createTailwindConfig();
        } else {
            echo "  ✅ tailwind.config.js found\n";
        }
        
        // Check app.css for Tailwind directives
        $appCssPath = resource_path('css/app.css');
        if (File::exists($appCssPath)) {
            $content = File::get($appCssPath);
            if (strpos($content, '@tailwind') === false) {
                $this->issues[] = "Tailwind directives missing in app.css";
                echo "  ❌ Tailwind directives missing!\n";
                $this->fixAppCss();
            } else {
                echo "  ✅ Tailwind directives found\n";
            }
        } else {
            $this->issues[] = "app.css file missing";
            echo "  ❌ app.css missing!\n";
            $this->createAppCss();
        }
    }
    
    private function createTailwindConfig()
    {
        echo "  📝 Creating tailwind.config.js...\n";
        
        $config = "/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}";
        
        File::put(base_path('tailwind.config.js'), $config);
        echo "    ✅ Created tailwind.config.js\n";
    }
    
    private function createAppCss()
    {
        echo "  📝 Creating app.css...\n";
        
        $css = "@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom styles */
.btn-primary {
    @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors;
}

.btn-success {
    @apply bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors;
}

.btn-danger {
    @apply bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors;
}";
        
        if (!File::exists(resource_path('css'))) {
            File::makeDirectory(resource_path('css'), 0755, true);
        }
        
        File::put(resource_path('css/app.css'), $css);
        echo "    ✅ Created app.css with Tailwind directives\n";
    }
    
    private function fixAppCss()
    {
        echo "  🔧 Fixing app.css...\n";
        
        $path = resource_path('css/app.css');
        $content = File::get($path);
        
        if (strpos($content, '@tailwind base') === false) {
            $tailwindImports = "@tailwind base;\n@tailwind components;\n@tailwind utilities;\n\n";
            $content = $tailwindImports . $content;
            File::put($path, $content);
            echo "    ✅ Added Tailwind directives\n";
        }
    }
    
    private function backupFiles()
    {
        echo "\n📁 Creating backups...\n";
        
        $files = [
            'resources/views/layouts/app.blade.php',
            'resources/views/staff/pengajuan/show.blade.php',
            'resources/css/app.css',
            'vite.config.js',
            'tailwind.config.js'
        ];
        
        foreach ($files as $file) {
            if (File::exists($file)) {
                $backup = $this->backupDir . '/' . basename($file);
                File::copy($file, $backup);
                echo "  ✅ " . basename($file) . "\n";
            }
        }
    }
    
    private function fixCompleteLayout()
    {
        echo "\n🏗️ Fixing complete layout...\n";
        
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content = File::get($layoutPath);
        
        // 1. Check Vite directive
        if (strpos($content, '@vite') === false) {
            echo "  ❌ @vite directive missing!\n";
            
            // Add Vite after <title>
            $viteDirective = "\n    @vite(['resources/css/app.css', 'resources/js/app.js'])\n";
            $content = preg_replace('/(<\/title>)/', '$1' . $viteDirective, $content);
            echo "  ✅ Added @vite directive\n";
        }
        
        // 2. Add @stack('styles') if missing
        if (strpos($content, "@stack('styles')") === false) {
            $content = str_replace('</head>', "    @stack('styles')\n</head>", $content);
            echo "  ✅ Added @stack('styles')\n";
        }
        
        // 3. Fix CDN links (add Tailwind CDN as fallback)
        if (strpos($content, 'cdn.tailwindcss.com') === false) {
            $tailwindCDN = '    <!-- Tailwind CSS CDN Fallback -->
    <script src="https://cdn.tailwindcss.com"></script>';
            
            $content = str_replace('</head>', $tailwindCDN . "\n</head>", $content);
            echo "  ✅ Added Tailwind CDN as fallback\n";
        }
        
        // 4. Remove problematic CSS
        $content = preg_replace('/<style>.*?OPTIMIZED LAYOUT FIX.*?<\/style>/s', '', $content);
        
        // 5. Add clean CSS
        $cleanCSS = '    <style>
        /* Clean Layout CSS */
        nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: white;
        }
        
        main {
            position: relative;
            z-index: 1;
        }
        
        .modal {
            z-index: 50;
        }
    </style>';
        
        $content = str_replace('</head>', $cleanCSS . "\n</head>", $content);
        
        File::put($layoutPath, $content);
        echo "  ✅ Layout fixed completely\n";
    }
    
    private function fixShowView()
    {
        echo "\n📄 Cleaning show.blade.php...\n";
        
        $showPath = resource_path('views/staff/pengajuan/show.blade.php');
        if (!File::exists($showPath)) {
            echo "  ❌ show.blade.php not found!\n";
            return;
        }
        
        $content = File::get($showPath);
        
        // Remove ALL @push('styles') blocks
        $content = preg_replace('/@push\([\'"]styles[\'"]\).*?@endpush/s', '', $content);
        echo "  ✅ Removed all inline styles\n";
        
        // Add minimal required styles
        $minimalStyles = '@push(\'styles\')
<style>
/* Minimal modal styles */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}
</style>
@endpush';
        
        // Add before @endsection
        $content = str_replace('@endsection', $minimalStyles . "\n@endsection", $content);
        
        File::put($showPath, $content);
        echo "  ✅ Show view cleaned\n";
    }
    
    private function rebuildAssets()
    {
        echo "\n🔨 Rebuilding assets...\n";
        
        // Check if npm/node exists
        $npmExists = shell_exec('npm -v 2>&1');
        if ($npmExists) {
            echo "  📦 Installing dependencies...\n";
            shell_exec('npm install 2>&1');
            
            echo "  🏗️ Building assets...\n";
            shell_exec('npm run build 2>&1');
            
            echo "  ✅ Assets rebuilt\n";
        } else {
            echo "  ❌ NPM not found - please run manually:\n";
            echo "     npm install\n";
            echo "     npm run build\n";
        }
    }
    
    private function clearEverything()
    {
        echo "\n🗑️ Clearing all caches...\n";
        
        $commands = [
            'view:clear',
            'config:clear',
            'route:clear',
            'cache:clear',
            'optimize:clear'
        ];
        
        foreach ($commands as $command) {
            try {
                \Artisan::call($command);
                echo "  ✅ {$command}\n";
            } catch (Exception $e) {
                echo "  ❌ {$command} failed\n";
            }
        }
        
        // Clear compiled views manually
        $viewsPath = storage_path('framework/views');
        if (File::exists($viewsPath)) {
            File::cleanDirectory($viewsPath);
            echo "  ✅ Manually cleared compiled views\n";
        }
    }
    
    private function showResults()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 RESULTS\n";
        echo str_repeat("=", 50) . "\n\n";
        
        if (count($this->issues) > 0) {
            echo "Issues found and fixed:\n";
            foreach ($this->issues as $issue) {
                echo "  • {$issue}\n";
            }
        } else {
            echo "✅ No major issues found\n";
        }
        
        echo "\n🎯 NEXT STEPS:\n";
        echo "1. Run: npm install\n";
        echo "2. Run: npm run build\n";
        echo "3. Run: php artisan serve\n";
        echo "4. Hard refresh browser (Ctrl+Shift+Delete -> Clear Cache)\n";
        echo "5. Test the page again\n";
        
        echo "\n💾 Backup saved to:\n";
        echo "{$this->backupDir}\n";
    }
}

// Execute
try {
    $fixer = new CompleteFix();
    $fixer->diagnoseAndFix();
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>