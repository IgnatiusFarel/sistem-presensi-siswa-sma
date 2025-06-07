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
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $allowedPerPage = [10, 25, 50, 100]; 
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 10;
            }

            $pengurus = DaftarPengurus::with('user')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Data Pengurus Berhasil diambil!',
            'data' => $pengurus->items(),
            'meta' => [
                'current_page' => $pengurus->currentPage(),
                'per_page' => $pengurus->perPage(), 
                'total_items' => $pengurus->total(),
                'total_pages' => $pengurus->lastPage(), 
                'next_page_url' => $pengurus->nextPageUrl(),
                'prev_page_url' => $pengurus->previousPageUrl(), 
            ]
        ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching data pengurus: '. $e->getMessage());
            return response()->json([
                'success'=> false, 
                'message'=> 'Terjadi kesalahan saat mengambil data pengurus!'
            ], 500);
        }        
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'nip' => 'required|string|unique:daftar_pengurus,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
            'email' => 'required|email|unique:daftar_pengurus,email',
            'nomor_handphone' => 'required|string',
            'jabatan' => 'required|in:'.implode(',', DaftarPengurus::getAllJabatan()),
            'bidang_keahlian' => 'required|string',
            'pengurus' => 'required|string',
            'akses_kelas' => 'nullable',
            'status_kepegawaian' => 'required|in:'.implode(',', DaftarPengurus::getAllStatus()),
            'tanggal_bergabung' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'status' => 'error',
                'message' => 'Validasi data gagal!',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $userId = null;
                        
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
                
                // Buat user baru
                $user = User::create([
                    'name' => $request->nama,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'superadmin',
                ]);
                                
            }

            // Buat data pengurus
            $pengurus = DaftarPengurus::create([
                'user_id' => $user->user_id,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'alamat' => $request->alamat,
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
                'message' => 'Data data pengurus tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:daftar_pengurus,nip,'.$id,
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
            'tempat_tanggal_lahir' => 'required|string',
            'alamat' => 'required|string',
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
                'message' => 'Data pengurus tidak valid',
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
                'alamat' => $request->alamat,
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
}
