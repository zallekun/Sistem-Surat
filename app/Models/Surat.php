<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasNomorSurat;

class Surat extends Model
{
    use HasFactory, SoftDeletes, HasNomorSurat;

    protected $table = 'surat';

    protected $fillable = [
        'nomor_surat',
        'perihal',
        'tujuan_jabatan_id',
        'sifat_surat',
        'lampiran',
        'tembusan',
        'file_surat',
        'jenis_id',
        'status_id',
        'created_by',
        'tanggal_surat',
        'prodi_id',
        'fakultas_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentStatus()
    {
        return $this->belongsTo(StatusSurat::class, 'status_id');
    }

    public function tujuanJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'tujuan_jabatan_id');
    }

    public function disposisi()
    {
        return $this->hasMany(Disposisi::class, 'surat_id');
    }

    public function tracking()
    {
        return $this->hasMany(Tracking::class, 'surat_id');
    }

    public function lampiran()
    {
        return $this->hasMany(Lampiran::class, 'surat_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    /**
     * Get the status histories for the surat.
     */
    public function statusHistories()
    {
        return $this->hasMany(\App\Models\StatusHistory::class, 'surat_id');
    }
}