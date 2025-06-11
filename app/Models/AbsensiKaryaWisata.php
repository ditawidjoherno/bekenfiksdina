<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiKaryaWisata extends Model
{
    // ✅ Tambahkan baris ini
    protected $table = 'absensi_karya_wisata';

    protected $fillable = [
        'user_id',
        'kelas',
        'status',
        'waktu',
        'tanggal',
         'judul',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
