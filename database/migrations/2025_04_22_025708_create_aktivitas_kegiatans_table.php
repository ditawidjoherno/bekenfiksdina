<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aktivitas_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->string('kategori');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('tipe');
            $table->string('foto')->nullable(); // untuk menyimpan path foto
            $table->integer('total_days_left')->nullable(); // dapat dihitung secara dinamis juga
            $table->json('participants')->nullable(); // bisa simpan array ID siswa dari tabel users
            $table->unsignedBigInteger('penanggung_jawab_id'); // relasi ke tabel users (role guru)
            
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('penanggung_jawab_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_kegiatans');
    }
};
