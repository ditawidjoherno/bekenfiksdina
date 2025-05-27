<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyTour extends Model
{
    use HasFactory;

    protected $table = 'study_tour';

    protected $fillable = [
        'user_id',
        'kelas',
        'hari',
        'tanggal',
        'mulai',
        'selesai',
        'status',
        'tanggal_daftar',
        'waktu_daftar',
        'tujuan',
        'biaya'
    ];

    // Hanya kolom tanggal/timestamp yang benar-benar tipe date/datetime di sini
    protected $dates = [
        'tanggal',
        'tanggal_daftar',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}