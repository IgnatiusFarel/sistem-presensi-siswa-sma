<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaftarPengurusTable extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_pengurus', function (Blueprint $table) {
            $table->uuid('daftar_pengurus_id')->primary();
            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('nama');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
            $table->string('nip')->unique();
            $table->string('email')->unique();
            $table->string('nomor_handphone');
            $table->string('tempat_tanggal_lahir');
            $table->text('alamat');        
            $table->enum('jabatan', [
                'Administrator', 
                'Kepala Sekolah', 
                'Wakil Kepala Sekolah',
                'Guru',
                'Kepala Laboratorium',
                'Pustakawan',
                'Operator Sekolah',
                'Staf TU',
                'Satpam',
                'Petugas Kebersihan'
            ]);            
            $table->string('bidang_keahlian');
            $table->string('pengurus');
            $table->uuid('daftar_kelas_id')->nullable();            
            $table->json('akses_kelas')->nullable();            
            $table->enum('status_kepegawaian', [
                'PNS',
                'Honorer',
                'GTY',
                'PTY',
                'Kontrak',
                'Magang',
                'PPPK',
                'Outsourcing'
            ]);
            $table->date('tanggal_bergabung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_pengurus');
    }
};
