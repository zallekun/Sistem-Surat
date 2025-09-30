<?php
// app/Http/Controllers/AdminFakultasController.php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminFakultasController extends Controller
{
    public function index(Request $request)
    {
        $query = Fakultas::query();
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_fakultas', 'like', "%{$request->search}%")
                  ->orWhere('kode_fakultas', 'like', "%{$request->search}%");
            });
        }
        
        $fakultas = $query->paginate(15);
        
        return view('admin.master.fakultas.index', compact('fakultas'));
    }
    
    public function create()
    {
        return view('admin.master.fakultas.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode_fakultas' => 'required|string|max:10|unique:fakultas,kode_fakultas',
            'nama_fakultas' => 'required|string|max:255',
        ]);
        
        try {
            Fakultas::create($request->all());
            
            return redirect()->route('admin.fakultas.index')
                ->with('success', 'Fakultas berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating fakultas', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menambahkan fakultas');
        }
    }
    
    public function edit($id)
    {
        $fakultas = Fakultas::findOrFail($id);
        return view('admin.master.fakultas.edit', compact('fakultas'));
    }
    
    public function update(Request $request, $id)
    {
        $fakultas = Fakultas::findOrFail($id);
        
        $request->validate([
            'kode_fakultas' => 'required|string|max:10|unique:fakultas,kode_fakultas,' . $id,
            'nama_fakultas' => 'required|string|max:255',
        ]);
        
        try {
            $fakultas->update($request->all());
            
            return redirect()->route('admin.fakultas.index')
                ->with('success', 'Fakultas berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Error updating fakultas', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal mengupdate fakultas');
        }
    }
    
    public function destroy($id)
    {
        $fakultas = Fakultas::findOrFail($id);
        
        // Check if has prodi
        if ($fakultas->prodi()->count() > 0) {
            return back()->with('error', 'Fakultas tidak bisa dihapus karena masih ada prodi yang terkait');
        }
        
        try {
            $fakultas->delete();
            return redirect()->route('admin.fakultas.index')
                ->with('success', 'Fakultas berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting fakultas', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus fakultas');
        }
    }
}