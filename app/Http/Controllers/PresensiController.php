<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function form()
    {
        return view('presensi.form');
    }

    /**
     * Manajemen presensi untuk admin
     * Hanya admin yang bisa akses
     */
    public function index(Request $request)
    {
        // Validasi hanya admin yang bisa akses
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }

        $query = Presensi::with('user');

        // Filter berdasarkan tanggal jika ada
        if ($request->has('tanggal') && $request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $presensi = $query->latest('tanggal')->paginate(15);

        return view('admin.presensi.index', compact('presensi'));
    }

    /**
     * Rekapan presensi per bulan untuk admin
     * Hanya admin yang bisa akses
     */
    public function rekapan(Request $request)
    {
        // Validasi hanya admin yang bisa akses
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }

        // Ambil bulan dan tahun dari request
        // Default: Desember 2025 jika sekarang sebelum Desember 2025, atau bulan/tahun sekarang
        $tahunSekarang = now()->year;
        $bulanSekarang = now()->month;
        
        // Jika tahun sekarang < 2025, atau tahun 2025 tapi bulan < 12, default ke Desember 2025
        if ($tahunSekarang < 2025 || ($tahunSekarang == 2025 && $bulanSekarang < 12)) {
            $defaultBulan = 12;
            $defaultTahun = 2025;
        } else {
            $defaultBulan = $bulanSekarang;
            $defaultTahun = $tahunSekarang;
        }

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = $request->input('tahun', $defaultTahun);
        
        // Validasi: tahun minimal 2025
        if ($tahun < 2025) {
            $tahun = 2025;
        }
        
        // Validasi: jika tahun 2025, bulan minimal 12 (Desember)
        if ($tahun == 2025 && $bulan < 12) {
            $bulan = 12;
        }

        // Ambil semua karyawan yang punya NIK
        $karyawan = User::whereNotNull('nik')
            ->where('role', 'karyawan')
            ->orderBy('name')
            ->get();

        // Ambil data presensi untuk bulan dan tahun yang dipilih
        $presensiBulanan = Presensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('status', 1)
            ->get()
            ->groupBy('user_id');

        // Hitung rekapan untuk setiap karyawan
        $rekapan = [];
        foreach ($karyawan as $k) {
            $presensiKaryawan = $presensiBulanan->get($k->id, collect());
            $rekapan[] = [
                'karyawan' => $k,
                'total_hadir' => $presensiKaryawan->count(),
                'total_hari' => Carbon::create($tahun, $bulan, 1)->daysInMonth,
                'persentase' => $presensiKaryawan->count() > 0 
                    ? round(($presensiKaryawan->count() / Carbon::create($tahun, $bulan, 1)->daysInMonth) * 100, 2)
                    : 0
            ];
        }

        // Urutkan berdasarkan total hadir (tertinggi ke terendah)
        usort($rekapan, function($a, $b) {
            return $b['total_hadir'] <=> $a['total_hadir'];
        });

        return view('admin.presensi.rekapan', compact('rekapan', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string'
        ]);

        // Normalisasi NIK: trim, hapus karakter non-printable, hapus whitespace berlebih
        $nik = preg_replace('/\s+/', ' ', trim($request->nik));
        $nik = preg_replace('/[^\w\s-]/', '', $nik); // Hapus karakter khusus kecuali alphanumeric, space, dan dash
        
        // Log untuk debugging
        \Log::info('Presensi attempt', [
            'nik_raw' => $request->nik,
            'nik_normalized' => $nik,
            'nik_length' => strlen($nik)
        ]);
        
        // Cari dengan beberapa metode untuk memastikan match
        $user = null;
        
        // Method 1: Exact match (case insensitive, trim)
        $user = User::whereRaw('LOWER(TRIM(nik)) = ?', [strtolower($nik)])
            ->whereNotNull('nik')
            ->first();
        
        // Method 2: Jika tidak ditemukan, coba tanpa trim di database
        if (!$user) {
            $user = User::whereRaw('LOWER(nik) = ?', [strtolower($nik)])
                ->whereNotNull('nik')
                ->first();
        }
        
        // Method 3: Coba match dengan menghapus semua whitespace
        if (!$user) {
            $nikNoSpace = preg_replace('/\s+/', '', $nik);
            $user = User::whereNotNull('nik')
                ->get()
                ->filter(function($u) use ($nikNoSpace) {
                    $dbNik = preg_replace('/\s+/', '', $u->nik ?? '');
                    return strtolower($dbNik) === strtolower($nikNoSpace);
                })
                ->first();
        }

        if (!$user) {
            $allNiks = User::whereNotNull('nik')->pluck('nik')->toArray();
            
            \Log::warning('Presensi NIK tidak ditemukan', [
                'nik_raw' => $request->nik,
                'nik_normalized' => $nik,
                'nik_terdaftar' => $allNiks
            ]);
            
            $message = 'NIK tidak ditemukan. Pastikan NIK sudah terdaftar di sistem. NIK yang dicari: "' . $nik . '"';
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'debug' => config('app.debug') ? [
                        'nik_raw' => $request->nik,
                        'nik_normalized' => $nik
                    ] : null
                ], 404);
            }
            
            return redirect()->route('presensi.form')->with('error', $message);
        }

        // Pastikan user adalah karyawan, bukan admin
        if ($user->role === 'admin') {
            $message = 'Admin tidak perlu melakukan presensi.';
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            
            return redirect()->route('presensi.form')->with('error', $message);
        }

        $now = Carbon::now();

        // Cek apakah sudah presensi hari ini
        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $now->toDateString())
            ->first();

        if ($presensiHariIni) {
            $message = 'Anda sudah melakukan presensi hari ini pada jam ' . $presensiHariIni->jam_masuk;
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }
            
            return redirect()->route('presensi.form')->with('error', $message);
        }

        // Simpan presensi
        $presensi = Presensi::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'status' => 1,
            'tanggal' => $now,
            'jam_masuk' => $now->format('H:i:s'),
        ]);

        $successMessage = 'Presensi berhasil! ' . $user->name . ' - Jam: ' . $presensi->jam_masuk;
        
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => [
                    'nama' => $user->name,
                    'nik' => $user->nik,
                    'jam_masuk' => $presensi->jam_masuk,
                    'tanggal' => $presensi->tanggal->format('d/m/Y')
                ]
            ], 200);
        }

        return redirect()->route('presensi.form')->with('success', $successMessage);
    }
}