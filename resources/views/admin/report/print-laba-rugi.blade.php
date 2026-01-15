<!DOCTYPE html>
<html>
<head>
    <title>Laporan Laba Rugi - {{ $months[$month] }} {{ $year }}</title>
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
        .summary-box {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #000;
            background-color: #f9fafb;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            font-size: 16px;
            padding-top: 10px;
            border-top: 2px solid #000;
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
        .text-center {
            text-align: center;
        }
        .positive {
            color: #000;
        }
        .negative {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h2>Laporan Laba Rugi</h2>
        <p><strong>Langse Farma</strong></p>
        <p>{{ $months[$month] }} {{ $year }}</p>
        <p>Terakhir Update: {{ now()->format('d/m/Y H:i') }} (GMT +07:00)</p>
    </div>
    
    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-row">
            <span>Total Pendapatan:</span>
            <span>Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Total HPP:</span>
            <span>Rp {{ number_format($totalHPPBulanan, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>{{ $totalLabaRugiBulanan >= 0 ? 'Laba' : 'Rugi' }}:</span>
            <span>Rp {{ number_format(abs($totalLabaRugiBulanan), 0, ',', '.') }} ({{ number_format($persentaseLabaBulanan, 2) }}%)</span>
        </div>
    </div>
    
    @if(count($labaRugiHarian) > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th class="text-right">Pendapatan</th>
                    <th class="text-right">HPP</th>
                    <th class="text-right">Laba/Rugi</th>
                    <th class="text-right">% Laba</th>
                    <th class="text-center">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labaRugiHarian as $index => $hari)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($hari['tanggal'])->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</td>
                        <td class="text-right">Rp {{ number_format($hari['pendapatan'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($hari['hpp'], 0, ',', '.') }}</td>
                        <td class="text-right {{ $hari['laba_rugi'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $hari['laba_rugi'] >= 0 ? '+' : '' }}Rp {{ number_format($hari['laba_rugi'], 0, ',', '.') }}
                        </td>
                        <td class="text-right">{{ number_format($hari['persentase_laba'], 2) }}%</td>
                        <td class="text-center">{{ $hari['jumlah_transaksi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-right"><strong>TOTAL BULANAN:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($totalPendapatanBulanan, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($totalHPPBulanan, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ $totalLabaRugiBulanan >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalLabaRugiBulanan), 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($persentaseLabaBulanan, 2) }}%</strong></td>
                    <td class="text-center"><strong>{{ $totalTransaksiBulanan }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align: center; padding: 20px;">Tidak ada data laba/rugi pada bulan {{ $months[$month] }} {{ $year }}</p>
    @endif
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
