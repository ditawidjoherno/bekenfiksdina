<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('info_karya_wisata', function (Blueprint $table) {
            $table->id();
            $table->string('title');        // Judul acara
            $table->date('tanggal');        // Tanggal kegiatan
            $table->timestamps();           // created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_karya_wisata');
    }
};
