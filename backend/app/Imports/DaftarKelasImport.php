<?php

namespace App\Imports;

use App\Models\DaftarKelas;
use App\Models\DaftarPengurus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DaftarKelasImport implements ToCollection, WithHeadingRow
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        Log::info('Mulai import kelas, total baris: ' . $rows->count());

        foreach ($rows as $index => $row) {
            if ($row->filter()->isEmpty()) {
                Log::info("Baris " . ($index + 1) . " dilewati karena kosong.");
                continue;
            }

            $data = $row->toArray();
            Log::info("Proses baris " . ($index + 1) . ": " . json_encode($data));

            $validator = Validator::make($data, [
                'kode_kelas' => 'required|string|unique:daftar_kelas,kode_kelas',
                'nama_kelas' => 'required|string',
                'jurusan' => 'required|in:' . implode(',', [
                    DaftarKelas::JURUSAN_IPA,
                    DaftarKelas::JURUSAN_IPS,
                    DaftarKelas::JURUSAN_BAHASA,
                ]),
                'tingkat' => 'required|in:' . implode(',', [
                    DaftarKelas::TINGKAT_X,
                    DaftarKelas::TINGKAT_XI,
                    DaftarKelas::TINGKAT_XII,
                ]),
                'daftar_pengurus_id' => 'required|uuid|exists:daftar_pengurus,daftar_pengurus_id',
                'tahun_ajaran' => 'required|string',
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                $this->errorCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $index) {
                    $pengurus = DaftarPengurus::findOrFail($data['daftar_pengurus_id']);

                    DaftarKelas::create([
                        'kode_kelas' => $data['kode_kelas'],
                        'nama_kelas' => $data['nama_kelas'],
                        'jurusan' => $data['jurusan'],
                        'tingkat' => $data['tingkat'],
                        'daftar_pengurus_id' => $data['daftar_pengurus_id'],
                        'wali_kelas' => $pengurus->nama,
                        'tahun_ajaran' => $data['tahun_ajaran'],
                    ]);

                    Log::info("Kelas berhasil diimport: {$data['kode_kelas']}");
                    $this->successCount++;
                });
            } catch (\Exception $e) {
                Log::error("Gagal import baris " . ($index + 1) . ": " . $e->getMessage());
                $this->errors[] = "Baris " . ($index + 1) . " ({$data['kode_kelas']}): " . $e->getMessage();
                $this->errorCount++;
            }
        }

        Log::info("Import selesai. Sukses: {$this->successCount}, Gagal: {$this->errorCount}");
        if (!empty($this->errors)) {
            Log::error("Detail error: " . json_encode($this->errors));
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
