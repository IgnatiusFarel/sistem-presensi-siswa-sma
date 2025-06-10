<?php

namespace App\Http\Controllers;

use App\Models\DaftarSiswa;
use App\Models\Presensi;
use App\Models\PresensiSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PresensiSiswaController extends Controller
{
    // Mendapatkan presensi aktif untuk siswa
    public function getPresensiAktif()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        
        // Cari presensi hari ini yang masih aktif
        $presensiAktif = Presensi::where('tanggal', $today)
            ->where('status', 'aktif')
            ->whereRaw("TIME(?) BETWEEN jam_buka AND jam_tutup", [$now->format('H:i:s')])
            ->first();
        
        if (!$presensiAktif) {
            return response()->json(['message' => 'Tidak ada presensi aktif saat ini'], 404);
        }
        
        // Ambil data siswa yang login
        $siswa = DaftarSiswa::where('user_id', auth()->id())->first();
        if (!$siswa) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }
        
        // Ambil data presensi siswa
        $presensiSiswa = PresensiSiswa::where('presensi_id', $presensiAktif->presensi_id)
            ->where('daftar_siswa_id', $siswa->id)
            ->first();
            
        return response()->json([
            'data' => [
                'presensi' => $presensiAktif,
                'status_presensi' => $presensiSiswa ? $presensiSiswa->status : 'alpha'
            ]
        ]);
    }
    
    // Form hadir - untuk siswa melakukan presensi hadir
    public function submitHadir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|exists:presensi,presensi_id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifikasi presensi masih aktif
        $presensi = Presensi::find($request->presensi_id);
        if (!$presensi || $presensi->status !== 'aktif') {
            return response()->json(['message' => 'Presensi tidak aktif atau tidak ditemukan'], 400);
        }
        
        // Verifikasi waktu presensi valid
        $now = Carbon::now();
        $jamBuka = Carbon::parse($presensi->jam_buka);
        $jamTutup = Carbon::parse($presensi->jam_tutup);
        
        if ($now->lt($jamBuka) || $now->gt($jamTutup)) {
            return response()->json(['message' => 'Di luar waktu presensi'], 400);
        }

        // Cari data siswa
        $siswa = DaftarSiswa::where('user_id', auth()->id())->first();
        if (!$siswa) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }

        // Cari presensi siswa
        $presensiSiswa = PresensiSiswa::where('presensi_id', $request->presensi_id)
            ->where('daftar_siswa_id', $siswa->id)
            ->first();

        if (!$presensiSiswa) {
            // Buat data presensi jika belum ada
            $presensiSiswa = new PresensiSiswa([
                'presensi_id' => $request->presensi_id,
                'daftar_siswa_id' => $siswa->id,
                'user_id' => auth()->id(),
            ]);
        } elseif ($presensiSiswa->status !== 'alpha') {
            return response()->json(['message' => 'Anda sudah melakukan presensi sebelumnya'], 400);
        }

        // Tentukan status (hadir atau terlambat)
        $batasTerlambat = Carbon::parse($presensi->jam_buka)->addMinutes(15); // Misalnya batas terlambat 15 menit setelah buka
        $status = $now->lte($batasTerlambat) ? 'hadir' : 'terlambat';

        // Update data presensi
        $presensiSiswa->status = $status;
        $presensiSiswa->waktu_presensi = $now;
        $presensiSiswa->latitude = $request->latitude;
        $presensiSiswa->longitude = $request->longitude;
        $presensiSiswa->save();

        return response()->json([
            'message' => 'Presensi berhasil, status: ' . $status,
            'data' => $presensiSiswa
        ]);
    }
    
    // Form izin/sakit - untuk siswa melakukan presensi izin/sakit
    public function submitIzin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|exists:presensi,presensi_id',
            'jenis_izin' => 'required|in:izin,sakit',
            'bukti_surat' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verifikasi presensi masih aktif
        $presensi = Presensi::find($request->presensi_id);
        if (!$presensi || $presensi->status !== 'aktif') {
            return response()->json(['message' => 'Presensi tidak aktif atau tidak ditemukan'], 400);
        }

        // Cari data siswa
        $siswa = DaftarSiswa::where('user_id', auth()->id())->first();
        if (!$siswa) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }

        // Cari presensi siswa
        $presensiSiswa = PresensiSiswa::where('presensi_id', $request->presensi_id)
            ->where('daftar_siswa_id', $siswa->id)
            ->first();

        if (!$presensiSiswa) {
            // Buat data presensi jika belum ada
            $presensiSiswa = new PresensiSiswa([
                'presensi_id' => $request->presensi_id,
                'daftar_siswa_id' => $siswa->id,
                'user_id' => auth()->id(),
            ]);
        } elseif ($presensiSiswa->status !== 'alpha') {
            return response()->json(['message' => 'Anda sudah melakukan presensi sebelumnya'], 400);
        }

        // Upload bukti surat
        if ($request->hasFile('bukti_surat')) {
            $file = $request->file('bukti_surat');
            $fileName = time() . '_' . $siswa->id . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('bukti_surat', $fileName, 'public');
            
            // Update data presensi
            $presensiSiswa->status = $request->jenis_izin;
            $presensiSiswa->jenis_izin = $request->jenis_izin;
            $presensiSiswa->waktu_presensi = Carbon::now();
            $presensiSiswa->bukti_surat = $filePath;
            $presensiSiswa->keterangan = $request->keterangan;
            $presensiSiswa->save();

            return response()->json([
                'message' => 'Pengajuan ' . $request->jenis_izin . ' berhasil',
                'data' => $presensiSiswa
            ]);
        }
        
        return response()->json(['message' => 'Gagal upload bukti surat'], 400);
    }

    // Riwayat presensi siswa
    public function riwayatPresensi()
    {
        $siswa = DaftarSiswa::where('user_id', auth()->id())->first();
        if (!$siswa) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }
        
        $riwayat = PresensiSiswa::with('presensi')
            ->where('daftar_siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json(['data' => $riwayat]);
    }
}
