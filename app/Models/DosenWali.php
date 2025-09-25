<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenWali extends Model
{
    use HasFactory;

    protected $table = 'dosen_wali';

    protected $fillable = [
        'nama',
        'nid',
        'prodi_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the prodi that owns the dosen wali
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Scope to get only active dosen wali
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get dosen wali by prodi
     */
    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }
}