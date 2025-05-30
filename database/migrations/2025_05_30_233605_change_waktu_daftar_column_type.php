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
    Schema::table('study_tour', function (Blueprint $table) {
        $table->dateTime('waktu_daftar')->change(); // atau $table->date() jika hanya ingin tanggal
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_tour', function (Blueprint $table) {
            //
        });
    }
};
