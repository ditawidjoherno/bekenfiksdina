<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiEkskul extends Model
{
    protected $fillable = [
    'ekskul_id',
    'anggota_id',
    'tanggal',
    'status',
    'waktu_edit',
    'waktu_absen',
    'kegiatan',      // ⬅️ WAJIB ditambahkan
    'mulai',         // ⬅️ WAJIB ditambahkan
    'selesai',       // ⬅️ WAJIB ditambahkan
];


    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class);
    }

    public function anggota()
    {
        return $this->belongsTo(AnggotaEkskul::class, 'anggota_id');
    }
}
