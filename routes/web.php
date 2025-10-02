<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Health check
Route::get('/health', function () {
    return response('healthy', 200)->header('Content-Type', 'text/plain');
});

// Auth routes
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// SSO Callback - handle token from SSO (NO middleware!)
Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');

// Protected routes - require SSO authentication
Route::middleware(['sso.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Example: Role-based routes
Route::middleware(['sso.auth', 'sso.role:DOSEN,TENDIK'])->group(function () {
    // Route::get('/penilaian', [PenilaianController::class, 'index']);
});

Route::middleware(['sso.auth', 'sso.role:SUPERADMIN'])->group(function () {
    // Route::get('/admin', [AdminController::class, 'index']);
});
