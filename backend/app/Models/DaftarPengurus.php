<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarPengurus extends Model
{
    use HasFactory;

    protected $table = 'daftar_pengurus';
    
    protected $fillable = [
        'user_id',
        'nama',
        'nip',
        'jenis_kelamin',
        'agama',
        'tempat_tanggal_lahir',
        'alamat_rumah',
        'email',
        'nomor_handphone',
        'jabatan', // 'administrator' untuk superadmin, 'guru', 'staff', dll
        'bidang_keahlian',        
        'pengurus',
        'akses_kelas',
        'status_kepegawaian',
        'tanggal_bergabung',        
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
