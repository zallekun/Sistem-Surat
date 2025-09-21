<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_token',
        'nim',
        'nama_mahasiswa', 
        'email',
        'phone',
        'prodi_id',
        'jenis_surat_id',
        'keperluan',
        'additional_data',
        'status',
        'surat_id',
        'processed_by',
        'processed_at'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'processed_at' => 'datetime'
    ];

    // Relationships
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    public function scopeByJenisSurat($query, $jenis)
    {
        return $query->whereHas('jenisSurat', function($q) use ($jenis) {
            $q->where('kode_surat', $jenis);
        });
    }

    // Mutators & Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Menunggu</span>',
            'processed' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Diproses</span>',
            'completed' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Selesai</span>',
            'rejected' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Ditolak</span>',
        ];
        
        return $badges[$this->status] ?? $this->status;
    }

    public function getTrackingUrlAttribute()
    {
        return route('tracking.public', $this->tracking_token);
    }

    // Static Methods
    public static function generateTrackingToken()
    {
        do {
            $token = strtoupper(Str::random(8) . '-' . Str::random(8));
        } while (self::where('tracking_token', $token)->exists());
        
        return $token;
    }

    public static function findByToken($token)
    {
        return self::where('tracking_token', $token)->first();
    }

    // Helper Methods
    public function canBeProcessed()
    {
        return $this->status === 'pending';
    }

    public function markAsProcessed($suratId, $userId)
    {
        $this->update([
            'status' => 'processed',
            'surat_id' => $suratId,
            'processed_by' => $userId,
            'processed_at' => now()
        ]);
    }

    public function getProdiName()
    {
        return $this->prodi?->nama_prodi ?? 'Unknown';
    }

    public function getJenisSuratName()
    {
        return $this->jenisSurat?->nama_jenis ?? 'Unknown';
    }

    // Boot method untuk auto-generate tracking token
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->tracking_token) {
                $model->tracking_token = self::generateTrackingToken();
            }
        });
    }
}