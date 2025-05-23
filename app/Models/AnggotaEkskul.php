<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaEkskul extends Model
{
    protected $fillable = ['nama', 'nisn', 'kelas', 'status', 'ekskul_id'];

    protected $table = 'anggota_ekskul'; // ← tambahkan ini!
}
