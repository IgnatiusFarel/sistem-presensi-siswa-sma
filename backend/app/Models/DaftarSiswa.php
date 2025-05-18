<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarSiswa extends Model
{
    use HasFactory;

    protected $table = 'daftar_siswa';

    protected $fillable = [
        'user_id',
        'nama',
        'agama',
        'jenis_kelamin',
        'nis',
        'nisn',
        'email',
        'nomor_handphone',
        'tempat_tanggal_lahir',
        'alamat_rumah',
        'kelas',
        'nomor_absen',        
        'tanggal_bergabung'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function presensiSiswa()
    {
        return $this->hasMany(PresensiSiswa::class, 'daftar_siswa_id');
    }
}