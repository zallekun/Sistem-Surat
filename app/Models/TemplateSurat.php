<?php
// app/Models/TemplateSurat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    use HasFactory;

    protected $table = 'template_surat'; // ← Set table name

    protected $fillable = [
        'nama_template',
        'jenis_surat_id',
        'konten_template',
        'variables',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}