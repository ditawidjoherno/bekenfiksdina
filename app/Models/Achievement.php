<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    // Tambahkan 'ekskul_id' ke $fillable agar bisa diisi lewat create()
protected $fillable = ['date', 'championship', 'event', 'ekskul_id'];

    // Relasi: Achievement milik satu ekskul
    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class);
    }
}
