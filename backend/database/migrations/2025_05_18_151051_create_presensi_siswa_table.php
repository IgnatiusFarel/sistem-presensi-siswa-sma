<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiSiswaTable extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_siswa', function (Blueprint $table) {
            $table->uuid('presensi_siswa_id')->primary();
            $table->uuid('presensi_id');
            $table->foreign('presensi_id')
                ->references('presensi_id')->on('presensi')
                ->onDelete('cascade');
            $table->uuid('daftar_siswa_id');
            $table->foreign('daftar_siswa_id')
                ->references('daftar_siswa_id')->on('daftar_siswa')
                ->onDelete('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->timestamp('waktu_presensi')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('jenis_izin')->nullable();
            $table->string('bukti_surat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['presensi_id', 'daftar_siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_siswa');
    }
}
;
