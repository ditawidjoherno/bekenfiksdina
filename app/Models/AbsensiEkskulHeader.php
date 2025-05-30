<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiEkskulHeader extends Model
{
    use HasFactory;

    protected $table = 'absensi_ekskul_headers'; // pastikan ini sesuai dengan nama tabel kamu
    protected $fillable = [
        'ekskul_id',
        'tanggal',
        'kegiatan',
        'mulai',
        'selesai'
    ];
}
