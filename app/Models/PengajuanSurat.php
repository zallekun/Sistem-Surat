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
        'processed_at',
        'approved_by',    // tambah ini
        'approved_at',    // tambah ini
        'rejected_by',    // tambah ini
        'rejected_at',    // tambah ini
        'rejection_reason', // tambah ini

            // NEW WORKFLOW FIELDS
        'approved_by_prodi',
        'approved_at_prodi',
        'rejected_by_prodi',
        'rejected_at_prodi',
        'rejection_reason_prodi',
        'approved_by_fakultas',
        'approved_at_fakultas',
        'rejected_by_fakultas',
        'rejected_at_fakultas',
        'rejection_reason_fakultas',
        'notes',
        'direct_to_fakultas'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',    // tambah ini
        'rejected_at' => 'datetime',     // tambah ini
            // NEW WORKFLOW CASTS
        'approved_at_prodi' => 'datetime',
        'rejected_at_prodi' => 'datetime',
        'approved_at_fakultas' => 'datetime',
        'rejected_at_fakultas' => 'datetime',
        'direct_to_fakultas' => 'boolean'
    ];

    // ADD NEW RELATIONSHIPS
public function approvedByProdi()
{
    return $this->belongsTo(User::class, 'approved_by_prodi');
}

public function rejectedByProdi()
{
    return $this->belongsTo(User::class, 'rejected_by_prodi');
}

public function approvedByFakultas()
{
    return $this->belongsTo(User::class, 'approved_by_fakultas');
}

public function rejectedByFakultas()
{
    return $this->belongsTo(User::class, 'rejected_by_fakultas');
}

// ADD NEW SCOPES
public function scopePendingProdi($query)
{
    return $query->whereIn('status', ['pending', 'submitted']);
}

public function scopeApprovedProdi($query)
{
    return $query->whereIn('status', ['approved_prodi', 'approved_prodi_direct_fakultas']);
}

public function scopePendingFakultas($query)
{
    return $query->whereIn('status', ['approved_prodi', 'approved_prodi_direct_fakultas']);
}

public function scopeForFakultas($query, $fakultasId)
{
    return $query->whereHas('prodi', function($q) use ($fakultasId) {
        $q->where('fakultas_id', $fakultasId);
    });
}


    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

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

    // ADD STATUS HELPER METHODS
public function getStatusLabelAttribute()
{
    return match($this->status) {
        'pending', 'submitted' => 'Menunggu Review Prodi',
        'approved_prodi' => 'Disetujui Prodi - Menunggu Fakultas',
        'approved_prodi_direct_fakultas' => 'Langsung ke Fakultas',
        'rejected_prodi' => 'Ditolak Prodi',
        'approved_fakultas' => 'Disetujui Fakultas - Siap Generate Surat',
        'rejected_fakultas' => 'Ditolak Fakultas',
        'surat_generated' => 'Surat Telah Dibuat',
        'completed' => 'Selesai',
        'processed' => 'Sudah Diproses (Legacy)',
        default => ucfirst(str_replace('_', ' ', $this->status))
    };
}

public function getStatusColorAttribute()
{
    return match($this->status) {
        'pending', 'submitted' => 'bg-yellow-100 text-yellow-800',
        'approved_prodi', 'approved_prodi_direct_fakultas' => 'bg-blue-100 text-blue-800',
        'approved_fakultas' => 'bg-green-100 text-green-800',
        'rejected_prodi', 'rejected_fakultas' => 'bg-red-100 text-red-800',
        'surat_generated' => 'bg-purple-100 text-purple-800',
        'completed' => 'bg-gray-100 text-gray-800',
        'processed' => 'bg-indigo-100 text-indigo-800',
        default => 'bg-gray-100 text-gray-600'
    };
}

// ADD WORKFLOW CHECKING METHODS
public function canBeProcessedByProdi(): bool
{
    return in_array($this->status, ['pending', 'submitted']);
}

public function canBeProcessedByFakultas(): bool
{
    return in_array($this->status, ['approved_prodi', 'approved_prodi_direct_fakultas']);
}

public function canGenerateSurat(): bool
{
    return $this->status === 'approved_fakultas';
}

public function isRejected(): bool
{
    return in_array($this->status, ['rejected_prodi', 'rejected_fakultas']);
}

public function getLastRejectionReason(): ?string
{
    if ($this->status === 'rejected_fakultas' && $this->rejection_reason_fakultas) {
        return $this->rejection_reason_fakultas;
    }
    
    if ($this->status === 'rejected_prodi' && $this->rejection_reason_prodi) {
        return $this->rejection_reason_prodi;
    }
    
    return $this->rejection_reason; // Legacy field
}

// ADD WORKFLOW PROGRESSION METHODS
public function approveByProdi(int $userId, bool $directToFakultas = false): void
{
    $status = $directToFakultas ? 'approved_prodi_direct_fakultas' : 'approved_prodi';
    
    $this->update([
        'status' => $status,
        'approved_by_prodi' => $userId,
        'approved_at_prodi' => now(),
        'direct_to_fakultas' => $directToFakultas,
        'notes' => $directToFakultas ? 'Langsung diteruskan ke fakultas untuk jenis surat ini' : null
    ]);
}

public function rejectByProdi(int $userId, string $reason): void
{
    $this->update([
        'status' => 'rejected_prodi',
        'rejected_by_prodi' => $userId,
        'rejected_at_prodi' => now(),
        'rejection_reason_prodi' => $reason
    ]);
}

public function approveByFakultas(int $userId): void
{
    $this->update([
        'status' => 'approved_fakultas',
        'approved_by_fakultas' => $userId,
        'approved_at_fakultas' => now()
    ]);
}

public function rejectByFakultas(int $userId, string $reason): void
{
    $this->update([
        'status' => 'rejected_fakultas',
        'rejected_by_fakultas' => $userId,
        'rejected_at_fakultas' => now(),
        'rejection_reason_fakultas' => $reason
    ]);
}

public function markSuratGenerated(int $suratId, int $userId): void
{
    $this->update([
        'status' => 'surat_generated',
        'surat_id' => $suratId,
        'processed_by' => $userId,
        'processed_at' => now()
    ]);
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