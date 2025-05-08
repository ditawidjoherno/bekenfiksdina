<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailEkskul extends Model
{
    use HasFactory;

    protected $table = 'detail_ekskul';

    protected $fillable = [
        'ekskul_id',
        'deskripsi',
        'anggota_user_id',
        'informasi_ekskul',
        'capaian_prestasi',
    ];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'anggota_user_id');
    }
}
