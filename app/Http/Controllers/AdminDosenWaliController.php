<?php

namespace App\Http\Controllers;

use App\Models\DosenWali;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDosenWaliController extends Controller
{
    public function index(Request $request)
    {
        $query = DosenWali::with('prodi');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nid', 'like', "%{$request->search}%");
            });
        }

        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }

        $dosenWalis = $query->latest()->paginate(15);
        $prodis = Prodi::all();

        return view('admin.master.dosen-wali.index', compact('dosenWalis', 'prodis'));
    }

    public function create()
    {
        $prodis = Prodi::all();
        return view('admin.master.dosen-wali.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nid' => 'required|string|max:50|unique:dosen_wali,nid',
            'prodi_id' => 'required|exists:prodi,id',
            'is_active' => 'boolean',
        ]);

        try {
            DosenWali::create([
                'nama' => $request->nama,
                'nid' => $request->nid,
                'prodi_id' => $request->prodi_id,
                'is_active' => $request->input('is_active', true),
            ]);

            return redirect()->route('admin.dosen-wali.index')
                ->with('success', 'Dosen Wali berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating Dosen Wali', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menambahkan Dosen Wali');
        }
    }

    public function edit($id)
    {
        $dosenWali = DosenWali::findOrFail($id);
        $prodis = Prodi::all();
        return view('admin.master.dosen-wali.edit', compact('dosenWali', 'prodis'));
    }

    public function update(Request $request, $id)
    {
        $dosenWali = DosenWali::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'nid' => 'required|string|max:50|unique:dosen_wali,nid,' . $id,
            'prodi_id' => 'required|exists:prodi,id',
            'is_active' => 'boolean',
        ]);

        try {
            $dosenWali->update([
                'nama' => $request->nama,
                'nid' => $request->nid,
                'prodi_id' => $request->prodi_id,
                'is_active' => $request->input('is_active', true),
            ]);

            return redirect()->route('admin.dosen-wali.index')
                ->with('success', 'Dosen Wali berhasil diupdate');
        } catch (\Exception $e) {
            Log::error('Error updating Dosen Wali', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal mengupdate Dosen Wali');
        }
    }

    public function destroy($id)
    {
        $dosenWali = DosenWali::findOrFail($id);

        // TODO: Add check here if Dosen Wali is associated with any pengajuan_surats
        // This is complex because the relationship is in a JSON field.
        // For now, we allow deletion.

        try {
            $dosenWali->delete();
            return redirect()->route('admin.dosen-wali.index')
                ->with('success', 'Dosen Wali berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting Dosen Wali', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus Dosen Wali');
        }
    }
}
