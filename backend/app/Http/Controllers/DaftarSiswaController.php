<?php

namespace App\Http\Controllers;

use App\Models\DaftarSiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DaftarSiswaController extends Controller
{
    /**
     * Menampilkan daftar semua siswa
     */
    public function index()
    {
        $siswa = DaftarSiswa::with('user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ]);
    }

    /**
     * Menyimpan data siswa baru
     */
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
            'alamat_rumah' => 'required|string',
            'kelas' => 'required|string',
            'nomor_absen' => 'required|integer',
            'password' => 'required|min:6',
            'tanggal_bergabung' => 'required|date',
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
            // Buat user baru
            $user = User::create([
                'name' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'siswa',
            ]);

            // Buat data siswa
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
                'alamat_rumah' => $request->alamat_rumah,
                'kelas' => $request->kelas,
                'nomor_absen' => $request->nomor_absen,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil ditambahkan',
                'data' => $siswa
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail data siswa
     */
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

    /**
     * Update data siswa yang ada
     */
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
            'nis' => 'required|string|unique:daftar_siswa,nis,'.$id,
            'nisn' => 'required|string|unique:daftar_siswa,nisn,'.$id,
            'email' => 'required|email|unique:users,email,'.$siswa->user_id,
            'nomor_handphone' => 'required|string',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat_rumah' => 'required|string',
            'kelas' => 'required|string',
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
                'alamat_rumah' => $request->alamat_rumah,
                'kelas' => $request->kelas,
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

    /**
     * Hapus data siswa
     */
    public function destroy($id)
    {
        $siswa = DaftarSiswa::find($id);
        
        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Hapus user (akan menghapus siswa secara cascade)
            $user = User::find($siswa->user_id);
            $user->delete();
            
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data siswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
