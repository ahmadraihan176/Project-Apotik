<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Langse Farma',
            'email' => 'admin@langsefarma.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}