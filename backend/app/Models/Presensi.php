<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';
    protected $primaryKey = 'presensi_id';
    
    protected $fillable = [
        'tanggal',
        'jam_buka',
        'jam_tutup',
        'dibuka_pada',
        'ditutup_pada',
        'status',
        'dibuat_oleh',
        'keterangan'
    ];
    
    protected $casts = [
        'tanggal' => 'date',
        'jam_buka' => 'datetime',
        'jam_tutup' => 'datetime',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    // Relasi ke tabel users (pembuat presensi)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Relasi ke presensi siswa
    public function presensiSiswa()
    {
        return $this->hasMany(PresensiSiswa::class, 'presensi_id', 'presensi_id');
    }
    
    // Mendapatkan jumlah siswa hadir
    public function getJumlahHadirAttribute()
    {
        return $this->presensiSiswa()->where('status', 'hadir')->count();
    }
    
    // Mendapatkan jumlah siswa terlambat
    public function getJumlahTerlambatAttribute()
    {
        return $this->presensiSiswa()->where('status', 'terlambat')->count();
    }
    
    // Mendapatkan jumlah siswa izin dan sakit
    public function getJumlahIzinSakitAttribute()
    {
        return $this->presensiSiswa()
            ->whereIn('status', ['izin', 'sakit'])
            ->count();
    }
    
    // Mendapatkan jumlah siswa alpha
    public function getJumlahAlphaAttribute()
    {
        return $this->presensiSiswa()->where('status', 'alpha')->count();
    }
}
