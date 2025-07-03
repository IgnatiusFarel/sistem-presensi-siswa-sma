<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KomentarBerita extends Model
{
    use HasFactory;
    protected $table = 'komentar_berita';
    protected $primaryKey = 'komentar_berita_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'komentar_berita_id',
        'user_id',
        'daftar_berita_id',
        'komentar',
    ];

      protected static function booted()
    {
        static::creating(function ($model) {            
            if (empty($model->komentar_berita_id)) {
                $model->komentar_berita_id = (string) Str::uuid();
            }
        });
    }

    public function daftarBerita()
    {
        return $this->belongsTo(DaftarBerita::class, 'daftar_berita_id', 'daftar_berita_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
