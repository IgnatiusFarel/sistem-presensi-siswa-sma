<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\DaftarSiswa;
use App\Models\PresensiSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function getStatistikPresensiHariIni()
    {
        try {
            $tanggalHariIni = Carbon::today()->toDateString();
            
            $presensi = Presensi::where('tanggal', $tanggalHariIni)
                ->with('presensiSiswa')
                ->first();

            if (!$presensi) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Belum ada presensi untuk hari ini.',
                    'data' => [],
                    'total' => DaftarSiswa::count(),
                ]);
            }

            $data = [
                'Hadir' => $presensi->presensiSiswa->where('status_kehadiran', 'hadir')->count(),
                'Terlambat' => $presensi->presensiSiswa->where('status_kehadiran', 'terlambat')->count(),
                'Sakit' => $presensi->presensiSiswa->where('status_kehadiran', 'sakit')->count(),
                'Izin' => $presensi->presensiSiswa->where('status_kehadiran', 'izin')->count(),
                'Alpha' => $presensi->presensiSiswa->where('status_kehadiran', 'alpha')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik presensi hari ini berhasil diambil.',
                'total' => DaftarSiswa::count(),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting statistik presensi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil statistik presensi.',
            ], 500);
        }
    }
    public function getRekapPresensi()
    {
        try {
            $presensi = Presensi::with(['presensiSiswa.siswa.kelas'])->orderBy('created_at', 'desc')->get();

            $data = $presensi->map(function ($item) {
                $total_siswa = DaftarSiswa::count();

                $jumlah_hadir = $item->presensiSiswa->where('status_kehadiran', 'hadir')->count();
                $jumlah_izin = $item->presensiSiswa->where('status_kehadiran', 'izin')->count();
                $jumlah_sakit = $item->presensiSiswa->where('status_kehadiran', 'sakit')->count();
                $jumlah_alpha = $item->presensiSiswa->where('status_kehadiran', 'alpha')->count();

                $siswa_absen = $item->presensiSiswa->map(function ($ps) {
                    return [
                        'nama' => $ps->siswa->nama,
                        'nomor_absen' => $ps->siswa->nomor_absen,
                        'kelas' => $ps->siswa->kelas->nama_kelas ?? null,
                        'status' => $ps->status_kehadiran,
                        'surat_izin' => $ps->surat_izin ?? null,
                    ];
                });

                return [
                    'presensi_id' => $item->id,
                    'tanggal' => $item->tanggal,
                    'jam_buka' => $item->jam_buka,
                    'jam_tutup' => $item->jam_tutup,
                    'status' => $item->status,
                    'total_siswa' => $total_siswa,
                    'hadir' => $jumlah_hadir,
                    'izin' => $jumlah_izin,
                    'sakit' => $jumlah_sakit,
                    'alpha' => $jumlah_alpha,
                    'daftar_siswa' => $siswa_absen,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Rekap presensi berhasil diambil.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching presensi data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil rekap presensi.',
            ], 500);
        }
    }

   public function store(Request $request)
{
    $request->validate([
        'tanggal' => 'required|date|unique:presensi,tanggal',
        'jam_buka' => 'required|date_format:H:i',
        'jam_tutup' => 'required|date_format:H:i|after:jam_buka',
    ]);

    DB::beginTransaction();

    try {
        $presensi = Presensi::create([
            'tanggal' => $request->tanggal,
            'jam_buka' => $request->jam_buka,
            'jam_tutup' => $request->jam_tutup,
            'status' => 'aktif',
            'dibuat_oleh' => Auth::user()->user_id, // ✅ tambahkan ini
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil dibuat!',
            'data' => $presensi,
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error creating presensi: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Presensi gagal dibuat!',
            'error' => $e->getMessage(), // untuk debugging
        ], 500);
    }
}
}
