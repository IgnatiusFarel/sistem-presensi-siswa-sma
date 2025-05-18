<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'presensi_siswa';
    protected $primaryKey = 'presensi_siswa_id';
    
    protected $fillable = [
        'presensi_id',
        'daftar_siswa_id',
        'user_id',
        'status',
        'waktu_presensi',
        'latitude',
        'longitude',
        'jenis_izin',
        'bukti_surat',
        'keterangan',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Relasi ke presensi
    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'presensi_id');
    }

    // Relasi ke siswa
    public function siswa()
    {
        return $this->belongsTo(DaftarSiswa::class, 'daftar_siswa_id');
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
