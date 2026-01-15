<?php

if (!function_exists('getRoutePrefix')) {
    /**
     * Get route prefix based on user role or route
     * Returns 'admin' for admin users, 'karyawan' for karyawan users
     *
     * @return string
     */
    function getRoutePrefix(): string
    {
        // Cek route name dulu (paling akurat)
        if (request()->routeIs('karyawan.*')) {
            return 'karyawan';
        }
        
        if (request()->routeIs('admin.*')) {
            return 'admin';
        }

        // Check auth user
        if (auth()->check() && auth()->user() && auth()->user()->role === 'admin') {
            return 'admin';
        }
        
        return 'karyawan'; // Default untuk karyawan
    }
}

if (!function_exists('getLayoutName')) {
    /**
     * Get layout name - selalu return 'admin' karena karyawan menggunakan layout yang sama
     *
     * @return string
     */
    function getLayoutName(): string
    {
        // Semua user (admin dan karyawan) menggunakan layout admin yang sama
        return 'admin';
    }
}

if (!function_exists('getAvailableYears')) {
    /**
     * Get available years for dropdown filters
     * Returns years from 2026 to current year + 2 years ahead
     *
     * @return array
     */
    function getAvailableYears(): array
    {
        $currentYear = now()->year;
        $yearsAhead = 2; // Tahun ke depan yang ditampilkan
        $startYear = 2026; // Tahun minimal dimulai dari 2026
        
        // Generate array tahun dari 2026 sampai currentYear + yearsAhead
        $years = [];
        for ($year = $startYear; $year <= $currentYear + $yearsAhead; $year++) {
            $years[] = $year;
        }
        
        // Urutkan dari tahun terbesar ke terkecil (tahun terbaru di atas)
        rsort($years);
        
        return $years;
    }
}