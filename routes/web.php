<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

// Guest authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Public view-only routes
Route::get('/', [CustomerController::class, 'index'])->name('customer-list');
Route::get('/service', [ServiceController::class, 'index'])->name('service-list');

// Authenticated user routes (CRUD & logout)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Customer CRUD
    Route::get('/create-customer', [CustomerController::class, 'create'])->name('create-customer');
    Route::post('/store-customer', [CustomerController::class, 'store'])->name('store-customer');
    Route::get('/edit-customer/{customer}', [CustomerController::class, 'edit'])->name('edit-customer');
    Route::post('/update-customer/{customer}', [CustomerController::class, 'update'])->name('update-customer');
    Route::delete('/delete-customer/{customer}', [CustomerController::class, 'destroy'])->name('delete-customer');

    // Service CRUD
    Route::get('/create-service', [ServiceController::class, 'create'])->name('create-service');
    Route::post('/store-service', [ServiceController::class, 'store'])->name('store-service');
    Route::get('/edit-service/{service}', [ServiceController::class, 'edit'])->name('edit-service');
    Route::post('/update-service/{service}', [ServiceController::class, 'update'])->name('update-service');
    Route::delete('/delete-service/{service}', [ServiceController::class, 'destroy'])->name('delete-service');
});

// Admin-only routes
Route::middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
});
