<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanBarang;
use Illuminate\Http\Request;

class JatuhTempoController extends Controller
{
    public function index()
    {
        // Ambil semua penerimaan dengan pembayaran tempo yang belum dibayar
        $penerimaanTempo = PenerimaanBarang::where('jenis_pembayaran', 'tempo')
            ->whereNotNull('jatuh_tempo')
            ->where('status_pembayaran', 'belum_bayar')
            ->with(['user', 'details.medicine'])
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        // Kategorikan berdasarkan status
        $sudahJatuhTempo = $penerimaanTempo->filter(function ($item) {
            return $item->jatuh_tempo < now();
        });

        $akanJatuhTempo = $penerimaanTempo->filter(function ($item) {
            return $item->jatuh_tempo >= now() && $item->jatuh_tempo <= now()->addDays(7);
        });

        $belumJatuhTempo = $penerimaanTempo->filter(function ($item) {
            return $item->jatuh_tempo > now()->addDays(7);
        });

        return view('admin.jatuh-tempo.index', compact('sudahJatuhTempo', 'akanJatuhTempo', 'belumJatuhTempo', 'penerimaanTempo'));
    }

    public function markAsPaid($id)
    {
        $penerimaan = PenerimaanBarang::findOrFail($id);
        
        if ($penerimaan->jenis_pembayaran !== 'tempo') {
            return redirect()->back()
                ->with('error', 'Hanya pembelian tempo yang bisa ditandai sebagai sudah dibayar!');
        }

        $penerimaan->update([
            'status_pembayaran' => 'sudah_bayar',
            'tanggal_bayar' => now()
        ]);

        return redirect()->route('admin.jatuh-tempo.index')
            ->with('success', 'Pembelian berhasil ditandai sebagai sudah dibayar!');
    }
}
