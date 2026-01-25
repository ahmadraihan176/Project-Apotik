<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanFakturController extends Controller
{
    /**
     * Scan faktur menggunakan Gemini API
     * Mendukung: gambar (JPG, PNG, WEBP) dan PDF
     */
    public function scan(Request $request)
    {
        // Set max execution time untuk request ini (2 menit)
        set_time_limit(120);
        
        $request->validate([
            'faktur_image' => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:10240' // max 10MB
        ]);

        try {
            $file = $request->file('faktur_image');
            $mimeType = $file->getMimeType();
            
            // Jika PDF, convert ke gambar dulu atau kirim langsung ke Gemini
            // Gemini 1.5 Flash mendukung PDF secara native
            $fileData = base64_encode(file_get_contents($file->getRealPath()));
            
            // Untuk PDF, gunakan mime type application/pdf
            if ($file->getClientOriginalExtension() === 'pdf') {
                $mimeType = 'application/pdf';
            }

            // Panggil Gemini API
            $result = $this->callGeminiAPI($fileData, $mimeType);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'message' => 'Faktur berhasil di-scan!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal memproses faktur'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Scan Faktur Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call Gemini API untuk extract data dari gambar/PDF faktur
     */
    private function callGeminiAPI($fileData, $mimeType)
    {
        $apiKey = config('services.gemini.api_key');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'API Key Gemini belum dikonfigurasi'
            ];
        }

        $prompt = <<<PROMPT
Kamu adalah asisten yang ahli membaca faktur pembelian obat dari distributor farmasi Indonesia.

PENTING: Baca dan extract SEMUA item/baris obat yang ada di faktur ini. Jangan hanya 1 item, tapi SEMUA item yang tertera di tabel faktur.

Analisis gambar faktur ini dan extract data dalam format JSON dengan struktur berikut:
{
    "supplier": "nama supplier/PBF",
    "no_faktur": "nomor faktur",
    "tanggal_faktur": "YYYY-MM-DD",
    "jatuh_tempo": "YYYY-MM-DD atau null jika tidak ada",
    "jenis_pembayaran": "cash atau tempo",
    "items": [
        {
            "nama_obat": "nama obat lengkap",
            "kode_produk": "kode produk jika ada",
            "no_batch": "nomor batch",
            "expired": "YYYY-MM atau YYYY-MM-DD",
            "qty": angka jumlah,
            "satuan_beli": "BOX/STRIP/BOTOL/dll",
            "isi_per_kemasan": angka isi per box/kemasan (jika ada info @24 berarti 24),
            "unit_jual": "strip/tablet/botol/ml/dll (satuan terkecil untuk dijual)",
            "harga_beli": angka harga per satuan beli (sebelum diskon),
            "diskon_persen": angka persen diskon (0 jika tidak ada)
        }
    ],
    "subtotal": angka total sebelum ppn,
    "ppn": angka ppn,
    "total_bayar": angka grand total
}

Aturan penting:
1. WAJIB extract SEMUA baris item obat yang ada di faktur, bukan hanya 1 item
2. Untuk expired date, jika hanya ada bulan/tahun (misal 05/27), konversi ke format YYYY-MM (2027-05)
3. Untuk tanggal lengkap, gunakan format YYYY-MM-DD
4. Jika ada keterangan "BOX @ 24" atau "BOX @24", berarti satuan_beli = "box", isi_per_kemasan = 24
5. Unit jual adalah satuan terkecil (biasanya strip, tablet, botol, tube, ml)
6. Harga yang tertera biasanya adalah harga per satuan beli (per box/per strip), bukan per unit jual
7. Jika ada jatuh tempo, berarti jenis_pembayaran = "tempo", jika tidak ada = "cash"
8. Semua angka harus berupa number, bukan string
9. Jika tidak bisa membaca suatu field, isi dengan null
10. Pastikan response adalah HANYA JSON valid tanpa teks tambahan apapun
11. Array "items" harus berisi SEMUA obat yang ada di faktur

Analisis faktur ini dan extract SEMUA item:
PROMPT;

        // Gunakan gemini-2.5-flash yang terbaru
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $fileData
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 8192,
            ]
        ];

        // Retry mechanism untuk handle rate limit
        $maxRetries = 3;
        $retryDelay = 2; // seconds
        $response = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = Http::timeout(120)->post($url, $payload); // 120 detik timeout
            
            if ($response->successful()) {
                break;
            }
            
            // Jika rate limit (429) atau server sibuk (503), tunggu dan retry
            if (in_array($response->status(), [429, 503, 500]) && $attempt < $maxRetries) {
                Log::warning("Gemini API Error {$response->status()} - Attempt {$attempt}, waiting {$retryDelay}s...");
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
                continue;
            }
            
            break;
        }

        if (!$response || !$response->successful()) {
            Log::error('Gemini API Error: ' . ($response ? $response->body() : 'No response'));
            
            $errorMessage = 'Gagal menghubungi Gemini API';
            if ($response) {
                if ($response->status() === 429) {
                    $errorMessage = 'Terlalu banyak request. Silakan tunggu 1-2 menit dan coba lagi.';
                } elseif ($response->status() === 403) {
                    $errorMessage = 'API Key tidak valid atau tidak memiliki akses.';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Model AI tidak ditemukan. Hubungi administrator.';
                } elseif ($response->status() === 503) {
                    $errorMessage = 'Server AI sedang sibuk. Silakan coba lagi dalam beberapa detik.';
                } elseif ($response->status() === 500) {
                    $errorMessage = 'Server AI mengalami error. Silakan coba lagi.';
                } else {
                    $errorMessage = 'Gagal menghubungi Gemini API: ' . $response->status();
                }
            }
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }

        $responseData = $response->json();

        // Extract text dari response
        $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            return [
                'success' => false,
                'message' => 'Tidak ada response dari Gemini API'
            ];
        }

        // Parse JSON dari response
        // Gemini kadang menambahkan markdown code block, jadi kita perlu bersihkan
        $text = trim($text);
        $text = preg_replace('/^```json\s*/', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Bersihkan karakter yang tidak valid
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text); // Hapus control characters
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8'); // Fix encoding
        
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Parse Error: ' . json_last_error_msg() . ' - Raw: ' . substr($text, 0, 500));
            
            // Coba perbaiki JSON yang terpotong
            // Jika JSON terpotong di tengah array items, coba tutup dengan benar
            if (strpos($text, '"items"') !== false) {
                // Cari posisi terakhir objek item yang lengkap
                $fixedText = $this->tryFixIncompleteJson($text);
                if ($fixedText) {
                    $data = json_decode($fixedText, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        Log::info('JSON fixed successfully');
                    }
                }
            }
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Gagal memparse hasil scan. Response tidak lengkap. Silakan coba lagi atau foto dengan lebih jelas.'
                ];
            }
        }

        // Validasi dan format data
        $formattedData = $this->formatScanResult($data);

        return [
            'success' => true,
            'data' => $formattedData
        ];
    }

    /**
     * Coba perbaiki JSON yang terpotong
     */
    private function tryFixIncompleteJson($text)
    {
        // Cari item terakhir yang lengkap (ada closing brace)
        $lastCompleteItem = strrpos($text, '}');
        if ($lastCompleteItem === false) {
            return null;
        }
        
        // Potong sampai item terakhir yang lengkap
        $text = substr($text, 0, $lastCompleteItem + 1);
        
        // Hitung jumlah bracket yang belum ditutup
        $openBrackets = substr_count($text, '[') - substr_count($text, ']');
        $openBraces = substr_count($text, '{') - substr_count($text, '}');
        
        // Tutup bracket dan brace yang kurang
        $text .= str_repeat(']', $openBrackets);
        $text .= str_repeat('}', $openBraces);
        
        return $text;
    }

    /**
     * Format hasil scan agar sesuai dengan form Penerimaan Farmasi
     */
    private function formatScanResult($data)
    {
        $items = [];
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Konversi expired ke format date input (YYYY-MM-DD)
                $expiredDate = null;
                if (!empty($item['expired'])) {
                    $expired = $item['expired'];
                    // Jika format YYYY-MM, tambahkan hari pertama
                    if (preg_match('/^\d{4}-\d{2}$/', $expired)) {
                        $expiredDate = $expired . '-01';
                    } elseif (preg_match('/^\d{2}\/\d{2}$/', $expired)) {
                        // Format MM/YY
                        $parts = explode('/', $expired);
                        $expiredDate = '20' . $parts[1] . '-' . $parts[0] . '-01';
                    } else {
                        $expiredDate = $expired;
                    }
                }

                // Tentukan unit jual dari satuan beli
                $satuanBeli = strtolower($item['satuan_beli'] ?? 'box');
                $unitJual = strtolower($item['unit_jual'] ?? 'strip');
                
                // Mapping satuan beli ke unit kemasan
                $unitKemasan = $satuanBeli;
                if (in_array($satuanBeli, ['box', 'dos', 'kotak'])) {
                    $unitKemasan = 'box';
                }

                $items[] = [
                    'medicine_name' => $item['nama_obat'] ?? '',
                    'product_code' => $item['kode_produk'] ?? '',
                    'no_batch' => $item['no_batch'] ?? '',
                    'expired_date' => $expiredDate,
                    'quantity' => (int) ($item['qty'] ?? 1),
                    'unit_kemasan' => $unitKemasan,
                    'isi_per_box' => (int) ($item['isi_per_kemasan'] ?? 0),
                    'unit_jual' => $unitJual,
                    'price' => (float) ($item['harga_beli'] ?? 0),
                    'discount_percent' => (float) ($item['diskon_persen'] ?? 0),
                ];
            }
        }

        // Format tanggal
        $tanggalFaktur = $data['tanggal_faktur'] ?? date('Y-m-d');
        $jatuhTempo = $data['jatuh_tempo'] ?? null;

        return [
            'supplier_name' => $data['supplier'] ?? '',
            'no_faktur' => $data['no_faktur'] ?? '',
            'receipt_date' => $tanggalFaktur,
            'jatuh_tempo' => $jatuhTempo,
            'jenis_pembayaran' => $data['jenis_pembayaran'] ?? 'cash',
            'items' => $items,
            'subtotal' => (float) ($data['subtotal'] ?? 0),
            'ppn' => (float) ($data['ppn'] ?? 0),
            'total_bayar' => (float) ($data['total_bayar'] ?? 0),
        ];
    }
}
