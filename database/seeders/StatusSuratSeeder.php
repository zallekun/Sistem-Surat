<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StatusSurat;

class StatusSuratSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['nama_status' => 'Draft', 'kode_status' => 'draft', 'warna_status' => '#6c757d', 'urutan' => 1],
            ['nama_status' => 'Review Kaprodi', 'kode_status' => 'review_kaprodi', 'warna_status' => '#ffc107', 'urutan' => 2],
            ['nama_status' => 'Revisi (opsional)', 'kode_status' => 'revisi_opsional', 'warna_status' => '#fd7e14', 'urutan' => 3],
            ['nama_status' => 'Disetujui Kaprodi', 'kode_status' => 'disetujui_kaprodi', 'warna_status' => '#28a745', 'urutan' => 4],
            ['nama_status' => 'Verifikasi Fakultas', 'kode_status' => 'verifikasi_fakultas', 'warna_status' => '#007bff', 'urutan' => 5],
            ['nama_status' => 'Terverifikasi', 'kode_status' => 'terverifikasi', 'warna_status' => '#17a2b8', 'urutan' => 6],
            ['nama_status' => 'Menunggu Disposisi', 'kode_status' => 'menunggu_disposisi', 'warna_status' => '#6f42c1', 'urutan' => 7],
            ['nama_status' => 'Disposisi', 'kode_status' => 'disposisi', 'warna_status' => '#6610f2', 'urutan' => 8],
            ['nama_status' => 'Menunggu TTD', 'kode_status' => 'menunggu_ttd', 'warna_status' => '#e83e8c', 'urutan' => 9],
            ['nama_status' => 'Selesai', 'kode_status' => 'selesai', 'warna_status' => '#28a745', 'urutan' => 10],
            ['nama_status' => 'Arsip', 'kode_status' => 'arsip', 'warna_status' => '#343a40', 'urutan' => 11],
        ];

        foreach ($statuses as $status) {
            StatusSurat::firstOrCreate(['kode_status' => $status['kode_status']], $status);
        }
    }
}
