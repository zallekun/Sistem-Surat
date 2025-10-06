<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['prodi', 'jabatan', 'roles']);
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by role
        if ($request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Filter by prodi
        if ($request->prodi_id) {
            $query->where('prodi_id', $request->prodi_id);
        }
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $users = $query->latest()->paginate(20);
        
        // Data for filters
        $roles = Role::all();
        $prodis = Prodi::all();
        
        return view('admin.users.index', compact('users', 'roles', 'prodis'));
    }
    
    /**
     * Show form to create new user
     */
    public function create()
    {
        $roles = Role::all();
        $prodis = Prodi::all();
        $jabatans = Jabatan::all();
        
        return view('admin.users.create', compact('roles', 'prodis', 'jabatans'));
    }
    
    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|unique:users,nip',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'prodi_id' => 'nullable|exists:prodi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
        ]);
        
        try {
            $user = User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'nip' => $request->nip,
                'password' => Hash::make($request->password),
                'prodi_id' => $request->prodi_id,
                'jabatan_id' => $request->jabatan_id,
                'is_active' => true,
            ]);
            
            $roleToAssign = $request->role;

            if ($request->jabatan_id) {
                $jabatan = Jabatan::find($request->jabatan_id);
                if ($jabatan) {
                    if ($jabatan->nama_jabatan === 'Staff Program Studi') {
                        $roleToAssign = 'staff_prodi';
                    } elseif ($jabatan->nama_jabatan === 'Staff Fakultas') {
                        $roleToAssign = 'staff_fakultas';
                    }
                }
            }

            $user->assignRole($roleToAssign);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
                
        } catch (\Exception $e) {
            Log::error('Error creating user', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan user');
        }
    }
    
    /**
     * Show form to edit user
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        $prodis = Prodi::all();
        $jabatans = Jabatan::all();
        
        return view('admin.users.edit', compact('user', 'roles', 'prodis', 'jabatans'));
    }
    
    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'nip' => 'nullable|string|unique:users,nip,' . $id,
            'role' => 'required|exists:roles,name',
            'prodi_id' => 'nullable|exists:prodi,id',
            'jabatan_id' => 'nullable|exists:jabatan,id',
        ]);
        
        try {
            $user->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'nip' => $request->nip,
                'prodi_id' => $request->prodi_id,
                'jabatan_id' => $request->jabatan_id,
            ]);
            
            $roleToAssign = $request->role;

            if ($request->jabatan_id) {
                $jabatan = Jabatan::find($request->jabatan_id);
                if ($jabatan) {
                    if ($jabatan->nama_jabatan === 'Staff Program Studi') {
                        $roleToAssign = 'staff_prodi';
                    } elseif ($jabatan->nama_jabatan === 'Staff Fakultas') {
                        $roleToAssign = 'staff_fakultas';
                    }
                }
            }

            $user->syncRoles([$roleToAssign]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diupdate');
                
        } catch (\Exception $e) {
            Log::error('Error updating user', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Gagal mengupdate user');
        }
    }
    
    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        try {
            $user->delete();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus');
                
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['error' => $e->getMessage()]);
            
            return back()->with('error', 'Gagal menghapus user');
        }
    }
    
    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error resetting password', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password',
            ], 500);
        }
    }
    
    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'is_active' => !$user->is_active,
            ]);
            
            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return response()->json([
                'success' => true,
                'message' => "User berhasil {$status}",
                'is_active' => $user->is_active,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling user status', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status user',
            ], 500);
        }
    }
}