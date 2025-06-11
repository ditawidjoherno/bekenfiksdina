<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi'; // nama tabel

// app/Models/Absensi.php
protected $fillable = [
    'user_id', 'kelas', 'hari', 'tanggal', 'mulai', 'selesai', 'status', 'waktu_absen'
];


    // Jika kamu ingin memformat tanggal otomatis,
    // bisa tambahkan properti $dates seperti ini
    protected $dates = [
        'tanggal',
        'mulai',
        'selesai',
        'waktu_absen',
    ];

    // Jika ada relasi ke user (misalnya siswa),
    // kamu bisa buat relasi seperti ini:
    public function siswa()
    {
        return $this->belongsTo(User::class, 'nisn', 'nisn');
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}