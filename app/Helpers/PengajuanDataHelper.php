<?php

namespace App\Helpers;

class PengajuanDataHelper
{
    /**
     * Parse additional_data dengan konsisten
     */
    public static function parseAdditionalData($data)
    {
        if (empty($data)) {
            return null;
        }
        
        if (is_array($data)) {
            return $data;
        }
        
        if (is_string($data)) {
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                \Log::error('JSON decode error: ' . $e->getMessage());
            }
            return ['data' => $data];
        }
        
        if (is_object($data)) {
            return (array) $data;
        }
        
        return null;
    }
    
    /**
     * Get status color class
     */
    public static function getStatusColor($status)
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'approved_prodi' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'rejected_prodi' => 'bg-red-100 text-red-800',
            'surat_generated' => 'bg-purple-100 text-purple-800'
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }
}