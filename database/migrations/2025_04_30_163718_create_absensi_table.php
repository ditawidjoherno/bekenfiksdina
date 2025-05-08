<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->string('kelas', 20);
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('mulai');
            $table->time('selesai'); // Akan diisi otomatis di event saving jika mau
            $table->integer('nomor');
            $table->foreignId('siswa_id')->constrained('users')->onDelete('cascade');
            $table->boolean('hadir')->default(false);
            $table->boolean('tidak_hadir')->default(false);
            $table->boolean('terlambat')->default(false);
            $table->timestamp('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
