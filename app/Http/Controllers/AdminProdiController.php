<?php
// app/Http/Controllers/AdminProdiController.php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminProdiController extends Controller
{
    public function index(Request $request)
    {
        $query = Prodi::with('fakultas');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_prodi', 'like', "%{$request->search}%")
                  ->orWhere('kode_prodi', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->fakultas_id) {
            $query->where('fakultas_id', $request->fakultas_id);
        }
        
        $prodis = $query->paginate(15);
        $fakultas = Fakultas::all();
        
        return view('admin.master.prodi.index', compact('prodis', 'fakultas'));
    }
    
    public function create()
    {
        $fakultas = Fakultas::all();
        return view('admin.master.prodi.create', compact('fakultas'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode_prodi' => 'required|string|max:10|unique:prodi,kode_prodi',
            'nama_prodi' => 'required|string|max:255',
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);
        
        try {
            Prodi::create($request->all());
            
            return redirect()->route('admin.prodi.index')
                ->with('success', 'Prodi berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating prodi', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menambahkan prodi');
        }
    }
    
    public function edit($id)
    {
        $prodi = Prodi::findOrFail($id);
        $fakultas = Fakultas::all();
        return view('admin.master.prodi.edit', compact('prodi', 'fakultas'));
    }
    
    public function update(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);
        
        $request->validate([
            'kode_prodi' => 'required|string|max:10|unique:prodi,kode_prodi,' . $id,
            'nama_prodi' => 'required|string|max:255',
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);
        
        try {
            $prodi->update($request->all());
            
            return redirect()->route('admin.prodi.index')
                ->with('success', 'Prodi berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Error updating prodi', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal mengupdate prodi');
        }
    }
    
    public function destroy($id)
    {
        $prodi = Prodi::findOrFail($id);
        
        // Check if prodi has users
        if ($prodi->users()->count() > 0) {
            return back()->with('error', 'Prodi tidak bisa dihapus karena masih ada user yang terkait');
        }
        
        // Check if prodi has pengajuan
        if ($prodi->pengajuanSurats()->count() > 0) {
            return back()->with('error', 'Prodi tidak bisa dihapus karena masih ada pengajuan yang terkait');
        }
        
        try {
            $prodi->delete();
            return redirect()->route('admin.prodi.index')
                ->with('success', 'Prodi berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting prodi', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus prodi');
        }
    }
}