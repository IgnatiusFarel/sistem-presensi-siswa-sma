<?php

namespace App\Http\Controllers;

use App\Models\DaftarBerita;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DaftarBeritaController extends Controller
{
    public function index()
    {
        try {
            $berita = DaftarBerita::with(['user', 'komentar.user'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil!',
                'data' => $berita,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal diambil!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'konten' => 'required|string',
            'kategori' => 'required|in:Pengumuman,Kegiatan,Prestasi,Informasi,Agenda,Lainnya',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
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

        try {
            $berita = DB::transaction(function () use ($request) {
                $path = $request->hasFile('thumbnail')
                    ? $request->file('thumbnail')->store('thumbnail', 'public')
                    : null;

                return DaftarBerita::create([
                    'slug' => Str::slug($request->judul) . '-' . Str::random(6),
                    'judul' => $request->judul,
                    'konten' => $request->konten,
                    'kategori' => $request->kategori,
                    'thumbnail' => $path,
                    'user_id' => $request->user_id,
                    'dibuat_oleh' => $request->dibuat_oleh,
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil ditambahkan!',
                'data' => $berita,
            ], 201);
        } catch (\Throwable $th) {
            Log::error('Error creating data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal ditambahkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $berita = DaftarBerita::with(['user', 'komentar.user'])->find($id);

            if (!$berita) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data berita tidak ditemukan!',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil ditampilkan!',
                'data' => $berita,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching detail data berita: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data berita gagal ditampilkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $berita = DaftarBerita::findOrFail($id);

        $data = $request->validate([
            'judul' => 'required|string',
            'konten' => 'required|string',
            'kategori' => 'required|in:Pengumuman,Kegiatan,Prestasi,Informasi,Agenda,Lainnya',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'user_id' => 'required|uuid|exists:users,user_id',
            'dibuat_oleh' => 'required|string',
            'remove_thumbnail' => 'sometimes|boolean',
        ]);

        try {
            $updated = DB::transaction(function () use ($request, $berita, $data) {
                if (!empty($data['remove_thumbnail']) && $berita->thumbnail) {
                    Storage::disk('public')->delete($berita->thumbnail);
                    $berita->thumbnail = null;
                }

                if ($request->hasFile('thumbnail')) {
                    if ($berita->thumbnail) {
                        Storage::disk('public')->delete($berita->thumbnail);
                    }
                    $berita->thumbnail = $request->file('thumbnail')
                        ->store('thumbnail', 'public');
                }

                if ($request->judul !== $berita->judul) {
                    $berita->slug = Str::slug($request->judul) . '-' . Str::random(6);
                }

                $berita->fill([
                    'judul' => $data['judul'],
                    'konten' => $data['konten'],
                    'kategori' => $data['kategori'],
                    'user_id' => $data['user_id'],
                    'dibuat_oleh' => $data['dibuat_oleh'],
                ]);

                $berita->save();
                return $berita->refresh();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diperbarui!',
                'data' => $updated,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error updating data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal diperbarui!',
                'error' => $th->getMessage()
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

        try {
            DB::transaction(function () use ($berita) {
                if ($berita->thumbnail && Storage::disk('public')->exists($berita->thumbnail)) {
                    Storage::disk('public')->delete($berita->thumbnail);
                }
                $berita->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!',
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal dihapus!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID data berita tidak valid!',
            ], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                $beritas = DaftarBerita::whereIn('daftar_berita_id', $ids)->get();

                foreach ($beritas as $berita) {
                    if ($berita->thumbnail && Storage::disk('public')->exists($berita->thumbnail)) {
                        Storage::disk('public')->delete($berita->thumbnail);
                    }
                    $berita->delete();
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil dihapus!',
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting multiple data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data berita gagal dihapus!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
