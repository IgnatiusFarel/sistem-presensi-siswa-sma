<?php

namespace App\Http\Controllers;

use App\Models\DaftarKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DaftarKelasController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $allowedPerPage = [10, 25, 50, 100];
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 10;
            }

            $pager = DaftarKelas::with('waliKelas')
                ->withCount(['siswa as jumlah_siswa'])
                ->with(['siswa:nama,daftar_kelas_id'])
                ->orderBy('updated_at', 'desc') 
                ->orderBy('created_at', 'desc') 
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Data kelas berhasil diambil!',
                'data' => $pager->items(),
                'meta' => [
                    'current_page' => $pager->currentPage(),
                    'per_page' => $pager->perPage(),
                    'total_items' => $pager->total(),
                    'total_pages' => $pager->lastPage(),
                    'next_page_url' => $pager->nextPageUrl(),
                    'prev_page_url' => $pager->previousPageUrl(),
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching data kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data kelas!'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
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
            'daftar_pengurus_id' => 'required|exists:daftar_pengurus,daftar_pengurus_id',
            'tahun_ajaran' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal!',
                'errors' => $v->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $kelas = DaftarKelas::create([
                'kode_kelas' => $request->kode_kelas,
                'nama_kelas' => $request->nama_kelas,
                'jurusan' => $request->jurusan,
                'tingkat' => $request->tingkat,
                'daftar_pengurus_id' => $request->daftar_pengurus_id,
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

        $v = Validator::make($request->all(), [
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
            'daftar_pengurus_id' => 'required|exists:daftar_pengurus,daftar_pengurus_id',
            'tahun_ajaran' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi data gagal!',
                'errors' => $v->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $kelas->update($v->validated());

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
}
