<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    public function index()
    {
        // Load all medicines for real-time client-side search (like cashier)
        $medicines = Medicine::latest()->get();
        $layout = getLayoutName();
        
        return view('admin.medicines.index', compact('medicines', 'layout'));
    }


    public function show(Medicine $medicine)
    {
        // Load history penerimaan dengan detail lengkap
        $penerimaanDetails = $medicine->penerimaanBarangDetails()
            ->with('penerimaanBarang.user')
            ->latest()
            ->get();
        
        // Load history penjualan
        $penjualanDetails = $medicine->transactionDetails()
            ->with('transaction.user')
            ->latest()
            ->get();
        
        $layout = getLayoutName();
        return view('admin.medicines.show', compact('medicine', 'penerimaanDetails', 'penjualanDetails', 'layout'));
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        $prefix = getRoutePrefix();
        return redirect()->route($prefix . '.medicines.index')
            ->with('success', 'Inventory berhasil dihapus!');
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        
        $medicinesQuery = Medicine::where('stock', '>', 0);
        
        if (!empty($query)) {
            $medicinesQuery->where(function($q) use ($query) {
                $q->where('name', 'like', $query . '%')
                  ->orWhere('code', 'like', $query . '%');
            });
        }

        $medicines = $medicinesQuery
            ->limit(50)
            ->get(['id', 'name', 'code', 'price', 'stock', 'unit']);

        return response()->json($medicines);
    }

    public function updatePrice(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'margin_percent' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            // Update harga jual di medicine jika ada
            if (isset($validated['selling_price'])) {
                $medicine->update(['price' => $validated['selling_price']]);
            }

            // Update harga beli, harga jual, dan margin di penerimaan barang detail terakhir
            $latestPenerimaanDetail = $medicine->penerimaanBarangDetails()->latest()->first();
            
            if ($latestPenerimaanDetail) {
                $updateData = [];
                
                if (isset($validated['purchase_price'])) {
                    $updateData['price'] = $validated['purchase_price'];
                }
                
                if (isset($validated['selling_price'])) {
                    $updateData['selling_price'] = $validated['selling_price'];
                }
                
                if (isset($validated['margin_percent'])) {
                    $updateData['margin_percent'] = $validated['margin_percent'];
                }
                
                if (!empty($updateData)) {
                    $latestPenerimaanDetail->update($updateData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}