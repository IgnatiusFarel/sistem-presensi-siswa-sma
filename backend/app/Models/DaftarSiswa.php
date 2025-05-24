<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarSiswa extends Model
{
    use HasFactory;

    protected $table = 'daftar_siswa';
    protected $primaryKey = 'daftar_siswa_id';

    public $incrementing = false;
    protected $keyType = 'string';
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
        'daftar_kelas_id',
        'nama_kelas',
        'nomor_absen',
        'tanggal_bergabung'
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'jenis_kelamin' => 'string',
        'agama' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function kelas()
    {
        return $this->belongsTo(DaftarKelas::class, 'daftar_kelas_id', 'daftar_kelas_id');
    }

    public function presensiSiswa()
    {
        return $this->hasMany(PresensiSiswa::class, 'daftar_siswa_id', 'daftar_siswa_id');
    }
}