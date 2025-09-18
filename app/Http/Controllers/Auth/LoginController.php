<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\LogAktivitas;

class LoginController extends Controller
{

    
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek user dan password
        if ($user && Hash::check($request->password, $user->password_hash)) {
            // Cek apakah user aktif
            if (!$user->is_active) {
                return back()
                    ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'])
                    ->withInput($request->only('email'));
            }

            // Login user
            Auth::login($user, $request->has('remember'));
            
            // Log aktivitas login
            $this->logActivity('Login ke sistem', $request);
            
            // Redirect ke intended page atau dashboard
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . $user->nama . '!');
        }

        // Login gagal
        return back()
            ->withErrors(['email' => 'Email atau password yang Anda masukkan salah.'])
            ->withInput($request->only('email'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        // Log aktivitas logout
        if (Auth::check()) {
            $this->logActivity('Logout dari sistem', $request);
        }

        // Logout user
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Log user activity
     */
    private function logActivity($aktivitas, Request $request)
    {
        try {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $aktivitas,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->url(),
            ]);
        } catch (\Exception $e) {
            // Log error tapi jangan stop proses login
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}