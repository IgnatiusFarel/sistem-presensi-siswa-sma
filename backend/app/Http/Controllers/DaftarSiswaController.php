<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DaftarSiswa;
use App\Models\DaftarKelas;
use App\Imports\DaftarSiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        } catch (\Throwable $th) {
            Log::error('Error fetching data kelas: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal diambil!',
                'error' => $th->getMessage()
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
        } catch (\Throwable $th) {
            Log::error('Error creating data berita: ' . $th->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal ditambahkan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
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
        } catch (\Throwable $th) {
            Log::error('Error fetching detail data siswa: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data siswa gagal ditampilkan!',
                'error' => $th->getMessage()
            ], 500);
        }
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
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating data siswa: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal diperbarui!',
                'error' => $th->getMessage()
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
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting data siswa: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal dihapus!',
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
                'message' => 'ID data siswa tidak valid!'
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
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting multiple data berita: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data siswa gagal dihapus!',
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

            $import = new DaftarSiswaImport;
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
                'message' => "Import daftar siswa berhasil dilakukan! Total: {$successCount} data",
                'success_count' => $successCount
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Gagal import Excel: ' . $th->getMessage());
            Log::error('Stack trace: ' . $th->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Import daftar siswa gagal dilakukan!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function export(Request $request): BinaryFileResponse
    {
        try {
            $kelasOptions = DaftarKelas::all(['daftar_kelas_id', 'nama_kelas']);

            // ✅ Header columns yang EXACT match dengan import
            $columns = [
                'nama',
                'jenis_kelamin',
                'agama',
                'nis',
                'nisn',
                'email',
                'nomor_handphone',
                'tempat_tanggal_lahir',
                'alamat',
                'nama_kelas',
                'nomor_absen',
                'tanggal_bergabung',
                'password',
            ];

            // ✅ Sample data yang benar
            $dummyData = [
                [
                    'Ahmad Fauzan',           // nama
                    'Laki-laki',              // jenis_kelamin
                    'Islam',                  // agama
                    '1001',                   // nis - as string
                    '202301001',              // nisn - as string  
                    'ahmad@example.com',      // email
                    '081234567890',           // nomor_handphone - as string
                    'Jakarta, 01 Januari 2001', // tempat_tanggal_lahir
                    'Jl. Merdeka No. 10, Jakarta', // alamat
                    'XI Bahasa 5O',           // nama_kelas - EXACT match dengan DB
                    1,                        // nomor_absen - as integer
                    '15/07/2023',             // tanggal_bergabung - d/m/Y format
                    'password123'             // password - as string
                ],
                [
                    'Siti Nurhaliza',         // nama
                    'Perempuan',              // jenis_kelamin
                    'Islam',                  // agama
                    '1002',                   // nis
                    '202301002',              // nisn
                    'siti@example.com',       // email
                    '081234567891',           // nomor_handphone
                    'Bandung, 05 Februari 2001', // tempat_tanggal_lahir
                    'Jl. Sudirman No. 20, Bandung', // alamat
                    'XI Bahasa 5O',           // nama_kelas 
                    2,                        // nomor_absen
                    '16/07/2023',             // tanggal_bergabung
                    'password123'             // password
                ]
            ];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // ✅ Set headers
            $sheet->fromArray($columns, null, 'A1');

            // ✅ Set sample data
            $sheet->fromArray($dummyData, null, 'A2');

            // ✅ Apply formatting
            // Bold headers
            $sheet->getStyle('A1:M1')->getFont()->setBold(true);

            // Auto-size columns
            foreach (range('A', 'M') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // ✅ Create reference sheet untuk daftar kelas
            $referenceSheet = $spreadsheet->createSheet();
            $referenceSheet->setTitle('Daftar Kelas');
            $referenceSheet->setCellValue('A1', 'DAFTAR KELAS YANG TERSEDIA:');
            $referenceSheet->getStyle('A1')->getFont()->setBold(true);

            $row = 3; // Start from row 3 to give some space
            foreach ($kelasOptions as $kelas) {
                $referenceSheet->setCellValue('A' . $row, $kelas->nama_kelas);
                $row++;
            }

            // Auto-size reference sheet
            $referenceSheet->getColumnDimension('A')->setAutoSize(true);

            // ✅ Add instructions
            $sheet->setCellValue('A' . (count($dummyData) + 3), 'PETUNJUK PENGISIAN:');
            $sheet->setCellValue('A' . (count($dummyData) + 4), '1. Pastikan format tanggal: DD/MM/YYYY (contoh: 15/07/2023)');
            $sheet->setCellValue('A' . (count($dummyData) + 5), '2. Nama kelas harus PERSIS sama dengan yang ada di sheet "Daftar Kelas"');
            $sheet->setCellValue('A' . (count($dummyData) + 6), '3. Jenis kelamin: Laki-laki atau Perempuan (huruf besar di awal)');
            $sheet->setCellValue('A' . (count($dummyData) + 7), '4. Agama: Islam, Kristen, Katolik, Hindu, Buddha, atau Konghucu');
            $sheet->setCellValue('A' . (count($dummyData) + 8), '5. Password minimal 8 karakter');

            // Bold instructions
            $sheet->getStyle('A' . (count($dummyData) + 3))->getFont()->setBold(true);

            $fileName = 'template_import_daftar_siswa.xlsx';

            $tempFile = tempnam(sys_get_temp_dir(), 'excel_export_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Throwable $th) {
            Log::error('Error download template import data siswa: ' . $th->getMessage());

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Gagal membuat template: ' . $th->getMessage());

            $tempFile = tempnam(sys_get_temp_dir(), 'excel_error_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            return response()->download($tempFile, 'error_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }
    }
}
