<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeysAfterInitialCreation extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_kelas', function (Blueprint $table) {
            $table->foreign('daftar_pengurus_id')
                  ->references('daftar_pengurus_id')
                  ->on('daftar_pengurus')
                  ->onDelete('set null'); 
        });

        Schema::table('daftar_pengurus', function (Blueprint $table) {
            $table->foreign('daftar_kelas_id')
                  ->references('daftar_kelas_id')
                  ->on('daftar_kelas')
                  ->onDelete('set null');
        });

        Schema::table('daftar_siswa', function (Blueprint $table) {
            $table->foreign('daftar_kelas_id')
                  ->references('daftar_kelas_id')
                  ->on('daftar_kelas')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('daftar_kelas', function (Blueprint $table) {
            $table->dropForeign(['daftar_pengurus_id']);
        });

        Schema::table('daftar_pengurus', function (Blueprint $table) {
            $table->dropForeign(['daftar_kelas_id']);
        });

        Schema::table('daftar_siswa', function (Blueprint $table) {
            $table->dropForeign(['daftar_kelas_id']);
        });
    }
}

