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
    Schema::create('ikut_serta_karya_wisata', function (Blueprint $table) {
        $table->id();
        $table->string('kelas');
        $table->date('tanggal_kegiatan'); // dari info_karya_wisata
        $table->string('judul'); // Karya Wisata
        $table->string('biaya');
        $table->date('batas_pendaftaran'); // endDate
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ikut_serta_karya_wisata');
    }
};
