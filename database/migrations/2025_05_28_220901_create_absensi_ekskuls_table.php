<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('absensi_ekskuls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekskul_id')->constrained('ekskuls')->onDelete('cascade');
            $table->foreignId('anggota_id')->constrained('anggota_ekskul')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'tidak hadir', 'terlambat']);
            $table->timestamp('waktu_edit')->nullable();
            $table->timestamps();
            $table->unique(['ekskul_id', 'anggota_id', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('absensi_ekskuls');
    }
};
