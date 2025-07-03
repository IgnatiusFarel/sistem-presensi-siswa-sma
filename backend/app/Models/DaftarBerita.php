<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class DaftarBerita extends Model
{
    use HasFactory;
    protected $table = 'daftar_berita';
    protected $primaryKey = 'daftar_berita_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'daftar_berita_id',
        'slug',
        'judul',
        'thumbnail',
        'kategori',
        'konten',
        'user_id',
        'dibuat_oleh',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->daftar_berita_id)) {
                $model->daftar_berita_id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getPreviewAttribute()
    {
        return Str::limit(strip_tags($this->konten), 150);
    }
    
    public function komentar()
    {
        return $this->hasMany(KomentarBerita::class, 'daftar_berita_id', 'daftar_berita_id');
    }
}
