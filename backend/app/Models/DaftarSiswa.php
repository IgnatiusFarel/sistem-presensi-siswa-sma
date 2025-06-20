<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'jenis_kelamin',
        'agama',
        'nis',
        'nisn',
        'email',
        'nomor_handphone',
        'tempat_tanggal_lahir',
        'alamat',
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

     protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->daftar_siswa_id)) {
                $model->daftar_siswa_id = (string) Str::uuid();
            }
        });
    }

      public function setNisAttribute($value)
    {
        $this->attributes['nis'] = trim((string) $value, '"');
    }

    public function setNisnAttribute($value)
    {
        $this->attributes['nisn'] = trim((string) $value, '"');
    }

    public function setNomorHandphoneAttribute($value)
    {
        $this->attributes['nomor_handphone'] = trim((string) $value, '"');
    }

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