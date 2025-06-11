<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoKaryaWisata extends Model
{
    protected $table = 'info_karya_wisata';

    protected $fillable = [
        'title',
        'tanggal',
    ];
    
}
