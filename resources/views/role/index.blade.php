@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-10">
    <div class="bg-white p-8 rounded-xl shadow-xl max-w-md w-full text-center">

        <h2 class="text-2xl font-bold mb-6">Pilih Role</h2>

        <a href="{{ route('login') }}" 
            class="block w-full py-3 bg-blue-600 text-white rounded-lg mb-4 hover:bg-blue-700">
            Admin
        </a>

        <a href="{{ route('presensi.form') }}" 
            class="block w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Karyawan
        </a>

    </div>
</div>
@endsection