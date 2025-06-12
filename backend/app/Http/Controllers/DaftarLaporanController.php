<?php

namespace App\Http\Controllers;

use App\Models\DaftarLaporan;
use App\Models\DaftarSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DaftarLaporanController extends Controller
{
     public function getDaftarSiswa()
    {
        try {
            $siswa = DaftarSiswa::select('daftar_siswa_id', 'nama', 'nama_kelas', 'nomor_absen')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data daftar siswa berhasil diambil!',
                'data' => $siswa,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching data siswa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data daftar siswa gagal diambil!'
            ], 500);
        }
    }
 public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'daftar_siswa_id' => 'required|exists:daftar_siswa,daftar_siswa_id',
            'jenis_perubahan' => 'required|in:Email,Password',
            'upload_bukti' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal!',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $laporan = DB::transaction(function () use ($request) {
                $path = $request->file('upload_bukti')->store('bukti-perubahan', 'public');

                return DaftarLaporan::create([
                    'daftar_siswa_id' => $request->daftar_siswa_id,
                    'jenis_perubahan' => $request->jenis_perubahan,
                    'upload_bukti' => $path,
                    'keterangan' => $request->keterangan,
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan perubahan akun berhasil dikirim!',
                'data' => $laporan,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error submitting laporan perubahan akun: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan perubahan akun gagal dikirim!'
            ], 500);
        }
    }

    public function index()
    {
        try {
            $laporan = DaftarLaporan::with('siswa:daftar_siswa_id,nama,kelas,no_absen')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan perubahan akun berhasil diambil!',
                'data' => $laporan,
            ], 200);
        } catch (\Exception $e) {
             \Log::error('Error fetching laporan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data laporan perubahan akun gagal diambil!'
            ], 500);
        }
    }

}