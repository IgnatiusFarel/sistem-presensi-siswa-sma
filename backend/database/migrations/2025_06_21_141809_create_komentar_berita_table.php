<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomentarBeritaTable extends Migration
{
    public function up(): void
    {
        Schema::create('komentar_berita', function (Blueprint $table) {
            $table->uuid('komentar_berita_id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('komentar_berita_id')->on('komentar_berita')->onDelete('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->uuid('daftar_berita_id');
            $table->foreign('daftar_berita_id')->references('daftar_berita_id')->on('daftar_berita')->onDelete('cascade');
            $table->text('komentar');
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('komentar_berita');
    }
};
