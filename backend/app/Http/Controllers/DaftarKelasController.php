<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DaftarKelasImport;
use App\Models\DaftarPengurus;
use App\Models\DaftarKelas;

class DaftarKelasController extends Controller
{
    public function index()
    {
        try {
            $kelas = DaftarKelas::with('waliKelas')
                ->withCount(['siswa as jumlah_siswa'])
                ->with(['siswa:nama,daftar_kelas_id'])
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil diambil!',
                'data' => $kelas,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching data kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas gagal diambil!'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_kelas' => 'required|string|unique:daftar_kelas,kode_kelas',
            'nama_kelas' => 'required|string',
            'jurusan' => [
                'required',
                Rule::in([
                    DaftarKelas::JURUSAN_IPA,
                    DaftarKelas::JURUSAN_IPS,
                    DaftarKelas::JURUSAN_BAHASA,
                ]),
            ],
            'tingkat' => [
                'required',
                Rule::in([
                    DaftarKelas::TINGKAT_X,
                    DaftarKelas::TINGKAT_XI,
                    DaftarKelas::TINGKAT_XII,
                ]),
            ],
            'daftar_pengurus_id' => 'required|uuid|exists:daftar_pengurus,daftar_pengurus_id',
            'tahun_ajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pengurus = DaftarPengurus::findOrFail($request->daftar_pengurus_id);
            $kelas = DaftarKelas::create([
                'kode_kelas' => $request->kode_kelas,
                'nama_kelas' => $request->nama_kelas,
                'jurusan' => $request->jurusan,
                'tingkat' => $request->tingkat,
                'daftar_pengurus_id' => $request->daftar_pengurus_id,
                'wali_kelas' => $pengurus->nama,
                'tahun_ajaran' => $request->tahun_ajaran,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil ditambahkan!',
                'data' => $kelas->load('waliKelas'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas gagal ditambahkan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $kelas = DaftarKelas::with('waliKelas')->find($id);

        if (!$kelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $kelas
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $kelas = DaftarKelas::find($id);
        if (!$kelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_kelas' => [
                'required',
                'string',
                Rule::unique('daftar_kelas', 'kode_kelas')
                    ->ignore($id, 'daftar_kelas_id'),
            ],
            'nama_kelas' => 'required|string',
            'jurusan' => [
                'required',
                Rule::in([
                    DaftarKelas::JURUSAN_IPA,
                    DaftarKelas::JURUSAN_IPS,
                    DaftarKelas::JURUSAN_BAHASA,
                ]),
            ],
            'tingkat' => [
                'required',
                Rule::in([
                    DaftarKelas::TINGKAT_X,
                    DaftarKelas::TINGKAT_XI,
                    DaftarKelas::TINGKAT_XII,
                ]),
            ],
            'daftar_pengurus_id' => 'required|uuid|exists:daftar_pengurus,daftar_pengurus_id',
            'tahun_ajaran' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas tidak valid!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pengurus = DaftarPengurus::findOrFail($request->daftar_pengurus_id);
            $kelas->update(array_merge(
                $validator->validated(),
                ['wali_kelas' => $pengurus->nama]
            ));
            $kelas->update($validator->validated());

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil diperbarui!',
                'data' => $kelas->fresh()->load('waliKelas'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas gagal diperbarui!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $kelas = DaftarKelas::find($id);

        if (!$kelas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $kelas->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil dihapus!'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas gagal dihapus!',
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
                'message' => 'Data kelas tidak ada!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $daftarkelas = DaftarKelas::whereIn('daftar_kelas_id', $ids)->get();
            foreach ($daftarkelas as $kelas) {
                $kelas->delete();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data kelas gagal dihapus!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

     public function import(Request $request)
     {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            \Log::info('Import dimulai dari file: ' . $path);

            $import = new DaftarKelasImport;
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            if ($errorCount > 0) {
                \Log::warning("Import selesai dengan error. Berhasil: {$successCount}, Gagal: {$errorCount}");

                return response()->json([
                    'status' => 'warning',
                    'message' => "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}",
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Import daftar pengurus berhasil! Total: {$successCount} data",
                'success_count' => $successCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Gagal import Excel: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Import daftar pengurus gagal!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
