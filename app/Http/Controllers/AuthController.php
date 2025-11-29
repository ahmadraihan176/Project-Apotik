<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Presensi;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

public function login(Request $request)
{
    $request->validate([
        'role' => 'required'
    ]);

    // === LOGIN KARYAWAN ===
   if ($request->role === 'karyawan') {

    Presensi::create([
        'nama' => $request->nama,
        'status' => 1,
        'tanggal' => now(),
    ]);
    return redirect()->route('login')->with('success', 'Presensi berhasil disimpan!');
}


    // === LOGIN ADMIN ===
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.'
    ]);
}

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}