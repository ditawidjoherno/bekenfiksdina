<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailEkskulTable extends Migration
{
    public function up()
    {
        Schema::create('detail_ekskul', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ekskul_id');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('anggota_user_id')->nullable();
            $table->text('informasi_ekskul')->nullable();
            $table->text('capaian_prestasi')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('ekskul_id')->references('id')->on('ekskul')->onDelete('cascade');
            $table->foreign('anggota_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_ekskul');
    }
}
