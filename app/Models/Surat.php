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
    public function pengajuan()
{
    return $this->hasOne(PengajuanSurat::class);
}

// Method untuk create surat dari pengajuan
public static function createFromPengajuan(PengajuanSurat $pengajuan, $userId)
{
    return self::create([
        'nomor_surat' => self::generateNomorSurat($pengajuan->jenisSurat->kode_surat),
        'perihal' => $pengajuan->keperluan,
        'tanggal_surat' => now()->toDateString(),
        'jenis_id' => $pengajuan->jenis_surat_id,
        'prodi_id' => $pengajuan->prodi_id,
        'fakultas_id' => $pengajuan->prodi->fakultas_id,
        'created_by' => $userId,
        'status' => 'draft',
        'tipe_surat' => 'keluar',
        'sifat_surat' => 'biasa',
        // Tambahan data dari pengajuan
        'isi_surat' => json_encode([
            'nim' => $pengajuan->nim,
            'nama_mahasiswa' => $pengajuan->nama_mahasiswa,
            'additional_data' => $pengajuan->additional_data
        ])
    ]);
}

// Method untuk generate nomor surat (sesuaikan dengan logic existing)
private static function generateNomorSurat($kodeSurat)
{
    $tahun = now()->year;
    $bulan = now()->format('m');
    
    // Get last number for this type
    $lastSurat = self::whereYear('created_at', $tahun)
        ->whereMonth('created_at', now()->month)
        ->whereHas('jenisSurat', function($q) use ($kodeSurat) {
            $q->where('kode_surat', $kodeSurat);
        })
        ->orderBy('id', 'desc')
        ->first();
    
    $urutan = $lastSurat ? 
        (int) explode('/', $lastSurat->nomor_surat)[0] + 1 : 1;
    
    return sprintf('%03d/UN27.XX/%s/%s/%s', $urutan, $kodeSurat, $bulan, $tahun);
}
}