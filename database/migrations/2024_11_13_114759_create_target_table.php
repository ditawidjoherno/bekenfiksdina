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
        Schema::create('target_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('tahun');
            $table->json('target_kpi');
            $table->decimal('total_nilai_kpi', 20, 2)->default(0);
            $table->decimal('total_realisasi', 20, 2)->default(0);
            $table->decimal('total_target', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('target_tahunan');
    }
};
