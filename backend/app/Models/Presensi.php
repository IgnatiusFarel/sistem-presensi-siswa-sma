<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'status',
        'dibuat_oleh',        
        'user_id',
    ];
    protected $casts = [
        'tanggal' => 'date',
        'jam_buka' => 'string',
        'jam_tutup' => 'string',        
        'status' => 'string',
    ];

      protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->presensi_id)) {
                $model->presensi_id = (string) Str::uuid();
            }
        });
    }

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
