<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Surat;
use Illuminate\Support\Facades\Auth;

class SuratApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Returns letters created by the authenticated user
        $surats = Surat::where('created_by', Auth::id())->paginate(15);
        return response()->json($surats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Logic for creating a letter via API
        // This would require validation and logic similar to CreateSuratForm.php
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $surat = Surat::with('currentStatus', 'createdBy', 'tujuanJabatan')->findOrFail($id);
        // Add authorization check if needed
        return response()->json($surat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Logic for updating a letter via API
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }

    /**
     * Submit the letter for verification.
     */
    public function submit(Request $request, $id)
    {
        // Logic for submitting a letter
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }

    /**
     * Verify (approve/reject) a letter.
     */
    public function verify(Request $request, $id)
    {
        // Logic for approving/rejecting a letter
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }

    /**
     * Create a disposition for a letter.
     */
    public function disposisi(Request $request, $id)
    {
        // Logic for creating a disposition
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }

    /**
     * Get tracking history for a letter.
     */
    public function tracking(Request $request, $id)
    {
        $surat = Surat::with('tracking.user.jabatan')->findOrFail($id);
        return response()->json($surat->tracking);
    }

    /**
     * Upload a final version of the letter.
     */
    public function final(Request $request, $id)
    {
        // Logic for uploading a final document
        return response()->json(['message' => 'Endpoint not implemented yet.'], 501);
    }
}