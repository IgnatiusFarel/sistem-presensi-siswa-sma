<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    // Pastikan incrementing dan type sesuai
    public $incrementing = true;
    protected $keyType = 'int';

   protected $fillable = [
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

   public function siswa()
   {
       return $this->hasOne(DaftarSiswa::class, 'user_id'); 
   }

   public function pengurus() 
   {
    return $this->hasOne(DaftarPengurus::class);
   }

   public function presensiSiswa()
   {
    return $this->hasMany(PresensiSiswa::class);
   }
}
