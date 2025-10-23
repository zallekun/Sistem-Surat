<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Add this line

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles; // Add HasRoles here

    protected $fillable = [
        'nama',
        'email',
        'password',
        'jabatan_id',
        'prodi_id',
        'nip',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function suratCreated()
    {
        return $this->hasMany(Surat::class, 'created_by');
    }

    public function disposisiDari()
    {
        return $this->hasMany(Disposisi::class, 'dari_user_id');
    }

    public function disposisiKepada()
    {
        return $this->hasMany(Disposisi::class, 'kepada_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function tracking()
    {
        return $this->hasMany(Tracking::class);
    /**
     * Get the fakultas for the user (through prodi).
     */
    }
    public function fakultas()
    {
        return $this->hasOneThrough(
            \App\Models\Fakultas::class,
            \App\Models\Prodi::class,
            'id', // Foreign key on prodi table
            'id', // Foreign key on fakultas table
            'prodi_id', // Local key on users table
            'fakultas_id' // Local key on prodi table
        );
    }


    /**
     * Get fakultas_id through prodi relationship
     */
    public function getFakultasIdAttribute()
    {
        return $this->prodi ? $this->prodi->fakultas_id : null;
    }

    }