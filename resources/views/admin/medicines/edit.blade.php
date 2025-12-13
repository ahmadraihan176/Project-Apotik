@extends('layouts.admin')

@section('title', 'Edit Obat')
@section('header', 'Edit Inventory')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        @php
            $routePrefix = request()->routeIs('karyawan.*') ? 'karyawan' : 'admin';
        @endphp
        <form action="{{ route($routePrefix . '.medicines.update', $medicine) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Kode Obat</label>
                <input type="text" value="{{ $medicine->code }}" disabled
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Nama Obat *</label>
                <input type="text" name="name" value="{{ old('name', $medicine->name) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('description', $medicine->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Harga (Rp) *</label>
                    <input type="number" name="price" value="{{ old('price', $medicine->price) }}" required min="0" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Stok *</label>
                    <input type="number" name="stock" value="{{ old('stock', $medicine->stock) }}" required min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 @error('stock') border-red-500 @enderror">
                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Satuan *</label>
                    <select name="unit" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="box" {{ old('unit', $medicine->unit) == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="strip" {{ old('unit', $medicine->unit) == 'strip' ? 'selected' : '' }}>Strip</option>
                        <option value="tablet" {{ old('unit', $medicine->unit) == 'tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="botol" {{ old('unit', $medicine->unit) == 'botol' ? 'selected' : '' }}>Botol</option>
                        <option value="tube" {{ old('unit', $medicine->unit) == 'tube' ? 'selected' : '' }}>Tube</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Tanggal Kadaluarsa</label>
                    <input type="date" name="expired_date" value="{{ old('expired_date', $medicine->expired_date?->format('Y-m-d')) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="flex-1 gradient-bg text-white font-semibold py-2 rounded-lg hover:opacity-90">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
                <a href="{{ route($routePrefix . '.medicines.index') }}" class="flex-1 text-center bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-400">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection