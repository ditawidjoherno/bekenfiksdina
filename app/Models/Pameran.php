<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pameran extends Model
{
    use HasFactory;

    protected $table = 'pameran';

    protected $fillable = [
        'user_id',
        'kelas',
        'hari_kegiatan',
        'tanggal_kegiatan',
        'batas_pendaftaran',
        'status',
        'tanggal_daftar',
        'waktu_daftar',
        'biaya'
    ];

    // Hanya kolom tanggal/timestamp yang benar-benar tipe date/datetime di sini
    protected $dates = [
        'tanggal',
        'tanggal_daftar',
        'hari_kegiatan',
        'tanggal_kegiatan',
        'batas_pendaftaran',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    

}
