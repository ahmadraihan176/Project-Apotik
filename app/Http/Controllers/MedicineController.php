<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineController extends Controller
{
    public function index()
    {
        $medicines = Medicine::latest()->paginate(10);
        $layout = getLayoutName();
        return view('admin.medicines.index', compact('medicines', 'layout'));
    }

    public function create()
    {
        $layout = getLayoutName();
        return view('admin.medicines.create', compact('layout'));
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

        $prefix = getRoutePrefix();
        return redirect()->route($prefix . '.medicines.index')
            ->with('success', 'Inventory berhasil ditambahkan!');
    }

    public function edit(Medicine $medicine)
    {
        $layout = getLayoutName();
        return view('admin.medicines.edit', compact('medicine', 'layout'));
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

        $prefix = getRoutePrefix();
        return redirect()->route($prefix . '.medicines.index')
            ->with('success', 'Inventory berhasil diupdate!');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        $prefix = getRoutePrefix();
        return redirect()->route($prefix . '.medicines.index')
            ->with('success', 'Inventory berhasil dihapus!');
    }
}