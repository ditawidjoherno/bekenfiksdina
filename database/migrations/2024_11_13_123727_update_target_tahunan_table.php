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
        Schema::table('target_tahunan', function (Blueprint $table) {
            // Mengubah tipe data kolom 'total_realisasi' menjadi DECIMAL
            $table->decimal('total_realisasi', 20, 2)->default(0)->change(); // 20 digit total, 2 digit desimal
            $table->decimal('total_nilai_kpi', 20, 2)->default(0)->change(); // Menambahkan default value 0
            $table->decimal('total_target', 20, 2)->default(0)->change(); // Jika kolom ini juga perlu diubah
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('target_tahunan', function (Blueprint $table) {
            // Mengembalikan tipe data menjadi INTEGER jika dibutuhkan
            $table->integer('total_realisasi')->change();
            $table->integer('total_nilai_kpi')->change();
            $table->integer('total_target')->change();
        });
    }
};
