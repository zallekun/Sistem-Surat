<?php
// app/Models/JenisSurat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    use HasFactory;

    protected $table = 'jenis_surat'; // ← Set table name

    protected $fillable = [
        'nama_jenis',
        'kode_surat',
        'template',
        'format_nomor',
        'deskripsi'
    ];

    public function surat()
    {
        return $this->hasMany(Surat::class, 'jenis_id');
    }
}