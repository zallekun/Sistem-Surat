<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$corrupted = DB::table('pengajuan_surats')
    ->whereRaw("additional_data LIKE '\"%' OR additional_data LIKE '%\\\\\"%'")
    ->get();

echo "Found " . $corrupted->count() . " records to fix\n";

foreach ($corrupted as $row) {
    try {
        $data = $row->additional_data;
        
        // Step 1: Remove outer quotes if present
        $data = trim($data, '"');
        
        // Step 2: Unescape the JSON (remove backslashes)
        $data = stripslashes($data);
        
        // Step 3: Fix semester integer if needed
        $data = preg_replace('/"semester":(\d+)/', '"semester":"$1"', $data);
        
        // Step 4: Validate JSON
        $test = json_decode($data, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Valid JSON, update with proper JSON string
            DB::table('pengajuan_surats')
                ->where('id', $row->id)
                ->update(['additional_data' => $data]);
            echo "✓ Fixed ID: {$row->id}\n";
        } else {
            // If still error, try more aggressive cleaning
            $data = str_replace('\\"', '"', $data);
            $data = str_replace('\\/', '/', $data);
            
            $test = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                DB::table('pengajuan_surats')
                    ->where('id', $row->id)
                    ->update(['additional_data' => $data]);
                echo "✓ Fixed ID: {$row->id} (aggressive clean)\n";
            } else {
                echo "✗ Failed ID: {$row->id} - " . json_last_error_msg() . "\n";
                echo "  Raw: " . substr($row->additional_data, 0, 50) . "...\n";
                echo "  Cleaned: " . substr($data, 0, 50) . "...\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error ID: {$row->id} - " . $e->getMessage() . "\n";
    }
}

echo "Done!\n";