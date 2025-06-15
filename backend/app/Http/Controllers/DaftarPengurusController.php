<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DaftarPengurus;
use App\Models\User;

class DaftarPengurusController extends Controller
{
    public function index()
    {
        try {
            $pengurus = DaftarPengurus::with('user')->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil diambil!',
                'data' => $pengurus,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching data pengurus: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal diambil!'
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
            'nip' => 'required|string|unique:daftar_pengurus,nip',
            'email' => 'required|email|unique:daftar_pengurus,email',
            'nomor_handphone' => 'required|string',
            'jabatan' => 'required|in:' . implode(',', DaftarPengurus::getAllJabatan()),
            'bidang_keahlian' => 'required|string',
            'pengurus' => 'required|string',
            'status_kepegawaian' => 'required|in:' . implode(',', DaftarPengurus::getAllStatus()),
            'akses_kelas' => 'nullable|array',
            'tanggal_bergabung' => 'required|date',
            'password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = null;

            if ($request->jabatan === DaftarPengurus::JABATAN_ADMIN) {

                $adminValidator = Validator::make($request->all(), [
                    'email' => 'unique:users,email',
                    'password' => 'required|min:8',
                ]);

                if ($adminValidator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data administrator tidak valid!',
                        'errors' => $adminValidator->errors()
                    ], 422);
                }

                $user = User::create([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'superadmin',
                ]);
            }

            $pengurus = DaftarPengurus::create([
                'user_id' => $user ? $user->user_id : null,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'nip' => $request->nip,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'jabatan' => $request->jabatan,
                'bidang_keahlian' => $request->bidang_keahlian,
                'pengurus' => $request->pengurus,
                'status_kepegawaian' => $request->status_kepegawaian,
                'akses_kelas' => $request->akses_kelas,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil ditambahkan!',
                'data' => $pengurus
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal ditambahkan!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pengurus = DaftarPengurus::with('user')->find($id);

        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pengurus
        ]);
    }

    public function update(Request $request, $id)
    {
        $pengurus = DaftarPengurus::find($id);

        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan!'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'nip' => 'required|string|unique:daftar_pengurus,nip,' . $id . ',daftar_pengurus_id',
            'email' => 'required|email|unique:users,email,' . $pengurus->user_id . ',user_id',
            'nomor_handphone' => 'required|string',
            'jabatan' => 'required|in:' . implode(',', DaftarPengurus::getAllJabatan()),
            'bidang_keahlian' => 'required|string',
            'pengurus' => 'required|string',
            'status_kepegawaian' => 'required|in:' . implode(',', DaftarPengurus::getAllStatus()),
            'akses_kelas' => 'nullable|array',
            'tanggal_bergabung' => 'required|date',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak valid!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $jabatanSebelumnya = $pengurus->jabatan;
            $jabatanBaru = $request->jabatan;

            if ($jabatanBaru === 'Administrator' && $jabatanSebelumnya !== 'Administrator') {
                if (!$request->filled('password')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Password diperlukan untuk akun Administrator baru!',
                        'errors' => ['password' => ['Password tidak boleh kosong.']]
                    ], 422);
                }

                $user = User::create([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'superadmin',
                ]);
                $pengurus->user_id = $user->user_id;
            } elseif ($jabatanBaru !== 'Administrator' && $jabatanSebelumnya === 'Administrator') {
                if ($pengurus->user_id) {
                    User::destroy($pengurus->user_id);
                    $pengurus->user_id = null;
                }
            } elseif ($jabatanBaru === 'Administrator' && $jabatanSebelumnya === 'Administrator') {
                if ($pengurus->user_id) {
                    $user = User::find($pengurus->user_id);
                    if ($user) {
                        $user->name = $request->nama;
                        $user->email = $request->email;

                        if ($request->filled('password')) {
                            $user->password = Hash::make($request->password);
                        }

                        $user->save();
                    }
                }
            }

            $pengurus->update([
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
                'nip' => $request->nip,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'jabatan' => $request->jabatan,
                'bidang_keahlian' => $request->bidang_keahlian,
                'pengurus' => $request->pengurus,
                'status_kepegawaian' => $request->status_kepegawaian,
                'akses_kelas' => $request->akses_kelas,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil diperbarui!',
                'data' => $pengurus->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal update pengurus: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal diperbarui!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $pengurus = DaftarPengurus::find($id);

        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan!'
            ], 404);
        }

        DB::beginTransaction();
        try {
            if ($pengurus->user_id) {
                User::destroy($pengurus->user_id);
            }

            $pengurus->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal dihapus!',
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
                'message' => 'Data ID pengurus tidak ada!'
            ], 400);
        }
        DB::beginTransaction();
        try {
            $daftarpengurus = DaftarPengurus::whereIn('daftar_pengurus_id', $ids)->get();

            foreach ($daftarpengurus as $pengurus) {
                if ($pengurus->user_id) {
                    User::destroy($pengurus->user_id);
                }
                $pengurus->delete();
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal dihapus!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
