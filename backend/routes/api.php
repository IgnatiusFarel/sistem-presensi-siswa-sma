<?php

use App\Http\Controllers\DaftarKelasController;
use App\Http\Controllers\DaftarPengurusController;
use App\Http\Controllers\DaftarSiswaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiSiswaController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/masuk', [AuthController::class, 'masuk']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout']);
        
    Route::middleware('role:superadmin')->group(function () {

        Route::apiResource('/daftar-siswa', DaftarSiswaController::class, [
            'parameters' => ['daftar-siswa' => 'id'] ]);
        Route::delete('/daftar-siswa', [DaftarSiswaController::class, 'destroyMultiple']);

        Route::apiResource('/daftar-pengurus', DaftarPengurusController::class, [
            'parameters' => ['daftar-pengurus' => 'id'] ]);
        Route::delete('/daftar-pengurus', [DaftarPengurusController::class, 'destroyMultiple']);

        Route::apiResource('/daftar-kelas', DaftarKelasController::class, [
            'parameters' => ['daftar-kelas' => 'id'] ]);
        Route::delete('/daftar-kelas', [DaftarKelasController::class, 'destroyMultiple']);

        Route::get('/presensi/statistik', [PresensiController::class, 'getStatistikPresensiHariIni']);
        Route::get('/presensi/rekap', [PresensiController::class, 'getRekapPresensi']);        
        Route::post('/presensi', [PresensiController::class, 'store']);
        Route::delete('/riwayat-presensi', [DaftarKelasController::class, 'destroyMultiple']);
        Route::get('/presensi/{id}', [PresensiController::class, 'show']);      
    });
    
    
    Route::middleware('role:siswa')->group(function () {
        Route::get('/profile', function () {
            $siswa = auth()->user()->siswa;
            return response()->json([
                'status' => 'success',
                'data' => $siswa
            ]);
        });
                
        Route::get('/presensi-siswa/aktif', [PresensiSiswaController::class, 'getPresensiAktif']);
        Route::post('/presensi-siswa/hadir', [PresensiSiswaController::class, 'submitHadir']);
        Route::post('/presensi-siswa/izin', [PresensiSiswaController::class, 'submitIzin']);
        Route::get('/presensi-siswa/riwayat', [PresensiSiswaController::class, 'riwayatPresensi']);
    });
});
