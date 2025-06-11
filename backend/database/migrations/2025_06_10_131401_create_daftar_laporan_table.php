<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarLaporanTable extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_laporan', function (Blueprint $table) {
            $table->uuid('daftar_laporan_id');
            $table->uuid('daftar_siswa_id');
            $table->foreign('daftar_siswa_id')->references('daftar_siswa_id')->on('daftar_siswa')->onDelete('cascade');
            $table->enum('jenis_perubahan', ['Email', 'Password']);
            $table->string('upload_bukti');
            $table->text('keterangan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_perubahan_akun');
    }
};
