<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'action',
        'performed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent approvable model (PengajuanSurat, Surat, etc).
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed this action.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope: Filter by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by approvable
     */
    public function scopeForApprovable($query, $approvable)
    {
        return $query->where('approvable_type', get_class($approvable))
                     ->where('approvable_id', $approvable->id);
    }

    /**
     * Scope: Recent first
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get human-readable action name
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'processed' => 'Diproses',
            'approved_prodi' => 'Disetujui Prodi',
            'rejected_prodi' => 'Ditolak Prodi',
            'approved_fakultas' => 'Disetujui Fakultas',
            'rejected_fakultas' => 'Ditolak Fakultas',
            'printed' => 'Dicetak',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'reviewed' => 'Direview',
            'forwarded' => 'Diteruskan',
            'signed' => 'Ditandatangani',
            default => ucfirst($this->action),
        };
    }

    /**
     * Check if this is a rejection action
     */
    public function isRejection(): bool
    {
        return in_array($this->action, ['rejected', 'rejected_prodi', 'rejected_fakultas']);
    }

    /**
     * Check if this is an approval action
     */
    public function isApproval(): bool
    {
        return in_array($this->action, ['approved_prodi', 'approved_fakultas']);
    }
}