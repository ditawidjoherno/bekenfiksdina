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
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabah')->onDelete('cascade');
            $table->enum('aktivitas', [
                'Tabungan', 'Depo Ritel', 'NTB - PBO', 'NOA BTN Move', 'Transaksi Teller',
                'Transaksi CRM', 'Operasional MKK', 'QRIS', 'EDC', 'Agen', 'Kuadran Agen',
                'NOA Payroll', 'VOA Payroll', 'NOA Pensiun', 'VOA Pensiun', 'VOA E-Batarapos',
                'NOA Giro', 'Akuisi Satker', 'CMS', 'Jumlah PKS PPO', 'DPK Lembaga'
            ]);
            $table->enum('tipe_nasabah', ['eksisting', 'baru']);
            $table->string('prospek');
            $table->integer('nominal_prospek');
            $table->integer('closing');
            $table->enum('status_aktivitas', ['selesai', 'ditunda']);
            $table->string('aktivitas_sales', ['belum diproses', 'diterima', 'ditolak'])->nullable();
            $table->string('keterangan_aktivitas')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};
