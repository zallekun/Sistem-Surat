<?php

namespace App\Helpers;

class StatusHelper
{
    /**
     * Get the text color (black or white) that contrasts best with a given background color.
     *
     * @param string $hexColor
     * @return string
     */
    public static function getTextColor($hexColor)
    {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) == 3) {
            $hexColor = $hexColor[0] . $hexColor[0] . $hexColor[1] . $hexColor[1] . $hexColor[2] . $hexColor[2];
        }
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        return ($yiq >= 128) ? '#000000' : '#FFFFFF';
    }

    public static function getPengajuanStatusColor($status)
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'approved_prodi' => 'bg-blue-100 text-blue-800',
            'approved_prodi_direct_fakultas' => 'bg-indigo-100 text-indigo-800',
            'rejected_prodi' => 'bg-red-100 text-red-800',
            'approved_fakultas' => 'bg-green-100 text-green-800',
            'rejected_fakultas' => 'bg-red-100 text-red-800',
            'surat_generated' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
        ];
        
        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }
    
    public static function getPengajuanStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu Review',
            'processed' => 'Disetujui Prodi',
            'approved_prodi' => 'Disetujui Prodi',
            'approved_prodi_direct_fakultas' => 'Diteruskan ke Fakultas',
            'rejected_prodi' => 'Ditolak Prodi',
            'approved_fakultas' => 'Disetujui Fakultas',
            'rejected_fakultas' => 'Ditolak Fakultas',
            'surat_generated' => 'Surat Dibuat',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
        ];
        
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}