<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiKpi extends Model
{
    use HasFactory;

    protected $table = 'nilai_kpi';

    protected $fillable = [
        'target_tahunan_id', 'nama_kpi', 'realisasi', 'pencapaian', 'nilai_kpi'
    ];

    protected $casts = [
        'realisasi' => 'array',
        'pencapaian' => 'array',
        'nilai_kpi' => 'array',
    ];

    public function targetTahunan()
    {
        return $this->belongsTo(TargetTahunan::class, 'target_tahunan_id');
    }
}
