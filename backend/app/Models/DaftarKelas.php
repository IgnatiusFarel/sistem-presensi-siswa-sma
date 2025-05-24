<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarKelas extends Model
{
    use HasFactory;

    protected $table = 'daftar_kelas';
    protected $primaryKey = 'daftar_kelas_id'; 
    public    $incrementing = false;
    protected $keyType      = 'string';
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
        'wali_kelas',
        'tahun_ajaran',
        'jumlah_siswa'
    ];

    public function waliKelas()
    {
        return $this->belongsTo(DaftarPengurus::class, 'wali_kelas', 'daftar_pengurus_id')->select('daftar_pengurus_id', 'nama');
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

    // protected static function booted() 
    // {
    //     DaftarSiswa::created(function($s) {
    //         DaftarKelas::where('daftar_kelas_id', $s->daftar_kelas_id)->increment('jumlah_siswa');
    //     }); 
        
    //     DaftarSiswa::deleted(function($s))
    // }
}
