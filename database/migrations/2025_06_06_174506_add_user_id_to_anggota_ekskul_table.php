<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToAnggotaEkskulTable extends Migration
{
    public function up()
    {
        Schema::table('anggota_ekskul', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // Jika ingin relasi foreign key (opsional):
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('anggota_ekskul', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}
