<?php
// fix_blade_syntax.php
$file = 'resources/views/fakultas/surat/show.blade.php';
$content = file_get_contents($file);

// Backup
copy($file, $file . '.backup');

// Find and fix the problematic section
$pattern = '/@if\($pengajuan && \$pengajuan->canGeneratePdf\(\)\).*?@endif.*?@if\(\$jenisSurat/s';
$replacement = '@if($pengajuan && $pengajuan->canGeneratePdf())
            <button onclick="generateSuratPDF({{ $pengajuan->id }})" 
                    style="display: inline-flex; align-items: center; padding: 10px 20px; background-color: #7c3aed; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">
                <i class="fas fa-file-pdf" style="margin-right: 8px;"></i>
                Generate PDF Surat
            </button>
        @endif
        
        @if($jenisSurat';

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $content);
echo "Fixed blade syntax error!\n";
?>