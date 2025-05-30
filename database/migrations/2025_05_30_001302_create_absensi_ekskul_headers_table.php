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
    Schema::create('absensi_ekskul_headers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('ekskul_id');
        $table->date('tanggal');
        $table->string('kegiatan');
        $table->string('mulai');
        $table->string('selesai');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_ekskul_headers');
    }
};
