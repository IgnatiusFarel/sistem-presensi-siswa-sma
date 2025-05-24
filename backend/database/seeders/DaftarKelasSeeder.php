<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DaftarKelas;
use App\Models\DaftarPengurus;
use Illuminate\Support\Str;

class DaftarKelasSeeder extends Seeder
{
    public function run(): void
    {        
        $waliIds = DaftarPengurus::pluck('daftar_pengurus_id')->toArray();

        $jurusan = ['IPA','IPS','Bahasa'];
        $tingkat = ['X','XI','XII'];

        for ($i = 0; $i < 5; $i++) {
        foreach ($jurusan as $j) {
            foreach ($tingkat as $t) {
                DaftarKelas::create([
                    'daftar_kelas_id' => (string) Str::uuid(),
                    'kode_kelas'      => "{$t}-{$j}-".Str::random(4),
                    'nama_kelas'      => "{$t} {$j} ".Str::random(2),
                    'jurusan'         => $j,
                    'tingkat'         => $t,
                    'wali_kelas'      => $waliIds[array_rand($waliIds)],
                    'tahun_ajaran'    => '2024/2025',               
                ]);
            }
        }
    }
}
}
