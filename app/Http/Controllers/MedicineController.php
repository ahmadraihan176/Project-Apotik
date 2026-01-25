<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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

    /**
     * Update gabungan untuk detail obat (unit + harga + margin + stok).
     * Dibuat supaya UI cukup 1 tombol simpan.
     */
    public function updateInfo(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'unit' => 'required|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'margin_percent' => 'nullable|numeric|min:0',
        ]);

        $hasPenerimaan = $medicine->penerimaanBarangDetails()->exists();

        DB::beginTransaction();
        try {
            // Unit jual (satuan penjualan)
            $medicine->unit = trim($validated['unit']);

            // Stok hanya boleh diubah jika tidak punya history penerimaan (obat import Excel)
            if (array_key_exists('stock', $validated)) {
                if ($hasPenerimaan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Obat ini memiliki history penerimaan. Stok tidak bisa diubah dari sini (gunakan Penerimaan Farmasi).'
                    ], 400);
                }
                $medicine->stock = (int) $validated['stock'];
            }

            // Harga beli & margin disimpan di medicines (agar obat tanpa history penerimaan tetap punya HPP/margin)
            if (array_key_exists('purchase_price', $validated) && $validated['purchase_price'] !== null) {
                $medicine->purchase_price = $validated['purchase_price'];
            }
            if (array_key_exists('margin_percent', $validated) && $validated['margin_percent'] !== null) {
                $medicine->margin_percent = $validated['margin_percent'];
            }

            // Selling price:
            // - Jika diinput langsung, pakai itu
            // - Jika kosong tapi ada purchase_price + margin_percent, hitung otomatis
            $sellingPrice = null;
            if (array_key_exists('selling_price', $validated) && $validated['selling_price'] !== null) {
                $sellingPrice = $validated['selling_price'];
            } else {
                $purchasePriceForCalc = array_key_exists('purchase_price', $validated) && $validated['purchase_price'] !== null
                    ? (float) $validated['purchase_price']
                    : (float) ($medicine->purchase_price ?? 0);
                $marginForCalc = array_key_exists('margin_percent', $validated) && $validated['margin_percent'] !== null
                    ? (float) $validated['margin_percent']
                    : (float) ($medicine->margin_percent ?? 0);

                if ($purchasePriceForCalc > 0) {
                    $sellingPrice = $purchasePriceForCalc * (1 + ($marginForCalc / 100));
                }
            }

            if ($sellingPrice !== null) {
                $medicine->price = round($sellingPrice, 2);
            }

            $medicine->save();

            // Sinkronkan ke penerimaan detail terakhir (kalau ada) supaya data tetap konsisten di history
            $latestPenerimaanDetail = $medicine->penerimaanBarangDetails()->latest()->first();
            if ($latestPenerimaanDetail) {
                $updateData = [
                    'unit_jual' => $medicine->unit,
                ];

                if (array_key_exists('purchase_price', $validated) && $validated['purchase_price'] !== null) {
                    $updateData['price'] = $validated['purchase_price'];
                }
                if ($sellingPrice !== null) {
                    $updateData['selling_price'] = round($sellingPrice, 2);
                }
                if (array_key_exists('margin_percent', $validated) && $validated['margin_percent'] !== null) {
                    $updateData['margin_percent'] = $validated['margin_percent'];
                }

                $latestPenerimaanDetail->update($updateData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:10240' // max 10MB
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            
            // Baca semua sheet (4 sheet)
            $sheetCount = $spreadsheet->getSheetCount();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $totalRowsProcessed = 0;
            $skippedEmptyRows = 0;
            $skippedDuplicateRows = 0;
            $duplicateDetails = [];
            $emptyRowDetails = [];
            $totalRowsInSheet = [];

            // Loop melalui semua sheet
            for ($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++) {
                $worksheet = $spreadsheet->getSheet($sheetIndex);
                $sheetName = $worksheet->getTitle();
                $rows = $worksheet->toArray();
                
                // Hitung total baris di sheet (termasuk header)
                $totalRowsInSheet[$sheetName] = count($rows);

                // Skip header row (row pertama)
                $headerSkipped = false;
                $sheetRowIndex = 0;

                foreach ($rows as $rowIndex => $row) {
                    $sheetRowIndex++;
                    
                    // Skip header row
                    if (!$headerSkipped) {
                        $headerSkipped = true;
                        continue;
                    }

                    // Format Excel yang diharapkan:
                    // Hanya membaca kolom Nama Obat (kolom kedua/kolom B)
                    // Normalisasi data - handle null, empty string, dan whitespace
                    $name = '';
                    if (isset($row[1]) && $row[1] !== null) {
                        $name = trim((string)$row[1]);
                    }
                    
                    // Cek juga kolom lain untuk memastikan baris tidak kosong
                    $hasData = false;
                    for ($col = 0; $col < 4; $col++) {
                        if (isset($row[$col]) && $row[$col] !== null) {
                            $cellValue = trim((string)$row[$col]);
                            if ($cellValue !== '') {
                                $hasData = true;
                                break;
                            }
                        }
                    }

                    // Skip baris yang benar-benar kosong (tidak ada data sama sekali)
                    if (!$hasData) {
                        $skippedEmptyRows++;
                        $emptyRowDetails[] = "Sheet '{$sheetName}' Baris {$sheetRowIndex}";
                        continue;
                    }
                    
                    // Jika nama obat kosong, skip baris ini
                    if (empty($name)) {
                        $skippedEmptyRows++;
                        $emptyRowDetails[] = "Sheet '{$sheetName}' Baris {$sheetRowIndex}: Nama obat kosong";
                        continue;
                    }

                    // Set default values untuk field lainnya
                    $stock = 0; // Default stok 0
                    $parsedExpiredDate = null; // Tidak ada tanggal kadaluarsa

                    // Cek apakah obat dengan nama yang sama sudah ada (case insensitive, trim whitespace)
                    $existingMedicine = Medicine::whereRaw('TRIM(LOWER(name)) = ?', [strtolower(trim($name))])->first();

                    if ($existingMedicine) {
                        // Jika sudah ada, skip (tidak update apapun, hanya ambil nama baru saja)
                        $skippedDuplicateRows++;
                        $duplicateDetails[] = "Sheet '{$sheetName}' Baris {$sheetRowIndex}: '{$name}' (sudah ada)";
                        continue;
                    } else {
                        // Generate code dari nama obat untuk obat baru
                        $code = $this->generateMedicineCode($name);
                        
                        // Buat obat baru dengan data default
                        Medicine::create([
                            'code' => $code,
                            'name' => $name,
                            'description' => null,
                            'price' => 0, // Default harga 0, bisa diupdate manual
                            'stock' => 0, // Default stok 0
                            'unit' => 'box', // Default unit
                            'expired_date' => null, // Tidak ada tanggal kadaluarsa
                        ]);
                    }

                    $successCount++;
                    $totalRowsProcessed++;
                }
            }

            DB::commit();

            $prefix = getRoutePrefix();
            
            // Buat ringkasan per sheet
            $sheetSummary = [];
            foreach ($totalRowsInSheet as $sheetName => $totalRows) {
                $sheetSummary[] = "Sheet '{$sheetName}': {$totalRows} baris";
            }
            
            // Hitung total baris yang diproses (termasuk header)
            $totalRowsInAllSheets = array_sum($totalRowsInSheet);
            $totalDataRows = $totalRowsInAllSheets - $sheetCount; // Kurangi header per sheet
            $totalProcessed = $successCount + $skippedDuplicateRows + $skippedEmptyRows + $errorCount;
            $missingRows = $totalDataRows - $totalProcessed;
            
            $message = "Import selesai! {$successCount} data baru berhasil diimpor dari {$sheetCount} sheet. ";
            $message .= "Total baris data di Excel: {$totalDataRows} (dari {$totalRowsInAllSheets} baris termasuk header). ";
            $message .= "Total diproses: {$totalProcessed} ({$successCount} sukses, {$skippedDuplicateRows} duplikat, {$skippedEmptyRows} kosong). ";
            
            if ($missingRows > 0) {
                $message .= "⚠️ Ada {$missingRows} baris yang tidak terproses! ";
            }
            
            if ($skippedDuplicateRows > 0) {
                $message .= "{$skippedDuplicateRows} data diabaikan karena sudah ada. ";
            }
            
            if ($skippedEmptyRows > 0) {
                $message .= "{$skippedEmptyRows} baris kosong diabaikan. ";
                // Tampilkan beberapa contoh baris kosong
                if (count($emptyRowDetails) > 0 && count($emptyRowDetails) <= 5) {
                    $message .= "Baris kosong: " . implode(', ', $emptyRowDetails) . ". ";
                }
            }
            
            if ($errorCount > 0) {
                $message .= "{$errorCount} data gagal diimpor. ";
                // Tampilkan semua error (tidak dibatasi)
                if (count($errors) > 0) {
                    $message .= "Detail error: " . implode(' | ', $errors);
                }
            }
            
            // Tampilkan detail duplikat jika ada (maksimal 10)
            if ($skippedDuplicateRows > 0 && count($duplicateDetails) > 0) {
                $message .= " Duplikat (contoh): " . implode(', ', array_slice($duplicateDetails, 0, 10));
                if (count($duplicateDetails) > 10) {
                    $message .= " dan " . (count($duplicateDetails) - 10) . " lainnya.";
                }
            }
            
            // Tambahkan info per sheet jika ada lebih dari 1 sheet
            if ($sheetCount > 1) {
                $message .= " (" . implode(', ', $sheetSummary) . ")";
            }

            return redirect()->route($prefix . '.medicines.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $prefix = getRoutePrefix();
            return redirect()->route($prefix . '.medicines.index')
                ->with('error', 'Gagal mengimpor file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Update stok untuk obat yang diimport via Excel (tidak punya history penerimaan)
     */
    public function updateStock(Request $request, Medicine $medicine)
    {
        // Cek apakah obat ini diimport via Excel (tidak punya history penerimaan)
        $hasPenerimaan = $medicine->penerimaanBarangDetails()->exists();
        
        if ($hasPenerimaan) {
            return response()->json([
                'success' => false,
                'message' => 'Obat ini memiliki history penerimaan. Gunakan menu Penerimaan Farmasi untuk menambah stok.'
            ], 400);
        }

        $validated = $request->validate([
            'stock' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $medicine->update(['stock' => $validated['stock']]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate code untuk obat berdasarkan nama
     */
    private function generateMedicineCode($name)
    {
        // Ambil huruf pertama dari setiap kata (maksimal 3 huruf)
        $words = explode(' ', strtoupper(trim($name)));
        $code = '';
        $wordCount = 0;
        
        foreach ($words as $word) {
            $word = preg_replace('/[^A-Z0-9]/', '', $word); // Hanya ambil alphanumeric
            if (!empty($word) && $wordCount < 3) {
                $code .= substr($word, 0, 1);
                $wordCount++;
            }
        }
        
        // Jika code kosong, gunakan 3 huruf pertama dari nama
        if (empty($code)) {
            $code = substr(strtoupper(preg_replace('/[^A-Z0-9]/', '', $name)), 0, 3);
        }
        
        // Tambahkan timestamp untuk memastikan unik
        $timestamp = substr(time(), -6); // 6 digit terakhir dari timestamp
        $code .= $timestamp;
        
        // Cek apakah code sudah ada, jika ya tambahkan angka increment
        $baseCode = $code;
        $counter = 1;
        while (Medicine::where('code', $code)->exists()) {
            $code = $baseCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
            if ($counter > 99) {
                // Jika masih konflik, tambahkan random
                $code = $baseCode . rand(100, 999);
            }
        }
        
        return $code;
    }
}