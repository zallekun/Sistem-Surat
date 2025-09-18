<?php

namespace App\Traits;

use App\Models\Surat;
use App\Models\Fakultas;
use App\Models\Prodi;

trait HasNomorSurat
{
    /**
     * Generate a new unique letter number.
     *
     * @param int $fakultasId
     * @param int|null $prodiId
     * @param int|null $year
     * @param string|null $existingNomorSurat If provided, will generate a revision number.
     * @param bool $isRevision If true, will generate a revision number based on existingNomorSurat.
     * @return string
     */
    public function generateNomorSurat(int $fakultasId, ?int $prodiId = null, ?int $year = null, ?string $existingNomorSurat = null, bool $isRevision = false): string
    {
        $year = $year ?? now()->year;
        $fakultas = Fakultas::findOrFail($fakultasId);
        $kodeFakultas = $fakultas->kode_fakultas;
        
        $kodeProdi = 'FAK'; // Default code for faculty-level letters
        if ($prodiId) {
            $prodi = Prodi::findOrFail($prodiId);
            $kodeProdi = $prodi->kode_prodi;
        }

        if ($isRevision && $existingNomorSurat) {
            // Handle revision: YYYY/FF/PP/NNNa
            preg_match('/^(.*?)(\d{3})([a-z])?$/i', $existingNomorSurat, $matches);

            $basePart = $matches[1]; // e.g., 2025/FT/IF/
            $baseNumber = (int)($matches[2] ?? 0); // e.g., 1
            $currentRevisionChar = $matches[3] ?? ''; // e.g., 'a' or ''

            if ($currentRevisionChar) {
                $nextRevisionChar = chr(ord($currentRevisionChar) + 1);
            } else {
                $nextRevisionChar = 'a'; // First revision
            }
            return $basePart . str_pad($baseNumber, 3, '0', STR_PAD_LEFT) . $nextRevisionChar;

        } else {
            // Generate new sequential number according to format: [TAHUN]/[KODE_FAKULTAS]/[KODE_PRODI]/[NOMOR]
            $prefix = $year . '/' . $kodeFakultas . '/' . $kodeProdi . '/';

            $lastSurat = Surat::where('nomor_surat', 'like', $prefix . '%')
                                ->orderBy('nomor_surat', 'desc')
                                ->first();

            $lastNumber = 0;
            if ($lastSurat) {
                preg_match('/' . preg_quote($prefix, '/') . '(\d{3})([a-z])?$/i', $lastSurat->nomor_surat, $matches);
                $lastNumber = (int) ($matches[1] ?? 0);
            }

            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            return $prefix . $newNumber;
        }
    }
}