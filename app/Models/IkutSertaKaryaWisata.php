<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IkutSertaKaryaWisata extends Model
{
    protected $table = 'ikut_serta_karya_wisata';

    protected $fillable = [
        'kelas',
        'tanggal_kegiatan',
        'judul',
        'biaya',
        'batas_pendaftaran',
    ];
}

