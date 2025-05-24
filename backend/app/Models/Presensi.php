<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';
    protected $primaryKey = 'presensi_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_SELESAI = 'selesai';

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
        'jam_buka' => 'time',
        'jam_tutup' => 'time',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
        'status' => 'string',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'user_id');
    }

    public function presensiSiswa()
    {
        return $this->hasMany(PresensiSiswa::class, 'presensi_id', 'presensi_id');
    }

    public function getJumlahHadirSiswa()
    {
        return $this->presensiSiswa()->where('status', 'hadir')->count();
    }

    public function getJumlahIzinSiswa()
    {
        return $this->presensiSiswa()->where('status', 'izin')->count();
    }

    public function getJumlahSakitSiswa()
    {
        return $this->presensiSiswa()->where('status', 'sakit')->count();
    }

    public function getJumlahAlphaSiswa()
    {
        return $this->presensiSiswa()->where('status', 'alpha')->count();
    }
}
