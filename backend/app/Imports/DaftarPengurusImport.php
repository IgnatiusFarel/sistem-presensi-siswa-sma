<?php

namespace App\Imports;

use App\Models\DaftarPengurus;
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

class DaftarPengurusImport implements ToCollection, WithHeadingRow
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        \Log::info('Mulai import pengurus dengan ' . $rows->count() . ' baris data');

        foreach ($rows as $index => $row) {
            \Log::info("Processing row " . ($index + 1) . ": " . json_encode($row->toArray()));

            if ($row->filter()->isEmpty()) {
                \Log::info("Skipping empty row " . ($index + 1));
                continue;
            }

            if (!empty($row['tanggal_bergabung'])) {
                try {
                    $value = $row['tanggal_bergabung'];
                    if (is_numeric($value)) {
                        $row['tanggal_bergabung'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } else {
                        $row['tanggal_bergabung'] = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                    }
                    \Log::info("Tanggal bergabung berhasil dikonversi: " . $row['tanggal_bergabung']);
                } catch (\Exception $e) {
                    \Log::error("Format tanggal salah di baris " . ($index + 1) . ": " . $e->getMessage());
                    $this->errors[] = "Baris " . ($index + 1) . ": Format tanggal salah";
                    $this->errorCount++;
                    continue;
                }
            }

            $data = $row->toArray();

            // Konversi NIP dan nomor HP ke string
            if (isset($data['nip'])) {
                $data['nip'] = (string) $data['nip'];
            }
            if (isset($data['nomor_handphone'])) {
                $data['nomor_handphone'] = (string) $data['nomor_handphone'];
            }

            \Log::info("Data untuk validasi: " . json_encode($data));

            $validator = Validator::make($data, [
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
                'akses_kelas' => 'nullable|string',
                'tanggal_bergabung' => 'required|date',
                'password' => 'nullable|string|min:8',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                $this->errorCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $index) {
                    $user = null;

                    if ($data['jabatan'] === DaftarPengurus::JABATAN_ADMIN) {
                        $adminValidator = Validator::make($data, [
                            'email' => 'unique:users,email',
                            'password' => 'required|string|min:8',
                        ]);

                        if ($adminValidator->fails()) {
                            throw new \Exception("Administrator: " . implode(', ', $adminValidator->errors()->all()));
                        }

                        $user = User::create([
                            'name' => $data['nama'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password']),
                            'role' => 'superadmin',
                        ]);

                        \Log::info("User administrator berhasil dibuat untuk {$data['nama']}");
                    }

                    DaftarPengurus::create([
                        'user_id' => $user?->user_id,
                        'nama' => $data['nama'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'agama' => $data['agama'],
                        'tempat_tanggal_lahir' => $data['tempat_tanggal_lahir'],
                        'alamat' => $data['alamat'],
                        'nip' => $data['nip'],
                        'email' => $data['email'],
                        'nomor_handphone' => $data['nomor_handphone'],
                        'jabatan' => $data['jabatan'],
                        'bidang_keahlian' => $data['bidang_keahlian'],
                        'pengurus' => $data['pengurus'],
                        'status_kepegawaian' => $data['status_kepegawaian'],
                        'akses_kelas' => $data['akses_kelas'] ?? null,
                        'tanggal_bergabung' => $data['tanggal_bergabung'],
                    ]);

                    \Log::info("Pengurus berhasil diimport: {$data['nama']}");
                    $this->successCount++;
                });
            } catch (\Exception $e) {
                \Log::error("Gagal import baris " . ($index + 1) . ": {$data['nama']} | " . $e->getMessage());
                $this->errors[] = "Baris " . ($index + 1) . " ({$data['nama']}): " . $e->getMessage();
                $this->errorCount++;
            }
        }

        \Log::info("Import selesai. Berhasil: {$this->successCount}, Gagal: {$this->errorCount}");
        if (!empty($this->errors)) {
            \Log::error("Detail kesalahan: " . json_encode($this->errors));
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
