<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    protected $table = 'ekskul';

    protected $fillable = [
        'nama_ekskul',
        'penanggung_jawab',
        'cover_gambar',
    ];

    public function detail()
    {
        return $this->hasMany(DetailEkskul::class, 'ekskul_id');
    }
}
