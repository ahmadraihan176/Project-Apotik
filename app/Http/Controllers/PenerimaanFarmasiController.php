<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\PenerimaanBarang;
use App\Models\PenerimaanBarangDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PenerimaanFarmasiController extends Controller
{
    public function create()
    {
        // Ambil supplier yang sudah pernah digunakan (distinct)
        $suppliers = PenerimaanBarang::whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct()
            ->orderBy('supplier_name')
            ->pluck('supplier_name')
            ->toArray();
        
        // Generate nomor urut otomatis per bulan
        $nextNoUrut = PenerimaanBarang::generateNoUrut();
        
        return view('admin.penerimaan-farmasi.create', compact('suppliers', 'nextNoUrut'));
    }

    public function getNoUrut(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $noUrut = PenerimaanBarang::generateNoUrut($date);
        
        return response()->json([
            'no_urut' => $noUrut
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_date' => 'required|date',
            'supplier_name' => 'nullable|string|max:255',
            'jenis_penerimaan' => 'nullable|string|max:255',
            'no_sp' => 'nullable|string|max:255',
            'no_faktur' => 'nullable|string|max:255',
            'jenis_pembayaran' => 'required|in:cash,tempo',
            'jatuh_tempo' => 'nullable|date|required_if:jenis_pembayaran,tempo',
            'diterima_semua' => 'nullable|string|max:255',
            'no_urut' => 'nullable|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.medicine_name' => 'required|string|max:255',
            'details.*.product_code' => 'nullable|string|max:255',
            'details.*.unit_kemasan' => 'nullable|string|max:255',
            'details.*.no_batch' => 'nullable|string|max:255',
            'details.*.expired_date' => 'nullable|date',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.price' => 'required|numeric|min:0',
            'details.*.unit_jual' => 'required|string|max:255',
            'details.*.isi_per_box' => 'nullable|integer|min:1',
            'details.*.description' => 'nullable|string',
            'details.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'details.*.discount_amount' => 'nullable|numeric|min:0',
            'details.*.margin_percent' => 'nullable|numeric|min:0',
            'details.*.selling_price' => 'nullable|numeric|min:0',
            'ppn_percent' => 'nullable|numeric|min:0|max:100',
            'ppn_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Hitung total dari detail items (setelah diskon per item)
            $subtotal = 0;
            $totalQuantity = 0;
            foreach ($validated['details'] as $detail) {
                $itemSubtotal = ($detail['price'] * $detail['quantity']);
                $itemDiscount = $detail['discount_amount'] ?? ($itemSubtotal * ($detail['discount_percent'] ?? 0) / 100);
                $itemSubtotal -= $itemDiscount;
                $subtotal += $itemSubtotal;
                $totalQuantity += $detail['quantity'];
            }

            // Hitung PPN (otomatis 11%) dari subtotal
            $ppnPercent = 11; // PPN selalu 11%
            $ppnAmount = $subtotal * $ppnPercent / 100;
            $grandTotal = $subtotal + $ppnAmount;

            // Generate nomor urut otomatis jika belum ada
            $noUrut = $validated['no_urut'] ?? null;
            if (!$noUrut || empty($noUrut)) {
                $noUrut = PenerimaanBarang::generateNoUrut($validated['receipt_date']);
            }
            
            // Buat penerimaan barang
            $penerimaanBarang = PenerimaanBarang::create([
                'receipt_code' => PenerimaanBarang::generateCode(),
                'receipt_date' => $validated['receipt_date'],
                'supplier_name' => $validated['supplier_name'] ?? null,
                'jenis_penerimaan' => $validated['jenis_penerimaan'] ?? null,
                'no_sp' => $validated['no_sp'] ?? null,
                'no_faktur' => $validated['no_faktur'] ?? null,
                'jenis_pembayaran' => $validated['jenis_pembayaran'],
                'jatuh_tempo' => $validated['jatuh_tempo'] ?? null,
                'diterima_semua' => $validated['diterima_semua'] ?? null,
                'no_urut' => $noUrut,
                'total' => $subtotal,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'ppn_percent' => 11, // PPN selalu 11%
                'ppn_amount' => $ppnAmount,
                'grand_total' => $grandTotal,
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? null
            ]);

            // Buat detail items
            foreach ($validated['details'] as $detail) {
                // Validasi isi_per_box jika kemasan = box
                $unitKemasan = $detail['unit_kemasan'] ?? $detail['unit_jual'];
                if ($unitKemasan === 'box' && (!isset($detail['isi_per_box']) || $detail['isi_per_box'] <= 0)) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Isi per box harus diisi jika kemasan adalah box!');
                }
                
                // Hitung harga per unit setelah diskon per item
                // 1. Hitung subtotal item (harga beli × quantity)
                $itemSubtotal = ($detail['price'] * $detail['quantity']);
                
                // 2. Hitung diskon per item
                $itemDiscount = $detail['discount_amount'] ?? ($itemSubtotal * ($detail['discount_percent'] ?? 0) / 100);
                $itemSubtotalAfterDiscount = $itemSubtotal - $itemDiscount;
                
                // 3. Hitung PPN per item secara proporsional
                // PPN per item = (subtotal item setelah diskon / subtotal) × PPN total
                $ppnPerItem = $subtotal > 0 ? ($itemSubtotalAfterDiscount / $subtotal) * $ppnAmount : 0;
                
                // 4. Hitung subtotal per item dengan PPN (ini yang disimpan)
                $itemSubtotalWithPPN = $itemSubtotalAfterDiscount + $ppnPerItem;
                
                // 5. Hitung harga jual per unit jual (untuk disimpan di medicines.price)
                // Jika kemasan = box, harga per unit jual = (harga per kemasan setelah diskon + PPN) / isi_per_box
                // Jika kemasan bukan box, harga per unit jual = harga per kemasan setelah diskon + PPN
                $priceWithPPN = 0;
                
                if ($unitKemasan === 'box' && isset($detail['isi_per_box']) && $detail['isi_per_box'] > 0) {
                    // Harga per box setelah diskon + PPN
                    $pricePerBoxWithPPN = $itemSubtotalWithPPN / $detail['quantity'];
                    // Harga per unit jual (strip/tablet) = harga per box / isi per box
                    $priceWithPPN = $pricePerBoxWithPPN / $detail['isi_per_box'];
                } else {
                    // Harga per kemasan setelah diskon + PPN
                    $pricePerKemasanWithPPN = $itemSubtotalWithPPN / $detail['quantity'];
                    // Jika kemasan = unit jual, langsung pakai
                    $priceWithPPN = $pricePerKemasanWithPPN;
                }
                
                // Hitung stok dalam unit jual
                // Jika kemasan = box, stok = quantity × isi_per_box
                // Jika kemasan bukan box, stok = quantity
                $stockToAdd = 0;
                
                if ($unitKemasan === 'box' && isset($detail['isi_per_box']) && $detail['isi_per_box'] > 0) {
                    $stockToAdd = $detail['quantity'] * $detail['isi_per_box'];
                } else {
                    $stockToAdd = $detail['quantity'];
                }
                
                // Ambil harga jual dari input (sudah termasuk margin)
                // Jika tidak ada, hitung dari margin
                if (isset($detail['selling_price']) && $detail['selling_price'] > 0) {
                    $sellingPrice = $detail['selling_price'];
                } else {
                    // Hitung dari margin jika selling_price tidak ada
                    $marginPercent = $detail['margin_percent'] ?? 0;
                    $sellingPrice = $priceWithPPN * (1 + $marginPercent / 100);
                }
                
                // Cek apakah obat sudah ada (case-insensitive)
                $medicine = Medicine::whereRaw('LOWER(name) = ?', [strtolower($detail['medicine_name'])])->first();
                
                // Jika belum ada, buat obat baru dengan harga jual (setelah margin)
                if (!$medicine) {
                    // Gunakan kode produk dari form jika ada, atau generate baru
                    $productCode = $detail['product_code'] ?? null;
                    if (!$productCode || empty($productCode)) {
                        $productCode = 'MED' . strtoupper(Str::random(6));
                    }
                    
                    $medicine = Medicine::create([
                        'name' => $detail['medicine_name'],
                        'code' => $productCode,
                        'description' => $detail['description'] ?? null,
                        'price' => $sellingPrice, // Harga jual per unit jual (setelah margin)
                        'stock' => 0, // Akan ditambah di bawah
                        'unit' => $detail['unit_jual'], // Unit jual (strip/tablet/ml/dll)
                        'expired_date' => $detail['expired_date'] ?? null
                    ]);
                } else {
                    // Update unit jual jika berbeda
                    if ($medicine->unit !== $detail['unit_jual']) {
                        $medicine->update(['unit' => $detail['unit_jual']]);
                    }
                    // Harga jual akan diupdate setelah detail disimpan dengan weighted average
                }

                PenerimaanBarangDetail::create([
                    'penerimaan_barang_id' => $penerimaanBarang->id,
                    'medicine_id' => $medicine->id,
                    'unit_kemasan' => $unitKemasan,
                    'isi_per_box' => ($unitKemasan === 'box' && isset($detail['isi_per_box'])) ? $detail['isi_per_box'] : null,
                    'unit_jual' => $detail['unit_jual'],
                    'no_batch' => $detail['no_batch'] ?? null,
                    'expired_date' => $detail['expired_date'] ?? null,
                    'quantity' => $detail['quantity'],
                    'price' => $priceWithPPN, // Harga beli per unit jual (setelah diskon + PPN, sebelum margin)
                    'discount_percent' => $detail['discount_percent'] ?? 0,
                    'discount_amount' => $itemDiscount,
                    'margin_percent' => $detail['margin_percent'] ?? 0,
                    'selling_price' => $sellingPrice, // Harga jual per unit jual (setelah margin)
                    'subtotal' => $itemSubtotalWithPPN // Subtotal = subtotal setelah diskon + PPN per item
                ]);

                // Update stok obat (tambah stok dalam unit jual)
                $medicine->addStock($stockToAdd);
                
                // Update harga jual dengan weighted average dari semua batch (termasuk yang baru saja disimpan)
                // Ini memastikan medicines.price tidak hanya mengikuti batch terakhir
                // Hanya hitung dari batch yang punya selling_price (abaikan NULL untuk batch lama)
                if ($medicine) {
                    $pembelianData = DB::table('penerimaan_barang_details')
                        ->where('medicine_id', $medicine->id)
                        ->whereNotNull('selling_price')
                        ->where('selling_price', '>', 0)
                        ->selectRaw('
                            SUM(selling_price * 
                                CASE 
                                    WHEN unit_kemasan = "box" AND isi_per_box > 0 
                                    THEN quantity * isi_per_box
                                    ELSE quantity 
                                END
                            ) as total_selling_price_weighted,
                            SUM(CASE 
                                WHEN unit_kemasan = "box" AND isi_per_box > 0 
                                THEN quantity * isi_per_box
                                ELSE quantity 
                            END) as total_qty_unit_jual
                        ')
                        ->first();
                    
                    if ($pembelianData && $pembelianData->total_qty_unit_jual > 0) {
                        // Weighted average selling price
                        $avgSellingPrice = $pembelianData->total_selling_price_weighted / $pembelianData->total_qty_unit_jual;
                        $medicine->update(['price' => round($avgSellingPrice, 2)]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.medicines.index')
                ->with('success', 'Penerimaan farmasi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

