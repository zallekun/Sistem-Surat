<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tracking;
use App\Models\Surat;

class TrackingController extends Controller
{
    public function index()
    {
        $trackings = Tracking::with(['surat', 'user'])->latest()->get();
        return view('tracking.index', compact('trackings'));
    }

    public function show($id)
    {
        $surat = Surat::with(['trackings.user', 'currentStatus'])->findOrFail($id);
        return view('tracking.show', compact('surat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'surat_id' => 'required|exists:surat,id',
            'status' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Tracking::create([
            'surat_id' => $request->surat_id,
            'user_id' => auth()->id(),
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Tracking berhasil ditambahkan');
    }
}