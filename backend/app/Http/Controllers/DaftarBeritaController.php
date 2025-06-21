<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaftarBerita;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DaftarBeritaController extends Controller
{
    public function index()
    {
        try {
            $berita = DaftarBerita::with(['user', 'komentar.user'])->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil diambil!',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching data berita: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal diambil!'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul' => 'required|string',
                'konten' => 'required|string',
                'kategori' => 'required|in:pengumuman,kegiatan,prestasi,informasi,agenda,lainnya',
                'thumbnail' => 'nullable|string',
                'user_id' => 'required|uuid|exists:users,user_id',
                'dibuat_oleh' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data berita tidak valid!',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            $berita = DaftarBerita::create([
                'slug' => Str::slug($request->judul) . '-' . Str::random(6),
                'judul' => $request->judul,
                'konten' => $request->konten,
                'kategori' => $request->kategori,
                'thumbnail' => $request->thumbnail,
                'user_id' => $request->user_id,
                'dibuat_oleh' => $request->dibuat_oleh,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil ditambahkan!',
                'data' => $berita
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error storing data berita: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal ditambahkan!'
            ], 500);
        }
    }

    public function show($id)
    {
        $berita = DaftarBerita::with(['user', 'komentar.user'])->find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $berita
        ], 200);

    }

    public function update(Request $request, $id)
    {
        $berita = DaftarBerita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'konten' => 'required|string',
            'kategori' => 'required|in:pengumuman,kegiatan,prestasi,informasi,agenda,lainnya',
            'thumbnail' => 'nullable|string',
            'user_id' => 'required|uuid|exists:users,user_id',
            'dibuat_oleh' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->has('judul')) {
                $berita->slug = Str::slug($request->judul) . '-' . Str::random(6);
            }

            $berita->update([
                'judul' => $request->judul,
                'konten' => $request->konten,
                'kategori' => $request->kategori,
                'thumbnail' => $request->thumbnail,
                'user_id' => $request->user_id,
                'dibuat_oleh' => $request->dibuat_oleh,
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diperbarui!',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating data berita: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal diperbarui!',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function destroy($id)
    {
        $berita = DaftarBerita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $berita->delete();
            DB::commit();


            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!'
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal dihapus!',
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
                'message' => 'Data ID siswa tidak ada!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            DaftarBerita::whereIn('daftar_berita_id', $ids)->delete();


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!'
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal dihapus!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}