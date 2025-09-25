<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BarcodeSignature extends Model
{
    protected $table = 'barcode_signatures';
    
    protected $fillable = [
        'fakultas_id',
        'pejabat_nama',
        'pejabat_nid',
        'pejabat_jabatan',
        'pejabat_pangkat',
        'barcode_path',
        'is_active',
        'description'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }
    
    public function suratGenerated()
    {
        return $this->hasMany(SuratGenerated::class);
    }
    
    public function getBarcodeUrlAttribute()
    {
        return $this->barcode_path ? Storage::url($this->barcode_path) : null;
    }
}