<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', [
                'superadmin',
                'kepala_sekolah',
                'wakil_kepala_sekolah',
                'guru',
                'kepala_laboratorium',
                'pustakawan',
                'operator_sekolah',
                'staf_tu',
                'satpam',
                'petugas_kebersihan',
                'siswa'
            ])->default('siswa');
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
