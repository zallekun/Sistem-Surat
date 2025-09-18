<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckJabatan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$allowedJabatans
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$allowedJabatans)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Load user relationships
        $user->load('jabatan', 'role');
        
        // Get user's jabatan and role
        $userJabatan = $user->jabatan->nama_jabatan ?? null;
        $userRole = $user->role->name ?? null;
        
        // Create mapping between roles and jabatan names
        $roleToJabatanMap = [
            'admin' => ['Admin', 'Administrator'],
            'super_admin' => ['Super Admin', 'Administrator'],
            'kaprodi' => ['Kepala Program Studi', 'Kaprodi'],
            'staff_prodi' => ['Staff Program Studi', 'Staff Prodi'],
            'staff_fakultas' => ['Staff Fakultas'],
            'dekan' => ['Dekan'],
            'wadek' => ['Wakil Dekan', 'Wakil Dekan Bidang Akademik', 'Wakil Dekan Bidang Keuangan', 'Wakil Dekan Bidang Kemahasiswaan'],
            'kabag_tu' => ['Kepala Bagian TU', 'Kabag TU']
        ];
        
        // Check if user has any of the allowed jabatans
        foreach ($allowedJabatans as $allowedJabatan) {
            // Direct jabatan check
            if ($userJabatan && strcasecmp($userJabatan, $allowedJabatan) === 0) {
                return $next($request);
            }
            
            // Check if allowed jabatan matches user's role
            if ($userRole) {
                // Check if the allowed jabatan is in the role's mapped jabatans
                if (isset($roleToJabatanMap[$userRole])) {
                    foreach ($roleToJabatanMap[$userRole] as $mappedJabatan) {
                        if (strcasecmp($mappedJabatan, $allowedJabatan) === 0) {
                            return $next($request);
                        }
                    }
                }
                
                // Also check if role name directly matches
                if (strcasecmp($userRole, $allowedJabatan) === 0) {
                    return $next($request);
                }
            }
        }
        
        // Special case: Allow admin/super_admin to access everything
        if ($userRole && in_array($userRole, ['admin', 'super_admin'])) {
            return $next($request);
        }
        
        // Log for debugging
        \Log::warning('CheckJabatan middleware blocked access', [
            'user_id' => $user->id,
            'user_jabatan' => $userJabatan,
            'user_role' => $userRole,
            'allowed_jabatans' => $allowedJabatans,
            'route' => $request->route()->getName(),
        ]);
        
        abort(403, 'Unauthorized: Jabatan Anda tidak memiliki akses ke halaman ini.');
    }
}