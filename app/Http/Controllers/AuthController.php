<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('pilih.role');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

public function login(Request $request)
{
    // Validasi role harus dipilih
    $request->validate([
        'role' => 'required|in:admin,karyawan'
    ]);

    // Validasi email dan password
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ], [
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Password harus diisi.'
    ]);

    $credentials = [
        'email' => $request->email,
        'password' => $request->password
    ];

    if (Auth::attempt($credentials)) {
        // Regenerate session untuk keamanan (tidak flush karena akan menghapus auth session)
        $request->session()->regenerate();
        
        $user = Auth::user();
        
        // === LOGIN ADMIN ===
        if ($request->role === 'admin') {
            // Pastikan user yang login memiliki role admin
            // Jika role null atau kosong (user lama), set sebagai admin
            if (empty($user->role) || $user->role === null || $user->role === '') {
                $user->role = 'admin';
                $user->save();
            }
            
            // Jika user yang login adalah karyawan, tolak akses admin
            if ($user->role === 'karyawan') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Email ini terdaftar sebagai karyawan. Silakan pilih role Karyawan.'
                ]);
            }
            
            // Pastikan role adalah admin sebelum redirect
            if ($user->role !== 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akses ditolak. Hanya admin yang dapat login dengan role Admin.'
                ]);
            }
            
            return redirect()->route('admin.dashboard');
        }
        
        // === LOGIN KARYAWAN ===
        if ($request->role === 'karyawan') {
            // Pastikan user yang login memiliki role karyawan
            // Jika role null atau kosong (user lama), set sebagai karyawan
            if (empty($user->role) || $user->role === null || $user->role === '') {
                $user->role = 'karyawan';
                $user->save();
            }
            
            // Jika user yang login adalah admin, tolak akses karyawan
            if ($user->role === 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Email ini terdaftar sebagai admin. Silakan pilih role Admin.'
                ]);
            }
            
            // Pastikan role adalah karyawan sebelum redirect
            if ($user->role !== 'karyawan') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akses ditolak. Hanya karyawan yang dapat login dengan role Karyawan. Role saat ini: ' . ($user->role ?? 'null')
                ]);
            }
            
            return redirect()->route('karyawan.dashboard');
        }
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.'
    ]);
}

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}