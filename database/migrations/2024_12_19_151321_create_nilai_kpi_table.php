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
        Schema::create('nilai_kpi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_tahunan_id')->constrained('target_tahunan')->onDelete('cascade');
            $table->enum('nama_kpi', [
                'Tabungan', 'Depo Ritel', 'NTB - PBO', 'NOA BTN Move', 'Transaksi Teller', 
                'Transaksi CRM', 'Operasional MKK', 'QRIS', 'EDC', 'Agen', 'Kuadran Agen', 
                'NOA Payroll', 'VOA Payroll', 'NOA Pensiun', 'VOA Pensiun', 'VOA E-Batarapos', 
                'NOA Giro', 'Akuisi Satker', 'CMS', 'Jumlah PKS PPO', 'DPK Lembaga'
            ]);
            $table->json('realisasi')->nullable()->comment('Data realisasi dalam format JSON');
            $table->json('pencapaian')->nullable()->comment('Data pencapaian dalam format JSON');
            $table->json('nilai_kpi')->nullable()->comment('Data nilai KPI dalam format JSON');
            $table->timestamps();
        });        
    }

    public function down()
    {
        Schema::dropIfExists('nilai_kpi');
    }
};
