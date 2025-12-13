<?php

if (!function_exists('getRoutePrefix')) {
    /**
     * Get route prefix based on user role or route
     * Returns 'admin' for admin users, 'karyawan' for karyawan users
     */
    function getRoutePrefix()
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
     * Get layout name based on user role or route
     */
    function getLayoutName()
    {
        // Cek route name dulu (paling akurat)
        if (request()->routeIs('karyawan.*')) {
            return 'karyawan';
        }
        
        if (request()->routeIs('admin.*')) {
            return 'admin';
        }
        
        // Cek auth user
        if (auth()->check() && auth()->user() && auth()->user()->role === 'admin') {
            return 'admin';
        }
        
        return 'karyawan'; // Default
    }
}

