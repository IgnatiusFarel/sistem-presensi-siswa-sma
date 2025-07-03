<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Class CreateDaftarBeritaTable extends Migration
{   
    public function up(): void
    {
        Schema::create('daftar_berita', function (Blueprint $table) {
            $table->uuid('daftar_berita_id')->primary();
            $table->string('slug')->unique();
            $table->string('judul');
            $table->string('thumbnail')->nullable();
            $table->enum('kategori', ['Pengumuman', 'Kegiatan', 'Prestasi', 'Informasi', 'Agenda', 'Lainnya'])->default('Informasi');
            $table->longText('konten');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');            
            $table->string('dibuat_oleh');
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('daftar_berita');
    }
};
