<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    /**
     * Validasi bahwa hanya admin yang bisa akses
     * Karyawan TIDAK BISA akses Master Karyawan
     */
    private function checkAdminAccess()
    {
        if (!auth()->check()) {
            abort(403, 'Akses ditolak. Silakan login terlebih dahulu.');
        }
        
        $user = auth()->user();
        
        if ($user->role === 'karyawan') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman Master Karyawan.');
        }
        
        // Jika role null atau kosong (user lama), set sebagai admin
        if (empty($user->role)) {
            $user->role = 'admin';
            $user->save();
        }
        
        if ($user->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();
        $karyawan = User::whereNotNull('nik')->latest()->paginate(10);
        return view('admin.karyawan.index', compact('karyawan'));
    }

    public function create()
    {
        $this->checkAdminAccess();
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|unique:users,nik|max:50',
            'email' => 'nullable|email|unique:users,email',
        ]);

        // Generate password default jika email tidak ada
        $validated['password'] = bcrypt('password123'); // Default password, bisa diganti
        
        // Jika email tidak diisi, generate dari nama
        if (empty($validated['email'])) {
            $validated['email'] = strtolower(str_replace(' ', '', $validated['name'])) . '@apotik.local';
        }

        // Set role sebagai karyawan
        $validated['role'] = 'karyawan';

        User::create($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan!');
    }

    public function edit(User $karyawan)
    {
        $this->checkAdminAccess();
        return view('admin.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, User $karyawan)
    {
        $this->checkAdminAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|unique:users,nik,' . $karyawan->id . '|max:50',
            'email' => 'nullable|email|unique:users,email,' . $karyawan->id,
        ]);

        $karyawan->update($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil diupdate!');
    }

    public function destroy(User $karyawan)
    {
        $this->checkAdminAccess();
        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus!');
    }
}
