<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetTahunan extends Model
{
    use HasFactory;

    protected $table = 'target_tahunan';

    protected $fillable = [
        'user_id',
        'tahun',
        'target_kpi',
        'total_nilai_kpi',
        'total_realisasi',
        'total_target',
    ];

    protected $casts = [
        'target_kpi' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function nilaiKpi()
{
    return $this->hasMany(NilaiKpi::class, 'target_tahunan_id');
}

}
