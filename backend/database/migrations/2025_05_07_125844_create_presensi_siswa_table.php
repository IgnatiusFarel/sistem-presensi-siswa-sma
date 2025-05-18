<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiSiswaTable extends Migration
{
    public function up(): void
    {        
        Schema::create('presensi_siswa', function (Blueprint $table) {
            $table->id('presensi_siswa_id');
            $table->foreignId('presensi_id')
            ->constrained('presensi', 'presensi_id')
            ->onDelete('cascade');
            
        // Foreign key ke daftar_siswa
        $table->foreignId('daftar_siswa_id')
            ->constrained('daftar_siswa', 'daftar_siswa_id')
            ->onDelete('cascade');
            
        // Ubah referensi ke user_id
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')
              ->references('user_id')      // Referensi ke user_id
              ->on('users')
              ->onDelete('cascade');
              
            // Tambahkan status "terlambat" sesuai UI
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->timestamp('waktu_presensi')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('jenis_izin')->nullable();
            $table->string('bukti_surat')->nullable(); // Path ke file bukti
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Tambahkan unique constraint untuk mencegah duplikasi presensi
            $table->unique(['presensi_id', 'daftar_siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_siswa');    
    }
};
