<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DaftarPengurus extends Model
{
    use HasFactory;
    protected $table = 'daftar_pengurus';
    protected $primaryKey = 'daftar_pengurus_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public const JABATAN_ADMIN = 'Administrator';
    public const JABATAN_KEPSEK = 'Kepala Sekolah';
    public const JABATAN_WAKASEK = 'Wakil Kepala Sekolah';
    public const JABATAN_GURU = 'Guru';
    public const JABATAN_KALAB = 'Kepala Laboratorium';
    public const JABATAN_PUSTAKAWAN = 'Pustakawan';
    public const JABATAN_OPERATOR = 'Operator Sekolah';
    public const JABATAN_TU = 'Staf TU';
    public const JABATAN_SATPAM = 'Satpam';
    public const JABATAN_KEBERSIHAN = 'Petugas Kebersihan';

    public static function getAllJabatan()
    {
        return [
            self::JABATAN_ADMIN,
            self::JABATAN_KEPSEK,
            self::JABATAN_WAKASEK,
            self::JABATAN_GURU,
            self::JABATAN_KALAB,
            self::JABATAN_PUSTAKAWAN,
            self::JABATAN_OPERATOR,
            self::JABATAN_TU,
            self::JABATAN_SATPAM,
            self::JABATAN_KEBERSIHAN
        ];
    }

    public static function getAllStatus()
    {
        return [
            self::STATUS_PNS,
            self::STATUS_HONORER,
            self::STATUS_GTY,
            self::STATUS_PTY,
            self::STATUS_KONTRAK,
            self::STATUS_MAGANG,
            self::STATUS_PPPK,
            self::STATUS_OUTSOURCING
        ];
    }

    public const STATUS_PNS = 'PNS';
    public const STATUS_HONORER = 'Honorer';
    public const STATUS_GTY = 'GTY';
    public const STATUS_PTY = 'PTY';
    public const STATUS_KONTRAK = 'Kontrak';
    public const STATUS_MAGANG = 'Magang';
    public const STATUS_PPPK = 'PPPK';
    public const STATUS_OUTSOURCING = 'Outsourcing';

    protected $fillable = [
        'user_id',
        'nama',
        'jenis_kelamin',
        'agama',
        'tempat_tanggal_lahir',
        'alamat',
        'nip',
        'email',
        'nomor_handphone',
        'jabatan',
        'bidang_keahlian',
        'pengurus',
        'daftar_kelas_id',
        'akses_kelas',
        'status_kepegawaian',
        'tanggal_bergabung',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'jenis_kelamin' => 'string',
        'agama' => 'string',
        'jabatan' => 'string',
        'status_kepegawaian' => 'string',
        'akses_kelas' => 'array',
    ];

    protected static function booted() 
    {
        static::creating(function($model) {
            if (empty($model->daftar_pengurus_id)) {
                $model->daftar_pengurus_id = (string) Str::uuid();
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function waliKelas()
    {
        return $this->hasMany(DaftarKelas::class, 'wali_kelas', 'daftar_pengurus_id');
    }

       public function aksesKelas(): BelongsToMany
    {
        return $this->belongsToMany(DaftarKelas::class);     
    }
}
