<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_ekskuls', function (Blueprint $table) {
            $table->time('waktu_edit')->nullable()->change(); // ✅ ubah ke time
        });
    }

    public function down()
    {
        Schema::table('absensi_ekskuls', function (Blueprint $table) {
            $table->timestamp('waktu_edit')->nullable()->change(); // untuk rollback
        });
    }
};
