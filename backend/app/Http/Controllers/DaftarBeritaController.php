<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaftarBerita;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DaftarBeritaController extends Controller
{
    public function index()
    {
        try {
            $berita = DaftarBerita::with(['user', 'komentar.user'])->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil!',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching berita: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data berita!',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'konten' => 'required|string',
            'kategori' => 'required|in:Pengumuman,Kegiatan,Prestasi,Informasi,Agenda,Lainnya',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'user_id' => 'required|uuid|exists:users,user_id',
            'dibuat_oleh' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak valid!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $thumbnailPath = null;

            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/thumbnails', $filename);
                $thumbnailPath = 'storage/thumbnails/' . $filename;
            }

            $berita = DaftarBerita::create([
                'slug' => Str::slug($request->judul) . '-' . Str::random(6),
                'judul' => $request->judul,
                'konten' => $request->konten,
                'kategori' => $request->kategori,
                'thumbnail' => $thumbnailPath,
                'user_id' => $request->user_id,
                'dibuat_oleh' => $request->dibuat_oleh,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil ditambahkan!',
                'data' => $berita,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error storing berita: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan berita!',
            ], 500);
        }
    }

    public function show($id)
    {
        $berita = DaftarBerita::with(['user', 'komentar.user'])->find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $berita,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $berita = DaftarBerita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'konten' => 'required|string',
            'kategori' => 'required|in:pengumuman,kegiatan,prestasi,informasi,agenda,lainnya',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'user_id' => 'required|uuid|exists:users,user_id',
            'dibuat_oleh' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak valid!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('thumbnail')) {
                // Hapus file lama jika ada
                if ($berita->thumbnail && file_exists(public_path($berita->thumbnail))) {
                    unlink(public_path($berita->thumbnail));
                }

                $file = $request->file('thumbnail');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/thumbnails', $filename);
                $berita->thumbnail = 'storage/thumbnails/' . $filename;
            }

            $berita->slug = Str::slug($request->judul) . '-' . Str::random(6);
            $berita->judul = $request->judul;
            $berita->konten = $request->konten;
            $berita->kategori = $request->kategori;
            $berita->user_id = $request->user_id;
            $berita->dibuat_oleh = $request->dibuat_oleh;
            $berita->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diperbarui!',
                'data' => $berita,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating berita: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui berita!',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $berita = DaftarBerita::find($id);

        if (!$berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita tidak ditemukan!',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Hapus file thumbnail dari storage
            if ($berita->thumbnail && file_exists(public_path($berita->thumbnail))) {
                unlink(public_path($berita->thumbnail));
            }

            $berita->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!',
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus berita!',
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
                'message' => 'ID tidak valid!',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $beritas = DaftarBerita::whereIn('daftar_berita_id', $ids)->get();

            foreach ($beritas as $berita) {
                if ($berita->thumbnail && file_exists(public_path($berita->thumbnail))) {
                    unlink(public_path($berita->thumbnail));
                }

                $berita->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!',
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
