<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiTable extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->uuid('presensi_id')->primary();            
            $table->date('tanggal');
            $table->time('jam_buka'); 
            $table->time('jam_tutup'); 
            $table->timestamp('dibuka_pada')->nullable(); 
            $table->timestamp('ditutup_pada')->nullable(); 
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->uuid('dibuat_oleh');
            $table->foreign('dibuat_oleh')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('keterangan')->nullable(); 
            $table->timestamps();

            $table->unique(['tanggal', 'jam_buka']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
