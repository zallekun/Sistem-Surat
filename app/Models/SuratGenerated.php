<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SuratGenerated extends Model
{
    use HasFactory;

    protected $table = "surat_generated";

    protected $fillable = [
        "pengajuan_id",
        "file_path", 
        "barcode_signature_id",
        "status",
        "metadata",
        "signed_url",        // NEW - for signed document link
        "notes",            // NEW - for additional notes
        "nomor_surat",      // NEW - for document number (if not in metadata)
        "signed_by",        // NEW - who signed the document
        "signed_at",        // NEW - when it was signed
        "generated_by"      // NEW - who generated/processed
    ];

    protected $casts = [
        "metadata" => "array",
        "signed_at" => "datetime"
    ];

    /**
     * Get the pengajuan that owns this generated surat
     */
    public function pengajuan()
    {
        return $this->belongsTo(PengajuanSurat::class, "pengajuan_id");
    }

    /**
     * Get the user who generated this surat
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, "generated_by");
    }

    /**
     * Get the barcode signature
     */
    public function barcodeSignature()
    {
        return $this->belongsTo(BarcodeSignature::class, "barcode_signature_id");
    }

    /**
     * Check if local file exists
     */
    public function fileExists()
    {
        return $this->file_path && Storage::disk("public")->exists($this->file_path);
    }

    /**
     * Check if signed URL is available
     */
    public function hasSignedUrl()
    {
        return !empty($this->signed_url);
    }

    /**
     * Check if any download option is available
     */
    public function hasDownloadOption()
    {
        return $this->fileExists() || $this->hasSignedUrl();
    }

    /**
     * Get download URL - prioritize signed_url over file_path
     */
    public function getDownloadUrlAttribute()
    {
        // If signed URL exists, use that (for completed documents)
        if ($this->hasSignedUrl()) {
            return $this->signed_url;
        }

        // Otherwise, use local file if exists
        if ($this->fileExists()) {
            return route("tracking.download", $this->pengajuan_id);
        }
        
        return null;
    }

    /**
     * Get the document number from various sources
     */
    public function getDocumentNumber()
    {
        // Try nomor_surat field first
        if (!empty($this->nomor_surat)) {
            return $this->nomor_surat;
        }

        // Try from metadata
        if (isset($this->metadata['nomor_surat'])) {
            return $this->metadata['nomor_surat'];
        }

        // Try from pengajuan surat_data
        if ($this->pengajuan && $this->pengajuan->surat_data) {
            $suratData = is_array($this->pengajuan->surat_data) 
                ? $this->pengajuan->surat_data 
                : json_decode($this->pengajuan->surat_data, true);
            
            if (isset($suratData['nomor_surat'])) {
                return $suratData['nomor_surat'];
            }
        }

        return null;
    }

    /**
     * Get signer information
     */
    public function getSignerInfo()
    {
        $signer = [];
        
        // From signed_by field
        if (!empty($this->signed_by)) {
            $signer['nama'] = $this->signed_by;
        }

        // Try from metadata
        if (isset($this->metadata['penandatangan'])) {
            $signer = array_merge($signer, $this->metadata['penandatangan']);
        }

        // Try from pengajuan surat_data
        if ($this->pengajuan && $this->pengajuan->surat_data) {
            $suratData = is_array($this->pengajuan->surat_data) 
                ? $this->pengajuan->surat_data 
                : json_decode($this->pengajuan->surat_data, true);
            
            if (isset($suratData['penandatangan'])) {
                $signer = array_merge($signer, $suratData['penandatangan']);
            }
        }

        return $signer;
    }

    /**
     * Check if document is completed (signed)
     */
    public function isCompleted()
    {
        return $this->status === 'completed' && ($this->hasSignedUrl() || $this->signed_at);
    }

    /**
     * Mark as completed with signed URL
     */
    public function markCompleted($signedUrl, $signedBy, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'signed_url' => $signedUrl,
            'signed_by' => $signedBy,
            'signed_at' => now(),
            'notes' => $notes
        ]);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'generated' => 'Dibuat',
            'printed' => 'Dicetak',
            'completed' => 'Selesai/Ditandatangani',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'generated' => 'bg-blue-100 text-blue-800',
            'printed' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-600'
        };
    }

    /**
     * Scope for completed documents
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for documents with signed URLs
     */
    public function scopeWithSignedUrl($query)
    {
        return $query->whereNotNull('signed_url')->where('signed_url', '!=', '');
    }
}