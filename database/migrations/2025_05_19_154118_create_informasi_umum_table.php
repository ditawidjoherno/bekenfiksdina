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
    Schema::create('informasi_umum', function (Blueprint $table) {
        $table->id();
        $table->date('date'); // format: YYYY-MM-DD
        $table->string('title');
        $table->text('text');
        $table->string('author');
        $table->string('time'); // bebas format, contoh: "17.30 pm"
        $table->string('color')->nullable(); // ex: bg-yellow-400
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_umum');
    }
};
