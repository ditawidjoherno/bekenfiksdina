<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('study_tour', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // FK ke tabel users
            $table->string('kelas', 20);
            $table->string('hari', 50);
            $table->date('tanggal');
            $table->string('tujuan', 255);         // Tempat tujuan
            $table->integer('biaya');       // Biaya (misal: 150000.00)
            $table->string('mulai', 20);
            $table->string('selesai', 20);
            $table->enum('status', ['Daftar', 'Tidak Daftar']);
            $table->time('waktu_daftar');
            $table->timestamps();
            $table->date('tanggal_daftar');  
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_tour');
    }
};