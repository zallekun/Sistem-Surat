<?php
// app/Models/PengajuanSurat.php - UPDATED VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_surats';

    protected $fillable = [
        'tracking_token',
        'mahasiswa_id',
        'nim',
        'nama_mahasiswa', 
        'email',
        'phone',
        'prodi_id',
        'jenis_surat_id',
        'keperluan',
        'additional_data',
        'surat_data',           // NEW - untuk data yang diedit
        'status',
        'surat_id',
        'processed_by',
        'processed_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
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
        'printed_at',           // NEW - waktu print untuk TTD
        'printed_by',           // NEW - user yang print
        'completed_at',         // NEW - waktu selesai
        'completed_by',         // NEW - user yang menyelesaikan
        'surat_generated_id',   // NEW - link ke surat_generated
        'notes',
        'direct_to_fakultas',
        'surat_pengantar_url',
        'surat_pengantar_nomor',
        'surat_pengantar_generated_at',
        'surat_pengantar_generated_by',
        'ttd_kaprodi_image',
        'nota_dinas_number',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'surat_data' => 'array',        // NEW
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approved_at_prodi' => 'datetime',
        'rejected_at_prodi' => 'datetime',
        'approved_at_fakultas' => 'datetime',
        'rejected_at_fakultas' => 'datetime',
        'printed_at' => 'datetime',     // NEW
        'completed_at' => 'datetime',   // NEW
        'direct_to_fakultas' => 'boolean',
        'surat_pengantar_generated_at' => 'datetime'
    ];

    // Tambah relationship
public function suratPengantarGeneratedBy()
{
    return $this->belongsTo(User::class, 'surat_pengantar_generated_by');
}

// Helper method
public function hasSuratPengantar()
{
    return !empty($this->surat_pengantar_url);
}

public function needsSuratPengantar()
{
    return in_array($this->jenisSurat->kode_surat, ['KP', 'TA']);
}

public function mahasiswa(): BelongsTo
{
    return $this->belongsTo(Mahasiswa::class);
}

    // EXISTING RELATIONSHIPS
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

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

    // NEW RELATIONSHIPS
    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function suratGenerated()
    {
        return $this->belongsTo(\App\Models\SuratGenerated::class, 'surat_generated_id');
    }

    public function trackingHistory()
    {
        return $this->hasMany(\App\Models\TrackingHistory::class, 'pengajuan_id')->orderBy('created_at', 'desc');
    }

    // EXISTING SCOPES
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



    public function scopeForFakultasAccess($query, $fakultasId)
{
    return $query->whereHas('prodi', function($q) use ($fakultasId) {
        $q->where('fakultas_id', $fakultasId);
    })
    ->where(function($q) {
        // MA logic
        $q->where(function($subQ) {
            $subQ->whereHas('jenisSurat', function($js) {
                $js->where('kode_surat', 'MA');
            })->whereIn('status', [
                'approved_prodi',
                'approved_fakultas',
                'sedang_ditandatangani',
                'completed',
                'rejected_fakultas'
            ]);
        })
        // KP/TA logic with pengantar check
        ->orWhere(function($subQ) {
            $subQ->whereHas('jenisSurat', function($js) {
                $js->whereIn('kode_surat', ['KP', 'TA']);
            })
            ->whereNotNull('surat_pengantar_url')
            ->whereIn('status', [
                'pengantar_generated',
                'approved_fakultas',
                'sedang_ditandatangani',
                'completed',
                'rejected_fakultas'
            ]);
        });
    });
}

    // EXISTING ACCESSORS
public function getStatusLabelAttribute()
{
    return match($this->status) {
        'pending', 'submitted' => 'Menunggu Review Prodi',
        'approved_prodi' => $this->needsSuratPengantar() 
            ? 'Disetujui Prodi - Perlu Surat Pengantar' 
            : 'Disetujui Prodi - Menunggu Fakultas',
        'pengantar_generated' => 'Surat Pengantar Tersedia - Menunggu Fakultas',
        'approved_fakultas' => 'Disetujui Fakultas - Siap Cetak',
        'rejected_prodi' => 'Ditolak Prodi',
        'rejected_fakultas' => 'Ditolak Fakultas',
        'sedang_ditandatangani' => 'Sedang Proses TTD Fisik',
        'completed' => 'Selesai',
        'surat_generated' => 'Surat Telah Dibuat', // legacy
        'processed' => 'Sudah Diproses', // legacy
        default => ucfirst(str_replace('_', ' ', $this->status))
    };
}

public function getStatusColorAttribute()
{
    return match($this->status) {
        'pending', 'submitted' => 'bg-yellow-100 text-yellow-800',
        'approved_prodi' => 'bg-blue-100 text-blue-800',
        'pengantar_generated' => 'bg-indigo-100 text-indigo-800', // Ubah warna
        'approved_fakultas' => 'bg-green-100 text-green-800',
        'rejected_prodi', 'rejected_fakultas' => 'bg-red-100 text-red-800',
        'sedang_ditandatangani' => 'bg-orange-100 text-orange-800',
        'completed' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-600'
    };
}

    // EXISTING HELPER METHODS
    public function canBeProcessedByProdi(): bool
    {
        return in_array($this->status, ['pending', 'submitted']);
    }

public function canBeAccessedByFakultas()
{
    // MA: approved_prodi onwards
    if ($this->jenisSurat->kode_surat === 'MA') {
        return in_array($this->status, [
            'approved_prodi', 
            'approved_fakultas',
            'sedang_ditandatangani',
            'completed',
            'rejected_fakultas'
        ]);
    }
    
    // KP/TA: HARUS ada surat pengantar
    if (in_array($this->jenisSurat->kode_surat, ['KP', 'TA'])) {
        return $this->hasSuratPengantar() && in_array($this->status, [
            'pengantar_generated',
            'approved_fakultas',
            'sedang_ditandatangani',
            'completed',
            'rejected_fakultas'
        ]);
    }
    
    return false;
}

// Tambahkan method untuk cek alur yang benar
public function canGenerateSurat(): bool 
{
    // MA: langsung setelah approved_prodi
    if ($this->jenisSurat->kode_surat === 'MA') {
        return $this->status === 'approved_prodi' || $this->status === 'approved_fakultas';
    }
    
    // KP/TA: harus ada pengantar dulu
    if (in_array($this->jenisSurat->kode_surat, ['KP', 'TA'])) {
        return $this->status === 'pengantar_generated' || $this->status === 'approved_fakultas';
    }
    
    return false;
}

public function getNextStatus(): string
{
    return match($this->status) {
        'pending' => 'approved_prodi',
        'approved_prodi' => $this->needsSuratPengantar() 
            ? 'pengantar_generated' 
            : 'approved_fakultas',
        'pengantar_generated' => 'approved_fakultas',
        'approved_fakultas' => 'sedang_ditandatangani',
        'sedang_ditandatangani' => 'completed',
        default => $this->status
    };
}

public function canBeViewedByFakultas(): bool
{
    // MA bisa langsung dilihat setelah approved_prodi
    if ($this->jenisSurat->kode_surat === 'MA' && $this->status === 'approved_prodi') {
        return true;
    }
    
    // KP/TA harus ada pengantar dulu
    if (in_array($this->jenisSurat->kode_surat, ['KP', 'TA'])) {
        return $this->status === 'pengantar_generated';
    }
    
    // Status lanjutan
    return in_array($this->status, [
        'approved_fakultas', 
        'sedang_ditandatangani', 
        'completed',
        'rejected_fakultas'
    ]);
}
    
    public function canGeneratePengantar()
{
    // Only KP and TA need surat pengantar
    if (!in_array($this->jenisSurat->kode_surat, ['KP', 'TA'])) {
        return false;
    }
    
    // Must be approved by prodi
    if ($this->status !== 'approved_prodi') {
        return false;
    }
    
    // Should not have surat pengantar yet
    if (!empty($this->surat_pengantar_url)) {
        return false;
    }
    
    return true;
}

    // NEW HELPER METHODS FOR NEW WORKFLOW
    public function canEditSurat(): bool
    {
        return !in_array($this->status, ['sedang_ditandatangani', 'completed']);
    }

public function canPrintSurat(): bool
{
    // Jika sudah pernah print, tidak bisa print lagi (kecuali cetak ulang)
    if ($this->printed_at) {
        return false;
    }
    
    // MA bisa print setelah approved_prodi atau approved_fakultas
    if ($this->jenisSurat->kode_surat === 'MA') {
        return in_array($this->status, ['approved_prodi', 'approved_fakultas']);
    }
    
    // KP/TA bisa print setelah pengantar_generated atau approved_fakultas
    if (in_array($this->jenisSurat->kode_surat, ['KP', 'TA'])) {
        return in_array($this->status, ['pengantar_generated', 'approved_fakultas']);
    }
    
    // Legacy support
    return $this->status === 'processed';
}

    public function canUploadSignedLink(): bool
    {
        return $this->status === 'sedang_ditandatangani';
    }

    public function isReadyForSigning(): bool
    {
        return $this->status === 'sedang_ditandatangani';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasPdfFile(): bool
    {
        return $this->status === 'completed' 
               && $this->suratGenerated 
               && (!empty($this->suratGenerated->file_path) || !empty($this->suratGenerated->signed_url));
    }

    // NEW WORKFLOW METHODS
    public function markAsPrinted(int $userId): void
    {
        $this->update([
            'status' => 'sedang_ditandatangani',
            'printed_by' => $userId,
            'printed_at' => now()
        ]);
    }

    public function completeWithSignedLink(string $signedUrl, int $userId, string $notes = null): void
    {
        // Create or update surat_generated
        $suratGenerated = \App\Models\SuratGenerated::updateOrCreate(
            ['pengajuan_id' => $this->id],
            [
                'nomor_surat' => $this->getSuratData('nomor_surat') ?? 'N/A',
                'signed_url' => $signedUrl,
                'signed_by' => $this->getSuratData('penandatangan.nama') ?? 'Pejabat Fakultas',
                'signed_at' => now(),
                'generated_by' => $userId,
                'status' => 'completed',
                'notes' => $notes
            ]
        );

        $this->update([
            'status' => 'completed',
            'surat_generated_id' => $suratGenerated->id,
            'completed_by' => $userId,
            'completed_at' => now()
        ]);
    }

    // HELPER METHODS FOR SURAT DATA
    public function getSuratData(string $key = null, $default = null)
    {
        if (!$this->surat_data) {
            return $default;
        }

        if ($key === null) {
            return $this->surat_data;
        }

        return data_get($this->surat_data, $key, $default);
    }

    public function setSuratData(array $data): void
    {
        $this->update(['surat_data' => $data]);
    }

    public function updateSuratData(string $key, $value): void
    {
        $suratData = $this->surat_data ?? [];
        data_set($suratData, $key, $value);
        $this->update(['surat_data' => $suratData]);
    }

    // EXISTING METHODS (keep all existing methods from your model)
    public function getAdditionalDataAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = trim($value, '"');
            
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to decode additional_data', [
                    'id' => $this->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [];
    }

    public function setAdditionalDataAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['additional_data'] = json_encode($value);
        } else {
            $this->attributes['additional_data'] = $value;
        }
    }

    // Keep all other existing methods...
    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->hasPdfFile()) {
            return route('tracking.download', $this->id);
        }
        return null;
    }
public function getNimAttribute($value)
{
    // If mahasiswa relationship is loaded, use it
    if ($this->relationLoaded('mahasiswa') && $this->mahasiswa) {
        return $this->mahasiswa->nim;
    }
    
    // Otherwise return direct attribute (for backward compatibility during migration)
    return $value ?? $this->attributes['nim'] ?? null;
}

/**
 * Get nama from mahasiswa relationship
 */
public function getNamaMahasiswaAttribute($value)
{
    if ($this->relationLoaded('mahasiswa') && $this->mahasiswa) {
        return $this->mahasiswa->nama;
    }
    
    return $value ?? $this->attributes['nama_mahasiswa'] ?? null;
}




    public static function generateTrackingToken()
    {
        do {
            $token = strtoupper(Str::random(8) . '-' . Str::random(8));
        } while (self::where('tracking_token', $token)->exists());
        
        return $token;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->tracking_token) {
                $model->tracking_token = self::generateTrackingToken();
            }
        });
    }

    public function approvalHistories(): MorphMany
{
    return $this->morphMany(ApprovalHistory::class, 'approvable')
                ->orderBy('created_at', 'desc');
}

public function latestApproval()
{
    return $this->morphOne(ApprovalHistory::class, 'approvable')
                ->latestOfMany();
}

public function logApproval(string $action, ?string $notes = null, ?array $metadata = null): ApprovalHistory
{
    return $this->approvalHistories()->create([
        'action' => $action,
        'performed_by' => auth()->id(),
        'notes' => $notes,
        'metadata' => $metadata,
    ]);
}

/**
 * Check if has specific approval
 */
public function hasApproval(string $action): bool
{
    return $this->approvalHistories()
                ->where('action', $action)
                ->exists();
}

/**
 * Get approval by action type
 */
public function getApprovalByAction(string $action): ?ApprovalHistory
{
    return $this->approvalHistories()
                ->where('action', $action)
                ->first();
}

/**
 * Get who processed this pengajuan
 */
public function getProcessedByUser(): ?User
{
    return $this->getApprovalByAction('processed')?->performedBy;
}

/**
 * Get when processed
 */
public function getProcessedTime(): ?\Carbon\Carbon
{
    return $this->getApprovalByAction('processed')?->created_at;
}

}