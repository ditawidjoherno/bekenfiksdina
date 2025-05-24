<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('informasi_ekskuls', function (Blueprint $table) {
        $table->foreignId('ekskul_id')->constrained('ekskuls')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('informasi_ekskuls', function (Blueprint $table) {
        $table->dropForeign(['ekskul_id']);
        $table->dropColumn('ekskul_id');
    });
    }
};
