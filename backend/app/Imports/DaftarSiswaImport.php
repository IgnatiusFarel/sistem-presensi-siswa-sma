<?php

namespace App\Imports;

use App\Models\DaftarSiswa;
use App\Models\User;
use App\Models\DaftarKelas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DaftarSiswaImport implements ToCollection, WithHeadingRow
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        \Log::info('Mulai import dengan ' . $rows->count() . ' baris data');
        
        foreach ($rows as $index => $row) {
            \Log::info("Processing row " . ($index + 1) . ": " . json_encode($row->toArray()));
            
            // Skip baris kosong
            if ($row->filter()->isEmpty()) {
                \Log::info("Skipping empty row " . ($index + 1));
                continue;
            }

            // Proses tanggal bergabung
            if (!empty($row['tanggal_bergabung'])) {
                try {
                    $value = $row['tanggal_bergabung'];

                    if (is_numeric($value)) {
                        $row['tanggal_bergabung'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } else {
                        $row['tanggal_bergabung'] = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                    }
                    
                    \Log::info("Tanggal berhasil dikonversi: " . $row['tanggal_bergabung']);
                } catch (\Exception $e) {
                    \Log::error("Format tanggal salah pada baris " . ($index + 1) . ": " . $e->getMessage());
                    $this->errors[] = "Baris " . ($index + 1) . ": Format tanggal salah";
                    $this->errorCount++;
                    continue;
                }
            }

            $data = $row->toArray();
            
            // Konversi field numerik ke string
            if (isset($data['nis'])) {
                $data['nis'] = (string) $data['nis'];
            }
            if (isset($data['nisn'])) {
                $data['nisn'] = (string) $data['nisn'];
            }
            if (isset($data['nomor_handphone'])) {
                $data['nomor_handphone'] = (string) $data['nomor_handphone'];
            }
            if (isset($data['nomor_absen'])) {
                $data['nomor_absen'] = (int) $data['nomor_absen'];
            }
            
            // Debug: tampilkan data dan tipe data
            \Log::info("Data untuk validasi: " . json_encode($data));
            \Log::info("Tipe data - NIS: " . gettype($data['nis'] ?? null) . 
                      ", NISN: " . gettype($data['nisn'] ?? null) . 
                      ", HP: " . gettype($data['nomor_handphone'] ?? null));

            $validator = Validator::make($data, [
                'nama' => 'required|string',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
                'tempat_tanggal_lahir' => 'required|string',
                'alamat' => 'required|string',
                'nis' => 'required|string|unique:daftar_siswa,nis',
                'nisn' => 'required|string|unique:daftar_siswa,nisn',
                'email' => 'required|email|unique:users,email',
                'nomor_handphone' => 'required|string',
                'daftar_kelas_id' => 'required|string|exists:daftar_kelas,daftar_kelas_id',
                'nomor_absen' => 'required|integer',
                'tanggal_bergabung' => 'required|date',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                \Log::error('Baris ' . ($index + 1) . ' tidak valid', [
                    'row' => $data,
                    'errors' => $errors
                ]);
                
                $this->errors[] = "Baris " . ($index + 1) . ": " . implode(', ', $errors);
                $this->errorCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $index) {
                    // Cek apakah kelas ada
                    $kelas = DaftarKelas::where('daftar_kelas_id', $data['daftar_kelas_id'])->first();
                    
                    if (!$kelas) {
                        throw new \Exception("Kelas dengan ID {$data['daftar_kelas_id']} tidak ditemukan");
                    }
                    
                    \Log::info("Kelas ditemukan: " . $kelas->nama_kelas);

                    // Buat user terlebih dahulu
                    $user = User::create([
                        'name' => $data['nama'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'role' => 'siswa',
                    ]);

                    \Log::info("User berhasil dibuat dengan ID: " . $user->user_id);

                    // Buat daftar siswa
                    $siswa = DaftarSiswa::create([
                        'daftar_siswa_id' => (string) Str::uuid(),
                        'user_id' => $user->user_id,
                        'nama' => $data['nama'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'agama' => $data['agama'],
                        'tempat_tanggal_lahir' => $data['tempat_tanggal_lahir'],
                        'alamat' => $data['alamat'],
                        'nis' => $data['nis'],
                        'nisn' => $data['nisn'],
                        'email' => $data['email'],
                        'nomor_handphone' => $data['nomor_handphone'],
                        'daftar_kelas_id' => $data['daftar_kelas_id'],
                        'nama_kelas' => $kelas->nama_kelas,
                        'nomor_absen' => $data['nomor_absen'],
                        'tanggal_bergabung' => $data['tanggal_bergabung'],
                    ]);

                    \Log::info('Berhasil import siswa: ' . $data['nama'] . ' dengan ID: ' . $siswa->daftar_siswa_id);
                    $this->successCount++;
                });
            } catch (\Exception $e) {
                \Log::error("Gagal import baris " . ($index + 1) . ": {$data['nama']} | Error: {$e->getMessage()}");
                $this->errors[] = "Baris " . ($index + 1) . " ({$data['nama']}): " . $e->getMessage();
                $this->errorCount++;
            }
        }
        
        // Log ringkasan
        \Log::info("Import selesai. Berhasil: {$this->successCount}, Gagal: {$this->errorCount}");
        if (!empty($this->errors)) {
            \Log::error("Error details: " . json_encode($this->errors));
        }
    }
    
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    public function getErrorCount()
    {
        return $this->errorCount;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}