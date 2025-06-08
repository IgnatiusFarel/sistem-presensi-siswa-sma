<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DaftarKelas extends Model
{
    use HasFactory;
    protected $table = 'daftar_kelas';
    protected $primaryKey = 'daftar_kelas_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public const JURUSAN_IPA = 'IPA';
    public const JURUSAN_IPS = 'IPS';
    public const JURUSAN_BAHASA = 'Bahasa';
    public const TINGKAT_X = 'X';
    public const TINGKAT_XI = 'XI';
    public const TINGKAT_XII = 'XII';

    protected $fillable = [
        'kode_kelas',
        'nama_kelas',
        'jurusan',
        'tingkat',
        'daftar_pengurus_id',
        'wali_kelas',
        'tahun_ajaran',        
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->daftar_kelas_id)) {
                $model->daftar_kelas_id = (string) Str::uuid();
            }
        });
    }

    public function waliKelas()
    {
        return $this->belongsTo(DaftarPengurus::class, 'daftar_pengurus_id', 'daftar_pengurus_id')
            ->select('daftar_pengurus_id', 'nama');
    }
    
    public function siswa()
    {
        return $this->hasMany(DaftarSiswa::class, 'daftar_kelas_id', 'daftar_kelas_id');
    }

    public function getJumlahSiswa()
    {
        $this->jumlah_siswa = $this->siswa()->count();
        $this->save();
    }
}
