<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\PresensiSiswa;
use App\Traits\HasLocationResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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
                        'id'         => $item->presensi_siswa_id,
                        'tanggal'    => Carbon::parse($item->presensi->tanggal)->format('Y-m-d'),
                        'jam'        => optional($item->waktu_presensi)->format('H:i'),
                        'status'     => ucfirst($item->status),
                        'keterangan' => $item->keterangan,
                    ];
                });

        return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat presensi berhasil diambil!',
                'data'    => $riwayat,
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
                'hadir' => $counts['hadir']  ?? 0,
                'izin'  => $counts['izin']   ?? 0,
                'sakit' => $counts['sakit']  ?? 0,
                'alpha' => $counts['alpha']  ?? 0,
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Rekap presensi anda berhasil diambil!',
               'total'        => $totalKegiatan,
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
        'latitude'    => 'required|numeric',
        'longitude'   => 'required|numeric',
        'jenis_kegiatan' => 'nullable|in:Izin,Sakit',
        'keterangan'     => 'nullable|string|max:300',
        'upload_bukti'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // max 5MB
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Validasi gagal',
            'errors'  => $validator->errors(),
        ], 422);
    }

    DB::beginTransaction();
    try {
        $userId = auth()->user()->user_id;
        $lat = $request->latitude;
        $lng = $request->longitude;
        $lokasi = $this->resolveLocation($lat, $lng);

        $siswa = auth()->user()->siswa;
if (!$siswa) {
    throw new \Exception("User tidak memiliki data siswa");
}

        $status = $request->jenis_kegiatan 
            ? strtolower($request->jenis_kegiatan) // "izin" atau "sakit"
            : PresensiSiswa::STATUS_HADIR;

        $data = [            
            'presensi_id'       => $request->presensi_id,
            'user_id'           => $userId,
             'daftar_siswa_id'   => $siswa->daftar_siswa_id,
            'status'            => $status,
            'waktu_presensi'    => now(),
            'latitude'          => $lat,
            'longitude'         => $lng,
            'lokasi'            => $lokasi,
            'keterangan'        => $request->keterangan,
        ];

        if ($request->hasFile('upload_bukti')) {
            $file = $request->file('upload_bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_izin_sakit', $filename, 'public');
            $data['upload_bukti'] = $path;
        }

        PresensiSiswa::create($data);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil disimpan!',
            'data' => [
                'lokasi' => $lokasi,
            ],
        ]);
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

