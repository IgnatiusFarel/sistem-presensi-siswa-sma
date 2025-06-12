<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\DaftarSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{

    public function getPresensiAktif()
    {
        $today = now()->toDateString();
        $now = now('Asia/Jakarta')->format('H:i:s');

        $presensi = Presensi::where('tanggal', $today)
            ->where('jam_buka', '<=', $now)
            ->where('jam_tutup', '>=', $now)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $presensi ? array_merge(
                $presensi->toArray(),
                ['status_dinamis' => $this->hitungStatusPresensi($presensi)]
            ) : null,
        ], 200);
    }
    public function getRekapPresensi()
    {
        try {
            $today = Carbon::today()->toDateString();
            $presensi = Presensi::where('tanggal', $today)
                ->with('presensiSiswa')
                ->first();

            $totalSiswa = DaftarSiswa::count();

            if (!$presensi) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Belum ada kegiatan presensi untuk hari ini!',
                    'total' => $totalSiswa,
                    'data' => [],
                ], 200);
            }

            $data = [
                'Hadir' => $presensi->presensiSiswa->where('status_kehadiran', 'hadir')->count(),
                'Sakit' => $presensi->presensiSiswa->where('status_kehadiran', 'sakit')->count(),
                'Izin' => $presensi->presensiSiswa->where('status_kehadiran', 'izin')->count(),
                'Alpha' => $presensi->presensiSiswa->where('status_kehadiran', 'alpha')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Rekap presensi harian berhasil diambil!',
                'total' => $totalSiswa,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting rekap presensi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Rekap presensi harian gagal diambil!',
            ], 500);
        }
    }
    public function index()
    {
        try {
            $list = Presensi::with(['presensiSiswa.siswa.kelas'])
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $list->map(function ($p) {
                $totalSiswa = DaftarSiswa::count();
                $hadir = $p->presensiSiswa->where('status_kehadiran', 'hadir')->count();
                $izin = $p->presensiSiswa->where('status_kehadiran', 'izin')->count();
                $sakit = $p->presensiSiswa->where('status_kehadiran', 'sakit')->count();
                $alpha = $p->presensiSiswa->where('status_kehadiran', 'alpha')->count();

                $siswaList = $p->presensiSiswa->map(fn($ps) => [
                    'nama' => $ps->siswa->nama,
                    'nomor_absen' => $ps->siswa->nomor_absen,
                    'kelas' => $ps->siswa->kelas->nama_kelas ?? null,
                    'status' => $ps->status_kehadiran,
                    'surat_izin' => $ps->surat_izin ?? null,
                ]);

                return [
                    'presensi_id' => $p->presensi_id,
                    'tanggal' => $p->tanggal,
                    'jam_buka' => $p->jam_buka,
                    'jam_tutup' => $p->jam_tutup,
                    'status' => $p->status,
                    'total_siswa' => $totalSiswa,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                    'daftar_siswa' => $siswaList,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data presensi harian berhasil diambil!',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching presensi data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data presensi harian gagal diambil!',
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
                 'dibuat_oleh' => auth()->user()->user_id,
    'user_id' => auth()->user()->user_id
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
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function hitungStatusPresensi($presensi)
    {
        $now = Carbon::now('Asia/Jakarta')->format('H:i:s');

        if ($now < $presensi->jam_buka) {
            return 'belum dimulai';
        }

        if ($now >= $presensi->jam_buka && $now <= $presensi->jam_tutup) {
            return 'aktif';
        }

        return 'selesai';
    }
}
