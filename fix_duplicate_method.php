<?php
/**
 * Fix Duplicate Method in FakultasStaffController
 * File: fix_duplicate_method.php
 * 
 * Removes duplicate show() method declaration
 */

class DuplicateMethodFixer {
    private $backupDir = 'storage/method_fix_backups';
    private $controllerPath = 'app/Http/Controllers/FakultasStaffController.php';
    
    public function run() {
        echo "\n===== FIX DUPLICATE METHOD SCRIPT =====\n\n";
        
        if (!file_exists($this->controllerPath)) {
            echo "❌ ERROR: File FakultasStaffController.php tidak ditemukan!\n";
            return;
        }
        
        // Create backup
        $this->createBackup();
        
        // Read file content
        $content = file_get_contents($this->controllerPath);
        
        // Find all show() method positions
        $pattern = '/public\s+function\s+show\s*\(/';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (count($matches[0]) > 1) {
            echo "⚠️  Found " . count($matches[0]) . " show() methods\n";
            echo "📍 Positions: ";
            foreach ($matches[0] as $match) {
                $line = $this->getLineNumber($content, $match[1]);
                echo "Line $line, ";
            }
            echo "\n\n";
            
            // Keep only the first complete show() method
            $fixedContent = $this->removeSecondShowMethod($content);
            
            // Save fixed content
            if (file_put_contents($this->controllerPath, $fixedContent)) {
                echo "✅ SUCCESS: Duplicate method removed!\n";
                echo "📁 Backup saved at: {$this->backupDir}\n";
            } else {
                echo "❌ ERROR: Failed to write fixed file\n";
            }
            
        } else if (count($matches[0]) == 1) {
            echo "✅ Only one show() method found - no duplicates\n";
        } else {
            echo "⚠️  No show() method found\n";
        }
    }
    
    private function createBackup() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $this->backupDir . '/FakultasStaffController.' . $timestamp . '.backup';
        
        if (copy($this->controllerPath, $backupPath)) {
            echo "✅ Backup created: $backupPath\n\n";
        }
    }
    
    private function getLineNumber($content, $offset) {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
    
    private function removeSecondShowMethod($content) {
        // Find the positions of both show methods
        $lines = explode("\n", $content);
        $inShow = false;
        $braceCount = 0;
        $showCount = 0;
        $startLine = -1;
        $endLine = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            // Check if this is the start of a show method
            if (preg_match('/public\s+function\s+show\s*\(/', $line)) {
                $showCount++;
                
                if ($showCount == 2) {
                    $startLine = $i;
                    $inShow = true;
                    $braceCount = 0;
                }
            }
            
            // Count braces if we're in the second show method
            if ($inShow) {
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');
                
                // Check if method is complete
                if ($braceCount == 0 && strpos($line, '}') !== false) {
                    $endLine = $i;
                    break;
                }
            }
        }
        
        // Remove the second show method
        if ($startLine >= 0 && $endLine >= 0) {
            echo "🔧 Removing duplicate method from line " . ($startLine + 1) . " to " . ($endLine + 1) . "\n";
            
            // Remove lines
            for ($i = $endLine; $i >= $startLine; $i--) {
                unset($lines[$i]);
            }
            
            // Also remove the parseAdditionalData method if it appears twice
            $fixedContent = implode("\n", $lines);
            $fixedContent = $this->removeDuplicateParseMethod($fixedContent);
            
            return $fixedContent;
        }
        
        return $content;
    }
    
    private function removeDuplicateParseMethod($content) {
        // Check for duplicate parseAdditionalData method
        $pattern = '/private\s+function\s+parseAdditionalData\s*\([^)]*\)\s*\{/';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        
        if (count($matches[0]) > 1) {
            echo "🔧 Found duplicate parseAdditionalData method, removing...\n";
            
            // Similar logic to remove the second occurrence
            $lines = explode("\n", $content);
            $inMethod = false;
            $braceCount = 0;
            $methodCount = 0;
            $startLine = -1;
            $endLine = -1;
            
            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];
                
                if (preg_match('/private\s+function\s+parseAdditionalData\s*\(/', $line)) {
                    $methodCount++;
                    
                    if ($methodCount == 2) {
                        $startLine = $i;
                        $inMethod = true;
                        $braceCount = 0;
                    }
                }
                
                if ($inMethod) {
                    $braceCount += substr_count($line, '{');
                    $braceCount -= substr_count($line, '}');
                    
                    if ($braceCount == 0 && strpos($line, '}') !== false) {
                        $endLine = $i;
                        break;
                    }
                }
            }
            
            if ($startLine >= 0 && $endLine >= 0) {
                for ($i = $endLine; $i >= $startLine; $i--) {
                    unset($lines[$i]);
                }
                return implode("\n", $lines);
            }
        }
        
        return $content;
    }
    
    public function restore() {
        echo "\n===== RESTORE FROM BACKUP =====\n\n";
        
        if (!is_dir($this->backupDir)) {
            echo "❌ No backup directory found\n";
            return;
        }
        
        // Get latest backup
        $backups = glob($this->backupDir . '/FakultasStaffController.*.backup');
        if (empty($backups)) {
            echo "❌ No backup files found\n";
            return;
        }
        
        rsort($backups);
        $latestBackup = $backups[0];
        
        echo "📁 Restoring from: " . basename($latestBackup) . "\n";
        
        if (copy($latestBackup, $this->controllerPath)) {
            echo "✅ File restored successfully\n";
        } else {
            echo "❌ Failed to restore file\n";
        }
    }
}

// Main execution
try {
    if (!file_exists('artisan')) {
        throw new Exception("Script harus dijalankan dari root Laravel project!");
    }
    
    $fixer = new DuplicateMethodFixer();
    
    if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === 'restore') {
        $fixer->restore();
    } else {
        $fixer->run();
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n";