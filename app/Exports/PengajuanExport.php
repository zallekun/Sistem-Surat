<?php
// app/Exports/PengajuanExport.php

namespace App\Exports;

use App\Models\PengajuanSurat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengajuanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection()
    {
        $query = PengajuanSurat::with(['prodi', 'jenisSurat', 'mahasiswa'])
            ->withTrashed();
        
        // Apply filters
        if (!empty($this->filters['search'])) {
            $query->where(function($q) {
                $search = $this->filters['search'];
                $q->where('tracking_token', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nama_mahasiswa', 'like', "%{$search}%");
            });
        }
        
        if (!empty($this->filters['prodi_id'])) {
            $query->where('prodi_id', $this->filters['prodi_id']);
        }
        
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        
        if (!empty($this->filters['jenis_surat_id'])) {
            $query->where('jenis_surat_id', $this->filters['jenis_surat_id']);
        }
        
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        
        return $query->get();
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
            'Completed At',
            'Processing Time (Days)',
            'Deleted At',
        ];
    }
    
    public function map($pengajuan): array
    {
        static $no = 1;
        
        $processingTime = null;
        if ($pengajuan->completed_at) {
            $processingTime = $pengajuan->created_at->diffInDays($pengajuan->completed_at);
        }
        
        return [
            $no++,
            $pengajuan->tracking_token,
            $pengajuan->nim,
            $pengajuan->nama_mahasiswa,
            $pengajuan->email ?? '-',
            $pengajuan->phone ?? '-',
            $pengajuan->prodi->nama_prodi ?? '-',
            $pengajuan->jenisSurat->nama_jenis ?? '-',
            $pengajuan->keperluan,
            ucfirst(str_replace('_', ' ', $pengajuan->status)),
            $pengajuan->created_at->format('d-m-Y H:i'),
            $pengajuan->completed_at ? $pengajuan->completed_at->format('d-m-Y H:i') : '-',
            $processingTime ?? '-',
            $pengajuan->deleted_at ? $pengajuan->deleted_at->format('d-m-Y H:i') : '-',
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