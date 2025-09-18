<?php
// app/Http/Controllers/DisposisiController.php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function store(Request $request, $id)
    {
        // Implementasi disposisi
        $surat = Surat::findOrFail($id);
        
        // Logic untuk disposisi
        
        return redirect()->back()->with('success', 'Disposisi berhasil disimpan');
    }
}