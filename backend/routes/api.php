<?php

use App\Http\Controllers\DaftarKelasController;
use App\Http\Controllers\DaftarPengurusController;
use App\Http\Controllers\DaftarSiswaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiSiswaController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route publik untuk login
Route::post('/masuk', [AuthController::class, 'masuk']);

// Routes yang membutuhkan autentikasi
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/keluar', [AuthController::class, 'logout']);
    
    // Routes khusus untuk superadmin
    Route::middleware('role:superadmin')->group(function () {
        // Manajemen Siswa - hanya superadmin yang dapat CRUD
        
        
        // Manajemen Pengurus - hanya superadmin yang dapat CRUD
        Route::apiResource('/daftar-siswa', DaftarSiswaController::class, [
            'parameters' => ['daftar-siswa' => 'id']
        ]);
         Route::delete('/daftar-siswa', [DaftarSiswaController::class, 'destroyMultiple']);
        Route::apiResource('/daftar-pengurus', DaftarPengurusController::class, [
            'parameters' => ['daftar-pengurus' => 'id']
        ]);
        Route::delete('/daftar-pengurus', [DaftarPengurusController::class, 'destroyMultiple']);
        Route::apiResource('/daftar-kelas', DaftarKelasController::class, [
            'parameters' => ['daftar-kelas' => 'id']
        ]);
        Route::delete('/daftar-kelas', [DaftarKelasController::class, 'destroyMultiple']);
        
        // Manajemen Presensi - untuk superadmin
        Route::get('/presensi', [PresensiController::class, 'index']);
        Route::post('/presensi', [PresensiController::class, 'store']);
        Route::get('/presensi/{id}', [PresensiController::class, 'show']);
        Route::post('/presensi/{id}/tutup', [PresensiController::class, 'tutupPresensi']);
        Route::put('/presensi/{presensiId}/siswa/{siswaId}', [PresensiController::class, 'updateStatusSiswa']);
    });
    
    // Routes khusus untuk siswa
    Route::middleware('role:siswa')->group(function () {
        // Profil siswa - siswa hanya bisa melihat profil mereka sendiri
        Route::get('/profile', function () {
            $siswa = auth()->user()->siswa;
            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ]);
        });
        
        // Presensi - untuk siswa
        Route::get('/presensi-siswa/aktif', [PresensiSiswaController::class, 'getPresensiAktif']);
        Route::post('/presensi-siswa/hadir', [PresensiSiswaController::class, 'submitHadir']);
        Route::post('/presensi-siswa/izin', [PresensiSiswaController::class, 'submitIzin']);
        Route::get('/presensi-siswa/riwayat', [PresensiSiswaController::class, 'riwayatPresensi']);
    });
});
