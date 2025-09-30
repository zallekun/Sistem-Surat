<?php
// app/Exports/AuditTrailExport.php

namespace App\Exports;

use App\Models\AuditTrail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditTrailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function collection()
    {
        $query = AuditTrail::with('user');
        
        if (!empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }
        
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        
        if (!empty($this->filters['date'])) {
            $query->whereDate('created_at', $this->filters['date']);
        }
        
        return $query->latest()->get();
    }
    
    public function headings(): array
    {
        return [
            'No',
            'Waktu',
            'User',
            'Action',
            'Model Type',
            'Model ID',
            'Reason',
            'IP Address',
        ];
    }
    
    public function map($log): array
    {
        static $no = 1;
        
        return [
            $no++,
            $log->created_at->format('d-m-Y H:i:s'),
            $log->user->nama ?? 'Unknown',
            ucfirst(str_replace('_', ' ', $log->action)),
            class_basename($log->model_type),
            $log->model_id,
            $log->reason,
            $log->ip_address,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
        ];
    }
}