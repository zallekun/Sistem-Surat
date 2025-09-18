<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings'; // This one is correct (plural)

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description'
    ];
}