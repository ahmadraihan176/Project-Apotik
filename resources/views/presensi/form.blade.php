@extends('layouts.app')

@section('title', 'Presensi Karyawan')

@section('content')
<div class="min-h-screen flex items-center justify-center gradient-bg p-6">
    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-4xl">
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-qrcode text-4xl text-sky-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800">Presensi Karyawan</h2>
            <p class="text-gray-500 mt-1">Scan barcode/QR code dari ID card untuk presensi</p>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- SUCCESS MESSAGE (AJAX) --}}
        <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-5">
            <i class="fas fa-check-circle mr-2"></i><span id="successText"></span>
        </div>

        {{-- ERROR MESSAGE (AJAX) --}}
        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5">
            <i class="fas fa-exclamation-circle mr-2"></i><span id="errorText"></span>
        </div>

        {{-- SCANNER AREA --}}
        <div class="mb-6">
            <div class="relative">
                <div id="scanner-container" class="w-full bg-black rounded-lg overflow-hidden" style="min-height: 400px;">
                    <video id="video" class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden"></canvas>
                    <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center">
                        <div class="border-4 border-white rounded-lg" style="width: 250px; height: 250px;">
                            <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white"></div>
                            <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white"></div>
                            <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white"></div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white"></div>
                        </div>
                    </div>
                </div>
                <div id="scanner-status" class="mt-4 text-center">
                    <p class="text-gray-600">
                        <i class="fas fa-camera mr-2"></i>
                        <span id="statusText">Mengaktifkan kamera...</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- MANUAL INPUT (FALLBACK) --}}
        <div class="border-t pt-6">
            <details class="cursor-pointer">
                <summary class="text-gray-700 font-semibold mb-3">
                    <i class="fas fa-keyboard mr-2"></i>Input Manual NIK (Jika scanner tidak berfungsi)
                </summary>
                <form id="manualForm" class="mt-4">
                    @csrf
                    <div class="flex gap-3">
                        <input type="text" 
                               id="manualNik" 
                               name="nik" 
                               placeholder="Masukkan NIK"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <button type="submit" 
                                class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                            <i class="fas fa-check mr-2"></i>Submit
                        </button>
                    </div>
                </form>
            </details>
        </div>

        <div class="text-center mt-6 space-y-3">
            <div>
                <a href="{{ route('pilih.role') }}" class="text-sky-600 hover:text-sky-800 font-medium transition inline-block">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Pilih Role
                </a>
            </div>
            <div id="loginButtonContainer" class="hidden mt-4">
                <a href="{{ route('login.form') }}" class="inline-block px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login ke Sistem
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
<script>
    let scannerActive = false;
    let scanning = false;
    let lastScannedCode = null;
    let scanCount = 0;
    let scanConfirmation = null;
    let messageTimeout = null;
    let currentRequest = null; // Track current request to prevent duplicate

    // Initialize QuaggaJS Scanner
    function initScanner() {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner-container'),
                constraints: {
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    facingMode: "environment", // Use back camera on mobile
                    aspectRatio: { ideal: 1.7777777778 }
                }
            },
            locator: {
                patchSize: "medium", // Changed from "large" to "medium" for better accuracy
                halfSample: true // Enable half sample for better performance
            },
            numOfWorkers: 2, // Reduced from 4 to 2 for better stability
            frequency: 5, // Reduced from 10 to 5 to prevent duplicate scans
            decoder: {
                readers: [
                    "code_128_reader"  // Only CODE128 for better accuracy (format barcode karyawan)
                ],
                debug: {
                    drawBoundingBox: true,
                    showFrequency: false,
                    drawScanline: true,
                    showPattern: false
                },
                multiple: false // Disable multiple barcode detection for accuracy
            },
            locate: true
        }, function(err) {
            if (err) {
                console.error('Scanner initialization error:', err);
                document.getElementById('statusText').textContent = 'Gagal mengaktifkan kamera. Gunakan input manual.';
                document.getElementById('statusText').classList.add('text-red-600');
                return;
            }
            console.log("Scanner initialized successfully");
            Quagga.start();
            scannerActive = true;
            document.getElementById('statusText').textContent = 'Arahkan kamera ke barcode/QR code';
            document.getElementById('statusText').classList.remove('text-gray-600');
            document.getElementById('statusText').classList.add('text-green-600');
        });

        Quagga.onDetected(function(result) {
            if (scanning) return; // Prevent multiple scans
            
            // Check confidence score (minimum 0.5 for accuracy)
            const confidence = result.codeResult.decodedCodes ? 
                result.codeResult.decodedCodes.reduce((acc, code) => acc + code.error, 0) / result.codeResult.decodedCodes.length : 0;
            
            // Skip if confidence is too low (error too high)
            if (confidence > 0.3) {
                console.warn('Low confidence scan, ignoring:', confidence);
                return;
            }
            
            const code = result.codeResult.code;
            const codeFormat = result.codeResult.format || 'unknown';
            
            // Validate code (should not be empty)
            if (!code || (typeof code === 'string' && code.trim() === '')) {
                console.warn('Empty barcode detected, ignoring...');
                return;
            }
            
            // Normalisasi NIK: convert to string, trim, remove non-printable chars
            let nik = String(code).trim();
            // Hapus karakter non-printable dan whitespace berlebih
            nik = nik.replace(/\s+/g, '').trim(); // Remove all whitespace, not just multiple
            // Hapus karakter kontrol dan karakter non-ASCII
            nik = nik.replace(/[\x00-\x1F\x7F-\x9F]/g, '');
            // Hapus karakter non-alphanumeric kecuali jika memang diperlukan
            nik = nik.replace(/[^a-zA-Z0-9]/g, '');
            
            // Validasi minimal panjang NIK (minimal 3 karakter)
            if (nik.length < 3) {
                console.warn('NIK terlalu pendek setelah normalisasi:', nik);
                return;
            }
            
            // Validasi maksimal panjang NIK (maksimal 50 karakter)
            if (nik.length > 50) {
                console.warn('NIK terlalu panjang setelah normalisasi:', nik);
                return;
            }
            
            // Debouncing: hanya proses jika kode sama dengan scan sebelumnya (konfirmasi)
            if (lastScannedCode === nik) {
                scanCount++;
            } else {
                lastScannedCode = nik;
                scanCount = 1;
            }
            
            // Hanya proses jika kode terdeteksi minimal 2 kali berturut-turut (untuk akurasi)
            if (scanCount < 2) {
                console.log('Waiting for confirmation scan. Count:', scanCount, 'NIK:', nik);
                return;
            }
            
            // Reset counter
            scanCount = 0;
            lastScannedCode = null;
            scanning = true;
            
            console.log('Barcode confirmed:', {
                code: nik,
                format: codeFormat,
                confidence: confidence,
                length: nik.length
            });
            
            // Jangan stop scanner, biarkan tetap aktif untuk scan berikutnya
            // Hanya set flag scanning untuk prevent duplicate processing
            // Quagga tetap berjalan di background
            
            // Process the scanned NIK
            processPresensi(nik);
            
            // Reset scanning flag setelah delay singkat untuk memungkinkan scan berikutnya
            // Tapi tetap prevent duplicate dengan currentRequest check
            setTimeout(() => {
                if (scanning && !currentRequest) {
                    scanning = false;
                }
            }, 500);
        });

        // Handle processing errors
        Quagga.onProcessed(function(result) {
            const drawingCtx = Quagga.canvas.ctx.overlay;
            const drawingCanvas = Quagga.canvas.dom.overlay;

            if (result) {
                if (result.boxes) {
                    drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                    result.boxes.filter(function (box) {
                        return box !== result.box;
                    }).forEach(function (box) {
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                    });
                }

                if (result.box) {
                    Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
                }

                if (result.codeResult && result.codeResult.code) {
                    Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
                }
            }
        });
    }

    // Function untuk show message dengan auto-hide
    function showMessage(type, message, autoHide = true) {
        // Clear previous timeout
        if (messageTimeout) {
            clearTimeout(messageTimeout);
            messageTimeout = null;
        }
        
        // Hide all messages first
        document.getElementById('successMessage').classList.add('hidden');
        document.getElementById('errorMessage').classList.add('hidden');
        
        // Show appropriate message
        if (type === 'success') {
            document.getElementById('successText').textContent = message;
            document.getElementById('successMessage').classList.remove('hidden');
        } else {
            document.getElementById('errorText').textContent = message;
            document.getElementById('errorMessage').classList.remove('hidden');
        }
        
        // Auto-hide after 4 seconds
        if (autoHide) {
            messageTimeout = setTimeout(() => {
                document.getElementById('successMessage').classList.add('hidden');
                document.getElementById('errorMessage').classList.add('hidden');
                messageTimeout = null;
            }, 4000);
        }
        
        // Scroll to top to show message
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Function untuk update status text
    function updateStatus(text, type = 'info') {
        const statusEl = document.getElementById('statusText');
        if (!statusEl) return;
        
        statusEl.textContent = text;
        statusEl.classList.remove('text-yellow-600', 'text-red-600', 'text-green-600', 'text-gray-600');
        
        switch(type) {
            case 'processing':
                statusEl.classList.add('text-yellow-600');
                break;
            case 'error':
                statusEl.classList.add('text-red-600');
                break;
            case 'success':
                statusEl.classList.add('text-green-600');
                break;
            default:
                statusEl.classList.add('text-gray-600');
        }
    }

    // Process presensi
    function processPresensi(nik) {
        // Prevent duplicate request
        if (currentRequest) {
            console.log('Request masih diproses, mengabaikan scan baru');
            return;
        }
        // Normalisasi NIK yang lebih ketat
        nik = String(nik).trim();
        // Hapus semua whitespace
        nik = nik.replace(/\s+/g, '');
        // Hapus karakter kontrol dan non-printable
        nik = nik.replace(/[\x00-\x1F\x7F-\x9F]/g, '');
        // Hapus karakter non-alphanumeric (hanya angka dan huruf)
        nik = nik.replace(/[^a-zA-Z0-9]/g, '');
        // Convert ke uppercase untuk konsistensi
        nik = nik.toUpperCase();
        
        console.log('Processing presensi dengan NIK:', {
            original: nik,
            normalized: nik,
            type: typeof nik,
            length: nik.length,
            charCodes: nik.split('').map(c => c.charCodeAt(0))
        });
        
        // Validasi NIK tidak kosong
        if (!nik || nik.length === 0) {
            console.error('NIK kosong setelah normalisasi');
            showMessage('error', 'NIK tidak valid. Silakan coba lagi.');
            resetScanner();
            return;
        }
        
        updateStatus('Memproses presensi...', 'processing');
        
        // Hide previous messages
        document.getElementById('successMessage').classList.add('hidden');
        document.getElementById('errorMessage').classList.add('hidden');

        const token = document.querySelector('meta[name="csrf-token"]')?.content || 
                     document.querySelector('input[name="_token"]')?.value;
        
        if (!token) {
            console.error('CSRF token tidak ditemukan');
            showMessage('error', 'Token keamanan tidak ditemukan. Silakan refresh halaman.');
            resetScanner();
            return;
        }
        
        console.log('Sending NIK to server:', nik);
        
        // Set current request untuk prevent duplicate
        currentRequest = { nik: nik, timestamp: Date.now() };
        
        fetch('{{ route("presensi.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nik: nik })
        })
        .then(response => response.json())
        .then(data => {
            // Clear current request
            currentRequest = null;
            
            if (data.success) {
                // Show success message
                const successMsg = `${data.message} - ${data.data.nama} (NIK: ${data.data.nik}) - Jam: ${data.data.jam_masuk}`;
                showMessage('success', successMsg);
                
                // Show login button option
                const loginButtonContainer = document.getElementById('loginButtonContainer');
                if (loginButtonContainer) {
                    loginButtonContainer.classList.remove('hidden');
                }
                
                updateStatus('Presensi berhasil! Siap untuk scan berikutnya...', 'success');
                
                // Reset scanner immediately untuk presensi berikutnya (tanpa delay)
                // Scanner tetap aktif, hanya reset state untuk scan berikutnya
                resetScanner();
            } else {
                // Show error message
                showMessage('error', data.message);
                updateStatus('Presensi gagal. Siap untuk scan berikutnya...', 'error');
                
                console.error('Presensi error:', data);
                
                // Reset scanner immediately untuk scan berikutnya
                resetScanner();
            }
        })
        .catch(error => {
            // Clear current request
            currentRequest = null;
            
            console.error('Error:', error);
            showMessage('error', 'Terjadi kesalahan. Silakan coba lagi.');
            updateStatus('Terjadi kesalahan. Siap untuk scan berikutnya...', 'error');
            
            // Reset scanner immediately untuk scan berikutnya
            resetScanner();
        });
    }

    // Reset scanner state (scanner tetap aktif, hanya reset state)
    function resetScanner() {
        scanning = false;
        lastScannedCode = null;
        scanCount = 0;
        if (scanConfirmation) {
            clearTimeout(scanConfirmation);
            scanConfirmation = null;
        }
        
        // Update status text hanya jika tidak ada request yang sedang diproses
        if (!currentRequest) {
            updateStatus('Arahkan kamera ke barcode/QR code', 'success');
        }
        
        // Jika scanner tidak aktif, restart scanner
        if (!scannerActive) {
            // Restart scanner dengan delay minimal
            setTimeout(function() {
                // Double check scanner masih tidak aktif sebelum restart
                if (!scannerActive) {
                    initScanner();
                }
            }, 100);
        }
    }

    // Manual form submission
    document.getElementById('manualForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const nik = document.getElementById('manualNik').value.trim();
        
        if (!nik) {
            alert('Silakan masukkan NIK');
            return;
        }
        
        processPresensi(nik);
        document.getElementById('manualNik').value = '';
    });

    // Initialize scanner on page load
    window.addEventListener('load', function() {
        // Check if browser supports getUserMedia
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            initScanner();
        } else {
            document.getElementById('statusText').textContent = 'Browser tidak mendukung akses kamera. Gunakan input manual.';
            document.getElementById('statusText').classList.add('text-red-600');
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (scannerActive) {
            Quagga.stop();
        }
    });
</script>
@endpush
@endsection
