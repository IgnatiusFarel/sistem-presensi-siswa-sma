<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DaftarLaporan extends Model
{ 
    use HasFactory; 

    protected $table = 'daftar_laporan';
    protected $primaryKey = 'daftar_laporan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'daftar_laporan_id',
        'daftar_siswa_id',
        'jenis_perubahan',
        'upload_bukti',
        'keterangan',
    ];

       protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->daftar_laporan_id)) {
                $model->daftar_laporan_id = (string) Str::uuid();
            }
        });
    }

    public function siswa()
    {
        return $this->belongsTo(DaftarSiswa::class, 'daftar_siswa_id', 'daftar_siswa_id');
    }
}