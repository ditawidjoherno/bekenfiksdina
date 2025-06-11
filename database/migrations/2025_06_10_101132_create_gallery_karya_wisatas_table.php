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
    Schema::create('gallery_karya_wisatas', function (Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->date('tanggal');
        $table->text('url'); // tempat simpan URL gambar
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_karya_wisatas');
    }
};
