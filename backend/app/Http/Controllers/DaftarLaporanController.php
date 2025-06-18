<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DaftarLaporan;
use App\Models\DaftarSiswa;
use App\Models\User;

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
            $laporan = DaftarLaporan::with('siswa:daftar_siswa_id,nama,nama_kelas,nomor_absen')
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

    public function delete($id)
    {
        $laporan = DaftarLaporan::find($id);

        if (!$laporan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data laporan tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            if ($laporan->user_id && User::find($laporan->user_id)) {
                User::destroy($laporan->user_id);
            }

            $laporan->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data laporan gagal dihapus!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data ID laporan tidak ditemukan!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $laporanList = DaftarLaporan::whereIn('daftar_laporan_id', $ids)->get();

            foreach ($laporanList as $laporan) {
                if ($laporan->user_id && User::find($laporan->user_id)) {
                    User::destroy($laporan->user_id);
                }

                $laporan->delete();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Semua data laporan berhasil dihapus!'
            ],200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Penghapusan data laporan gagal!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}