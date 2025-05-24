<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DaftarPengurus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DaftarPengurusSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Buat / ambil User superadmin
        $user = User::firstOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'user_id'  => Str::uuid()->toString(),
                'name'     => 'Super Admin',
                'password' => Hash::make('superadmin'),
                'role'     => User::ROLE_SUPERADMIN,
            ]
        );

        // 2) Buat / ambil record di daftar_pengurus
        DaftarPengurus::firstOrCreate(
            // Kunci unik untuk cek existing
            ['nip' => 'ADMIN001'],
            [
                // Semua kolom wajib diisi
                'daftar_pengurus_id' => Str::uuid()->toString(),
                'user_id'            => $user->user_id,
                'nama'               => 'Administrator Sekolah',
                'jenis_kelamin'      => 'Laki-laki',
                'agama'              => 'Islam',
                'nip'                => 'ADMIN001',
                'email'              => $user->email,               // wajib
                'nomor_handphone'    => '081234567890',            // wajib
                'tempat_tanggal_lahir'=> 'Jakarta, 1 Januari 1980', // wajib
                'alamat_rumah'       => 'Jl. Merdeka No.1',         // wajib
                'jabatan'            => 'Administrator',            // wajib
                'bidang_keahlian'    => 'Manajemen',               // wajib
                'pengurus'           => 'Admin',                   // wajib
                'akses_kelas'        => 'Semua',                   // wajib
                'status_kepegawaian' => 'PNS',                     // wajib
                'tanggal_bergabung'  => now()->toDateString(),     // wajib
            ]
        );
    }
}
