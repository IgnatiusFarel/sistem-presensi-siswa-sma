<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiTable extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id('presensi_id');            
            $table->date('tanggal');
            $table->time('jam_buka')->nullable(); // Waktu pembukaan presensi
            $table->time('jam_tutup')->nullable(); // Waktu penutupan presensi
            $table->timestamp('dibuka_pada')->nullable(); // Timestamp kapan dibuka
            $table->timestamp('ditutup_pada')->nullable(); // Timestamp kapan ditutup
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->unsignedBigInteger('dibuat_oleh');
            $table->foreign('dibuat_oleh')
                  ->references('user_id')      // Referensi ke user_id
                  ->on('users')
                  ->onDelete('cascade');
            $table->string('keterangan')->nullable(); // Keterangan tambahan
            $table->timestamps();

            $table->unique(['tanggal', 'jam_buka']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
