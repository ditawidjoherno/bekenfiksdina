<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToStudyToursTable extends Migration
{
    public function up()
    {
        Schema::table('study_tour', function (Blueprint $table) {
            $table->string('title')->nullable()->after('tanggal');
        });
    }

    public function down()
    {
        Schema::table('study_tour', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
}
