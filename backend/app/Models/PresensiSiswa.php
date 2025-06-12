<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class PresensiSiswa extends Model
{
    use HasFactory;
    protected $table = 'presensi_siswa';
    protected $primaryKey = 'presensi_siswa_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public const STATUS_HADIR = 'hadir';
    public const STATUS_IZIN = 'izin';
    public const STATUS_SAKIT = 'sakit';
    public const STATUS_ALPHA = 'alpha';

    protected $fillable = [
        'presensi_id',
        'daftar_siswa_id',
        'user_id',
        'status',
        'waktu_presensi',
        'latitude',
        'longitude',
        'lokasi', 
        'jenis_kegiatan',
        'upload_bukti',
        'keterangan',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'status' => 'string',
    ];

       protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->presensi_siswa_id)) {
                $model->presensi_siswa_id = (string) Str::uuid();
            }
        });
    }

    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'presensi_id');
    }

    public function siswa()
    {
        return $this->belongsTo(DaftarSiswa::class, 'daftar_siswa_id', 'daftar_siswa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
