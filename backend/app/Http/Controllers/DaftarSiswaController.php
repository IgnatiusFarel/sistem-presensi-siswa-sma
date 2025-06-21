<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DaftarSiswaImport;
use App\Models\DaftarSiswa;
use App\Models\DaftarKelas;
use App\Models\User;

class DaftarSiswaController extends Controller
{
    public function index()
    {
        try {
            $siswa = DaftarSiswa::with('user')
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil diambil!',
                'data' => $siswa,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching data kelas: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal diambil!'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'nis' => 'required|string|unique:daftar_siswa,nis',
            'nisn' => 'required|string|unique:daftar_siswa,nisn',
            'email' => 'required|email|unique:users,email',
            'nomor_handphone' => 'required|string',
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
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
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
                'message' => 'Data siswa tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data siswa berhasil ditampilkan!',
            'data' => $siswa
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $siswa = DaftarSiswa::find($id);

        if (!$siswa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'nis' => 'required|string|unique:daftar_siswa,nis,' . $siswa->daftar_siswa_id . ',daftar_siswa_id',
            'nisn' => 'required|string|unique:daftar_siswa,nisn,' . $siswa->daftar_siswa_id . ',daftar_siswa_id',
            'email' => 'required|email|unique:users,email,' . $siswa->user_id . ',user_id',
            'nomor_handphone' => 'required|string',
            'daftar_kelas_id' => 'required|uuid|exists:daftar_kelas,daftar_kelas_id',
            'nama_kelas' => 'required|string',
            'nomor_absen' => 'required|integer',
            'tanggal_bergabung' => 'nullable|date',
            'password' => 'nullable|string|min:8',
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
            $user = User::findOrFail($siswa->user_id);

            $user->name = $request->nama;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $siswa->update([
                'nama' => $request->nama,
                'agama' => $request->agama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'daftar_kelas_id' => $request->daftar_kelas_id,
                'nama_kelas' => $kelas->nama_kelas,
                'nomor_absen' => $request->nomor_absen,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil diperbarui!',
                'data' => $siswa
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating data siswa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal diperbarui!',
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
            if ($siswa->user_id) {
                User::destroy($siswa->user_id);
            }

            $siswa->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil dihapus!'
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal dihapus!',
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
            $siswas = DaftarSiswa::whereIn('daftar_siswa_id', $ids)->get();

            foreach ($siswas as $siswa) {
                if ($siswa->user_id) {
                    User::destroy($siswa->user_id);
                }
                $siswa->delete();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data siswa berhasil dihapus!'
            ], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal dihapus!',
                'error' => $e->getMessage()
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

            $import = new DaftarSiswaImport;
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
                ], 201);
            }

            return response()->json([
                'status' => 'success',
                'message' => "Import daftar siswa berhasil! Total: {$successCount} data",
                'success_count' => $successCount
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Gagal import Excel: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Import daftar siswa gagal!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
