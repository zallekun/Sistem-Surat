<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusHistory extends Model
{
    use SoftDeletes;
    
    protected $table = 'status_histories';
    
    protected $fillable = [
        'surat_id',
        'status_id',
        'user_id',
        'keterangan',
        'created_at',
        'updated_at'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    /**
     * Get the surat that owns the status history.
     */
    public function surat()
    {
        return $this->belongsTo(\App\Models\Surat::class, 'surat_id');
    }
    
    /**
     * Get the status for this history entry.
     */
    public function status()
    {
        return $this->belongsTo(\App\Models\StatusSurat::class, 'status_id');
    }
    
    /**
     * Get the user who made this status change.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}