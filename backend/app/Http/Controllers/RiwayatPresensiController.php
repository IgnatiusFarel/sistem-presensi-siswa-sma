<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatPresensiController extends Controller
{
    public function index()
    {
        try {
            $rekap = Presensi::orderBy('created_at', 'desc')
                ->get()
                ->map(function ($presensi) {
                    return [
                        'presensi_id' => $presensi->presensi_id,
                        'tanggal' => $presensi->tanggal->format('Y-m-d'),
                        'jam_buka' => Carbon::parse($presensi->jam_buka)->format('H:i'),
                        'jam_tutup' => Carbon::parse($presensi->jam_tutup)->format('H:i'),
                        'hadir' => $presensi->presensiSiswa()->where('status', 'hadir')->count(),
                        'izin' => $presensi->presensiSiswa()->where('status', 'izin')->count(),
                        'sakit' => $presensi->presensiSiswa()->where('status', 'sakit')->count(),
                        'alpha' => $presensi->presensiSiswa()->where('status', 'alpha')->count(),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat presensi berhasil diambil!',
                'data' => $rekap,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching riwayat presensi: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Riwayat presensi gagal diambil!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $presensi = Presensi::with(['presensiSiswa.siswa.kelas'])
                ->find($id);

            if (!$presensi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data riwayat presensi tidak ditemukan!',
                ], 404);
            }

            $daftarSiswa = $presensi->presensiSiswa->map(fn($ps) => [
                'nama' => $ps->siswa->nama,
                'nomor_absen' => $ps->siswa->nomor_absen,
                'kelas' => $ps->siswa->kelas->nama_kelas,
                'jam_masuk' => $ps->waktu_presensi,
                'status' => $ps->status,
                'lokasi' => $ps->lokasi,
                'jenis_kegiatan' => $ps->jenis_kegiatan ?? null,
                'upload_bukti' => $ps->upload_bukti ?? null,
                'keterangan' => $ps->keterangan ?? null,
            ]);

            $data = [
                'presensi_id' => $presensi->presensi_id,
                'tanggal' => $presensi->tanggal->format('Y-m-d'),
                'jam_buka' => Carbon::parse($presensi->jam_buka)->format('H:i'),
                'jam_tutup' => Carbon::parse($presensi->jam_tutup)->format('H:i'),
                'daftar_siswa' => $daftarSiswa,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat presensi berhasil ditampilkan!',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching detail data riwayat presensi: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data riwayat presensi gagal ditampilkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $riwayatpresensi = Presensi::find($id);

        if (!$riwayatpresensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data riwayat presensi tidak ditemukan!'
            ], 404);
        }

        try {
            DB::transaction(function () use ($riwayatpresensi) {
                $riwayatpresensi->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat presensi berhasil dihapus!'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting data riwayat presensi: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data riwayat presensi gagal dihapus!',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID data riwayat presensi tidak valid!'
            ], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                Presensi::whereIn('presensi_id', $ids)->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Beberapa riwayat presensi berhasil dihapus!'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error deleting multiple data riwayat presensi: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Riwayat presensi gagal dihapus!',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}

