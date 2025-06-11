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
    Schema::table('absensi_karya_wisata', function (Blueprint $table) {
        $table->string('judul')->after('tanggal');
    });
}

public function down()
{
    Schema::table('absensi_karya_wisata', function (Blueprint $table) {
        $table->dropColumn('judul');
    });
}

};
