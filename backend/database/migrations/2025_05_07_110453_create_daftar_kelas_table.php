<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarKelasTable extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_kelas', function (Blueprint $table) {
            $table->uuid('daftar_kelas_id')->primary();            
            $table->string('kode_kelas')->unique();
            $table->string('nama_kelas'); 
            $table->enum('jurusan', ['IPA', 'IPS', 'Bahasa']);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->uuid('daftar_pengurus_id')->nullable();            
            $table->string('wali_kelas');
            $table->string('tahun_ajaran');
            $table->integer('jumlah_siswa')->default(0); 
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('daftar_kelas');
    }
};
