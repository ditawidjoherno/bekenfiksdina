<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTourGalleriesTable extends Migration
{
    public function up()
    {
        Schema::create('tour_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_tour_id');
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('study_tour_id')->references('id')->on('study_tour')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tour_galleries');
    }
}
