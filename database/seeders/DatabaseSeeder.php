<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@langsefarma.com'],
            [
                'name' => 'Admin Langse Farma',
                'password' => Hash::make('admin123'),
                'role' => 'admin', // Set role sebagai admin
            ]
        );

        User::updateOrCreate(
            ['email' => 'karyawan@langsefarma.com'],
            [
                'name' => 'Karyawan Langse Farma',
                'password' => Hash::make('karyawan123'),
                'role' => 'karyawan',
            ]
        );
        
        // Update semua karyawan yang namanya "Karyawan Contoh" menjadi "Karyawan Langse Farma"
        User::where('name', 'Karyawan Contoh')
            ->where('role', 'karyawan')
            ->update(['name' => 'Karyawan Langse Farma']);
    }
}