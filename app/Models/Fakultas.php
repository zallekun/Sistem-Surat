<?php
// app/Models/Fakultas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas'; // ← Set table name

    protected $fillable = [
        'nama_fakultas',
        'kode_fakultas',
        'dekan_id',
        'wadek1_id',
        'wadek2_id',
        'wadek3_id'
    ];

        public function prodi()
    {
        return $this->hasMany(Prodi::class, 'fakultas_id');
    }
    
    public function users()
    {
        return $this->hasManyThrough(User::class, Prodi::class);
    }
}