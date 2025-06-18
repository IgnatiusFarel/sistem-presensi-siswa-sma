<?php

use App\Http\Controllers\DaftarKelasController;
use App\Http\Controllers\DaftarLaporanController;
use App\Http\Controllers\DaftarPengurusController;
use App\Http\Controllers\DaftarSiswaController;
use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiSiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RiwayatPresensiController;
use Illuminate\Support\Facades\Route;

Route::post('/masuk', [AuthController::class, 'masuk']);
Route::get('/daftar-siswa-aktif', [DaftarLaporanController::class, 'getDaftarSiswa']);
Route::post('/daftar-laporan', [DaftarLaporanController::class, 'store']);
Route::get('/reverse-geocode', [GeolocationController::class, 'reverseGeocode']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout']);

    Route::middleware('role:superadmin')->group(function () {

        // 📁 Daftar Laporan
        Route::get('/daftar-laporan', [DaftarLaporanController::class, 'index']);

        // 📁 Daftar Siswa
        Route::prefix('daftar-siswa')->controller(DaftarSiswaController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::post('/', 'store');
            Route::post('/import','import');
            Route::patch('{id}', 'update');
            Route::delete('{id}', 'destroy');
            Route::delete('/', 'destroyMultiple');            
        });

        // 📁 Daftar Pengurus
        Route::prefix('daftar-pengurus')->controller(DaftarPengurusController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::post('/', 'store');
            Route::post('/import','import');
            Route::patch('{id}', 'update');
            Route::delete('{id}', 'destroy');
            Route::delete('/', 'destroyMultiple');
        });

        // 📁 Daftar Kelas
        Route::prefix('daftar-kelas')->controller(DaftarKelasController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::post('/', 'store');
            Route::post('/import','import');
            Route::patch('{id}', 'update');
            Route::delete('{id}', 'destroy');
            Route::delete('/', 'destroyMultiple');
        });

        // 📁 Presensi
        Route::prefix('presensi')->controller(PresensiController::class)->group(function () {
            Route::get('/aktif', 'getPresensiAktif');
            Route::get('/rekap', 'getRekapPresensi');
            Route::get('/', 'index');
            Route::post('/', 'store');
        });

        // 📁 Riwayat Presensi
        Route::prefix('riwayat-presensi')->controller(RiwayatPresensiController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('{id}', 'show');
            Route::delete('{id}', 'destroy');
            Route::delete('/', 'destroyMultiple');
        });
    });

    Route::middleware('role:siswa')->group(function () {
        Route::get('/profile', function () {
            $siswa = auth()->user()->siswa;
            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ]);
        });

        // 📁 Presensi Siswa
        Route::prefix('presensi-siswa')->controller(PresensiSiswaController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/rekap', 'getRekapPresensi');
            Route::post('/', 'store');            
            Route::get('/hari-ini', [PresensiController::class, 'getPresensiAktif']);
        });

    });
    
});
