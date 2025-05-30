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
    Schema::table('informasi_umum', function (Blueprint $table) {
        $table->string('photo')->nullable()->after('author');
    });
}

public function down(): void
{
    Schema::table('informasi_umum', function (Blueprint $table) {
        $table->dropColumn('photo');
    });
}
};
