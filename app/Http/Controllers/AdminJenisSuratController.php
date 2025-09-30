<?php
// app/Http/Controllers/AdminJenisSuratController.php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminJenisSuratController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisSurat::query();
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama_jenis', 'like', "%{$request->search}%")
                  ->orWhere('kode_surat', 'like', "%{$request->search}%");
            });
        }
        
        $jenisSurat = $query->paginate(15);
        
        return view('admin.master.jenis-surat.index', compact('jenisSurat'));
    }
    
    public function create()
    {
        return view('admin.master.jenis-surat.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode_surat' => 'required|string|max:10|unique:jenis_surat,kode_surat',
            'nama_jenis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        
        try {
            JenisSurat::create($request->all());
            
            return redirect()->route('admin.jenis-surat.index')
                ->with('success', 'Jenis surat berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating jenis surat', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menambahkan jenis surat');
        }
    }
    
    public function edit($id)
    {
        $jenisSurat = JenisSurat::findOrFail($id);
        return view('admin.master.jenis-surat.edit', compact('jenisSurat'));
    }
    
    public function update(Request $request, $id)
    {
        $jenisSurat = JenisSurat::findOrFail($id);
        
        $request->validate([
            'kode_surat' => 'required|string|max:10|unique:jenis_surat,kode_surat,' . $id,
            'nama_jenis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);
        
        try {
            $jenisSurat->update($request->all());
            
            return redirect()->route('admin.jenis-surat.index')
                ->with('success', 'Jenis surat berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Error updating jenis surat', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal mengupdate jenis surat');
        }
    }
    
    public function destroy($id)
    {
        $jenisSurat = JenisSurat::findOrFail($id);
        
        // Check if has pengajuan
        if ($jenisSurat->pengajuanSurats()->count() > 0) {
            return back()->with('error', 'Jenis surat tidak bisa dihapus karena masih ada pengajuan yang terkait');
        }
        
        try {
            $jenisSurat->delete();
            return redirect()->route('admin.jenis-surat.index')
                ->with('success', 'Jenis surat berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting jenis surat', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus jenis surat');
        }
    }
}