<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DaftarSiswa;
use App\Models\DaftarKelas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DaftarSiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelasIds = DaftarKelas::pluck('daftar_kelas_id')->toArray();
        $sex      = ['Laki-laki','Perempuan'];

        for ($i = 1; $i <= 30; $i++) {
            $nama  = "Siswa{$i} Test";
            $email = "siswa{$i}@sekolah.test";
            $user  = User::create([
                'user_id'  => (string) Str::uuid(),
                'name'     => $nama,
                'email'    => $email,
                'password' => Hash::make('password'),
                'role'     => User::ROLE_SISWA,
            ]);

            // Pilih kelas dan ambil nama_kelas-nya
            $selectedKelasId = $kelasIds[array_rand($kelasIds)];
            $kelasRecord     = DaftarKelas::find($selectedKelasId);

            DaftarSiswa::create([
                'daftar_siswa_id'     => (string) Str::uuid(),
                'user_id'             => $user->user_id,
                'nama'                => $nama,
                'nis'                 => 'NIS'.sprintf('%04d',$i),
                'nisn'                => 'NISN'.sprintf('%06d',$i),
                'jenis_kelamin'       => $sex[$i % 2],
                'tempat_tanggal_lahir'=> 'Kota Test, '.now()->subYears(15)->format('d F Y'),
                'agama'               => 'Islam',
                'alamat'              => 'Jl. Siswa No.'.($i),
                'nomor_handphone'     => '082'.rand(1000000,9999999),
                'email'               => $email,
                'daftar_kelas_id'     => $selectedKelasId,
                'nama_kelas'          => $kelasRecord->nama_kelas,    // <- wajib
                'nomor_absen'         => $i,
                'tanggal_bergabung'   => now()->subYears(1),
            ]);
        }
    }
}
