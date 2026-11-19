<?php

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Master\TypeExaminationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/daftar-online', [RegistrationController::class, 'online'])->name('registrations.online');
Route::post('/daftar-online', [RegistrationController::class, 'storeOnline'])
    ->middleware('throttle:6,1')->name('registrations.online.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => view('dashboard.index'))
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::get('/pendaftaran', [RegistrationController::class, 'index'])
        ->middleware('can:registrations.view')->name('registrations.index');
    Route::get('/pendaftaran/baru', [RegistrationController::class, 'create'])
        ->middleware('can:registrations.manage')->name('registrations.create');
    Route::get('/pendaftaran/cari-pasien', [RegistrationController::class, 'lookupPatient'])
        ->middleware('can:registrations.manage')->name('registrations.lookup');
    Route::post('/pendaftaran', [RegistrationController::class, 'store'])
        ->middleware('can:registrations.manage')->name('registrations.store');

    Route::prefix('master')->name('master.')->group(function (): void {
        Route::get('/jenis-pemeriksaan', [TypeExaminationController::class, 'index'])
            ->middleware('can:master.type_examinations.view')->name('type_examinations.index');
        Route::get('/jenis-pemeriksaan/data', [TypeExaminationController::class, 'data'])
            ->middleware('can:master.type_examinations.view')->name('type_examinations.data');
        Route::post('/jenis-pemeriksaan', [TypeExaminationController::class, 'store'])
            ->middleware('can:master.type_examinations.manage')->name('type_examinations.store');
        Route::put('/jenis-pemeriksaan/{type_examination}', [TypeExaminationController::class, 'update'])
            ->middleware('can:master.type_examinations.manage')->name('type_examinations.update');
        Route::delete('/jenis-pemeriksaan/{type_examination}', [TypeExaminationController::class, 'destroy'])
            ->middleware('can:master.type_examinations.manage')->name('type_examinations.destroy');


        Route::get('/pengguna', [UserController::class, 'index'])
            ->middleware('can:master.users.view')->name('users.index');
        Route::get('/pengguna/data', [UserController::class, 'data'])
            ->middleware('can:master.users.view')->name('users.data');
        Route::post('/pengguna', [UserController::class, 'store'])
            ->middleware('can:master.users.manage')->name('users.store');
        Route::put('/pengguna/{user}', [UserController::class, 'update'])
            ->middleware('can:master.users.manage')->name('users.update');
    });

    Route::prefix('akses')->name('access.')->group(function (): void {


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
