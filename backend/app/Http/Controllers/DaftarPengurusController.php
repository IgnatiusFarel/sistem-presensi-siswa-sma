<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\DaftarKelas;
use App\Models\DaftarPengurus;
use App\Imports\DaftarPengurusImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DaftarPengurusController extends Controller
{
    public function index()
    {
        try {
            $kelasList = DaftarKelas::select('daftar_kelas_id', 'nama_kelas')->get()->keyBy('daftar_kelas_id');
            $pengurus = DaftarPengurus::with('user')->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get()->map(function ($item) use ($kelasList) {
                return [
                    ...$item->toArray(),
                    'akses_kelas' => collect($item->akses_kelas)
                        ->filter(fn($kelasId) => is_string($kelasId))
                        ->map(function ($kelasId) use ($kelasList) {
                            return [
                                'daftar_kelas_id' => $kelasId,
                                'nama_kelas' => $kelasList[$kelasId]->nama_kelas ?? 'Tidak ditemukan'
                            ];
                        })->values()
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil diambil!',
                'data' => $pengurus,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching data pengurus: ' . $th->getMessage());
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
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creating data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal ditambahkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pengurus = DaftarPengurus::with('user')->find($id);

            if (!$pengurus) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pengurus tidak ditemukan!'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengurus berhasil ditampilkan!',
                'data' => $pengurus
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error fetching detail data pengurus: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data pengurus gagal ditampilkan!',
                'error' => $th->getMessage()
            ], 500);
        }
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
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal diperbarui!',
                'error' => $th->getMessage()
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
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting data pengurus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal dihapus!',
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
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting multiple data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengurus gagal dihapus!',
                'error' => $th->getMessage()
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
            Log::info('Import dimulai dari file: ' . $path);

            $import = new DaftarPengurusImport;
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            if ($errorCount > 0) {
                Log::warning("Import selesai dengan error. Berhasil: {$successCount}, Gagal: {$errorCount}");

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
                'message' => "Import daftar pengurus berhasil! Total: {$successCount} data",
                'success_count' => $successCount
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Gagal import Excel: ' . $th->getMessage());
            Log::error('Stack trace: ' . $th->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Import daftar pengurus gagal!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

 public function export(Request $request): BinaryFileResponse
{
    try {
        // Ambil semua pilihan enum dari model
        $jabatanList = DaftarPengurus::getAllJabatan();
        $statusList = DaftarPengurus::getAllStatus();

        // Header kolom sesuai import
        $columns = [
            'nama',
            'jenis_kelamin', // Laki-laki / Perempuan
            'agama', // Islam / Kristen / Katolik / Hindu / Buddha / Konghucu
            'tempat_tanggal_lahir',
            'alamat',
            'nip',
            'email',
            'nomor_handphone',
            'jabatan', // ambil dari DaftarPengurus::getAllJabatan()
            'bidang_keahlian',
            'pengurus',
            'status_kepegawaian', // ambil dari DaftarPengurus::getAllStatus()
            'akses_kelas',
            'tanggal_bergabung', // format: dd/mm/YYYY atau Excel date
            'password', // hanya wajib untuk jabatan Administrator
        ];

        // Contoh data dummy
        $dummyData = [
            [
                'Budi Santoso',               // nama
                'Laki-laki',                  // jenis_kelamin
                'Islam',                      // agama
                'Jakarta, 12/08/1980',         // tempat_tanggal_lahir
                'Jl. Merdeka No. 1',           // alamat
                '1234567890',                  // nip
                'budi@example.com',            // email
                '081234567890',                // nomor_handphone
                $jabatanList[0] ?? 'Guru',     // jabatan
                'Matematika',                  // bidang_keahlian
                'Ya',                          // pengurus
                $statusList[0] ?? 'Tetap',     // status_kepegawaian
                'Kelas X IPA 1',               // akses_kelas
                '01/01/2024',                  // tanggal_bergabung
                'password123',                 // password (kosongkan jika bukan admin)
            ],
        ];

        // Buat spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray($dummyData, null, 'A2');

        // Simpan ke file sementara
        $fileName = 'template_import_daftar_pengurus.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            Log::error('Error download template import data pengurus: ' . $th->getMessage());

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Gagal membuat template: ' . $th->getMessage());

            $tempFile = tempnam(sys_get_temp_dir(), 'excel_error_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            return response()->download($tempFile, 'error_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(shouldDelete: true);
        }
    }
}
