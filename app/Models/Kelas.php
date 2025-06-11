<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $fillable = ['nama_kelas'];

    public function siswa()
    {
        return $this->hasMany(User::class, 'kelas', 'nama_kelas');
    }
}

