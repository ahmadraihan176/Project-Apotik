@extends('layouts.admin')

@section('title', 'Kasir')
@section('header', 'Sistem Kasir')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Product List -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Daftar Obat</h3>
        
        <input type="text" id="searchMedicine" placeholder="Cari obat..." 
            class="w-full px-4 py-2 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto" id="medicineList">
            @foreach($medicines as $medicine)
                <div class="medicine-item border border-gray-200 rounded-lg p-4 hover:border-sky-500 cursor-pointer transition" 
                    data-id="{{ $medicine->id }}"
                    data-name="{{ $medicine->name }}"
                    data-price="{{ $medicine->price }}"
                    data-stock="{{ $medicine->stock }}"
                    onclick="addToCart(this)">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-semibold text-gray-800">{{ $medicine->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $medicine->code }}</p>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                            {{ $medicine->stock }} {{ $medicine->unit }}
                        </span>
                    </div>
                    <p class="mt-2 text-sky-600 font-semibold">Rp {{ number_format($medicine->price, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Cart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Keranjang Belanja</h3>
        
        <div id="cartItems" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
            <p class="text-gray-500 text-center py-8">Keranjang kosong</p>
        </div>

        <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between text-lg font-semibold">
                <span>Total:</span>
                <span id="totalAmount" class="text-sky-600">Rp 0</span>
            </div>

            <input type="number" id="paidAmount" placeholder="Jumlah Bayar" min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">

            <div class="flex justify-between text-lg">
                <span>Kembalian:</span>
                <span id="changeAmount" class="font-semibold text-green-600">Rp 0</span>
            </div>

            <button onclick="processTransaction()" id="btnProcess"
                class="w-full gradient-bg text-white font-semibold py-3 rounded-lg hover:opacity-90 mt-4" disabled>
                <i class="fas fa-cash-register mr-2"></i>Proses Transaksi
            </button>

            <button onclick="clearCart()" class="w-full bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                <i class="fas fa-trash mr-2"></i>Kosongkan Keranjang
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];

// Search functionality
document.getElementById('searchMedicine').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.medicine-item');
    
    items.forEach(item => {
        const name = item.dataset.name.toLowerCase();
        if (name.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function addToCart(element) {
    const medicine = {
        id: element.dataset.id,
        name: element.dataset.name,
        price: parseFloat(element.dataset.price),
        stock: parseInt(element.dataset.stock),
        quantity: 1
    };

    const existingItem = cart.find(item => item.id === medicine.id);
    
    if (existingItem) {
        if (existingItem.quantity < medicine.stock) {
            existingItem.quantity++;
        } else {
            alert('Stok tidak mencukupi!');
            return;
        }
    } else {
        cart.push(medicine);
    }

    updateCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

function updateQuantity(id, delta) {
    const item = cart.find(item => item.id === id);
    if (item) {
        const newQty = item.quantity + delta;
        if (newQty > 0 && newQty <= item.stock) {
            item.quantity = newQty;
            updateCart();
        } else if (newQty <= 0) {
            removeFromCart(id);
        } else {
            alert('Stok tidak mencukupi!');
        }
    }
}

function updateCart() {
    const cartContainer = document.getElementById('cartItems');
    const totalElement = document.getElementById('totalAmount');
    const btnProcess = document.getElementById('btnProcess');

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="text-gray-500 text-center py-8">Keranjang kosong</p>';
        totalElement.textContent = 'Rp 0';
        btnProcess.disabled = true;
        return;
    }

    let html = '';
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;

        html += `
            <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h5 class="font-semibold text-sm">${item.name}</h5>
                        <p class="text-xs text-gray-500">Rp ${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <button onclick="removeFromCart('${item.id}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button onclick="updateQuantity('${item.id}', -1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center font-semibold">${item.quantity}</span>
                        <button onclick="updateQuantity('${item.id}', 1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                    <span class="font-semibold text-sky-600">Rp ${subtotal.toLocaleString('id-ID')}</span>
                </div>
            </div>
        `;
    });

    cartContainer.innerHTML = html;
    totalElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
    btnProcess.disabled = false;

    calculateChange();
}

document.getElementById('paidAmount').addEventListener('input', calculateChange);

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    const change = paid - total;

    document.getElementById('changeAmount').textContent = 'Rp ' + (change >= 0 ? change : 0).toLocaleString('id-ID');
}

function clearCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        cart = [];
        document.getElementById('paidAmount').value = '';
        updateCart();
    }
}

function processTransaction() {
    if (cart.length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;

    if (paid < total) {
        alert('Jumlah pembayaran kurang!');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.cashier.store") }}';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    cart.forEach((item, index) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = `items[${index}][medicine_id]`;
        idInput.value = item.id;
        form.appendChild(idInput);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = `items[${index}][quantity]`;
        qtyInput.value = item.quantity;
        form.appendChild(qtyInput);
    });

    const paidInput = document.createElement('input');
    paidInput.type = 'hidden';
    paidInput.name = 'paid_amount';
    paidInput.value = paid;
    form.appendChild(paidInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection