<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SuratGenerated extends Model
{
    protected $table = 'surat_generated';
    
    protected $fillable = [
        'pengajuan_id',
        'nomor_surat',
        'barcode_signature_id',
        'file_path',
        'generated_by',
        'signed_by',
        'signed_at',
        'status'
    ];
    
    protected $dates = ['signed_at', 'created_at', 'updated_at'];
    
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanSurat::class);
    }
    
    public function barcodeSignature()
    {
        return $this->belongsTo(BarcodeSignature::class);
    }
    
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
    
    public function getDownloadUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}