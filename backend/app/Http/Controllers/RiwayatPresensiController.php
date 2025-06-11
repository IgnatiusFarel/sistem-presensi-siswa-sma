<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
                        'tanggal'     => $presensi->tanggal->format('Y-m-d'),
                        'jam_buka'    => $presensi->jam_buka,
                        'jam_tutup'   => $presensi->jam_tutup,
                        'hadir'       => $presensi->presensiSiswa()->where('status', 'hadir')->count(),
                        'izin'        => $presensi->presensiSiswa()->where('status', 'izin')->count(),
                        'sakit'       => $presensi->presensiSiswa()->where('status', 'sakit')->count(),
                        'alpha'       => $presensi->presensiSiswa()->where('status', 'alpha')->count(),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat presensi berhasil diambil!',
                'data' => $rekap,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching riwayat presensi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Riwayat presensi gagal diambil!'
            ], 500);
        }
    }

    public function show($id)
    {
        $presensi = Presensi::with(['presensiSiswa.siswa.kelas'])->find($id);

        if (! $presensi) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Riwayat presensi tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $presensi,
        ], 200);
    }


    public function destroy($id)
{
    $riwayatpresensi = Presensi::find($id);

    if (!$riwayatpresensi) {
        return response()->json([
            'status' => 'error',
            'message' => 'Riwayat presensi tidak ditemukan!'
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

    } catch (\Exception $e) {
        \Log::error('Error deleting riwayat presensi: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Riwayat presensi gagal dihapus!',
            'error' => $e->getMessage(),
        ], 500);
    }
}

   public function destroyMultiple(Request $request)
{
    $ids = $request->input('ids');

    if (!is_array($ids) || empty($ids)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Riwayat presensi tidak valid!'
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

    } catch (\Exception $e) {
        \Log::error('Error deleting multiple riwayat presensi: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Riwayat presensi gagal dihapus!',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}

