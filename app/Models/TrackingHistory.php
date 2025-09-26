<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingHistory extends Model
{
    protected $table = 'tracking_histories';
    
    protected $fillable = [
        'pengajuan_id',
        'status',
        'description',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper method to create tracking history
    public static function log(int $pengajuanId, string $status, string $description, int $createdBy = null): self
    {
        return self::create([
            'pengajuan_id' => $pengajuanId,
            'status' => $status,
            'description' => $description,
            'created_by' => $createdBy ?? auth()->id()
        ]);
    }

    // Scopes
    public function scopeForPengajuan($query, $pengajuanId)
    {
        return $query->where('pengajuan_id', $pengajuanId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}