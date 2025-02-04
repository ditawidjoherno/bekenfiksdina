<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumentasi extends Model
{
    use HasFactory;

    protected $table = 'dokumentasi';

    protected $fillable = ['aktivitas_id', 'file_path'];

// App\Models\Dokumentasi.php
public function aktivitas()
{
    return $this->belongsTo(Aktivitas::class, 'aktivitas_id');
}

}
