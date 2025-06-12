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
            $table->date('tanggal')->unique();
            $table->time('jam_buka'); 
            $table->time('jam_tutup');             
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('dibuat_oleh');
            $table->timestamps();            
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
