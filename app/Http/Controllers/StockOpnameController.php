<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Medicine;
use App\Models\PenerimaanBarangDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['user', 'details.medicine'])
            ->latest()
            ->paginate(10);
        
        $layout = getLayoutName();
        return view('admin.stock-opname.index', compact('opnames', 'layout'));
    }

    public function create()
    {
        $medicines = Medicine::orderBy('name')->get();
        $layout = getLayoutName();
        return view('admin.stock-opname.create', compact('medicines', 'layout'));
    }

    public function getMedicineBatch(Request $request)
    {
        try {
            $medicineId = $request->get('medicine_id');
            
            if (!$medicineId) {
                return response()->json([
                    'success' => false,
                    'batch_number' => null, 
                    'expired_date' => null,
                    'message' => 'Medicine ID tidak ditemukan'
                ]);
            }

            // Convert to integer to ensure proper matching
            $medicineId = (int) $medicineId;

            // Ambil batch number terbaru dari penerimaan barang detail untuk obat ini
            // Cari yang memiliki no_batch tidak kosong
            $latestDetail = PenerimaanBarangDetail::where('medicine_id', $medicineId)
                ->whereRaw("no_batch IS NOT NULL AND no_batch != '' AND TRIM(no_batch) != ''")
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Log untuk debugging (bisa dihapus setelah fix)
            \Log::info('Batch lookup', [
                'medicine_id' => $medicineId,
                'found' => $latestDetail ? true : false,
                'batch_number' => $latestDetail ? $latestDetail->no_batch : null
            ]);

            if ($latestDetail && !empty(trim($latestDetail->no_batch))) {
                return response()->json([
                    'success' => true,
                    'batch_number' => trim($latestDetail->no_batch),
                    'expired_date' => $latestDetail->expired_date ? $latestDetail->expired_date->format('Y-m-d') : null
                ]);
            }

            return response()->json([
                'success' => false,
                'batch_number' => null, 
                'expired_date' => null,
                'message' => 'Tidak ada data batch untuk obat ini. Pastikan obat sudah pernah diterima melalui Penerimaan Farmasi dengan nomor batch.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMedicineBatch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'batch_number' => null,
                'expired_date' => null,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.medicine_id' => 'required|exists:medicines,id',
            'details.*.batch_number' => 'nullable|string|max:255',
            'details.*.expired_date' => 'nullable|date',
            'details.*.condition' => 'required|in:baik,rusak,kadaluarsa,hampir_kadaluarsa,retur',
            'details.*.physical_stock' => 'required|integer|min:0',
            'details.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'opname_date' => $validated['opname_date'],
                'user_id' => auth()->id(),
                'status' => $request->input('save_as_draft') ? 'draft' : 'completed',
                'notes' => $validated['notes'] ?? null
            ]);

            foreach ($validated['details'] as $detail) {
                $medicine = Medicine::findOrFail($detail['medicine_id']);
                
                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'medicine_id' => $detail['medicine_id'],
                    'batch_number' => $detail['batch_number'] ?? null,
                    'expired_date' => $detail['expired_date'] ?? null,
                    'condition' => $detail['condition'],
                    'system_stock' => $medicine->stock,
                    'physical_stock' => $detail['physical_stock'],
                    'notes' => $detail['notes'] ?? null
                ]);
            }

            DB::commit();

            $message = $request->input('save_as_draft') 
                ? 'Stok opname berhasil disimpan sebagai draft!' 
                : 'Stok opname berhasil dibuat!';

            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.stock-opname.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['user', 'approver', 'details.medicine']);
        $layout = getLayoutName();
        return view('admin.stock-opname.show', compact('stockOpname', 'layout'));
    }

    public function edit(StockOpname $stockOpname)
    {
        $stockOpname->load('details.medicine');
        $medicines = Medicine::orderBy('name')->get();
        $layout = getLayoutName();
        return view('admin.stock-opname.edit', compact('stockOpname', 'medicines', 'layout'));
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.medicine_id' => 'required|exists:medicines,id',
            'details.*.batch_number' => 'nullable|string|max:255',
            'details.*.expired_date' => 'nullable|date',
            'details.*.condition' => 'required|in:baik,rusak,kadaluarsa,hampir_kadaluarsa,retur',
            'details.*.physical_stock' => 'required|integer|min:0',
            'details.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $wasApproved = $stockOpname->isApproved();
            
            // Jika opname sudah disetujui, revert stok terlebih dahulu
            if ($wasApproved) {
                foreach ($stockOpname->details as $oldDetail) {
                    $medicine = $oldDetail->medicine;
                    
                    // Revert stok ke system_stock (stok sebelum approval)
                    if ($oldDetail->condition === 'baik' && $oldDetail->difference != 0) {
                        $medicine->stock = $oldDetail->system_stock;
                        $medicine->save();
                    }
                }
            }

            // Update opname
            $stockOpname->update([
                'opname_date' => $validated['opname_date'],
                'status' => $request->input('save_as_draft') ? 'draft' : 'completed',
                'notes' => $validated['notes'] ?? null,
                // Reset approval jika sudah disetujui sebelumnya
                'approved_by' => null,
                'approved_at' => null
            ]);

            // Hapus detail lama
            $stockOpname->details()->delete();

            // Buat detail baru
            foreach ($validated['details'] as $detail) {
                $medicine = Medicine::findOrFail($detail['medicine_id']);
                
                StockOpnameDetail::create([
                    'stock_opname_id' => $stockOpname->id,
                    'medicine_id' => $detail['medicine_id'],
                    'batch_number' => $detail['batch_number'] ?? null,
                    'expired_date' => $detail['expired_date'] ?? null,
                    'condition' => $detail['condition'],
                    'system_stock' => $medicine->stock,
                    'physical_stock' => $detail['physical_stock'],
                    'notes' => $detail['notes'] ?? null
                ]);
            }

            DB::commit();

            $message = $request->input('save_as_draft') 
                ? 'Stok opname berhasil diupdate sebagai draft!' 
                : 'Stok opname berhasil diupdate!';
            
            if ($wasApproved) {
                $message .= ' Status approval telah direset. Silakan approve ulang setelah update.';
            }

            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.stock-opname.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->isApproved()) {
            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.stock-opname.index')
                ->with('error', 'Tidak dapat menghapus opname yang sudah disetujui!');
        }

        $stockOpname->delete();

        $prefix = getRoutePrefix();
        return redirect()->route($prefix . '.stock-opname.index')
            ->with('success', 'Stok opname berhasil dihapus!');
    }

    public function approve(StockOpname $stockOpname)
    {
        if ($stockOpname->isApproved()) {
            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.stock-opname.show', $stockOpname)
                ->with('error', 'Opname ini sudah disetujui sebelumnya!');
        }

        DB::beginTransaction();
        try {
            // Update status opname
            $stockOpname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Adjust stock untuk setiap detail
            foreach ($stockOpname->details as $detail) {
                $medicine = $detail->medicine;
                
                // Hanya adjust jika kondisi baik dan ada selisih
                if ($detail->condition === 'baik' && $detail->difference != 0) {
                    $medicine->stock = $detail->physical_stock;
                    $medicine->save();
                }
            }

            DB::commit();

            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.stock-opname.show', $stockOpname)
                ->with('success', 'Stok opname berhasil disetujui dan stok telah disesuaikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
