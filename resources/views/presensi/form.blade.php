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
                patchSize: "large",
                halfSample: false
            },
            numOfWorkers: 4,
            frequency: 10,
            decoder: {
                readers: [
                    "code_128_reader",  // Prioritas untuk CODE128 (format barcode karyawan)
                    "code_39_reader",
                    "code_39_vin_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ],
                debug: {
                    drawBoundingBox: true,
                    showFrequency: false,
                    drawScanline: true,
                    showPattern: false
                }
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
            scanning = true;
            
            const code = result.codeResult.code;
            const codeFormat = result.codeResult.format || 'unknown';
            
            console.log('Barcode detected:', {
                code: code,
                format: codeFormat,
                type: typeof code,
                length: code ? code.length : 0
            });
            
            // Validate code (should not be empty)
            if (!code || (typeof code === 'string' && code.trim() === '')) {
                console.warn('Empty barcode detected, ignoring...');
                scanning = false;
                return;
            }
            
            // Normalisasi NIK: convert to string, trim, remove non-printable chars
            let nik = String(code).trim();
            // Hapus karakter non-printable dan whitespace berlebih
            nik = nik.replace(/\s+/g, ' ').trim();
            // Hapus karakter kontrol
            nik = nik.replace(/[\x00-\x1F\x7F]/g, '');
            
            console.log('NIK normalized:', {
                original: code,
                normalized: nik,
                length: nik.length
            });
            
            // Validasi minimal panjang NIK
            if (nik.length < 1) {
                console.warn('NIK terlalu pendek setelah normalisasi');
                scanning = false;
                return;
            }
            
            // Stop scanner temporarily
            Quagga.stop();
            scannerActive = false;
            
            // Process the scanned NIK
            processPresensi(nik);
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

    // Process presensi
    function processPresensi(nik) {
        // Normalisasi NIK (trim, hapus whitespace berlebih, hapus karakter kontrol)
        nik = String(nik).trim();
        nik = nik.replace(/\s+/g, ' ').trim();
        nik = nik.replace(/[\x00-\x1F\x7F]/g, ''); // Hapus karakter kontrol
        
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
            document.getElementById('errorText').textContent = 'NIK tidak valid. Silakan coba lagi.';
            document.getElementById('errorMessage').classList.remove('hidden');
            setTimeout(() => {
                resetScanner();
            }, 2000);
            return;
        }
        
        document.getElementById('statusText').textContent = 'Memproses presensi...';
        document.getElementById('statusText').classList.add('text-yellow-600');
        
        // Hide previous messages
        document.getElementById('successMessage').classList.add('hidden');
        document.getElementById('errorMessage').classList.add('hidden');

        const token = document.querySelector('meta[name="csrf-token"]')?.content || 
                     document.querySelector('input[name="_token"]')?.value;
        
        if (!token) {
            console.error('CSRF token tidak ditemukan');
            document.getElementById('errorText').textContent = 'Token keamanan tidak ditemukan. Silakan refresh halaman.';
            document.getElementById('errorMessage').classList.remove('hidden');
            setTimeout(() => {
                resetScanner();
            }, 2000);
            return;
        }
        
        console.log('Sending NIK to server:', nik);
        
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
            if (data.success) {
                // Show success message
                document.getElementById('successText').textContent = 
                    `${data.message} - ${data.data.nama} (NIK: ${data.data.nik}) - Jam: ${data.data.jam_masuk}`;
                document.getElementById('successMessage').classList.remove('hidden');
                
                // Stop scanner
                if (scannerActive) {
                    Quagga.stop();
                    scannerActive = false;
                }
                
                // Show login button option
                const loginButtonContainer = document.getElementById('loginButtonContainer');
                if (loginButtonContainer) {
                    loginButtonContainer.classList.remove('hidden');
                }
                
                // Scroll to top to show success message
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Reset scanner after 3 seconds untuk presensi berikutnya
                setTimeout(() => {
                    resetScanner();
                }, 3000);
            } else {
                // Show error message
                document.getElementById('errorText').textContent = data.message;
                document.getElementById('errorMessage').classList.remove('hidden');
                
                console.error('Presensi error:', data);
                
                // Reset scanner after 3 seconds
                setTimeout(() => {
                    resetScanner();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorText').textContent = 'Terjadi kesalahan. Silakan coba lagi.';
            document.getElementById('errorMessage').classList.remove('hidden');
            
            setTimeout(() => {
                resetScanner();
            }, 2000);
        });
    }

    // Reset scanner
    function resetScanner() {
        scanning = false;
        if (!scannerActive) {
            initScanner();
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
