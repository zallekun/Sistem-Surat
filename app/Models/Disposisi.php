<?php
// app/Models/Disposisi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_id', 'dari_user', 'kepada_user', 'instruksi',
        'deadline', 'prioritas', 'status', 'catatan',
        'tanggal_baca', 'tanggal_selesai'
    ];

    protected $casts = [
        'deadline' => 'date',
        'tanggal_baca' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function dariUser()
    {
        return $this->belongsTo(User::class, 'dari_user');
    }

    public function kepadaUser()
    {
        return $this->belongsTo(User::class, 'kepada_user');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                     ->whereIn('status', ['pending', 'dibaca', 'diproses']);
    }
}