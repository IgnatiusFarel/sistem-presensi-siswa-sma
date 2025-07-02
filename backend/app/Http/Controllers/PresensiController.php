<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DaftarSiswa;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function getPresensiAktif()
    {
        try {
            $today = now()->toDateString();
            $now = now('Asia/Jakarta')->format('H:i');

            $presensi = Presensi::where('tanggal', $today)
                ->where('jam_buka', '<=', $now)
                ->where('jam_tutup', '>=', $now)
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => $presensi ? array_merge(
                    [
                        'presensi_id' => $presensi->presensi_id,
                        'tanggal' => $presensi->tanggal,
                        'jam_buka' => Carbon::parse($presensi->jam_buka)->format('H:i'),
                        'jam_tutup' => Carbon::parse($presensi->jam_tutup)->format('H:i'),
                    ],
                    ['status_dinamis' => $this->hitungStatusPresensi($presensi)]
                ) : null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting presensi aktif: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Data presensi aktif gagal diambil!'
            ], 500);
        }
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
                    'data' => [
                        'total' => $totalSiswa,
                        'rekap' => [
                            'Hadir' => 0,
                            'Sakit' => 0,
                            'Izin' => 0,
                            'Alpha' => 0,
                        ]
                    ],
                ], 200);
            }

            $rekap =
                [
                    'Hadir' => $presensi->presensiSiswa->where('status', 'hadir')->count(),
                    'Sakit' => $presensi->presensiSiswa->where('status', 'sakit')->count(),
                    'Izin' => $presensi->presensiSiswa->where('status', 'izin')->count(),
                    'Alpha' => $presensi->presensiSiswa->where('status', 'alpha')->count(),
                ];

            return response()->json([
                'status' => 'success',
                'message' => 'Rekap presensi hari ini  berhasil diambil!',
                'data' => [
                    'total' => $totalSiswa,
                    'rekap' => $rekap
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error getting rekap presensi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Rekap presensi hari ini gagal diambil!',
            ], 500);
        }
    }
    public function index()
    {
        try {
            $today = Carbon::today()->toDateString();

            $p = Presensi::with(['presensiSiswa.siswa.kelas'])
                ->whereDate('tanggal', $today)
                ->first();

            if (!$p) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada presensi untuk hari ini',
                    'data' => [],
                ], 200);
            }

            $daftarSiswa = $p->presensiSiswa->map(fn($ps) => [
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
                'presensi_id' => $p->presensi_id,
                'tanggal' => $p->tanggal,
                'jam_buka' => Carbon::parse($p->jam_buka)->format('H:i'),
                'jam_tutup' => Carbon::parse($p->jam_tutup)->format('H:i'),
                'daftar_siswa' => $daftarSiswa,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Data presensi hari ini berhasil diambil!',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching presensi data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data presensi hari ini gagal diambil!',
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
                'dibuat_oleh' => auth()->user()->name,
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
        $now = Carbon::now('Asia/Jakarta')->format('H:i');

        if ($now < $presensi->jam_buka) {
            return 'belum dimulai';
        }

        if ($now >= $presensi->jam_buka && $now <= $presensi->jam_tutup) {
            return 'aktif';
        }

        return 'selesai';
    }
}
