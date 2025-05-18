<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarSiswaTable extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_siswa', function (Blueprint $table) {
            $table->id('daftar_siswa_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                  ->references('user_id')      // Referensi ke user_id
                  ->on('users')
                  ->onDelete('cascade');
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']); 
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
            $table->string('nis')->unique();
            $table->string('nisn')->unique();
            $table->string('email')->unique();
            $table->string('nomor_handphone');
            $table->string('tempat_tanggal_lahir'); 
            $table->text('alamat_rumah');
            $table->string('kelas');
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