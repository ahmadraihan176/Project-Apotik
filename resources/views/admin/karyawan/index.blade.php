@extends('layouts.admin')

@section('title', 'Master Karyawan')
@section('header', 'Master Karyawan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Daftar Karyawan</h3>
        <a href="{{ route('admin.karyawan.create') }}" class="px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
            <i class="fas fa-plus mr-2"></i>Tambah Karyawan
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($karyawan as $k)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $k->nik }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $k->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $k->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div id="barcode-{{ $k->id }}" class="inline-block"></div>
                            <div class="mt-2 space-x-2">
                                <button onclick="downloadBarcode('{{ $k->nik }}', '{{ $k->name }}', {{ $k->id }})" class="text-green-600 hover:text-green-900 text-sm">
                                    <i class="fas fa-download"></i> Download
                                </button>
                                <button onclick="printBarcode('{{ $k->nik }}', '{{ $k->name }}')" class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('admin.karyawan.edit', $k) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.karyawan.destroy', $k) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Belum ada data karyawan. <a href="{{ route('admin.karyawan.create') }}" class="text-blue-600 hover:underline">Tambah karyawan pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $karyawan->links() }}
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode untuk setiap karyawan
    @foreach($karyawan as $k)
        JsBarcode("#barcode-{{ $k->id }}", "{{ $k->nik }}", {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true
        });
    @endforeach

    function downloadBarcode(nik, name, id) {
        // Get existing SVG from page
        const existingBarcode = document.getElementById('barcode-' + id);
        let svg = null;
        
        if (existingBarcode && existingBarcode.querySelector('svg')) {
            svg = existingBarcode.querySelector('svg');
        } else {
            // Create temporary SVG
            const tempDiv = document.createElement('div');
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            tempDiv.style.top = '-9999px';
            tempDiv.innerHTML = '<svg id="temp-barcode-' + id + '"></svg>';
            document.body.appendChild(tempDiv);
            
            JsBarcode("#temp-barcode-" + id, nik, {
                format: "CODE128",
                width: 2,
                height: 80,
                displayValue: true
            });
            
            svg = document.getElementById('temp-barcode-' + id);
            if (!svg) {
                alert('Gagal membuat barcode');
                return;
            }
        }
        
        // Create wrapper SVG with text
        const wrapperSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        wrapperSvg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        wrapperSvg.setAttribute('width', '400');
        wrapperSvg.setAttribute('height', '200');
        
        // Clone original SVG
        const clonedSvg = svg.cloneNode(true);
        clonedSvg.setAttribute('x', '50');
        clonedSvg.setAttribute('y', '20');
        clonedSvg.setAttribute('width', '300');
        clonedSvg.setAttribute('height', '80');
        
        // Add text elements
        const textName = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        textName.setAttribute('x', '200');
        textName.setAttribute('y', '130');
        textName.setAttribute('text-anchor', 'middle');
        textName.setAttribute('font-family', 'Arial');
        textName.setAttribute('font-size', '16');
        textName.setAttribute('font-weight', 'bold');
        textName.textContent = name;
        
        const textNik = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        textNik.setAttribute('x', '200');
        textNik.setAttribute('y', '150');
        textNik.setAttribute('text-anchor', 'middle');
        textNik.setAttribute('font-family', 'Arial');
        textNik.setAttribute('font-size', '14');
        textNik.textContent = 'NIK: ' + nik;
        
        wrapperSvg.appendChild(clonedSvg);
        wrapperSvg.appendChild(textName);
        wrapperSvg.appendChild(textNik);
        
        // Convert SVG to JPG
        const svgData = new XMLSerializer().serializeToString(wrapperSvg);
        const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
        const url = URL.createObjectURL(svgBlob);
        
        // Create image element to load SVG
        const img = new Image();
        img.onload = function() {
            // Create canvas
            const canvas = document.createElement('canvas');
            canvas.width = 400;
            canvas.height = 200;
            const ctx = canvas.getContext('2d');
            
            // Fill white background (important for JPG)
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Draw image on canvas
            ctx.drawImage(img, 0, 0);
            
            // Convert canvas to JPG blob
            canvas.toBlob(function(blob) {
                // Create download link
                const downloadUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = 'Barcode_' + name.replace(/[^a-z0-9]/gi, '_') + '_' + nik + '.jpg';
                document.body.appendChild(a);
                a.click();
                
                // Cleanup
                setTimeout(function() {
                    document.body.removeChild(a);
                    URL.revokeObjectURL(downloadUrl);
                    URL.revokeObjectURL(url);
                }, 100);
            }, 'image/jpeg', 0.95);
        };
        
        img.onerror = function() {
            alert('Gagal mengkonversi barcode ke gambar');
            URL.revokeObjectURL(url);
        };
        
        img.src = url;
    }

    function printBarcode(nik, name) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Barcode - ${name}</title>
                    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                    <style>
                        body { 
                            text-align: center; 
                            padding: 20px; 
                            font-family: Arial, sans-serif;
                        }
                        .barcode-container {
                            margin: 20px 0;
                        }
                        .info {
                            margin-top: 10px;
                            font-size: 14px;
                        }
                    </style>
                </head>
                <body>
                    <h2>${name}</h2>
                    <div class="barcode-container">
                        <svg id="barcode"></svg>
                    </div>
                    <div class="info">
                        <p><strong>NIK:</strong> ${nik}</p>
                    </div>
                    <script>
                        JsBarcode("#barcode", "${nik}", {
                            format: "CODE128",
                            width: 2,
                            height: 80,
                            displayValue: true
                        });
                        window.onload = function() {
                            window.print();
                        }
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endpush
@endsection

