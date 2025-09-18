<?php
// app/Models/StatusSurat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusSurat extends Model
{
    use HasFactory;

    protected $table = 'status_surat'; // ← Set table name

    protected $fillable = [
        'nama_status',
        'kode_status',
        'warna_status',
        'urutan',
        'deskripsi'
    ];

    public function surat()
    {
        return $this->hasMany(Surat::class, 'status_id');
    }
}