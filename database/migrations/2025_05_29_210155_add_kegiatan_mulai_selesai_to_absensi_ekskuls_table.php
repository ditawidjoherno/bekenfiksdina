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
    Schema::table('absensi_ekskuls', function (Blueprint $table) {
        $table->string('kegiatan')->nullable();
        $table->string('mulai')->nullable();
        $table->string('selesai')->nullable();
    });
}

public function down()
{
    Schema::table('absensi_ekskuls', function (Blueprint $table) {
        $table->dropColumn(['kegiatan', 'mulai', 'selesai']);
    });
}

};
