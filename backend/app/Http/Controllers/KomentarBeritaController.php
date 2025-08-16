<?php

namespace App\Http\Controllers;

use App\Models\KomentarBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KomentarBeritaController extends Controller
{
    public function index($daftar_berita_id)
    {
        try {
            $komentar = KomentarBerita::with(['user', 'replies.user'])
                ->where('daftar_berita_id', $daftar_berita_id)
                ->whereNull('parent_id')
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Komentar berhasil diambil!',
                'data' => $komentar,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching data komentar: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Komentar gagal diambil!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'daftar_berita_id' => 'required|exists:daftar_berita,daftar_berita_id',
            'komentar' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|uuid|exists:komentar_berita,komentar_berita_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Komentar gagal divalidasi!',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $komentar = KomentarBerita::create([
                'daftar_berita_id' => $request->daftar_berita_id,
                'komentar' => $request->komentar,
                'parent_id' => $request->parent_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            $komentar->load('user');

            return response()->json([
                'status' => 'success',
                'message' => $request->parent_id
                    ? 'Reply berhasil ditambahkan!'
                    : 'Komentar berhasil ditambahkan!',
                'data' => $komentar,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creatingkomentar: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Komentar gagal ditambahkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $komentar_id)
    {
        $komentar = KomentarBerita::findOrFail($komentar_id);
        if ($komentar->user_id !== auth()->user()->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ini bukan komentar Anda!',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $komentar->replies()->delete();
            $komentar->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Komentar berhasil dihapus!',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting komentar: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Komentar gagal dihapus!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}