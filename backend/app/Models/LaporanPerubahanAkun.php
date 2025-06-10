<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaporanPerubahanAkun extends Model
{ 
    use HasFactory; 

    protected $table = 'laporan_perubahan_akun';
    protected $primaryKey = 'laporan_perubahan_akun_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'laporan_perubahan_akun_id',
        'daftar_siswa_id',
        'jenis_perubahan',
        'upload_bukti',
        'keterangan',
    ];

       protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->laporan_perubahan_akun_id)) {
                $model->laporan_perubahan_akun_id = (string) Str::uuid();
            }
        });
    }

    public function siswa()
    {
        return $this->belongsTo(DaftarSiswa::class, 'daftar_siswa_id', 'daftar_siswa_id');
    }
}