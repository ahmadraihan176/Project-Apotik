<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pendapatan Bulanan - {{ $months[$month] }} {{ $year }}</title>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 1.5cm;
            size: A4;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #000;
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }
        .print-header h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        .print-header p {
            margin: 5px 0;
            font-size: 14px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 11px;
            border: 2px solid #000;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px 12px;
            text-align: left;
            color: #000;
        }
        th {
            background-color: #d1d5db;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tfoot {
            background-color: #d1d5db;
            font-weight: bold;
        }
        tfoot td {
            background-color: #d1d5db;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h2>Laporan Pendapatan Bulanan</h2>
        <p><strong>Langse Farma</strong></p>
        <p>{{ $months[$month] }} {{ $year }}</p>
        <p>Total Pendapatan: Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }} | Total Transaksi: {{ $totalTransaksiBulanan }}</p>
    </div>
    
    @if($pendapatanHarian->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jumlah Transaksi</th>
                    <th class="text-right">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendapatanHarian as $index => $hari)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($hari->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</td>
                        <td>{{ $hari->jumlah_transaksi }} transaksi</td>
                        <td class="text-right">Rp {{ number_format($hari->total_pendapatan, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>TOTAL PENDAPATAN BULANAN:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align: center; padding: 20px;">Tidak ada data pendapatan pada bulan {{ $months[$month] }} {{ $year }}</p>
    @endif
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
