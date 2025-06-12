<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarSiswaTable extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_siswa', function (Blueprint $table) {
            $table->uuid('daftar_siswa_id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']); 
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
            $table->string('tempat_tanggal_lahir'); 
            $table->text('alamat');
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->string('email')->unique();
            $table->string('nomor_handphone')->unique();
            $table->uuid('daftar_kelas_id')->nullable();            
            $table->string('nama_kelas');
            $table->integer('nomor_absen');
            $table->date('tanggal_bergabung');            
            $table->timestamps();
        });
    }
   
    public function down(): void
    {
        Schema::dropIfExists('daftar_siswa');
    }
};