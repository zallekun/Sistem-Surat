<?php
// app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole; // Alias Spatie's Role model

class Role extends SpatieRole // Extend Spatie's Role model
{
    use HasFactory;

    // Spatie's Role model uses 'name' as the primary identifier for roles.
    // No need to define $table or $fillable for 'name' if it's handled by Spatie's migration.
    // Ensure 'deskripsi' is handled if needed, but 'name' and 'guard_name' are core.
    protected $fillable = [
        'name', // Spatie's default role name column
        'deskripsi',
        'guard_name',
    ];
}
