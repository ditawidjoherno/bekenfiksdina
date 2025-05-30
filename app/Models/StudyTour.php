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
        'title', // ✅ Tambahkan baris ini
        'selesai',
        'status',
        'tanggal_daftar',
        'waktu_daftar',
        'tujuan',
        'biaya'
    ];

    protected $dates = [
        'tanggal',
        'tanggal_daftar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
