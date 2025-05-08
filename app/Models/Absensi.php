<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'kelas', 'hari', 'mulai', 'selesai', 'nomor', 'siswa_id',
        'hadir', 'tidak_hadir', 'terlambat', 'waktu',
    ];

    public $timestamps = false;

    // Tambahkan relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Atur agar 'selesai' otomatis diisi 10 menit setelah 'mulai'
    protected static function booted()
    {
        static::saving(function ($absensi) {
            if ($absensi->mulai && !$absensi->selesai) {
                $absensi->selesai = date('H:i:s', strtotime($absensi->mulai) + 600); // 600 detik = 10 menit
            }
        });
    }
}
