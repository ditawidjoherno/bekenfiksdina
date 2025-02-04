<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $table = 'target_tahunan';
    
    protected $fillable = ['staff_id', 'nama_kpi', 'bobot_penilaian', 'indikator', 'target'];

    protected $casts = [
        'target' => 'json',
    ];


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'nip');
    }
}
