<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;

class PresensiController extends Controller
{
public function form()
{
    return view('presensi.form');
}

public function store(Request $request)
{
    $request->validate([
        'nama' => 'required'
    ]);

    Presensi::create([
        'nama' => $request->nama,
        'status' => 1,
        'tanggal' => now(),
    ]);

    return redirect()->route('login')->with('success', 'Presensi berhasil! Silakan login admin jika diperlukan.');
}
}