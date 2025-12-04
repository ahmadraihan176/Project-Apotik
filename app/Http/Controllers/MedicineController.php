<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        // Load all medicines for real-time client-side search (like cashier)
        $medicines = Medicine::latest()->get();
        
        return view('admin.medicines.index', compact('medicines'));
    }

    public function create()
    {
        return view('admin.medicines.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'expired_date' => 'nullable|date'
        ]);

        $validated['code'] = 'MED' . strtoupper(Str::random(6));

        Medicine::create($validated);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Inventory berhasil ditambahkan!');
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string',
            'expired_date' => 'nullable|date'
        ]);

        $medicine->update($validated);

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Inventory berhasil diupdate!');
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
        
        return view('admin.medicines.show', compact('medicine', 'penerimaanDetails', 'penjualanDetails'));
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()->route('admin.medicines.index')
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
}