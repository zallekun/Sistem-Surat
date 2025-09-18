<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromQuery, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with('role', 'jabatan', 'prodi');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Email',
            'Email Verified At',
            'Password',
            'Remember Token',
            'Role ID',
            'Jabatan ID',
            'Prodi ID',
            'NIP',
            'Is Active',
            'Created At',
            'Updated At',
            'Deleted At',
            'Role Nama',
            'Jabatan Nama',
            'Prodi Nama',
        ];
    }
}