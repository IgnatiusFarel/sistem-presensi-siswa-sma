<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\DaftarPengurus;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run()
{
    $user = User::create([
        'name' => 'Administrator',
        'email' => 'superadmin@mail.com',
        'password' => Hash::make('superadmin'),
        'role' => 'superadmin',
    ]);

    DaftarPengurus::create([
        'user_id' => $user->id,
        'nama' => 'Administrator Sekolah',
        'nip' => 'ADMIN001',
        // Isi field lain yang diperlukan
        'jabatan' => 'Administrator',
        'jenis_kelamin' => 'Laki-laki',
        'agama' => 'Islam',
        'email' => 'admin@sekolah.id',
        'nomor_handphone' => '08123456789',
        'tempat_tanggal_lahir' => 'Jakarta, 1 Januari 1990',
        'alamat_rumah' => 'Jl. Pendidikan No. 1',
        'bidang_keahlian' => 'Administrasi',
        'pengurus' => 'Admin',
        'akses_kelas' => 'Semua',
        'status_kepegawaian' => 'PTY',
        'tanggal_bergabung' => now(),
    ]);
}
}
