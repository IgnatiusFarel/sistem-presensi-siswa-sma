<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\DaftarSiswa;
use App\Models\DaftarKelas;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DaftarSiswaImport implements ToCollection, WithHeadingRow, WithStartRow
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];
    
    public function startRow(): int
    {
        return 2;
    }

    private function getHeaderMapping(): array
    {
        return [
            'nama' => ['nama', 'name', 'nama_siswa', 'nama_lengkap'],
            'jenis_kelamin' => ['jenis_kelamin', 'gender', 'jk', 'kelamin'],
            'agama' => ['agama', 'religion', 'kepercayaan'],
            'nis' => ['nis', 'nomor_induk_siswa'],
            'nisn' => ['nisn', 'nomor_induk_siswa_nasional'],
            'email' => ['email', 'e_mail', 'alamat_email'],
            'nomor_handphone' => ['nomor_handphone', 'no_hp', 'telepon', 'phone', 'handphone'],
            'tempat_tanggal_lahir' => ['tempat_tanggal_lahir', 'ttl', 'tempat_lahir'],
            'alamat' => ['alamat', 'address', 'alamat_lengkap'],
            'nama_kelas' => ['nama_kelas', 'kelas', 'class', 'namakelas'],
            'nomor_absen' => ['nomor_absen', 'no_absen', 'absen'],
            'tanggal_bergabung' => ['tanggal_bergabung', 'tanggal_masuk', 'tgl_bergabung'],
            'password' => ['password', 'pwd', 'pass']
        ];
    }

    private function mapRowData(array $rawData): array
    {
        $mapping = $this->getHeaderMapping();
        $mappedData = [];
        
        foreach ($mapping as $dbField => $possibleHeaders) {
            $value = null;
                        
            foreach ($possibleHeaders as $header) {            
                if (array_key_exists($header, $rawData) && !empty(trim($rawData[$header]))) {
                    $value = trim($rawData[$header]);
                    break;
                }
                                
                foreach ($rawData as $key => $val) {
                    if (strtolower(trim($key)) === strtolower($header) && !empty(trim($val))) {
                        $value = trim($val);
                        break 2;
                    }
                }
            }
            
            $mappedData[$dbField] = $value;
        }
        
        return $mappedData;
    }

    public function collection(Collection $rows)
    {
        Log::info('Mulai import dengan ' . $rows->count() . ' baris data');
                
        $availableKelas = DaftarKelas::all(['nama_kelas'])->pluck('nama_kelas')->toArray();
        Log::info('Kelas yang tersedia di database: ' . json_encode($availableKelas));

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; 
                    
            if ($row->filter()->isEmpty()) {
                Log::info("Skipping empty row {$rowNumber}");
                continue;
            }
            
            $rawData = $row->toArray();
            
            Log::info("Raw data baris {$rowNumber}: " . json_encode($rawData));
            Log::info("Available keys baris {$rowNumber}: " . json_encode(array_keys($rawData)));

            $data = $this->mapRowData($rawData);
            
            if (empty($data['nama']) || 
                stripos($data['nama'], 'nama') !== false || 
                stripos($data['nama'], 'contoh') !== false ||
                stripos($data['nama'], 'example') !== false) {
                Log::info("Skipping header/instruction row {$rowNumber}");
                continue;
            }
            
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = trim($value);
                }
            }

            if (!empty($data['password'])) {
                $data['password'] = (string) $data['password'];
            }

            if (!empty($data['tanggal_bergabung'])) {
                try {
                    $value = $data['tanggal_bergabung'];

                    if (is_numeric($value)) {
                        $data['tanggal_bergabung'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } else {                        
                        $dateFormats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
                        $dateConverted = false;
                        
                        foreach ($dateFormats as $format) {
                            try {
                                $data['tanggal_bergabung'] = Carbon::createFromFormat($format, trim($value))->format('Y-m-d');
                                $dateConverted = true;
                                break;
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                        
                        if (!$dateConverted) {
                            throw new \Exception("Format tanggal tidak dikenali: {$value}");
                        }
                    }

                    Log::info("Tanggal berhasil dikonversi: " . $data['tanggal_bergabung']);
                } catch (\Throwable $th) {
                    Log::error("Format tanggal salah pada baris {$rowNumber}: " . $th->getMessage());
                    $this->errors[] = "Baris {$rowNumber}: Format tanggal salah - {$th->getMessage()}";
                    $this->errorCount++;
                    continue;
                }
            }
            
            if (!empty($data['nama_kelas'])) {
                $namaKelasNormalized = trim($data['nama_kelas']);
                
                $kelas = DaftarKelas::where('nama_kelas', $namaKelasNormalized)->first();
                            
                if (!$kelas) {
                    $kelas = DaftarKelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($namaKelasNormalized)])->first();
                }
                
                if (!$kelas) {
                    Log::error("Kelas tidak ditemukan: '{$namaKelasNormalized}' pada baris {$rowNumber}");
                    $this->errors[] = "Baris {$rowNumber}: Kelas '{$namaKelasNormalized}' tidak ditemukan. Tersedia: " . implode(', ', array_slice($availableKelas, 0, 5));
                    $this->errorCount++;
                    continue;
                }
                
                $data['daftar_kelas_id'] = $kelas->daftar_kelas_id;
                $data['nama_kelas'] = $kelas->nama_kelas;
            } else {
                Log::error("Nama kelas kosong pada baris {$rowNumber}");
                $this->errors[] = "Baris {$rowNumber}: Nama kelas wajib diisi";
                $this->errorCount++;
                continue;
            }
            
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

            $dataCheck = array_map(function($value) {
                return $value ?? 'NULL';
            }, $data);
            
            Log::info("Data sebelum validasi baris {$rowNumber}:", $dataCheck);

            $validationRules = [
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                'agama' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
                'tempat_tanggal_lahir' => 'required|string|max:255',
                'alamat' => 'required|string',
                'nis' => 'required|string|unique:daftar_siswa,nis',
                'nisn' => 'required|string|unique:daftar_siswa,nisn',
                'email' => 'required|email|unique:users,email',
                'nomor_handphone' => 'required|string|max:20',
                'nama_kelas' => 'required|string',
                'daftar_kelas_id' => 'required|string|exists:daftar_kelas,daftar_kelas_id',
                'nomor_absen' => 'required|integer|min:1',
                'tanggal_bergabung' => 'required|date',
                'password' => 'required|string|min:8',
            ];

            $validator = Validator::make($data, $validationRules);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                Log::error("Validasi gagal baris {$rowNumber}", [
                    'errors' => $errors,
                    'data_sample' => array_slice($data, 0, 5, true)
                ]);

                $this->errors[] = "Baris {$rowNumber}: " . implode(', ', $errors);
                $this->errorCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $rowNumber) {
                    $kelas = DaftarKelas::where('daftar_kelas_id', $data['daftar_kelas_id'])->first();

                    if (!$kelas) {
                        throw new \Exception("Kelas dengan ID {$data['daftar_kelas_id']} tidak ditemukan");
                    }

                    $user = User::create([
                        'name' => $data['nama'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'role' => 'siswa',
                    ]);

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

                    Log::info("Berhasil import siswa baris {$rowNumber}: {$data['nama']}");
                    $this->successCount++;
                });
            } catch (\Throwable $th) {
                Log::error("Gagal import baris {$rowNumber}: " . $th->getMessage());
                $namaDefault = $data['nama'] ?? 'Unknown';
                $this->errors[] = "Baris {$rowNumber} ({$namaDefault}): " . $th->getMessage();
                $this->errorCount++;
            }
        }

        Log::info("Import selesai. Berhasil: {$this->successCount}, Gagal: {$this->errorCount}");
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}