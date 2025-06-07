<?php

namespace App\Models;

use App\Models\DaftarSiswa;
use App\Models\DaftarPengurus;
use App\Models\PresensiSiswa;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_SISWA = 'siswa';
    public const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';
    public const ROLE_WAKIL_KEPALA_SEKOLAH = 'wakil_kepala_sekolah';
    public const ROLE_GURU = 'guru';
    public const ROLE_KEPALA_LABORATORIUM = 'kepala_laboratorium';
    public const ROLE_PUSTAKAWAN = 'pustakawan';
    public const ROLE_OPERATOR_SEKOLAH = 'operator_sekolah';
    public const ROLE_STAF_TU = 'staf_tu';
    public const ROLE_SATPAM = 'satpam';
    public const ROLE_PETUGAS_KEBERSIHAN = 'petugas_kebersihan';

    public static function mapJabatanToRole(string $jabatan): string
    {
        return match ($jabatan) {
            'Administrator' => self::ROLE_SUPERADMIN,
            'Kepala Sekolah' => self::ROLE_KEPALA_SEKOLAH,
            'Wakil Kepala Sekolah' => self::ROLE_WAKIL_KEPALA_SEKOLAH,
            'Guru' => self::ROLE_GURU,
            'Kepala Laboratorium' => self::ROLE_KEPALA_LABORATORIUM,
            'Pustakawan' => self::ROLE_PUSTAKAWAN,
            'Operator Sekolah' => self::ROLE_OPERATOR_SEKOLAH,
            'Staf TU' => self::ROLE_STAF_TU,
            'Satpam' => self::ROLE_SATPAM,
            'Petugas Kebersihan' => self::ROLE_PETUGAS_KEBERSIHAN,
            default => self::ROLE_SISWA,
        };
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->user_id) {
                $model->user_id = (string) Str::uuid();
            }
        });
    }

    public function siswa()
    {
        return $this->hasOne(DaftarSiswa::class, 'user_id', 'user_id');
    }

    public function pengurus()
    {
        return $this->hasOne(DaftarPengurus::class, 'user_id', 'user_id');
    }

    public function presensiSiswa()
    {
        return $this->hasMany(PresensiSiswa::class, 'user_id', 'user_id');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }
}
