@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Transaksi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-semibold text-gray-900">{{ $transaction->transaction_code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $transaction->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $transaction->user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-semibold text-green-600">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="showDetails({{ $transaction->id }})" 
                                class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                    <tr id="detail-{{ $transaction->id }}" class="hidden bg-gray-50">
                        <td colspan="6" class="px-6 py-4">
                            <div class="space-y-2">
                                <h4 class="font-semibold text-gray-800 mb-2">Detail Transaksi:</h4>
                                @foreach($transaction->details as $detail)
                                    <div class="flex justify-between items-center p-2 bg-white rounded">
                                        <div>
                                            <span class="font-medium">{{ $detail->medicine->name }}</span>
                                            <span class="text-gray-500 text-sm">x{{ $detail->quantity }}</span>
                                        </div>
                                        <span class="text-gray-700">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                                <div class="border-t pt-2 mt-2 flex justify-between font-semibold">
                                    <span>Total:</span>
                                    <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Bayar:</span>
                                    <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span>Kembalian:</span>
                                    <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Metode Pembayaran:</span>
                                    <span class="font-semibold">{{ $transaction->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
</div>

@push('scripts')
<script>
function showDetails(id) {
    const detailRow = document.getElementById('detail-' + id);
    detailRow.classList.toggle('hidden');
}
</script>
@endpush
@endsection