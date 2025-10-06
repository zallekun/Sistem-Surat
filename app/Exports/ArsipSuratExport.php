<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArsipSuratExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'prodi')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tracking Token',
            'NIM',
            'Nama Mahasiswa',
            'Email',
            'Phone',
            'Prodi',
            'Jenis Surat',
            'Keperluan',
            'Status',
            'Tanggal Pengajuan',
            'Tanggal Selesai',
            'Waktu Proses (Hari)',
        ];
    }

    public function map($pengajuan): array
    {
        static $no = 1;

        $processingTime = null;
        if ($pengajuan->completed_at && $pengajuan->created_at) {
            $processingTime = $pengajuan->created_at->diffInDays($pengajuan->completed_at);
        }

        return [
            $no++,
            $pengajuan->tracking_token,
            $pengajuan->mahasiswa->nim ?? '-',
            $pengajuan->mahasiswa->nama ?? '-',
            $pengajuan->mahasiswa->email ?? '-',
            $pengajuan->mahasiswa->phone ?? '-',
            $pengajuan->prodi->nama_prodi ?? '-',
            $pengajuan->jenisSurat->nama_jenis ?? '-',
            $pengajuan->keperluan ?? '-',
            ucfirst(str_replace('_', ' ', $pengajuan->status)),
            $pengajuan->created_at ? $pengajuan->created_at->format('d-m-Y H:i') : '-',
            $pengajuan->completed_at ? $pengajuan->completed_at->format('d-m-Y H:i') : '-',
            $processingTime ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }
}
