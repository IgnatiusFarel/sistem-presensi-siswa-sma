<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\DaftarSiswa;
use App\Models\PresensiSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresensiController extends Controller
{
    // Menampilkan daftar presensi (riwayat)
    public function index()
    {
        $presensi = Presensi::orderBy('tanggal', 'desc')->paginate(10);
        return response()->json(['data' => $presensi]);
    }

    // Membuat presensi baru (membuka presensi)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'jam_buka' => 'required',
            'jam_tutup' => 'required|after:jam_buka',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Buat record presensi baru
            $presensi = Presensi::create([
                'tanggal' => $request->tanggal,
                'jam_buka' => $request->jam_buka,
                'jam_tutup' => $request->jam_tutup,
                'dibuka_pada' => Carbon::now(),
                'status' => 'aktif',
                'dibuat_oleh' => auth()->id(),
                'keterangan' => $request->keterangan,
            ]);

            // Inisialisasi data presensi untuk semua siswa dengan status 'alpha'
            $siswaList = DaftarSiswa::all();
            foreach ($siswaList as $siswa) {
                PresensiSiswa::create([
                    'presensi_id' => $presensi->presensi_id,
                    'daftar_siswa_id' => $siswa->id,
                    'user_id' => $siswa->user_id,
                    'status' => 'alpha',
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Presensi berhasil dibuka',
                'data' => $presensi
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan', 'error' => $e->getMessage()], 500);
        }
    }

    // Menampilkan detail presensi dan daftar siswa
    public function show($id)
    {
        $presensi = Presensi::with(['presensiSiswa.siswa'])->find($id);
        if (!$presensi) {
            return response()->json(['message' => 'Data presensi tidak ditemukan'], 404);
        }
        return response()->json(['data' => $presensi]);
    }

    // Menutup presensi
    public function tutupPresensi($id)
    {
        $presensi = Presensi::find($id);
        if (!$presensi) {
            return response()->json(['message' => 'Data presensi tidak ditemukan'], 404);
        }

        if ($presensi->status === 'selesai') {
            return response()->json(['message' => 'Presensi sudah ditutup sebelumnya'], 400);
        }

        DB::beginTransaction();
        try {
            $presensi->update([
                'status' => 'selesai',
                'ditutup_pada' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Presensi berhasil ditutup',
                'data' => $presensi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan', 'error' => $e->getMessage()], 500);
        }
    }

    // Update status siswa (untuk superadmin)
    public function updateStatusSiswa(Request $request, $presensiId, $siswaId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $presensiSiswa = PresensiSiswa::where('presensi_id', $presensiId)
            ->where('daftar_siswa_id', $siswaId)
            ->first();

        if (!$presensiSiswa) {
            return response()->json(['message' => 'Data presensi siswa tidak ditemukan'], 404);
        }

        $presensiSiswa->update([
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'waktu_presensi' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Status presensi berhasil diperbarui',
            'data' => $presensiSiswa
        ]);
    }
}
