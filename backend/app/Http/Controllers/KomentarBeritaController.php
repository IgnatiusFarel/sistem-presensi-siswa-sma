<?php

namespace App\Http\Controllers;

use App\Models\KomentarBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KomentarBeritaController extends Controller
{
    public function index($id)
    {
        try {
            $komentar = KomentarBerita::with('user')
                ->where('daftar_berita_id', $id)
                ->orderBy('created_at', 'asc')
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
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,user_id',
            'daftar_berita_id' => 'required|uuid|exists:daftar_berita,daftar_berita_id',
            'komentar' => 'required|string',
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
                'user_id' => $request->user_id,
                'daftar_berita_id' => $request->daftar_berita_id,
                'komentar' => $request->komentar,
            ]);

            DB::commit();

            $komentar->load('user');
            return response()->json([
                'status' => 'success',
                'message' => 'Komentar berhasil ditambahkan!',
                'data' => $komentar,
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error storing komentar: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Komentar gagal ditambahkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}