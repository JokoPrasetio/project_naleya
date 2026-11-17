<?php

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => view('dashboard.index'))
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::prefix('akses')->name('access.')->group(function (): void {
        Route::get('/pengguna', [UserController::class, 'index'])
            ->middleware('can:access.users.view')->name('users.index');
        Route::get('/pengguna/data', [UserController::class, 'data'])
            ->middleware('can:access.users.view')->name('users.data');
        Route::post('/pengguna', [UserController::class, 'store'])
            ->middleware('can:access.users.manage')->name('users.store');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])
            ->middleware('can:access.users.manage')->name('users.update');

        Route::get('/peran', [RoleController::class, 'index'])
            ->middleware('can:access.roles.view')->name('roles.index');
        Route::post('/peran', [RoleController::class, 'store'])
            ->middleware('can:access.roles.manage')->name('roles.store');
        Route::put('/peran/{role}', [RoleController::class, 'update'])
            ->middleware('can:access.roles.manage')->name('roles.update');
        Route::delete('/peran/{role}', [RoleController::class, 'destroy'])
            ->middleware('can:access.roles.manage')->name('roles.destroy');
    });
});
