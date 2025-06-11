<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'users'; // karena data siswa disimpan di tabel 'users'
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama',
        'role',
        'nisn',
        'kelas',
        'jenis_kelamin',
        'agama',
        'email',
        'tanggal_lahir',
        'nomor_hp',
        'foto_profil',
        'password',
    ];
}
