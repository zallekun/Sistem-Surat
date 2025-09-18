<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposisiParalel extends Model
{
    use HasFactory;

    protected $table = 'disposisi_paralel';

    protected $fillable = [
        'surat_id',
        'dari_jabatan_id',
        'kepada_jabatan_ids',
        'status_per_jabatan',
    ];

    protected $casts = [
        'kepada_jabatan_ids' => 'array',
        'status_per_jabatan' => 'array',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function dariJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'dari_jabatan_id');
    }
}