<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PameranGallery extends Model
{
    use HasFactory;

    protected $fillable = ['pameran_id', 'image_path'];

    public function pameran()
    {
        return $this->belongsTo(Pameran::class);
    }
}
