<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\Jabatan;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'prodi', 'fakultas', 'jabatan'])->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $prodi = Prodi::all();
        $fakultas = Fakultas::all();
        $jabatan = Jabatan::all();
        return view('users.create', compact('roles', 'prodi', 'fakultas', 'jabatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create($request->only(['nama', 'email', 'password', 'prodi_id', 'fakultas_id', 'jabatan_id', 'role_id']));
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $user->load(['role', 'prodi', 'fakultas', 'jabatan']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $prodi = Prodi::all();
        $fakultas = Fakultas::all();
        $jabatan = Jabatan::all();
        return view('users.edit', compact('user', 'roles', 'prodi', 'fakultas', 'jabatan'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update($request->only(['nama', 'email', 'prodi_id', 'fakultas_id', 'jabatan_id', 'role_id']));
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}