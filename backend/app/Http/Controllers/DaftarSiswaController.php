<?php

namespace App\Http\Controllers;

use App\Models\DaftarKelas;
use App\Models\DaftarSiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class DaftarSiswaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $allowedPerPage = [10, 25, 50, 100];
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 10;
            }                    
            $siswa = DaftarSiswa::with('user')->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil diambil!',
                'data' => $siswa->items(),
                'meta' => [
                    'current_page' => $siswa->currentPage(),
                    'per_page' => $siswa->perPage(),
                    'total_items' => $siswa->total(),
                    'total_pages' => $siswa->lastPage(),
                    'next_page_url' => $siswa->nextPageUrl(),
                    'prev_page_url' => $siswa->previousPageUrl(),
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
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nis' => 'required|string|unique:daftar_siswa,nis',
            'nisn' => 'required|string|unique:daftar_siswa,nisn',
            'email' => 'required|email|unique:users,email',
            'nomor_handphone' => 'required|string',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'daftar_kelas_id' => 'required|uuid|exists:daftar_kelas,daftar_kelas_id',
            'nomor_absen' => 'required|integer',
            'tanggal_bergabung' => 'required|date',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $kelas = DaftarKelas::findOrFail($request->daftar_kelas_id);
            $user = User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'siswa',
            ]);

            $siswa = DaftarSiswa::create([
                'user_id' => $user->user_id,
                'nama' => $request->nama,
                'agama' => $request->agama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'daftar_kelas_id' => $request->daftar_kelas_id,
                'nama_kelas' => $kelas->nama_kelas,
                'nomor_absen' => $request->nomor_absen,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil ditambahkan!',
                'data' => $siswa
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal ditambahkan!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $siswa = DaftarSiswa::with('user')->find($id);

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ]);
    }

    public function update(Request $request, $id)
    {
        $siswa = DaftarSiswa::find($id);

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'nis' => 'required|string|unique:daftar_siswa,nis,' . $id,
            'nisn' => 'required|string|unique:daftar_siswa,nisn,' . $id,
            'email' => 'required|email|unique:users,email,' . $siswa->user_id,
            'nomor_handphone' => 'required|string',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'nama_kelas' => 'required|string',
            'nomor_absen' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update data user
            $user = User::find($siswa->user_id);
            $user->update([
                'name' => $request->nama,
                'email' => $request->email,
            ]);

            // Update password jika ada
            if ($request->has('password') && !empty($request->password)) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Update data siswa
            $siswa->update([
                'nama' => $request->nama,
                'agama' => $request->agama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'nama_kelas' => $request->nama_kelas,
                'nomor_absen' => $request->nomor_absen,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil diperbarui',
                'data' => $siswa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $siswa = DaftarSiswa::find($id);

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $user = User::find($siswa->user_id);
            $user->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal dihapus!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
