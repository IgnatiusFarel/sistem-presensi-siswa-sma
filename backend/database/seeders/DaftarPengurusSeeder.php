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
        $user = User::firstOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'user_id'  => Str::uuid()->toString(),
                'name'     => 'Super Admin',
                'password' => Hash::make('superadmin'),
                'role'     => User::ROLE_SUPERADMIN,
            ]
        );
        
        DaftarPengurus::firstOrCreate(            
            ['nip' => 'ADMIN001'],
            [                
                'daftar_pengurus_id' => Str::uuid()->toString(),
                'user_id'            => $user->user_id,
                'nama'               => 'Administrator Sekolah',
                'jenis_kelamin'      => 'Laki-laki',
                'agama'              => 'Islam',
                'nip'                => 'ADMIN001',
                'email'              => $user->email,               
                'nomor_handphone'    => '081234567890',            
                'tempat_tanggal_lahir'=> 'Jakarta, 1 Januari 1980',
                'alamat'       => 'Jl. Merdeka No.1',        
                'jabatan'            => 'Administrator',     
                'bidang_keahlian'    => 'Manajemen',         
                'pengurus'           => 'Admin',             
                'akses_kelas'        => 'Semua',             
                'status_kepegawaian' => 'PNS',               
                'tanggal_bergabung'  => now()->toDateString(),
            ]
        );
    }
}
