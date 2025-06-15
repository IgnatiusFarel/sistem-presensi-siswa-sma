<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\HasLocationResolver;
use App\Models\PresensiSiswa;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiSiswaController extends Controller
{
    use HasLocationResolver;
    public function index()
    {
        try {
            $userId = auth()->id();

            $riwayat = PresensiSiswa::with('presensi')
                ->where('user_id', $userId)
                ->orderBy('waktu_presensi', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->presensi_siswa_id,
                        'tanggal' => Carbon::parse($item->presensi->tanggal)->format('Y-m-d'),
                        'jam' => optional($item->waktu_presensi)->format('H:i'),
                        'status' => ucfirst($item->status),
                        'keterangan' => $item->keterangan,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat presensi berhasil diambil!',
                'data' => $riwayat,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching presensi data: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data riwayat presensi gagal diambil!',
            ], 500);
        }
    }

    public function getRekapPresensi()
    {
        try {
            $userId = auth()->id();

            // Total kegiatan presensi di sistem
            $totalKegiatan = Presensi::count();

            // Hitung per status untuk siswa ini
            $counts = PresensiSiswa::where('user_id', $userId)
                ->selectRaw("status, COUNT(*) as jumlah")
                ->groupBy('status')
                ->pluck('jumlah', 'status')
                ->toArray();

            // Pastikan semua status ada
            $data = [
                'hadir' => $counts['hadir'] ?? 0,
                'izin' => $counts['izin'] ?? 0,
                'sakit' => $counts['sakit'] ?? 0,
                'alpha' => $counts['alpha'] ?? 0,
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Rekap presensi anda berhasil diambil!',
                'total' => $totalKegiatan,
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|uuid|exists:presensi,presensi_id',
            'waktu_presensi' => 'required_without:jenis_kegiatan|date_format:H:i',
            'latitude' => 'required_without:jenis_kegiatan|numeric',
            'longitude' => 'required_without:jenis_kegiatan|numeric',
            'lokasi' => 'required_without:jenis_kegiatan|string|max:500',
            'jenis_kegiatan' => 'required_without:latitude|in:Izin,Sakit',
            'upload_bukti' => 'required_with:jenis_kegiatan|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'keterangan' => 'required_with:jenis_kegiatan|string|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Presensi Anda gagal di validasi!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $siswa = $user->siswa;
            if (!$siswa) {
                throw new \Exception("User tidak memiliki data siswa");
            }

            if ($request->filled('jenis_kegiatan')) {
                $status = strtolower($request->jenis_kegiatan); // "izin" atau "sakit"
            } else {
                $status = PresensiSiswa::STATUS_HADIR;
            }

            $data = [
                'presensi_id' => $request->presensi_id,
                'user_id' => $user->user_id,
                'daftar_siswa_id' => $siswa->daftar_siswa_id,
                'status' => $status,
                'waktu_presensi' => $request->waktu_presensi,
                'latitude' => $status === PresensiSiswa::STATUS_HADIR
                    ? $request->latitude
                    : null,
                'longitude' => $status === PresensiSiswa::STATUS_HADIR
                    ? $request->longitude
                    : null,
                'lokasi' => $status === PresensiSiswa::STATUS_HADIR
                    ? $request->lokasi
                    : null,
                'jenis_kegiatan' => $status !== PresensiSiswa::STATUS_HADIR
                    ? strtolower($request->jenis_kegiatan)
                    : null,
                'keterangan' => $status !== PresensiSiswa::STATUS_HADIR
                    ? $request->keterangan
                    : null,
            ];

            if ($request->hasFile('upload_bukti') && $status !== PresensiSiswa::STATUS_HADIR) {
                $file = $request->file('upload_bukti');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('bukti_izin_sakit', $filename, 'public');
                $data['upload_bukti'] = $path;
            }

            $ps = PresensiSiswa::create($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Presensi berhasil disimpan!',
                'data' => [
                    'id' => $ps->presensi_siswa_id,
                    'presensi_id' => $ps->presensi_id,
                    'status' => $ps->status,
                    'waktu_presensi' => $ps->waktu_presensi,
                    'latitude' => $ps->latitude,
                    'longitude' => $ps->longitude,
                    'lokasi' => $ps->lokasi,
                    'jenis_kegiatan' => $ps->jenis_kegiatan,
                    'upload_bukti' => $ps->upload_bukti,
                    'keterangan' => $ps->keterangan,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error presensi siswa: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Presensi gagal disimpan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}

