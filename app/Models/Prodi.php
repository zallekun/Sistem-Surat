<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodi';

    protected $fillable = [
        'nama_prodi',
        'kode_prodi',
        'fakultas_id',
        'kaprodi_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function kaprodi()
    {
        return $this->belongsTo(User::class, 'kaprodi_id');
    }
}
