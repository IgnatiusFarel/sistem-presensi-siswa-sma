<?php

namespace App\Http\Controllers;

use App\Models\DaftarPengurus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DaftarPengurusController extends Controller
{
    /**
     * Menampilkan daftar semua pengurus
     */
    public function index()
    {
        $pengurus = DaftarPengurus::with('user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $pengurus
        ]);
    }

    /**
     * Menyimpan data pengurus baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:daftar_pengurus,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat_rumah' => 'required|string',
            'email' => 'required|email',
            'nomor_handphone' => 'required|string',
            'jabatan' => 'required|in:Administrator,Kepala Sekolah,Wakil Kepala Sekolah,Guru,Kepala Laboratorium,Pustakawan,Operator Sekolah,Staf TU,Satpam,Petugas Kebersihan',
            'bidang_keahlian' => 'required|string',
            'pengurus' => 'required|string',
            'akses_kelas' => 'required|string',
            'status_kepegawaian' => 'required|in:PNS,Honorer,GTY,PTY,Kontrak,Magang,PPPK,Outsourcing',
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
            $userId = null;
            
            // Jika jabatan adalah Administrator, buat user baru
            if ($request->jabatan === 'Administrator') {
                // Tambahan validasi untuk administrator
                $adminValidator = Validator::make($request->all(), [
                    'email' => 'unique:users,email',
                    'password' => 'required|min:6',
                ]);
                
                if ($adminValidator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data administrator tidak valid',
                        'errors' => $adminValidator->errors()
                    ], 422);
                }
                
                // Buat user baru
                $user = User::create([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'superadmin',
                ]);
                
                $userId = $user->id;
            }

            // Buat data pengurus
            $pengurus = DaftarPengurus::create([
                'user_id' => $userId,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat_rumah' => $request->alamat_rumah,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'jabatan' => $request->jabatan,
                'bidang_keahlian' => $request->bidang_keahlian,
                'pengurus' => $request->pengurus,
                'akses_kelas' => $request->akses_kelas,
                'status_kepegawaian' => $request->status_kepegawaian,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil ditambahkan',
                'data' => $pengurus
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data pengurus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail data pengurus
     */
    public function show($id)
    {
        $pengurus = DaftarPengurus::with('user')->find($id);
        
        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pengurus
        ]);
    }

    /**
     * Update data pengurus yang ada
     */
    public function update(Request $request, $id)
    {
        $pengurus = DaftarPengurus::find($id);
        
        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:daftar_pengurus,nip,'.$id,
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat_rumah' => 'required|string',
            'email' => 'required|email',
            'nomor_handphone' => 'required|string',
            'jabatan' => 'required|in:Administrator,Kepala Sekolah,Wakil Kepala Sekolah,Guru,Kepala Laboratorium,Pustakawan,Operator Sekolah,Staf TU,Satpam,Petugas Kebersihan',
            'bidang_keahlian' => 'required|string',
            'pengurus' => 'required|string',
            'akses_kelas' => 'required|string',
            'status_kepegawaian' => 'required|in:PNS,Honorer,GTY,PTY,Kontrak,Magang,PPPK,Outsourcing',
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
            // Cek perubahan jabatan
            $jabatanSebelumnya = $pengurus->jabatan;
            $jabatanBaru = $request->jabatan;

            // Jika jabatan berubah menjadi Administrator
            if ($jabatanBaru === 'Administrator' && $jabatanSebelumnya !== 'Administrator') {
                // Tambahan validasi untuk administrator
                $adminValidator = Validator::make($request->all(), [
                    'email' => 'unique:users,email',
                    'password' => 'required|min:6',
                ]);
                
                if ($adminValidator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data administrator tidak valid',
                        'errors' => $adminValidator->errors()
                    ], 422);
                }
                
                // Buat user baru untuk admin
                $user = User::create([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'superadmin',
                ]);
                
                $pengurus->user_id = $user->id;
            }
            // Jika jabatan berubah dari Administrator menjadi bukan
            elseif ($jabatanBaru !== 'Administrator' && $jabatanSebelumnya === 'Administrator') {
                // Hapus user jika ada
                if ($pengurus->user_id) {
                    User::destroy($pengurus->user_id);
                    $pengurus->user_id = null;
                }
            }
            // Jika tetap Administrator
            elseif ($jabatanBaru === 'Administrator' && $jabatanSebelumnya === 'Administrator') {
                // Update data user jika ada
                if ($pengurus->user_id) {
                    $user = User::find($pengurus->user_id);
                    if ($user) {
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
                    }
                }
            }

            // Update data pengurus
            $pengurus->update([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat_rumah' => $request->alamat_rumah,
                'email' => $request->email,
                'nomor_handphone' => $request->nomor_handphone,
                'jabatan' => $request->jabatan,
                'bidang_keahlian' => $request->bidang_keahlian,
                'pengurus' => $request->pengurus,
                'akses_kelas' => $request->akses_kelas,
                'status_kepegawaian' => $request->status_kepegawaian,
                'tanggal_bergabung' => $request->tanggal_bergabung,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil diperbarui',
                'data' => $pengurus
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data pengurus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus data pengurus
     */
    public function destroy($id)
    {
        $pengurus = DaftarPengurus::find($id);
        
        if (!$pengurus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Jika pengurus adalah admin, hapus juga data user
            if ($pengurus->user_id) {
                User::destroy($pengurus->user_id);
            }
            
            $pengurus->delete();
            
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data pengurus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
