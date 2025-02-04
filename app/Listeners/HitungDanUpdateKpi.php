<?php

namespace App\Listeners;

use App\Events\AktivitasDitambahkan;
use App\Models\TargetTahunan;
use App\Models\NilaiKpi;
use Illuminate\Support\Facades\Log;

class HitungDanUpdateKpi
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\AktivitasDitambahkan  $event
     * @return void
     */
    public function handle(AktivitasDitambahkan $event)
    {
        $userId = $event->aktivitas->created_by;
        $tahun = date('Y');

        $targetTahunan = TargetTahunan::where('user_id', $userId)
                                      ->where('tahun', $tahun)
                                      ->first();

        if (!$targetTahunan) {
            Log::warning('Target tahunan tidak ditemukan.', [
                'user_id' => $userId,
                'tahun' => $tahun
            ]);
            return;
        }

        $namaKpi = $event->aktivitas->aktivitas;
        $targetKpi = json_decode($targetTahunan->target_kpi, true);

        $kpi = collect($targetKpi)->firstWhere('nama_kpi', $namaKpi);

        if ($kpi) {
            $bobotPenilaian = $kpi['bobot_penilaian'];
            $targetBulanan = json_decode($kpi['target'], true);

            $bulan = date('n');
            $realisasi = $event->aktivitas->closing;
            $totalRealisasi = $event->aktivitas->where('aktivitas', $namaKpi)->sum('closing');
            $targetBulanIni = $targetBulanan[$bulan - 1] ?? 0;

            // Pencapaian dalam bentuk persen
            $pencapaian = $targetBulanIni > 0 
                ? round(($totalRealisasi / $targetBulanIni) * 100, 1) 
                : 0;

            // Nilai KPI dalam bentuk persen sesuai dengan bobot penilaian
            $nilaiKpi = $targetBulanIni > 0 
                ? round(($totalRealisasi / $targetBulanIni) * $bobotPenilaian, 1) 
                : 0;

            // Menyimpan atau memperbarui nilai KPI di database
            $nilaiKpiRecord = NilaiKpi::updateOrCreate(
                [
                    'target_tahunan_id' => $targetTahunan->id,
                    'nama_kpi' => $namaKpi
                ],
                [
                    'realisasi' => $this->updateMonthlyData($bulan, $totalRealisasi, $targetTahunan->nilaiKpi, 'realisasi'),
                    'pencapaian' => $this->updateMonthlyData($bulan, $pencapaian, $targetTahunan->nilaiKpi, 'pencapaian'),
                    'nilai_kpi' => $this->updateMonthlyData($bulan, $nilaiKpi, $targetTahunan->nilaiKpi, 'nilai_kpi')
                ]
            );

            Log::info('Nilai KPI terupdate untuk aktivitas.', [
                'aktivitas_id' => $event->aktivitas->id,
                'nilai_kpi' => $nilaiKpiRecord
            ]);
        } else {
            Log::warning('Nama KPI tidak ditemukan di target tahunan.', [
                'nama_kpi' => $namaKpi,
                'user_id' => $userId,
                'tahun' => $tahun
            ]);
        }
    }

/**
 * Update nilai per bulan (menambahkan nilai untuk bulan yang sesuai)
 * 
 * @param  int $bulan
 * @param  mixed $value
 * @param  object $nilaiKpi
 * @param  string $field
 * @return string
 */
private function updateMonthlyData($bulan, $value, $nilaiKpi, $field)
{
    // Inisialisasi data bulan dengan nilai 0
    $data = array_fill(0, 12, 0);  // Membuat array dengan 12 elemen yang berisi 0

    // Update bulan yang sesuai dengan nilai yang dihitung (bulan index 0-11)
    $data[$bulan - 1] = round($value, 1);

    // Jika field adalah 'realisasi', 'pencapaian', atau 'nilai_kpi', pastikan kita menyimpan data yang sesuai
    if ($field == 'realisasi') {
        $nilaiKpi->$field = json_encode($data);
    } elseif ($field == 'pencapaian') {
        $nilaiKpi->$field = json_encode($data);
    } elseif ($field == 'nilai_kpi') {
        $nilaiKpi->$field = json_encode($data);
    }

    return json_encode($data);
}

}
