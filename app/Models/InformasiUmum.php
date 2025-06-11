<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformasiUmum extends Model
{
    use HasFactory;

    protected $table = 'informasi_umum';

    protected $fillable = [
    'date',
    'title',
    'text',
    'author',
    'photo', // ✅ Tambahkan ini
    'time',
    'color',
    'user_id',
];
public function user()
{
    return $this->belongsTo(User::class);
}

}
